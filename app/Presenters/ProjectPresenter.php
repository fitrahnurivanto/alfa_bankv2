<?php

namespace App\Presenters;

use App\Models\Project;

class ProjectPresenter
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get client name with safe null handling
     */
    public function getClientName(): string
    {
        if (!$this->project->client) {
            return 'N/A';
        }

        return $this->project->client->name 
            ?? $this->project->client->user?->name 
            ?? $this->project->client->company_name 
            ?? 'N/A';
    }

    /**
     * Get client email with safe null handling
     */
    public function getClientEmail(): string
    {
        if (!$this->project->client) {
            return '';
        }

        return $this->project->client->email 
            ?? $this->project->client->user?->email 
            ?? '';
    }

    /**
     * Get client phone with safe null handling
     */
    public function getClientPhone(): string
    {
        return $this->project->client?->phone ?? '';
    }

    /**
     * Get clean phone number in international format (62xxx)
     */
    public function getCleanPhone(): string
    {
        $phone = $this->getClientPhone();
        if (!$phone) {
            return '';
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        return $cleanPhone;
    }

    /**
     * Get WhatsApp link for client
     */
    public function getWhatsAppLink(): string
    {
        $phone = $this->getCleanPhone();
        return $phone ? "https://wa.me/{$phone}" : '#';
    }

    /**
     * Get payment request WhatsApp message
     */
    public function getPaymentRequestMessage(): string
    {
        if (!$this->project->order) {
            return '';
        }

        $clientName = $this->getClientName();
        $projectName = $this->project->project_name;
        $projectCode = $this->project->project_code;
        $totalAmount = 'Rp ' . number_format($this->project->order->total_amount, 0, ',', '.');
        $paidAmount = 'Rp ' . number_format($this->project->order->total_amount - $this->project->order->remaining_amount, 0, ',', '.');
        $remainingAmount = 'Rp ' . number_format($this->project->order->remaining_amount, 0, ',', '.');

        return 
            "Halo Kak *{$clientName}*\n\n" .
            "Kabar baik dari kami! Project Anda sudah hampir selesai\n\n" .
            "*DETAIL PROJECT*\n" .
            "Project: *{$projectName}*\n" .
            "Kode: {$projectCode}\n\n" .
            "----------------------------\n" .
            "*INFORMASI PEMBAYARAN*\n" .
            "Total Project: {$totalAmount}\n" .
            "Sudah Dibayar: {$paidAmount}\n" .
            "*Sisa Belum Lunas: {$remainingAmount}*\n" .
            "----------------------------\n\n" .
            "Untuk kelancaran proses penyelesaian, mohon bantuannya untuk segera melunasi sisa pembayaran ya Kak\n\n" .
            "_Dengan pelunasan tepat waktu, kami bisa langsung finalisasi project Anda!_\n\n" .
            "Ada pertanyaan atau butuh bantuan? Jangan ragu untuk hubungi kami ya!\n\n" .
            "Terima kasih atas kepercayaan dan kerjasamanya!";
    }

    /**
     * Get client initials for avatar
     */
    public function getClientInitials(): string
    {
        $name = $this->getClientName();
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get expense category badge classes
     */
    public function getExpenseCategoryBadge(?string $expenseType): array
    {
        $colors = config('project.expense_colors');
        
        // Handle null category
        if ($expenseType === null || trim($expenseType) === '') {
            return $colors['other'] ?? [
                'bg' => 'bg-gray-100',
                'text' => 'text-gray-800',
                'border' => 'border-gray-200'
            ];
        }
        
        $type = strtolower($expenseType);
        
        if (str_contains($type, 'honor') || str_contains($type, 'gaji')) {
            return $colors['honor'];
        } elseif (str_contains($type, 'tool') || str_contains($type, 'software')) {
            return $colors['tools'];
        } elseif (str_contains($type, 'ads') || str_contains($type, 'iklan')) {
            return $colors['advertising'];
        } elseif (str_contains($type, 'freelancer')) {
            return $colors['freelancer'];
        } elseif (str_contains($type, 'operasional')) {
            return $colors['operational'];
        } elseif (str_contains($type, 'material')) {
            return $colors['material'];
        }
        
        return $colors['other'];
    }

    /**
     * Get budget usage color class
     */
    public function getBudgetColorClass(float $percentage): string
    {
        $thresholds = config('project.budget_thresholds');
        
        if ($percentage < $thresholds['safe']) {
            return 'text-green-600';
        } elseif ($percentage < $thresholds['warning']) {
            return 'text-yellow-600';
        } elseif ($percentage < $thresholds['danger']) {
            return 'text-orange-600';
        }
        
        return 'text-red-600';
    }

    /**
     * Get budget progress bar color
     */
    public function getBudgetProgressBarColor(float $percentage): string
    {
        $thresholds = config('project.budget_thresholds');
        
        if ($percentage < $thresholds['safe']) {
            return 'bg-green-500';
        } elseif ($percentage < $thresholds['warning']) {
            return 'bg-yellow-500';
        } elseif ($percentage < $thresholds['danger']) {
            return 'bg-orange-500';
        }
        
        return 'bg-red-500';
    }

    /**
     * Check if project has remaining payment
     */
    public function hasRemainingPayment(): bool
    {
        return $this->project->order && $this->project->order->remaining_amount > 0;
    }

    /**
     * Check if project is active
     */
    public function isActive(): bool
    {
        return !in_array($this->project->status, ['completed', 'cancelled']);
    }

    /**
     * Format currency
     */
    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeClass(): string
    {
        $statusConfig = config('project.statuses');
        $color = $statusConfig[$this->project->status]['color'] ?? 'gray';
        
        return "bg-{$color}-100 text-{$color}-800";
    }

    /**
     * Get user initials
     */
    public function getUserInitials(string $name): string
    {
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get WhatsApp message for Project Created notification
     */
    public function getProjectCreatedMessage(): string
    {
        $clientName = $this->getClientName();
        $projectName = $this->project->project_name;
        $projectCode = $this->project->project_code;
        $deadline = $this->project->deadline ? $this->project->deadline->format('d M Y') : 'Belum ditentukan';
        $employees = $this->project->team->employees->pluck('name')->join(', ') ?: 'Belum ditentukan';

        $status = match($this->project->status) {
            'pending' => 'Menunggu',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'on_hold' => 'Ditunda',
            default => ucfirst($this->project->status),
        };

        $message = "🎯 *PROJECT BARU TELAH DIBUAT*\n\n";
        $message .= "Halo *{$clientName}*,\n\n";
        $message .= "Project baru telah dibuat untuk Anda.\n\n";
        $message .= "📋 *Detail Project:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Nama: *{$projectName}*\n";
        $message .= "Kode: {$projectCode}\n";
        $message .= "Status: {$status}\n";
        $message .= "Deadline: *{$deadline}*\n";
        $message .= "Tim: {$employees}\n\n";

        if ($this->project->description) {
            $message .= "📝 *Deskripsi:*\n";
            $message .= $this->project->description . "\n\n";
        }

        if ($this->project->order) {
            $totalAmount = 'Rp ' . number_format($this->project->order->total_amount, 0, ',', '.');
            $message .= "💰 *Total Nilai:* {$totalAmount}\n\n";
        }

        $message .= "Tim kami akan segera memulai pengerjaan project Anda.\n\n";
        $message .= "🚀 Terima kasih atas kepercayaan Anda!";

        return $message;
    }
}
