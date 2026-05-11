<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ExpenseMaintenanceController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user || !in_array($user->role, ['admin', 'superadmin'], true)) {
            abort(403, 'Hanya admin/superadmin yang dapat mengakses halaman ini.');
        }

        return view('admin.expense-maintenance.index');
    }

    public function run(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user || !in_array($user->role, ['admin', 'superadmin'], true)) {
            abort(403, 'Hanya admin/superadmin yang dapat menjalankan proses ini.');
        }

        $exitCode = Artisan::call('expenses:normalize', [
            '--force' => true,
        ]);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return redirect()
                ->route('admin.expense-maintenance.index')
                ->with('error', 'Proses normalisasi gagal dijalankan.')
                ->with('command_output', $output);
        }

        return redirect()
            ->route('admin.expense-maintenance.index')
            ->with('success', 'Normalisasi selesai. Expense non-honor berhasil di-approve otomatis.')
            ->with('command_output', $output);
    }
}
