<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Inventaris Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #4F46E5;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #333;
            font-weight: bold;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR INVENTARIS BARANG</h1>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Merek</th>
                <th>Model</th>
                <th>Nomor Seri</th>
                <th>Tgl Pengadaan</th>
                <th>Harga Beli</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Posisi</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Supplier</th>
                <th>Sumber</th>
                <th>Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventaris as $item)
                <tr>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori_barang }}</td>
                    <td>{{ $item->merek ?? '-' }}</td>
                    <td>{{ $item->model ?? '-' }}</td>
                    <td>{{ $item->nomor_seri ?? '-' }}</td>
                    <td>{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->harga_beli ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $item->jumlah }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>{{ $item->posisi }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $item->kondisi)) }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->supplier ?? '-' }}</td>
                    <td>{{ $item->sumber_pengadaan ?? '-' }}</td>
                    <td>{{ $item->penanggungJawab?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Barang: {{ $inventaris->count() }}</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
