<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ClassExpense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private const FINANCE_EXPENSE_CATEGORIES = ['trainer_honor', 'honor'];

    public function index(Request $request)
    {
        // Mark all expense notifications as read when finance opens this menu
        \App\Models\Notification::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereNull('read_at')
            ->where('type', 'expense_pending')
            ->update(['read_at' => now()]);

        $type = 'class'; // Alfa Bank hanya pakai Academy (Class Expenses)
        $status = $request->get('status', 'pending'); // Default 'pending'
        $period = $request->get('period', 'month_' . date('m'));
        $year = (int) $request->get('year', date('Y'));
        
        // Load Class Expenses
        $query = ClassExpense::with(['clas.kategori', 'clas.trainers', 'user', 'approvedBy'])
            ->whereIn('category', self::FINANCE_EXPENSE_CATEGORIES);
        
        // Filter by status
        $query->where('approval_status', $status);

        // Filter by period/year berdasarkan tanggal mulai kelas
        if ($period !== 'all' && preg_match('/^month_(\d{2})$/', $period, $m)) {
            $month = (int) $m[1];
            $startDate = now()->setYear($year)->setMonth($month)->startOfMonth()->toDateString();
            $endDate = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();
            $query->whereHas('clas', function ($clasQuery) use ($startDate, $endDate) {
                $clasQuery->whereBetween('start_date', [$startDate, $endDate]);
            });
        } else {
            $query->whereHas('clas', function ($clasQuery) use ($year) {
                $clasQuery->whereYear('start_date', $year);
            });
        }
        
        // Filter by class
        if ($request->filled('class')) {
            $query->where('clas_id', $request->class);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
            });
        }
        
        $expenses = $query->latest()->paginate(15)->withQueryString();
        $classes = \App\Models\Clas::orderBy('name')->get();
        $years = ClassExpense::query()
            ->join('clas', 'class_expenses.clas_id', '=', 'clas.id')
            ->whereIn('class_expenses.category', self::FINANCE_EXPENSE_CATEGORIES)
            ->selectRaw('YEAR(clas.start_date) as year')
            ->whereNotNull('clas.start_date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) date('Y')]);
        } elseif (!$years->contains($year)) {
            $years = $years->push($year)->unique()->sortDesc()->values();
        }
        
        return view('finance.expenses.index', compact('expenses', 'type', 'classes', 'status', 'period', 'year', 'years'));
    }
    
    /**
     * Approve Class Expense
     */
    public function approveClassExpense(ClassExpense $expense)
    {
        if (!in_array($expense->category, self::FINANCE_EXPENSE_CATEGORIES, true)) {
            return back()->with('error', 'Hanya expense kategori honor yang diproses di menu Finance.');
        }

        if ($expense->approval_status !== 'pending') {
            return back()->with('error', 'Expense ini sudah di' . $expense->approval_status);
        }
        
        $expense->update([
            'approval_status' => 'approved',
            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
        ]);

        if (in_array($expense->category, ['trainer_honor', 'honor']) && $expense->clas) {
            $expense->clas->syncTrainerHonorFromExpenses();
        }
        
        // Notify admin who created the expense
        if ($expense->user) {
            \App\Models\Notification::create([
                'user_id' => $expense->user->id,
                'type' => 'expense_approved',
                'title' => 'Expense Kelas Disetujui Finance',
                'message' => 'Expense ' . ($expense->description ?? 'lain-lain') . ' sebesar Rp ' . number_format($expense->amount, 0, ',', '.') . ' di kelas "' . $expense->clas->name . '" telah disetujui oleh Finance.',
                'data' => [
                    'expense_id' => $expense->id,
                    'expense_type' => 'class',
                    'class_id' => $expense->clas_id,
                    'class_name' => $expense->clas->name,
                    'amount' => $expense->amount,
                    'icon' => 'check-circle',
                    'action_url' => route('admin.classes.show', $expense->clas),
                ],
            ]);
        }
        
        return back()->with('success', 'Expense kelas berhasil diapprove dan admin dinotifikasi');
    }
    
    /**
     * Reject Class Expense
     */
    public function rejectClassExpense(Request $request, ClassExpense $expense)
    {
        if (!in_array($expense->category, self::FINANCE_EXPENSE_CATEGORIES, true)) {
            return back()->with('error', 'Hanya expense kategori honor yang diproses di menu Finance.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        if ($expense->approval_status !== 'pending') {
            return back()->with('error', 'Expense ini sudah di' . $expense->approval_status);
        }
        
        $expense->update([
            'approval_status' => 'rejected',
            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        if (in_array($expense->category, ['trainer_honor', 'honor']) && $expense->clas) {
            $expense->clas->syncTrainerHonorFromExpenses();
        }
        
        // Notify admin who created the expense
        if ($expense->user) {
            \App\Models\Notification::create([
                'user_id' => $expense->user->id,
                'type' => 'expense_rejected',
                'title' => 'Expense Kelas Ditolak Finance',
                'message' => 'Expense ' . ($expense->description ?? 'lain-lain') . ' sebesar Rp ' . number_format($expense->amount, 0, ',', '.') . ' di kelas "' . $expense->clas->name . '" ditolak oleh Finance. Alasan: ' . $validated['rejection_reason'],
                'data' => [
                    'expense_id' => $expense->id,
                    'expense_type' => 'class',
                    'class_id' => $expense->clas_id,
                    'class_name' => $expense->clas->name,
                    'amount' => $expense->amount,
                    'icon' => 'times-circle',
                    'rejection_reason' => $validated['rejection_reason'],
                    'action_url' => route('admin.classes.show', $expense->clas),
                ],
            ]);
        }
        
        return back()->with('success', 'Expense kelas berhasil direject dan admin dinotifikasi');
    }
}
