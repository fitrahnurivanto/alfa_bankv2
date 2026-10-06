<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>419 - Sesi Habis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,#eff6ff_0,#f8fafc_35%,#eef2ff_100%)] text-slate-900 font-sans">
    <div class="min-h-screen flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-2xl">
            <div class="bg-white/90 backdrop-blur rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="h-2 bg-linear-to-r from-rose-500 via-amber-400 to-blue-500"></div>
                <div class="p-8 md:p-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-sm font-semibold mb-6">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Sesi form sudah kedaluwarsa
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-4">Halaman perlu dimuat ulang</h1>
                    <p class="text-slate-600 text-lg leading-relaxed mb-8">
                        Permintaan yang Anda kirim tidak bisa diproses karena token keamanan sudah berubah atau session sudah tidak cocok.
                        Ini biasanya terjadi saat halaman dibiarkan terlalu lama, login ulang, atau tombol simpan diklik lebih dari sekali.
                    </p>

                    <div class="grid gap-4 md:grid-cols-2 mb-8">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <h2 class="font-semibold text-slate-900 mb-2">Yang perlu dilakukan</h2>
                            <ul class="space-y-2 text-slate-600 text-sm leading-6 list-disc pl-5">
                                <li>Refresh atau buka ulang halaman form.</li>
                                <li>Pastikan Anda masih login.</li>
                                <li>Isi ulang data lalu klik simpan sekali saja.</li>
                            </ul>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <h2 class="font-semibold text-slate-900 mb-2">Kenapa bisa terjadi</h2>
                            <p class="text-slate-600 text-sm leading-6">
                                Sistem keamanan Laravel memakai token CSRF untuk mencegah pengiriman data palsu.
                                Jika halaman terlalu lama terbuka atau request terkirim dua kali, token lama dianggap tidak valid.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" onclick="window.location.reload()" class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                            Muat Ulang Halaman
                        </button>
                        <a href="{{ url()->previous() ?: url('/') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold hover:bg-slate-50 transition">
                            Kembali
                        </a>
                    </div>

                    <p class="mt-6 text-xs text-slate-500">
                        Jika masalah ini sering muncul, laporkan ke tim teknis karena kemungkinan ada halaman yang masih tersimpan cache atau sesi login yang terlalu singkat.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>