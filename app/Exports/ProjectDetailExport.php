<?php

namespace App\Exports;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProjectDetailExport
{
    protected $project;

    public function __construct(Project $project)
    {
        // Fresh load from database with all relationships
        $this->project = Project::with([
            'client.user',
            'order.items.service.category',
            'teams.members.user',
            'expenses.createdBy'
        ])->find($project->id);
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        
        // Remove default sheet
        $spreadsheet->removeSheetByIndex(0);
        
        // Add sheets
        $this->addProjectInfoSheet($spreadsheet);
        $this->addExpensesSheet($spreadsheet);
        $this->addTeamSheet($spreadsheet);
        
        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);
        
        return $spreadsheet;
    }

    protected function addProjectInfoSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Project Info');
        
        $project = $this->project;
        $client = $project->client;
        $order = $project->order;
        
        // Header
        $sheet->setCellValue('A1', 'Field');
        $sheet->setCellValue('B1', 'Value');
        
        // Data
        $row = 2;
        $data = [
            ['Project Code', $project->project_code],
            ['Project Name', $project->project_name],
            ['Status', ucfirst($project->status)],
            ['Start Date', $project->start_date ? $project->start_date->format('d M Y') : '-'],
            ['Deadline', $project->end_date ? $project->end_date->format('d M Y') : '-'],
            ['Completed At', $project->completed_at ? $project->completed_at->format('d M Y') : '-'],
            ['Duration', $project->duration ?? '-'],
            ['Description', $project->description ?? '-'],
            ['Notes', $project->notes ?? '-'],
            ['', ''],
            ['CLIENT INFORMATION', ''],
        ];
        
        // Add client info - using same logic as ProjectPresenter
        $clientName = '-';
        $clientEmail = '-';
        $clientPhone = '-';
        $clientCompany = '-';
        
        if ($client) {
            // Use same logic as ProjectPresenter: check client fields first, then user fields
            $clientName = $client->name 
                ?? $client->user?->name 
                ?? $client->company_name 
                ?? '-';
            
            $clientEmail = $client->email 
                ?? $client->user?->email 
                ?? '-';
            
            $clientPhone = $client->phone ?? $client->contact_phone ?? '-';
            $clientCompany = $client->company_name ?? '-';
        }
        
        $data[] = ['Client Name', $clientName];
        $data[] = ['Email', $clientEmail];
        $data[] = ['Phone', $clientPhone];
        $data[] = ['Company', $clientCompany];
        
        $data[] = ['', ''];
        $data[] = ['ORDER/SERVICE INFORMATION', ''];
        
        // Add service items - manual load if needed
        $hasItems = false;
        
        if ($order) {
            // Try to get items from relationship
            $items = $order->items;
            
            // If no items loaded, try manual load
            if (!$items || count($items) == 0) {
                $items = \App\Models\OrderItem::where('order_id', $order->id)
                    ->with(['service', 'servicePackage'])
                    ->get();
            }
            
            if ($items && count($items) > 0) {
                foreach ($items as $item) {
                    // Get service name - use 'name' field not 'service_name'
                    $serviceName = $item->service?->name ?? '-';
                    
                    // Get package name - same logic as web view
                    $packageName = $item->servicePackage?->name ?? $item->package_name ?? 'Custom';
                    
                    $data[] = ['Layanan', $serviceName];
                    $data[] = ['Paket', $packageName];
                    $data[] = ['Quantity', $item->quantity ?? 1];
                    $data[] = ['Harga', 'Rp ' . number_format($item->subtotal ?? 0, 0, ',', '.')];
                    $data[] = ['', ''];
                    $hasItems = true;
                }
            }
        }
        
        if (!$hasItems) {
            $data[] = ['Layanan', '-'];
            $data[] = ['Paket', '-'];
            $data[] = ['Quantity', '-'];
            $data[] = ['Harga', '-'];
            $data[] = ['', ''];
        }
        
        // Payment info
        $data[] = ['PAYMENT INFORMATION', ''];
        $data[] = ['Total Amount', 'Rp ' . number_format($order ? $order->total_amount : 0, 0, ',', '.')];
        $data[] = ['Paid Amount', 'Rp ' . number_format($order ? $order->paid_amount : 0, 0, ',', '.')];
        
        // Payment Type - same logic as web view
        if ($order) {
            if ($order->payment_type === 'installment') {
                $paymentType = 'DP ' . $order->paid_installments . '/' . $order->installment_count;
                // Only show remaining amount for installment type
                $data[] = ['Remaining Amount', 'Rp ' . number_format($order->remaining_amount, 0, ',', '.')];
            } else {
                $paymentType = 'Lunas';
            }
        } else {
            $paymentType = '-';
        }
        $data[] = ['Payment Type', $paymentType];
        $data[] = ['Payment Status', ($order && $order->remaining_amount > 0) ? 'Belum Lunas' : 'Lunas'];
        
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item[0]);
            $sheet->setCellValue('B' . $row, $item[1]);
            $row++;
        }
        
        // Styling
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(50);
    }

    protected function addExpensesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Expenses');
        
        // Header
        $headers = ['Date', 'Category', 'Description', 'Amount', 'Status', 'Created By'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Data
        $row = 2;
        foreach ($this->project->expenses as $expense) {
            $sheet->setCellValue('A' . $row, $expense->expense_date->format('d M Y'));
            $sheet->setCellValue('B' . $row, $expense->expense_type);
            $sheet->setCellValue('C' . $row, $expense->description ?? '-');
            $sheet->setCellValue('D' . $row, 'Rp ' . number_format($expense->amount, 0, ',', '.'));
            $sheet->setCellValue('E' . $row, ucfirst($expense->approval_status ?? 'pending'));
            $sheet->setCellValue('F' . $row, $expense->createdBy->name ?? '-');
            $row++;
        }
        
        // Total row
        $sheet->setCellValue('C' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, 'Rp ' . number_format($this->project->expenses->sum('amount'), 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
        ]);
        
        // Styling
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function addTeamSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Team Members');
        
        // Header
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Role');
        $sheet->setCellValue('C1', 'Assigned Date');
        
        // Data
        $row = 2;
        foreach ($this->project->teams as $team) {
            // Team bisa punya banyak members via pivot table
            foreach ($team->members as $member) {
                $sheet->setCellValue('A' . $row, $member->user ? $member->user->name : '-');
                $sheet->setCellValue('B' . $row, $member->role ?? '-');
                $sheet->setCellValue('C' . $row, $member->assigned_at ? date('d M Y', strtotime($member->assigned_at)) : $team->created_at->format('d M Y'));
                $row++;
            }
        }
        
        // Styling
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function addTasksSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Tasks');
        
        // Header
        $headers = ['Task Name', 'Description', 'Status', 'Priority', 'Assignee', 'Due Date'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Data
        $row = 2;
        foreach ($this->project->tasks as $task) {
            $sheet->setCellValue('A' . $row, $task->task_name);
            $sheet->setCellValue('B' . $row, $task->description ?? '-');
            $sheet->setCellValue('C' . $row, ucfirst($task->status));
            $sheet->setCellValue('D' . $row, ucfirst($task->priority ?? '-'));
            $sheet->setCellValue('E' . $row, $task->assignee->name ?? '-');
            $sheet->setCellValue('F' . $row, $task->due_date ? $task->due_date->format('d M Y') : '-');
            $row++;
        }
        
        // Styling
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function addActivitiesSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Activity Timeline');
        
        // Header
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Action');
        $sheet->setCellValue('C1', 'Description');
        
        // Data
        $activities = \App\Models\ActivityLog::where('model', 'Project')
            ->where('model_id', $this->project->id)
            ->latest()
            ->take(50)
            ->get();
        
        $row = 2;
        foreach ($activities as $activity) {
            $sheet->setCellValue('A' . $row, $activity->created_at->format('d M Y H:i'));
            $sheet->setCellValue('B' . $row, $activity->action);
            $sheet->setCellValue('C' . $row, $activity->description);
            $row++;
        }
        
        // Styling
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
