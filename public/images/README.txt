=== INSTRUKSI: LOGO LOGIN PAGE ===

📁 FOLDER INI BERISI:
Logo untuk halaman login yang akan tampil secara permanen (hardcoded).

📋 CARA MENAMBAHKAN LOGO:

1. Simpan logo ALFABANK Anda dengan nama: "alfabank-logo.png"
   
2. Copy file tersebut ke folder ini:
   c:\laragon\www\alfa_bank\public\images\

3. Pastikan nama file PERSIS: alfabank-logo.png (huruf kecil semua)

4. Refresh halaman login (Ctrl+F5)

📐 SPESIFIKASI LOGO:
- Format: PNG (dengan background transparan lebih bagus)
- Ukuran Rekomendasi: 
  * Height: 80px - 120px
  * Width: bebas (akan auto-adjust)
- Aspect Ratio: 3:1 atau 4:1 (logo horizontal)

✨ FITUR:
- Logo akan tampil di header halaman login
- Otomatis ter-host saat deploy (karena di folder public)
- Jika logo tidak ada, akan fallback ke icon user circle

📝 CATATAN:
Berbeda dengan logo sidebar yang bisa diubah via CRUD, logo login ini bersifat STATIC dan harus diupdate manual dengan replace file.
