<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ClasController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InventarisController;

// Public routes - Redirect to Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware(['guest', \App\Http\Middleware\NoCacheHeaders::class])->group(function () {
    Route::get('/csrf-token', function (\Illuminate\Http\Request $request) {
        $request->session()->regenerateToken();

        return response()->json([
            'token' => csrf_token(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    })->name('csrf.token');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Forgot Password Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth', \App\Http\Middleware\NoCacheHeaders::class])->name('logout');
// Fallback for accidental GET /logout requests (e.g. old links/bookmarks)
Route::get('/logout', [AuthController::class, 'logout'])->middleware(['auth', \App\Http\Middleware\NoCacheHeaders::class]);

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard routes - role based
    Route::get('/dashboard', function() {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user instanceof \App\Models\User) {
            abort(403, 'User tidak valid.');
        }
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'marketing') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'akademik') {
            return redirect()->route('akademik.dashboard');
        } elseif ($user->role === 'finance') {
            return redirect()->route('finance.dashboard');
        } elseif ($user->role === 'trainer') {
            return redirect()->route('trainer.dashboard');
        } elseif ($user->role === 'client') {
            return redirect()->route('client.dashboard');
        } else {
            return redirect()->route('admin.dashboard');
        }
    })->name('dashboard');

    // Admin routes (Training Management)
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/dashboard/akademik', [\App\Http\Controllers\Akademik\DashboardController::class, 'index'])->name('dashboard.akademik');
        Route::get('/dashboard/finance', [\App\Http\Controllers\Finance\FinanceDashboardController::class, 'index'])->name('dashboard.finance');
        Route::get('/dashboard/filter-data', [DashboardController::class, 'getFilteredData'])->name('dashboard.filter-data');
        Route::post('/dashboard/save-target', [DashboardController::class, 'saveTarget'])->name('dashboard.save-target');
        Route::get('/dashboard/calendar-events', [DashboardController::class, 'getCalendarEvents'])->name('dashboard.calendar-events');
        
        // Clients management (Peserta) - DISEMBUNYIKAN SEMENTARA
        /*
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        */

        // Classes management (Kelas)
        Route::get('/classes', [ClasController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [ClasController::class, 'create'])->name('classes.create');
        Route::get('/classes/approved', [ClasController::class, 'showclas'])->name('classes.showclas');
        Route::post('/classes', [ClasController::class, 'store'])->name('classes.store');
        Route::get('/classes/{clas}', [ClasController::class, 'show'])->name('classes.show');
        Route::get('/classes/{clas}/edit', [ClasController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{clas}', [ClasController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{clas}', [ClasController::class, 'destroy'])->name('classes.destroy');
        Route::post('/classes/{clas}/approve', [ClasController::class, 'approve'])->name('classes.approve');
        Route::post('/classes/{clas}/reject', [ClasController::class, 'reject'])->name('classes.reject');
        Route::post('/classes/{clas}/mark-as-done', [ClasController::class, 'markAsDone'])->name('classes.mark-as-done');
        Route::patch('/classes/{clas}/update-revenue', [ClasController::class, 'updateRevenue'])->name('classes.update-revenue');
        Route::patch('/classes/{clas}/update-payment', [ClasController::class, 'updatePayment'])->name('classes.update-payment');
            Route::patch('/classes/{clas}/update-schedule', [ClasController::class, 'updateSchedule'])->name('classes.update-schedule');
        Route::post('/classes/{clas}/grade-files/{gradeFile}/review', [ClasController::class, 'reviewGradeFile'])->name('classes.review-grade-file');
        Route::get('/classes/{clas}/grade-files/{gradeFile}/download', [ClasController::class, 'downloadGradeFile'])->name('classes.download-grade-file');
        Route::post('/classes/{clas}/graduation-summary', [ClasController::class, 'updateGraduationSummary'])->name('classes.update-graduation-summary');

        // Class Expenses (Biaya tambahan kelas)
        Route::post('/classes/{class}/expenses', [\App\Http\Controllers\Admin\ClassExpenseController::class, 'store'])->name('classes.expenses.store');
        Route::get('/class-expenses/{expense}/edit', [\App\Http\Controllers\Admin\ClassExpenseController::class, 'edit'])->name('class-expenses.edit');
        Route::put('/class-expenses/{expense}', [\App\Http\Controllers\Admin\ClassExpenseController::class, 'update'])->name('class-expenses.update');
        Route::delete('/class-expenses/{expense}', [\App\Http\Controllers\Admin\ClassExpenseController::class, 'destroy'])->name('class-expenses.destroy');

        // Tracking Kelas
        Route::get('/tracking', [ClasController::class, 'track'])->name('tracking.index');
        
        // Trainer Management (Pengajar)
        Route::resource('trainers', \App\Http\Controllers\Admin\TrainerController::class);

        // Trainings (Program Pelatihan)
        Route::resource('trainings', \App\Http\Controllers\Admin\TrainingController::class);
        Route::get('/api/trainings/by-category', [\App\Http\Controllers\Admin\TrainingController::class, 'getByCategory'])->name('api.trainings.by-category');
        Route::post('/api/trainings/quick-create', [\App\Http\Controllers\Admin\TrainingController::class, 'quickCreate'])->name('api.trainings.quick-create');

        // Laporan (Reports)
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'exportCsv'])->name('laporan.export');

        // Absensi Pengajar Monitoring (Admin + Akademik)
        Route::get('/trainer-attendance', [LaporanController::class, 'trainerAttendanceIndex'])->name('trainer-attendance.index');
        Route::get('/trainer-attendance/export-excel', [LaporanController::class, 'exportTrainerAttendanceExcel'])->name('trainer-attendance.export-excel');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Expense maintenance (browser trigger for expense normalization)
        Route::get('/expense-maintenance', [\App\Http\Controllers\Admin\ExpenseMaintenanceController::class, 'index'])
            ->name('expense-maintenance.index');
        Route::post('/expense-maintenance/run', [\App\Http\Controllers\Admin\ExpenseMaintenanceController::class, 'run'])
            ->name('expense-maintenance.run');

        // Payment Requests (Admin - approve/reject)
        Route::resource('payment-requests', \App\Http\Controllers\Admin\PaymentRequestController::class)
            ->only(['index', 'show', 'update']);

        // User Management (Admin only)
        Route::resource('users', \App\Http\Controllers\Admin\UserManagementController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });

    // Inventaris Barang Management (Admin & Finance)
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('inventaris', InventarisController::class)
            ->parameter('inventaris', 'inventaris');
        Route::get('/inventaris/{inventaris}/edit-posisi', [InventarisController::class, 'editPosisi'])->name('inventaris.edit-posisi');
        Route::patch('/inventaris/{inventaris}/update-posisi', [InventarisController::class, 'updatePosisi'])->name('inventaris.update-posisi');
        Route::get('/inventaris/{inventaris}/edit-kondisi', [InventarisController::class, 'editKondisi'])->name('inventaris.edit-kondisi');
        Route::patch('/inventaris/{inventaris}/update-kondisi', [InventarisController::class, 'updateKondisi'])->name('inventaris.update-kondisi');
        Route::post('/inventaris/{inventaris}/upload-photo', [InventarisController::class, 'uploadPhoto'])->name('inventaris.upload-photo');
        Route::delete('/inventaris-photos/{photo}', [InventarisController::class, 'deletePhoto'])->name('inventaris-photos.destroy');
        Route::get('/inventaris/export/pdf', [InventarisController::class, 'exportPdf'])->name('inventaris.exportPdf');
        Route::get('/inventaris/export/excel', [InventarisController::class, 'exportExcel'])->name('inventaris.exportExcel');
    });

    // Akademik routes
    Route::middleware(['akademik'])->prefix('akademik')->name('akademik.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Akademik\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/classes', [\App\Http\Controllers\Akademik\ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{class}', [\App\Http\Controllers\Akademik\ClassController::class, 'show'])->name('classes.show');
        Route::get('/classes/{clas}/edit', [ClasController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{clas}', [ClasController::class, 'update'])->name('classes.update');
        Route::patch('/classes/{clas}/update-schedule', [ClasController::class, 'updateSchedule'])->name('classes.update-schedule');
        Route::post('/classes/{class}/graduation-summary', [\App\Http\Controllers\Akademik\ClassController::class, 'updateGraduationSummary'])->name('classes.update-graduation-summary');
    });

    // Trainer routes
    Route::middleware(['trainer'])->prefix('trainer')->name('trainer.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Trainer\DashboardController::class, 'index'])
            ->name('dashboard');
        
        // Classes (Kelas Saya)
        Route::get('/classes', [\App\Http\Controllers\Trainer\ClassController::class, 'index'])
            ->name('classes.index');
        Route::get('/classes/{class}', [\App\Http\Controllers\Trainer\ClassController::class, 'show'])
            ->name('classes.show');
        Route::post('/classes/{class}/grade-file/upload', [\App\Http\Controllers\Trainer\ClassController::class, 'uploadGradeFile'])
            ->name('classes.upload-grade-file');
        Route::get('/classes/{class}/grade-files/{gradeFile}/download', [\App\Http\Controllers\Trainer\ClassController::class, 'downloadGradeFile'])
            ->name('classes.download-grade-file');
        Route::post('/classes/{class}/graduation-summary', [\App\Http\Controllers\Trainer\ClassController::class, 'updateGraduationSummary'])
            ->name('classes.update-graduation-summary');

        // Trainer attendance (absen pengajar)
        Route::get('/attendance', [\App\Http\Controllers\Trainer\AttendanceController::class, 'index'])
            ->name('attendance.index');
        Route::post('/attendance/check-in', [\App\Http\Controllers\Trainer\AttendanceController::class, 'checkIn'])
            ->name('attendance.check-in');
        Route::post('/attendance/check-out', [\App\Http\Controllers\Trainer\AttendanceController::class, 'checkOut'])
            ->name('attendance.check-out');
        Route::post('/attendance/add-session', [\App\Http\Controllers\Trainer\AttendanceController::class, 'addSession'])
            ->name('attendance.add-session');
        
        // Payment Requests
        Route::resource('payment-requests', \App\Http\Controllers\Trainer\PaymentRequestController::class)
            ->only(['index', 'create', 'store', 'show']);
        Route::get('/payment-requests/{paymentRequest}/proof/download', [\App\Http\Controllers\Trainer\PaymentRequestController::class, 'downloadProof'])
            ->name('payment-requests.download-proof');
    });

    // Client routes (Peserta) - DISEMBUNYIKAN SEMENTARA
    /*
    Route::middleware(['client'])->prefix('client')->name('client.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'clientDashboard'])->name('dashboard');
        
        // Classes (Kelas Saya)
        Route::get('/classes', [\App\Http\Controllers\Client\ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{class}', [\App\Http\Controllers\Client\ClassController::class, 'show'])->name('classes.show');
    });
    */

    // Finance routes
    Route::middleware(['finance'])->prefix('finance')->name('finance.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Finance\FinanceDashboardController::class, 'index'])->name('dashboard');
        
        // Class Expense Management (Biaya Kelas)
        Route::get('/expenses', [\App\Http\Controllers\Finance\ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/class-expenses/{expense}/approve', [\App\Http\Controllers\Finance\ExpenseController::class, 'approveClassExpense'])->name('class-expenses.approve');
        Route::post('/class-expenses/{expense}/reject', [\App\Http\Controllers\Finance\ExpenseController::class, 'rejectClassExpense'])->name('class-expenses.reject');
        
        // Payment Requests (Finance)
        Route::get('/payment-requests', [\App\Http\Controllers\Finance\PaymentRequestController::class, 'index'])->name('payment-requests.index');
        Route::get('/payment-requests/{paymentRequest}', [\App\Http\Controllers\Finance\PaymentRequestController::class, 'show'])->name('payment-requests.show');
        Route::get('/payment-requests/{paymentRequest}/proof/download', [\App\Http\Controllers\Finance\PaymentRequestController::class, 'downloadProof'])->name('payment-requests.download-proof');
        Route::post('/payment-requests/{paymentRequest}/approve', [\App\Http\Controllers\Finance\PaymentRequestController::class, 'approve'])->name('payment-requests.approve');
        Route::post('/payment-requests/{paymentRequest}/reject', [\App\Http\Controllers\Finance\PaymentRequestController::class, 'reject'])->name('payment-requests.reject');
        Route::post('/payment-requests/{paymentRequest}/mark-as-paid', [\App\Http\Controllers\Finance\PaymentRequestController::class, 'markAsPaid'])->name('payment-requests.mark-as-paid');
    });
});

// Notification routes (for all authenticated users)
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
});
