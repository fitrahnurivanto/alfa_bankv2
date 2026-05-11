<?php
/**
 * Clear All Classes Data
 * PERHATIAN: Script ini akan menghapus SEMUA data kelas!
 */

// Load Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Keamanan: hanya bisa dijalankan jika ada parameter konfirmasi
$confirm = $_GET['confirm'] ?? '';

if ($confirm !== 'yes') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Clear Classes Data</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #dc2626;
                margin-top: 0;
            }
            .warning {
                background: #fef2f2;
                border: 2px solid #dc2626;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .info {
                background: #eff6ff;
                border-left: 4px solid #3b82f6;
                padding: 15px;
                margin: 20px 0;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                border-radius: 5px;
                text-decoration: none;
                font-weight: 600;
                margin: 10px 5px;
            }
            .btn-danger {
                background: #dc2626;
                color: white;
            }
            .btn-secondary {
                background: #6b7280;
                color: white;
            }
            .btn-danger:hover {
                background: #b91c1c;
            }
            ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            code {
                background: #f3f4f6;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>⚠️ Clear All Classes Data</h1>
            
            <div class="warning">
                <strong>PERINGATAN!</strong><br>
                Script ini akan menghapus <strong>SEMUA data kelas</strong> dari database.<br>
                Data yang dihapus <strong>TIDAK DAPAT DIKEMBALIKAN!</strong>
            </div>

            <div class="info">
                <strong>Data yang akan dihapus:</strong>
                <ul>
                    <li>Semua data kelas (tabel <code>clas</code>)</li>
                    <li>Relasi trainer kelas (tabel <code>clas_trainer</code>)</li>
                    <li>Chat kelas (tabel <code>project_chats</code>)</li>
                    <li>Expense kelas (tabel <code>class_expenses</code>)</li>
                    <li>Notifikasi terkait kelas</li>
                </ul>
            </div>

            <?php
            try {
                // Hitung jumlah data
                $totalClasses = DB::table('clas')->count();
                $totalTrainerRelations = DB::table('clas_trainer')->count();
                $totalChats = DB::table('project_chats')->where('class_id', '!=', null)->count();
                $totalExpenses = DB::table('class_expenses')->count();
                
                echo '<div class="info">';
                echo '<strong>Jumlah Data Saat Ini:</strong><ul>';
                echo "<li>Kelas: <strong>$totalClasses</strong></li>";
                echo "<li>Relasi Trainer: <strong>$totalTrainerRelations</strong></li>";
                echo "<li>Chat Kelas: <strong>$totalChats</strong></li>";
                echo "<li>Expenses: <strong>$totalExpenses</strong></li>";
                echo '</ul></div>';
            } catch (Exception $e) {
                echo '<div class="warning">Error mengambil data: ' . $e->getMessage() . '</div>';
            }
            ?>

            <div style="margin-top: 30px;">
                <a href="?confirm=yes" class="button btn-danger" onclick="return confirm('Apakah Anda YAKIN ingin menghapus SEMUA data kelas? Tindakan ini tidak dapat dibatalkan!')">
                    🗑️ Ya, Hapus Semua Data Kelas
                </a>
                <a href="<?php echo $_SERVER['HTTP_REFERER'] ?? '/admin/classes'; ?>" class="button btn-secondary">
                    ← Batal
                </a>
            </div>

            <div class="info" style="margin-top: 30px;">
                <strong>💡 Tip:</strong><br>
                Setelah menghapus data, Anda bisa langsung mulai input data kelas baru dari menu Admin.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Jika konfirmasi = yes, lakukan penghapusan
try {
    DB::beginTransaction();
    
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Clear Classes - Processing</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .success {
                background: #f0fdf4;
                border-left: 4px solid #22c55e;
                padding: 15px;
                margin: 10px 0;
                border-radius: 5px;
            }
            .step {
                padding: 10px;
                margin: 5px 0;
                background: #f9fafb;
                border-radius: 5px;
            }
            h1 { color: #16a34a; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🗑️ Menghapus Data Kelas...</h1>";
    
    // 1. Hapus notifikasi terkait kelas
    echo "<div class='step'>1️⃣ Menghapus notifikasi kelas...</div>";
    $deletedNotifications = DB::table('notifications')
        ->whereRaw("JSON_EXTRACT(data, '$.class_id') IS NOT NULL")
        ->delete();
    echo "<div class='success'>✅ Berhasil menghapus $deletedNotifications notifikasi</div>";
    
    // 2. Hapus chat kelas
    echo "<div class='step'>2️⃣ Menghapus chat kelas...</div>";
    $deletedChats = DB::table('project_chats')->where('class_id', '!=', null)->delete();
    echo "<div class='success'>✅ Berhasil menghapus $deletedChats chat</div>";
    
    // 3. Hapus expenses kelas
    echo "<div class='step'>3️⃣ Menghapus class expenses...</div>";
    $deletedExpenses = DB::table('class_expenses')->delete();
    echo "<div class='success'>✅ Berhasil menghapus $deletedExpenses expenses</div>";
    
    // 4. Hapus relasi trainer
    echo "<div class='step'>4️⃣ Menghapus relasi trainer...</div>";
    $deletedTrainerRelations = DB::table('clas_trainer')->delete();
    echo "<div class='success'>✅ Berhasil menghapus $deletedTrainerRelations relasi trainer</div>";
    
    // 5. Hapus kelas
    echo "<div class='step'>5️⃣ Menghapus data kelas...</div>";
    $deletedClasses = DB::table('clas')->delete();
    echo "<div class='success'>✅ Berhasil menghapus $deletedClasses kelas</div>";
    
    // 6. Reset auto increment
    echo "<div class='step'>6️⃣ Reset auto increment ID...</div>";
    DB::statement('ALTER TABLE clas AUTO_INCREMENT = 1');
    DB::statement('ALTER TABLE clas_trainer AUTO_INCREMENT = 1');
    DB::statement('ALTER TABLE class_expenses AUTO_INCREMENT = 1');
    DB::statement('ALTER TABLE project_chats AUTO_INCREMENT = 1');
    echo "<div class='success'>✅ Auto increment direset ke 1</div>";
    
    DB::commit();
    
    echo "
            <div class='success' style='margin-top: 30px; padding: 20px;'>
                <h2 style='margin-top: 0; color: #16a34a;'>✅ Berhasil!</h2>
                <p>Semua data kelas telah dihapus. Database siap untuk input data baru.</p>
                <p><strong>Total dihapus:</strong></p>
                <ul>
                    <li>Kelas: $deletedClasses</li>
                    <li>Relasi Trainer: $deletedTrainerRelations</li>
                    <li>Chat: $deletedChats</li>
                    <li>Expenses: $deletedExpenses</li>
                    <li>Notifikasi: $deletedNotifications</li>
                </ul>
            </div>
            
            <div style='margin-top: 20px;'>
                <a href='/admin/classes/create' style='display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;'>
                    ➕ Tambah Kelas Baru
                </a>
                <a href='/admin/classes' style='display: inline-block; padding: 12px 24px; background: #6b7280; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; margin-left: 10px;'>
                    📋 Lihat Daftar Kelas
                </a>
            </div>
            
            <div style='margin-top: 30px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 5px;'>
                <strong>⚠️ Jangan lupa:</strong> Hapus file ini setelah selesai!<br>
                Hapus file: <code>public/clear_classes.php</code>
            </div>
        </div>
    </body>
    </html>";
    
} catch (Exception $e) {
    DB::rollBack();
    
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Clear Classes - Error</title>
        <style>
            body {
                font-family: sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
            }
            .error {
                background: #fef2f2;
                border: 2px solid #dc2626;
                padding: 20px;
                border-radius: 10px;
                color: #dc2626;
            }
        </style>
    </head>
    <body>
        <div class='error'>
            <h2>❌ Error!</h2>
            <p>" . $e->getMessage() . "</p>
            <p><a href='?'>← Kembali</a></p>
        </div>
    </body>
    </html>";
}
