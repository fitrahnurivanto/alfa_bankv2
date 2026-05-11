<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\InventarisMovement;
use App\Models\InventarisPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseStorageService;

class InventarisController extends Controller
{
    protected $supabaseStorage;

    public function __construct(SupabaseStorageService $supabaseStorage)
    {
        $this->supabaseStorage = $supabaseStorage;
    }

    protected function checkAccess()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'finance'])) {
            abort(403, 'Anda tidak memiliki akses ke Inventaris Barang.');
        }
    }
    public function index()
    {
        $this->checkAccess();
        
        $inventaris = Inventaris::with('penanggungJawab', 'photos')
            ->when(request('search'), function ($query) {
                $search = request('search');
                return $query->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            })
            ->when(request('kategori'), function ($query) {
                return $query->where('kategori_barang', request('kategori'));
            })
            ->when(request('kondisi'), function ($query) {
                return $query->where('kondisi', request('kondisi'));
            })
            ->when(request('posisi'), function ($query) {
                return $query->where('posisi', request('posisi'));
            })
            ->paginate(20);

        $kategoris = Inventaris::select('kategori_barang')
            ->distinct()
            ->pluck('kategori_barang');

        return view('admin.inventaris.index', compact('inventaris', 'kategoris'));
    }

    public function create()
    {
        $this->checkAccess();
        
        $users = \App\Models\User::where('role', '!=', 'student')->get();
        return view('admin.inventaris.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:255',
            'merek' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:255',
            'tanggal_pengadaan' => 'required|date',
            'harga_beli' => 'nullable|numeric|min:0',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'posisi' => 'required|string|max:255',
            'kondisi' => 'required|in:baru,baik,perbaikan,rusak_ringan,rusak_berat,hilang',
            'status' => 'required|in:aktif,nonaktif,disposed',
            'supplier' => 'nullable|string|max:255',
            'sumber_pengadaan' => 'nullable|string|max:255',
            'penanggung_jawab' => 'nullable|exists:users,id',
            'catatan' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            $validated['kode_barang'] = Inventaris::generateKodeBarang();
            $inventaris = Inventaris::create($validated);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = 'inventaris/barang_' . $inventaris->id . '/' . $filename;
                    
                    $uploadedUrl = $this->supabaseStorage->upload($photo, $path);
                    if (!$uploadedUrl) {
                        Log::error('Gagal upload foto inventaris', ['path' => $path]);
                        continue;
                    }
                    
                    InventarisPhoto::create([
                        'inventaris_id' => $inventaris->id,
                        'file_path' => $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            return redirect()->route('admin.inventaris.show', $inventaris->id)
                ->with('success', 'Barang berhasil ditambahkan dengan kode: ' . $inventaris->kode_barang);
        } catch (\Exception $e) {
            Log::error('Error saat tambah inventaris: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan barang. ' . $e->getMessage());
        }
    }

    public function show(Inventaris $inventaris)
    {
        $this->checkAccess();
        
        $inventaris->load('penanggungJawab', 'photos', 'movements.user');
        $movements = $inventaris->movements()->latest()->get();
        return view('admin.inventaris.show', compact('inventaris', 'movements'));
    }

    public function edit(Inventaris $inventaris)
    {
        $this->checkAccess();
        
        $users = \App\Models\User::where('role', '!=', 'student')->get();
        return view('admin.inventaris.edit', compact('inventaris', 'users'));
    }

    public function update(Request $request, Inventaris $inventaris)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:255',
            'merek' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'nomor_seri' => 'nullable|string|max:255',
            'tanggal_pengadaan' => 'required|date',
            'harga_beli' => 'nullable|numeric|min:0',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'posisi' => 'required|string|max:255',
            'kondisi' => 'required|in:baru,baik,perbaikan,rusak_ringan,rusak_berat,hilang',
            'status' => 'required|in:aktif,nonaktif,disposed',
            'supplier' => 'nullable|string|max:255',
            'sumber_pengadaan' => 'nullable|string|max:255',
            'penanggung_jawab' => 'nullable|exists:users,id',
            'catatan' => 'nullable|string',
        ]);

        try {
            $inventaris->update($validated);

            return redirect()->route('admin.inventaris.show', $inventaris->id)
                ->with('success', 'Barang berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error saat update inventaris: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui barang.');
        }
    }

    public function destroy(Inventaris $inventaris)
    {
        $this->checkAccess();
        
        try {
            $inventaris->delete();
            return redirect()->route('admin.inventaris.index')
                ->with('success', 'Barang berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error saat hapus inventaris: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus barang.');
        }
    }

    public function editPosisi(Inventaris $inventaris)
    {
        $this->checkAccess();
        
        return view('admin.inventaris.edit-posisi', compact('inventaris'));
    }

    public function updatePosisi(Request $request, Inventaris $inventaris)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'posisi' => 'required|string|max:255',
            'alasan' => 'required|string|min:10',
        ]);

        try {
            $oldPosisi = $inventaris->posisi;

            $inventaris->update(['posisi' => $validated['posisi']]);

            InventarisMovement::create([
                'inventaris_id' => $inventaris->id,
                'tipe_perubahan' => 'posisi_change',
                'dari' => $oldPosisi,
                'ke' => $validated['posisi'],
                'alasan' => $validated['alasan'],
                'diubah_oleh' => Auth::id(),
            ]);

            return redirect()->route('admin.inventaris.show', $inventaris->id)
                ->with('success', 'Posisi barang berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error saat update posisi: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui posisi barang.');
        }
    }

    public function editKondisi(Inventaris $inventaris)
    {
        $this->checkAccess();
        
        return view('admin.inventaris.edit-kondisi', compact('inventaris'));
    }

    public function updateKondisi(Request $request, Inventaris $inventaris)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'kondisi' => 'required|in:baru,baik,perbaikan,rusak_ringan,rusak_berat,hilang',
            'alasan' => 'required|string|min:10',
        ]);

        try {
            $oldKondisi = $inventaris->kondisi;

            $inventaris->update(['kondisi' => $validated['kondisi']]);

            InventarisMovement::create([
                'inventaris_id' => $inventaris->id,
                'tipe_perubahan' => 'kondisi_change',
                'dari' => $oldKondisi,
                'ke' => $validated['kondisi'],
                'alasan' => $validated['alasan'],
                'diubah_oleh' => Auth::id(),
            ]);

            return redirect()->route('admin.inventaris.show', $inventaris->id)
                ->with('success', 'Kondisi barang berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error saat update kondisi: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui kondisi barang.');
        }
    }

    public function deletePhoto(InventarisPhoto $photo)
    {
        $this->checkAccess();
        
        try {
            $this->supabaseStorage->delete($photo->file_path);
            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat hapus foto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus foto.',
            ], 500);
        }
    }

    public function uploadPhoto(Request $request, Inventaris $inventaris)
    {
        $this->checkAccess();
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $photo = $request->file('photo');
            $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $path = 'inventaris/barang_' . $inventaris->id . '/' . $filename;
            
            $uploadedUrl = $this->supabaseStorage->upload($photo, $path);
            if (!$uploadedUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload foto ke Supabase',
                ], 500);
            }

            $photoRecord = InventarisPhoto::create([
                'inventaris_id' => $inventaris->id,
                'file_path' => $path,
                'file_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'file_size' => $photo->getSize(),
                'keterangan' => $request->input('keterangan'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil ditambahkan',
                'photo' => [
                    'id' => $photoRecord->id,
                    'url' => $uploadedUrl,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat upload foto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload foto: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportPdf()
    {
        $this->checkAccess();

        $inventaris = Inventaris::with('penanggungJawab', 'photos')
            ->when(request('search'), function ($query) {
                $search = request('search');
                return $query->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            })
            ->when(request('kategori'), function ($query) {
                return $query->where('kategori_barang', request('kategori'));
            })
            ->when(request('kondisi'), function ($query) {
                return $query->where('kondisi', request('kondisi'));
            })
            ->when(request('posisi'), function ($query) {
                return $query->where('posisi', request('posisi'));
            })
            ->get();

        $pdf = \PDF::loadView('admin.inventaris.export-pdf', compact('inventaris'));
        return $pdf->download('inventaris_barang_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportExcel()
    {
        $this->checkAccess();

        $inventaris = Inventaris::with('penanggungJawab', 'photos')
            ->when(request('search'), function ($query) {
                $search = request('search');
                return $query->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            })
            ->when(request('kategori'), function ($query) {
                return $query->where('kategori_barang', request('kategori'));
            })
            ->when(request('kondisi'), function ($query) {
                return $query->where('kondisi', request('kondisi'));
            })
            ->when(request('posisi'), function ($query) {
                return $query->where('posisi', request('posisi'));
            })
            ->get();

        $filename = 'inventaris_barang_' . date('Y-m-d_His') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventaris');

        // Headers
        $headers = ['Kode Barang', 'Nama Barang', 'Kategori', 'Merek', 'Model', 'Nomor Seri', 'Tanggal Pengadaan', 'Harga Beli', 'Jumlah', 'Satuan', 'Posisi', 'Kondisi', 'Status', 'Supplier', 'Sumber Pengadaan', 'Penanggung Jawab', 'Catatan'];
        $sheet->fromArray($headers, null, 'A1');

        // Format header - simple styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:Q1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        foreach ($inventaris as $item) {
            $data = [
                $item->kode_barang ?? '',
                $item->nama_barang ?? '',
                $item->kategori_barang ?? '',
                $item->merek ?? '',
                $item->model ?? '',
                $item->nomor_seri ?? '',
                $item->tanggal_pengadaan ? $item->tanggal_pengadaan->format('d/m/Y') : '',
                $item->harga_beli ?? 0,
                $item->jumlah ?? 0,
                $item->satuan ?? '',
                $item->posisi ?? '',
                ucfirst(str_replace('_', ' ', $item->kondisi ?? '')),
                ucfirst($item->status ?? ''),
                $item->supplier ?? '',
                $item->sumber_pengadaan ?? '',
                $item->penanggungJawab?->name ?? '-',
                $item->catatan ?? '',
            ];
            $sheet->fromArray($data, null, 'A' . $row);
            $row++;
        }

        // Auto resize columns
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save and output
        $tempPath = storage_path('app/temp_' . time() . '.xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
