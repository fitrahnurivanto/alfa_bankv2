<?php
/**
 * Recalculate Class Income for Existing Data
 * Script ini untuk memperbaiki data kelas yang sudah ada yang masih menggunakan perhitungan lama
 * (price × amount untuk reguler/privatekan sekarang price sudah total pendapatan
 */

// Load Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Clas;

$confirm = $_GET['confirm'] ?? '';

if ($confirm !== 'yes') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Recalculate Class Income</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 800px;
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
                color: #2563eb;
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
            .btn-primary {
                background: #2563eb;
                color: white;
            }
            .btn-secondary {
                background: #6b7280;
                color: white;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
            }
            th {
                background: #f3f4f6;
                font-weight: 600;
            }
            .old-value {
                color: #dc2626;
                text-decoration: line-through;
            }
            .new-value {
                color: #16a34a;
                font-weight: 600;
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
            <h1>🔧 Recalculate Class Income</h1>
            
            <div class="info">
                <strong>Perubahan perhitungan:</strong><br>
                Field <code>price</code> berubah dari <strong>"Harga per Siswa"</strong> menjadi <strong>"Pendapatan/Nilai Kelas"</strong> (sudah total).<br><br>
                Data lama yang memiliki perhitungan <code>income = price × amount - cost - honor</code> perlu diperbaiki menjadi <code>income = price - cost - honor</code>
            </div>

            <?php
            try {
                // Ambil semua kelas
                $classes = DB::table('clas')->get();
                
                $needRecalculation = [];
                
                foreach ($classes as $class) {
                    // Hitung ulang
                    $newIncome = $class->price - $class->cost - ($class->trainer_honor ?? 0);
                    
                    // Jika berbeda, berarti perlu recalculate
                    if (abs($class->income - $newIncome) > 0.01) {
                        $needRecalculation[] = [
                            'id' => $class->id,
                            'name' => $class->name,
                            'price' => $class->price,
                            'amount' => $class->amount,
                            'cost' => $class->cost,
                            'trainer_honor' => $class->trainer_honor ?? 0,
                            'old_income' => $class->income,
                            'new_income' => $newIncome,
                            'difference' => $newIncome - $class->income
                        ];
                    }
                }
                
                if (empty($needRecalculation)) {
                    echo '<div class="info">';
                    echo '<strong>✅ Semua data sudah benar!</strong><br>';
                    echo 'Tidak ada kelas yang perlu di-recalculate.';
                    echo '</div>';
                } else {
                    echo '<div class="warning">';
                    echo '<strong>⚠️ Ditemukan ' . count($needRecalculation) . ' kelas yang perlu diperbaiki:</strong>';
                    echo '</div>';
                    
                    echo '<table>';
                    echo '<thead><tr>';
                    echo '<th>Nama Kelas</th>';
                    echo '<th>Price</th>';
                    echo '<th>Siswa</th>';
                    echo '<th>Income Lama</th>';
                    echo '<th>Income Baru</th>';
                    echo '<th>Selisih</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($needRecalculation as $item) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                        echo '<td>Rp ' . number_format($item['price'], 0, ',', '.') . '</td>';
                        echo '<td>' . $item['amount'] . ' org</td>';
                        echo '<td class="old-value">Rp ' . number_format($item['old_income'], 0, ',', '.') . '</td>';
                        echo '<td class="new-value">Rp ' . number_format($item['new_income'], 0, ',', '.') . '</td>';
                        
                        $diff = $item['difference'];
                        $diffColor = $diff >= 0 ? '#16a34a' : '#dc2626';
                        echo '<td style="color: ' . $diffColor . ';">';
                        echo ($diff >= 0 ? '+' : '') . 'Rp ' . number_format($diff, 0, ',', '.');
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                    
                    echo '<div class="info">';
                    echo '<strong>Yang akan dilakukan:</strong><ul>';
                    echo '<li>Recalculate income untuk ' . count($needRecalculation) . ' kelas</li>';
                    echo '<li>Formula baru: <code>income = price - cost - trainer_honor</code></li>';
                    echo '<li>Tidak mengubah data price, amount, cost, atau trainer_honor</li>';
                    echo '</ul></div>';
                    
                    echo '<div style="margin-top: 30px;">';
                    echo '<a href="?confirm=yes" class="button btn-primary">🔧 Ya, Recalculate Semua</a>';
                    echo '<a href="/admin/classes" class="button btn-secondary">← Batal</a>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="warning">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
            
            <div class="info" style="margin-top: 30px;">
                <strong>⚠️ Catatan:</strong><br>
                Setelah recalculate selesai, <strong>refresh dashboard</strong> untuk melihat hasil yang benar.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Jika konfirmasi = yes, lakukan recalculation
try {
    DB::beginTransaction();
    
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Recalculate - Processing</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 700px;
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
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 14px;
            }
            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
            }
            th {
                background: #f3f4f6;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔧 Recalculating Class Income...</h1>";
    
    // Ambil semua kelas
    $classes = Clas::all();
    $updated = 0;
    $unchanged = 0;
    
    $results = [];
    
    foreach ($classes as $class) {
        // Hitung ulang income dengan formula baru
        $oldIncome = $class->income;
        $newIncome = $class->price - $class->cost - ($class->trainer_honor ?? 0);
        
        // Update jika berbeda
        if (abs($oldIncome - $newIncome) > 0.01) {
            $class->income = $newIncome;
            $class->save();
            
            $results[] = [
                'name' => $class->name,
                'old' => $oldIncome,
                'new' => $newIncome,
                'diff' => $newIncome - $oldIncome
            ];
            
            $updated++;
        } else {
            $unchanged++;
        }
    }
    
    DB::commit();
    
    echo "<div class='success'><strong>✅ Recalculation selesai!</strong></div>";
    
    echo "<div class='step'>";
    echo "<strong>Summary:</strong><ul>";
    echo "<li>Total kelas: " . $classes->count() . "</li>";
    echo "<li>Diupdate: <strong>$updated</strong> kelas</li>";
    echo "<li>Tidak berubah: $unchanged kelas</li>";
    echo "</ul></div>";
    
    if (!empty($results)) {
        echo "<div class='step'><strong>Detail perubahan:</strong></div>";
        echo "<table>";
        echo "<thead><tr><th>Nama Kelas</th><th>Income Lama</th><th>Income Baru</th><th>Selisih</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($result['name']) . "</td>";
            echo "<td>Rp " . number_format($result['old'], 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($result['new'], 0, ',', '.') . "</td>";
            
            $diff = $result['diff'];
            $diffColor = $diff >= 0 ? '#16a34a' : '#dc2626';
            echo "<td style='color: $diffColor; font-weight: 600;'>";
            echo ($diff >= 0 ? '+' : '') . 'Rp ' . number_format($diff, 0, ',', '.');
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
    }
    
    echo "
        <div class='success' style='margin-top: 30px; padding: 20px;'>
            <h3 style='margin-top: 0; color: #16a34a;'>✅ Selesai!</h3>
            <p>Data income sudah diperbaiki. Grafik dashboard sekarang akan menampilkan nilai yang benar.</p>
        </div>
        
        <div style='margin-top: 20px;'>
            <a href='/admin/dashboard' style='display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; font-weight: 600;'>
                📊 Lihat Dashboard
            </a>
            <a href='/admin/classes' style='display: inline-block; padding: 12px 24px; background: #6b7280; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; margin-left: 10px;'>
                📋 Lihat Daftar Kelas
            </a>
        </div>
        
        <div style='margin-top: 30px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 5px;'>
            <strong>⚠️ Jangan lupa:</strong> Hapus file ini setelah selesai!<br>
            Hapus file: <code>public/recalculate_class_income.php</code>
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
        <title>Recalculate - Error</title>
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
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p><a href='?'>← Kembali</a></p>
        </div>
    </body>
    </html>";
}
