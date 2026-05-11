<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassExpense;
use App\Models\Clas;
use Illuminate\Http\Request;

class ClassExpenseController extends Controller
{
    /**
     * Store a newly created expense for a class
     */
    public function store(Request $request, Clas $class)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user instanceof \App\Models\User) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user || !in_array($user->role, ['admin', 'superadmin', 'marketing', 'akademik'])) {
            abort(403, 'Tidak memiliki akses menambahkan expense kelas.');
        }

        $allowedCategories = [
            'honor',
            'transport',
            'meal',
            'accommodation',
            'equipment',
            'marketing',
            'venue_rent',
            'electricity',
            'goodie_bag',
            'other',
            // backward compatibility
            'operational_cost',
            'trainer_honor',
            'lain-lain',
        ];

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|in:' . implode(',', $allowedCategories),
        ]);

        // Marketing hanya boleh input biaya operasional/non-honor.
        if ($user->role === 'marketing' && in_array($validated['category'], ['trainer_honor', 'honor'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Marketing hanya dapat menginput biaya operasional (transport, tempat, konsumsi, dll), bukan honor trainer.']);
        }

        // Akademik hanya boleh input honor trainer.
        if ($user->role === 'akademik' && !in_array($validated['category'], ['trainer_honor', 'honor'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Akademik hanya dapat menginput honor trainer untuk payment request.']);
        }

        $validated['clas_id'] = $class->id;
        $validated['user_id'] = \Illuminate\Support\Facades\Auth::id();

        $isHonorExpense = in_array($validated['category'], ['trainer_honor', 'honor']);
        if ($isHonorExpense) {
            $validated['approval_status'] = 'pending';
        } else {
            // Biaya non-honor tidak perlu approval finance.
            $validated['approval_status'] = 'approved';
            $validated['approved_by'] = \Illuminate\Support\Facades\Auth::id();
            $validated['approved_at'] = now();
        }

        $expense = ClassExpense::create($validated);

        if ($isHonorExpense) {
            $class->syncTrainerHonorFromExpenses();
        }

        if ($isHonorExpense) {
            // Notify finance only for honor expenses.
            $financeUsers = \App\Models\User::where('role', 'finance')->get();
            foreach ($financeUsers as $financeUser) {
                \App\Models\Notification::create([
                    'user_id' => $financeUser->id,
                    'type' => 'expense_pending',
                    'title' => 'Expense Kelas Baru Perlu Validasi',
                    'message' => 'Expense baru "' . $validated['description'] . '" sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' di kelas "' . $class->name . '" menunggu validasi Finance.',
                    'data' => [
                        'expense_id' => $expense->id,
                        'expense_type' => 'class',
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'description' => $validated['description'],
                        'amount' => $validated['amount'],
                        'icon' => 'receipt',
                        'action_url' => route('finance.expenses.index'),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Expense honor berhasil ditambahkan dan menunggu validasi Finance.');
        }

        return redirect()->back()->with('success', 'Expense non-honor berhasil ditambahkan dan langsung disetujui tanpa validasi Finance.');
    }

    /**
     * Remove the specified expense
     */
    public function destroy(ClassExpense $expense)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user instanceof \App\Models\User) {
            abort(403, 'Unauthorized access.');
        }

        $isHonor = in_array($expense->category, ['trainer_honor', 'honor']);

        // Admin/superadmin boleh hapus semua. Marketing hanya non-honor. Akademik hanya honor.
        if (!$user->isAdmin()) {
            if ($user->role === 'marketing' && $isHonor) {
                abort(403, 'Marketing tidak dapat menghapus expense honor trainer.');
            }

            if ($user->role === 'akademik' && !$isHonor) {
                abort(403, 'Akademik hanya dapat menghapus expense honor trainer.');
            }

            if (!in_array($user->role, ['marketing', 'akademik'])) {
                abort(403, 'Tidak memiliki akses menghapus expense kelas.');
            }
        }

        // Honor expenses hanya bisa dihapus jika pending. Non-honor bisa dihapus kapan saja.
        if ($isHonor && $expense->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Honor expense yang sudah diapprove tidak dapat dihapus.');
        }

        $clas = $expense->clas;
        $isHonorExpense = in_array($expense->category, ['trainer_honor', 'honor']);

        $expense->delete();

        if ($isHonorExpense && $clas) {
            $clas->syncTrainerHonorFromExpenses();
        }

        return redirect()->back()->with('success', 'Expense berhasil dihapus.');
    }

    /**
     * Show edit form for expense
     */
    public function edit(ClassExpense $expense)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user instanceof \App\Models\User) {
            abort(403, 'Unauthorized access.');
        }

        $isHonor = in_array($expense->category, ['trainer_honor', 'honor']);

        // Honor expenses hanya bisa diedit jika pending
        if ($isHonor && $expense->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Honor expense yang sudah diapprove tidak dapat diedit.');
        }

        // Admin/superadmin boleh edit semua. Marketing hanya non-honor. Akademik hanya honor.
        if (!$user->isAdmin()) {
            if ($user->role === 'marketing' && $isHonor) {
                abort(403, 'Marketing tidak dapat mengedit expense honor trainer.');
            }

            if ($user->role === 'akademik' && !$isHonor) {
                abort(403, 'Akademik hanya dapat mengedit expense honor trainer.');
            }

            if (!in_array($user->role, ['marketing', 'akademik'])) {
                abort(403, 'Tidak memiliki akses mengedit expense kelas.');
            }
        }

        $allowedCategories = [
            'honor' => 'Honor',
            'transport' => 'Transport',
            'meal' => 'Makanan/Minuman',
            'accommodation' => 'Akomodasi',
            'equipment' => 'Peralatan',
            'marketing' => 'Marketing',
            'venue_rent' => 'Sewa Tempat',
            'electricity' => 'Listrik',
            'goodie_bag' => 'Goodie Bag',
            'other' => 'Lainnya',
            'operational_cost' => 'Biaya Operasional',
            'trainer_honor' => 'Honor Trainer',
            'lain-lain' => 'Lain-lain',
        ];

        return view('admin.classes.expenses.edit', compact('expense', 'allowedCategories'));
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, ClassExpense $expense)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user instanceof \App\Models\User) {
            abort(403, 'Unauthorized access.');
        }

        $isHonor = in_array($expense->category, ['trainer_honor', 'honor']);

        // Honor expenses hanya bisa diedit jika pending
        if ($isHonor && $expense->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Honor expense yang sudah diapprove tidak dapat diedit.');
        }

        // Admin/superadmin boleh edit semua. Marketing hanya non-honor. Akademik hanya honor.
        if (!$user->isAdmin()) {
            if ($user->role === 'marketing' && $isHonor) {
                abort(403, 'Marketing tidak dapat mengedit expense honor trainer.');
            }

            if ($user->role === 'akademik' && !$isHonor) {
                abort(403, 'Akademik hanya dapat mengedit expense honor trainer.');
            }

            if (!in_array($user->role, ['marketing', 'akademik'])) {
                abort(403, 'Tidak memiliki akses mengedit expense kelas.');
            }
        }

        $allowedCategories = [
            'honor',
            'transport',
            'meal',
            'accommodation',
            'equipment',
            'marketing',
            'venue_rent',
            'electricity',
            'goodie_bag',
            'other',
            'operational_cost',
            'trainer_honor',
            'lain-lain',
        ];

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|in:' . implode(',', $allowedCategories),
        ]);

        // Marketing hanya boleh edit biaya operasional/non-honor.
        if ($user->role === 'marketing' && in_array($validated['category'], ['trainer_honor', 'honor'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Marketing hanya dapat mengedit biaya operasional (transport, tempat, konsumsi, dll), bukan honor trainer.']);
        }

        // Akademik hanya boleh edit honor trainer.
        if ($user->role === 'akademik' && !in_array($validated['category'], ['trainer_honor', 'honor'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Akademik hanya dapat mengedit honor trainer untuk payment request.']);
        }

        $expense->update($validated);

        // Resync trainer honor if it's an honor expense
        $isHonorExpense = in_array($validated['category'], ['trainer_honor', 'honor']);
        if ($isHonorExpense && $expense->clas) {
            $expense->clas->syncTrainerHonorFromExpenses();
        }

        return redirect()->route('admin.classes.show', $expense->clas_id)
            ->with('success', 'Expense berhasil diupdate.');
    }
}
