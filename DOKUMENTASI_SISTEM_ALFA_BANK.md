# 📚 DOKUMENTASI SISTEM MANAGEMENT TRAINING ALFA BANK

**Versi:** 1.0  
**Tanggal:** 10 Maret 2026  
**Developer:** Tim Development  
**Client:** Alfa Bank - Lembaga Pendidikan  

---

## 📋 **DAFTAR ISI**

1. [Tentang Sistem](#1-tentang-sistem)
2. [Teknologi yang Digunakan](#2-teknologi-yang-digunakan)
3. [Struktur User & Hak Akses](#3-struktur-user--hak-akses)
4. [Fitur-Fitur Sistem](#4-fitur-fitur-sistem)
5. [Workflow Sistem](#5-workflow-sistem)
6. [Setup Production](#6-setup-production)
7. [Konfigurasi Supabase Storage](#7-konfigurasi-supabase-storage)
8. [Database Schema](#8-database-schema)
9. [Troubleshooting](#9-troubleshooting)
10. [Maintenance & Backup](#10-maintenance--backup)

---

## 1. TENTANG SISTEM

### **Nama Aplikasi:**
**Alfa Bank Training Management System**

### **Tujuan:**
Sistem manajemen pengelolaan kelas training, keuangan, dan administrasi untuk Lembaga Pendidikan Alfa Bank.

### **Fitur Utama:**
- ✅ Manajemen Kelas Training (Corporate, Regular, Private)
- ✅ Manajemen Keuangan (Revenue, Expenses, Payment Requests)
- ✅ Dashboard Finance dengan Multi-line Chart
- ✅ Approval System 2-Level (Admin & Finance)
- ✅ File Upload ke Cloud Storage (Supabase)
- ✅ Laporan & Export Excel/PDF
- ✅ Notifikasi Real-time
- ✅ Multi-role Access Control

---

## 2. TEKNOLOGI YANG DIGUNAKAN

### **Backend:**
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **Laravel** | 11.x | PHP Framework utama |
| **PHP** | 8.2+ | Server-side language |
| **MySQL** | 8.0+ | Database relational |
| **Composer** | 2.x | Dependency manager |

### **Frontend:**
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **Blade Templates** | - | View engine Laravel |
| **Tailwind CSS** | 3.x | CSS utility framework |
| **Alpine.js** | 3.x | JavaScript framework (minimal) |
| **Chart.js** | 4.4.0 | Data visualization |
| **Vite** | 5.x | Frontend build tool |

### **Third-Party Services:**
| Service | Fungsi | Konfigurasi |
|---------|--------|-------------|
| **Supabase Storage** | Cloud file storage (bukti transfer, dokumen) | `.env` SUPABASE_* |
| **Google OAuth** (Optional) | Login dengan Google | `.env` GOOGLE_* |
| **SMTP Gmail** (Optional) | Email notifications | `.env` MAIL_* |

### **Libraries & Packages:**
- **maatwebsite/excel** - Export Excel
- **barryvdh/laravel-dompdf** - Export PDF
- **intervention/image** - Image processing
- **guzzlehttp/guzzle** - HTTP client (Supabase API)

### **File Storage:**
- **Local (Development):** `storage/app/public`
- **Cloud (Production):** Supabase Storage bucket `alfabank`

---

## 3. STRUKTUR USER & HAK AKSES

### **Role Hierarchy:**

```
┌─────────────────────────────────────┐
│          ADMIN (Super User)         │
│  • Full access ke semua fitur       │
│  • Manage users, classes, clients   │
│  • Approve payment requests (L1)    │
│  • View all reports                 │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│            FINANCE                  │
│  • Manage keuangan                  │
│  • Approve expenses                 │
│  • Approve payment requests (L2)    │
│  • Upload bukti transfer            │
│  • View finance dashboard           │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│            TRAINER                  │
│  • View assigned classes            │
│  • Create payment request           │
│  • View own payment history         │
│  • Update class status              │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│            CLIENT                   │
│  • View own classes                 │
│  • View invoices                    │
│  • Limited read access              │
└─────────────────────────────────────┘
```

### **Default Users (Seeder Data):**

| Role | Email | Password | Nama |
|------|-------|----------|------|
| Admin | admin_alfayk@gmail.com | password123 | Admin Alfa Bank |
| Finance | finance_alfayk@gmail.com | password123 | Finance Alfa Bank |
| Trainer | budi.trainer@gmail.com | password123 | Budi Santoso |
| Client | client.test@gmail.com | password123 | PT Test Indonesia |

⚠️ **PENTING:** Ganti semua password setelah deployment production!

---

## 4. FITUR-FITUR SISTEM

### **A. Dashboard**

#### **Admin Dashboard:**
- Total classes (completed, active, pending)
- Revenue overview (6 month chart)
- Class count per month
- Recent activities
- Top trainers
- Pending approvals

#### **Finance Dashboard:**
- 7 Cards Statistics:
  * Total Revenue
  * Total Expenses
  * Total Payment Requests (Paid)
  * Net Profit
  * Pending Expenses (count + amount)
  * Pending Payment Requests (count + amount)
  * Sisa Pembayaran 2x Termin
- Multi-line chart (12 bulan):
  * Pendapatan kelas training
  * Total expenses
  * Payment requests (paid)
- Recent expenses & payment requests
- Period filter (Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini, Semua Periode)

#### **Trainer Dashboard:**
- Assigned classes
- Payment request status
- Upcoming classes
- Payment history

### **B. Class Management**

**Fitur:**
- Create/Edit/Delete classes
- 3 Types: Corporate, Regular, Private
- Payment types: Full, 2x Termin, 3x Termin
- Status workflow: Pending → Approved → Done → Cancelled
- Assign trainers (multiple)
- Track payments (paid_amount)
- BNSP certification tracking
- Export PDF untuk PKS & Certificate

**Fields Utama:**
- Training type & name
- Client information
- Start & end date
- Price & amount (participants)
- Payment type & status
- Trainers assigned
- Location & PIC details

### **C. Financial Management**

#### **Class Expenses:**
- Create expense (admin/trainer)
- Approval workflow:
  * Pending → Approved/Rejected (Finance)
- Track by class
- Export reports

#### **Payment Requests:**
- Create by trainer (auto from class honor)
- 2-Level approval:
  * Level 1: Admin approve
  * Level 2: Finance approve → Paid
- Auto-generate request number: `PR-YYMMDD-XXXX`
- Upload bukti transfer (Finance)
- Track fields:
  * requested_amount
  * approved_amount
  * approved_by (admin)
  * finance_approved_by
  * paid_by
  * payment_method
  * payment_reference
  * bukti_transfer_url

#### **Revenue Tracking:**
- Track pembayaran per class
- Support 2x & 3x termin
- Sisa pembayaran tracking
- Monthly revenue chart

### **D. Report & Export**

**Laporan yang Tersedia:**
- Laporan Keuangan (PDF/Excel)
- Laporan Kelas per Periode
- Laporan Payment Requests
- Laporan Expenses
- PKS & Certificate PDF

**Export Format:**
- PDF (DomPDF)
- Excel (Maatwebsite/Excel)

### **E. Settings**

**Konfigurasi:**
- Target omset bulanan
- Company information
- SMTP email settings (optional)
- Google OAuth (optional)

---

## 5. WORKFLOW SISTEM

### **A. Class Workflow**

```
┌──────────────┐
│ Admin Create │
│    Class     │
└──────┬───────┘
       │
       ↓
┌──────────────┐
│   PENDING    │ ← Admin review
└──────┬───────┘
       │ Approve
       ↓
┌──────────────┐
│   APPROVED   │ ← Class aktif, training berjalan
└──────┬───────┘
       │ Complete
       ↓
┌──────────────┐
│     DONE     │ ← Class selesai, trainer bisa request payment
└──────────────┘
```

### **B. Payment Request Workflow**

```
┌──────────────┐
│   Trainer    │
│ Create Request│
└──────┬───────┘
       │
       ↓
┌──────────────┐
│   PENDING    │
└──────┬───────┘
       │ (L1)
       ↓
┌──────────────┐
│     Admin    │
│   Approve    │ ← approved_by, approved_at
└──────┬───────┘
       │
       ↓
┌──────────────────┐
│ ADMIN_APPROVED   │
└──────┬───────────┘
       │ (L2)
       ↓
┌──────────────┐
│   Finance    │
│   Approve    │ ← finance_approved_by, finance_approved_at
└──────┬───────┘
       │
       ↓
┌────────────────────┐
│ FINANCE_APPROVED   │
└──────┬─────────────┘
       │ Pay & Upload Bukti
       ↓
┌──────────────┐
│   Finance    │
│ Mark as Paid │ ← paid_by, paid_at, bukti_transfer_url
└──────┬───────┘
       │
       ↓
┌──────────────┐
│     PAID     │ ← Done! Trainer dibayar
└──────────────┘
```

### **C. Expense Approval Workflow**

```
┌──────────────┐
│ Admin/Trainer│
│Create Expense│
└──────┬───────┘
       │
       ↓
┌──────────────┐
│   PENDING    │
└──────┬───────┘
       │
       ↓
┌──────────────┐
│   Finance    │
│ Approve/Reject│
└──────┬───────┘
       │
   ┌───┴───┐
   │       │
   ↓       ↓
APPROVED REJECTED
```

### **D. Finance Dashboard Data Flow**

```
┌─────────────────────┐
│   Classes (done)    │ → paid_amount → Total Revenue
└─────────────────────┘

┌─────────────────────┐
│  Class Expenses     │ → approved only → Total Expenses
│   (approved)        │
└─────────────────────┘

┌─────────────────────┐
│ Payment Requests    │ → paid only → Total Payment Requests
│      (paid)         │
└─────────────────────┘

FORMULA:
Net Profit = Total Revenue - (Total Expenses + Total Payment Requests)
```

---

## 6. SETUP PRODUCTION

### **A. Server Requirements**

**Minimum:**
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer 2.x
- Node.js 18+ & NPM (untuk build frontend)
- 512 MB RAM minimum
- 1 GB storage minimum

**Recommended:**
- PHP 8.3
- MySQL 8.0
- 2 GB RAM
- 5 GB storage
- SSL Certificate (HTTPS)

### **B. cPanel Upload & Setup**

#### **Step 1: Prepare Files**

1. **Hapus file-file yang tidak perlu:**
```bash
# File test/development yang bisa dihapus:
test_*.php
check_*.php
quick_*.php
fix_*.php
verify_*.php
.editorconfig
.styleci.yml
phpunit.xml
tests/ (folder)
```

2. **Build frontend:**
```bash
npm install
npm run build
```

3. **Compress ke ZIP:**
   - Zip seluruh folder `alfa_bank`
   - Nama: `alfa_bank_production.zip`

#### **Step 2: Upload ke cPanel**

1. Login ke **cPanel**
2. Buka **File Manager**
3. Navigate ke `public_html` atau folder domain
4. **Upload** `alfa_bank_production.zip`
5. **Extract** zip file
6. **Move** semua isi folder `alfa_bank` ke root (atau subdirectory)

#### **Step 3: Set Permissions**

```bash
# Via cPanel Terminal atau SSH
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env
```

#### **Step 4: Create Database**

1. Buka **MySQL Databases** di cPanel
2. **Create Database**: `alfabank_db`
3. **Create User**: `alfabank_user`
4. **Set Password**: (strong password)
5. **Add User to Database** dengan **ALL PRIVILEGES**
6. Catat:
   - DB Name
   - DB User
   - DB Password
   - DB Host (biasanya `localhost`)

#### **Step 5: Configure .env**

Edit file `.env` via cPanel File Manager:

```env
APP_NAME="Alfa Bank"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=alfabank_db
DB_USERNAME=alfabank_user
DB_PASSWORD=your_db_password_here

# Supabase Storage
SUPABASE_URL=https://czoolrgsiwozutzfpkpj.supabase.co
SUPABASE_KEY=eyJhbGc... (your service_role key)
SUPABASE_BUCKET=alfabank

```

⚠️ **Set `APP_DEBUG=false` di production!**

#### **Step 6: Run Migrations**

Via cPanel Terminal atau SSH:

```bash
cd /path/to/alfa_bank
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### **Step 7: Set Document Root**

Di cPanel → **Domains** → **Your Domain** → Set document root ke:
```
/home/username/public_html/alfa_bank/public
```

### **C. Testing Production**

1. Akses `https://yourdomain.com`
2. Login dengan user admin: `admin_alfayk@gmail.com` / `password123`
3. Test upload foto (akan ke Supabase)
4. Test create class, payment request
5. **GANTI PASSWORD SEMUA USER!**

---

## 7. KONFIGURASI SUPABASE STORAGE

### **A. Setup Bucket**

Bucket `alfabank` sudah dibuat dengan konfigurasi:

- **Name:** `alfabank`
- **Public:** ✅ Yes
- **Region:** Southeast Asia (Singapore)

### **B. Storage Policies**

3 Policies yang sudah di-setup:

#### **1. Public Read Access (SELECT)**
```sql
CREATE POLICY "Public Read Access"
ON storage.objects
FOR SELECT
USING (bucket_id = 'alfabank');
```
**Fungsi:** Semua orang bisa lihat/download file via URL public

#### **2. Authenticated Upload (INSERT)**
```sql
CREATE POLICY "Authenticated Upload"
ON storage.objects
FOR INSERT
WITH CHECK (bucket_id = 'alfabank');
```
**Fungsi:** Laravel backend bisa upload file dengan service_role key

#### **3. Authenticated Delete (DELETE)**
```sql
CREATE POLICY "Authenticated Delete"
ON storage.objects
FOR DELETE
USING (bucket_id = 'alfabank');
```
**Fungsi:** Laravel backend bisa delete file jika perlu

### **C. Credentials**

**Project URL:**
```
https://czoolrgsiwozutzfpkpj.supabase.co
```

**Service Role Key:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImN6b29scmdzaXdvenV0emZwa3BqIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2OTQ0NTA0MSwiZXhwIjoyMDg1MDIxMDQxfQ.KWo9tKOF57rrch0B82H05TY1zVMl9zifYYRA3Vrn5Ns
```

⚠️ **PENTING:** Service role key adalah SECRET! Jangan share ke public!

### **D. URL Pattern File**

File yang diupload bisa diakses via URL:
```
https://czoolrgsiwozutzfpkpj.supabase.co/storage/v1/object/public/alfabank/[PATH]/[FILENAME]
```

Contoh:
```
https://czoolrgsiwozutzfpkpj.supabase.co/storage/v1/object/public/alfabank/payment-proofs/69aa924d2820c.png
```

### **E. Folder Structure**

Bucket `alfabank` disarankan punya struktur:
```
alfabank/
├── payment-proofs/     (bukti transfer payment requests)
├── class-files/        (dokumen kelas)
├── expense-receipts/   (bukti pengeluaran)
└── documents/          (dokumen lainnya)
```

---

## 8. DATABASE SCHEMA

### **A. Tables Overview**

| Table | Jumlah Columns | Fungsi Utama |
|-------|----------------|--------------|
| `users` | 15 | User accounts (admin, finance, trainer, client) |
| `clas` | 40+ | Training classes |
| `trainings` | 10 | Master training programs |
| `kategoris` | 6 | Class categories |
| `payment_requests` | 20 | Trainer payment requests |
| `class_expenses` | 12 | Class operational expenses |
| `notifications` | 8 | User notifications |
| `activity_logs` | 8 | System activity logs |
| `clients` | 12 | Client companies |
| `settings` | 5 | System settings |

### **B. Key Relationships**

```
users (1) ──────< (∞) payment_requests
users (1) ──────< (∞) clas (as trainer, via pivot)
clients (1) ─────< (∞) clas
trainings (1) ───< (∞) clas
kategoris (1) ───< (∞) clas
clas (1) ────────< (∞) class_expenses
clas (1) ────────< (∞) payment_requests
```

### **C. Critical Fields**

**payment_requests:**
- `request_number` (unique, auto-generated)
- `approved_by` (L1: Admin)
- `finance_approved_by` (L2: Finance)
- `paid_by` (Finance yang bayar)
- `bukti_transfer_url` (Supabase URL)

**clas:**
- `slug` (required for SEO)
- `paid_amount` (untuk tracking pembayaran)
- `payment_type` (full, 2x termin, 3x termin)
- `status` (pending, approved, done, cancelled)

**class_expenses:**
- `approval_status` (pending, approved, rejected)
- `approved_by` (Finance)

---

## 9. TROUBLESHOOTING

### **A. Common Issues**

#### **1. Error 500 - Internal Server Error**
```
SOLUSI:
1. Set APP_DEBUG=true di .env (temporary)
2. Check storage/logs/laravel.log
3. Pastikan permissions benar (755 storage, 755 bootstrap/cache)
4. Clear cache: php artisan cache:clear
```

#### **2. Database Connection Failed**
```
SOLUSI:
1. Cek DB credentials di .env
2. Test koneksi mysql via command line
3. Pastikan user punya privileges
4. Check DB host (localhost atau IP)
```

#### **3. Upload File Gagal (Supabase)**
```
SOLUSI:
1. Cek SUPABASE_URL dan SUPABASE_KEY di .env
2. Pastikan menggunakan service_role key (bukan anon)
3. Verify bucket "alfabank" ada dan public
4. Check 3 policies sudah dibuat
5. Test URL file bisa diakses di browser
```

#### **4. Foto Tidak Bisa Diakses (401 Unauthorized)**
```
SOLUSI:
1. Pastikan bucket alfabank di-set PUBLIC
2. Check policy "Public Read Access" (SELECT) ada
3. Verify URL format benar (path relatif di database)
4. Clear browser cache
```

#### **5. CSS/JS Tidak Muncul**
```
SOLUSI:
1. Run: npm run build
2. Check file ada di public/build/
3. Clear browser cache (Ctrl+Shift+R)
4. Verify document root mengarah ke /public
```

### **B. Maintenance Commands**

```bash
# Clear all cache
php artisan optimize:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue work (jika ada)
php artisan queue:work --daemon

# Check system status
php artisan about
```

---

## 10. MAINTENANCE & BACKUP

### **A. Regular Maintenance**

**Daily:**
- Monitor storage/logs/laravel.log untuk errors
- Check disk space

**Weekly:**
- Backup database
- Review activity logs
- Check Supabase storage usage

**Monthly:**
- Update composer packages (jika ada security patch)
- Review dan remove old notifications
- Archive old activity logs

### **B. Database Backup**

**Via cPanel:**
1. Buka **phpMyAdmin**
2. Select database `alfabank_db`
3. Click **Export**
4. Format: SQL
5. Download

**Via SSH:**
```bash
mysqldump -u alfabank_user -p alfabank_db > backup_$(date +%Y%m%d).sql
```

**Restore:**
```bash
mysql -u alfabank_user -p alfabank_db < backup_20260310.sql
```

### **C. File Backup**

**Important Directories:**
```
.env (credentials)
storage/app/ (local files jika ada)
database/migrations/ (schema history)
```

⚠️ **Jangan backup:**
- `vendor/` (bisa di-install ulang via composer)
- `node_modules/` (bisa di-install ulang via npm)
- `storage/logs/` (temporary)
- `storage/framework/cache/` (temporary)

### **D. Supabase Storage Backup**

File di Supabase sudah di-backup otomatis oleh Supabase.

**Manual backup:**
1. Buka Supabase dashboard
2. Storage → alfabank bucket
3. Download files yang penting
4. Simpan di backup server/cloud lain

---

## 📞 **KONTAK SUPPORT**

Jika ada pertanyaan atau issue:
- Dokumentasi: File ini
- Log errors: `storage/logs/laravel.log`
- Supabase dashboard: https://supabase.com/dashboard

---

## ✅ **CHECKLIST DEPLOYMENT**

Sebelum go-live:

- [ ] Upload files ke cPanel
- [ ] Set permissions (755 storage, bootstrap/cache)
- [ ] Create database & user di cPanel
- [ ] Configure .env (DB, Supabase, APP_URL)
- [ ] Set APP_DEBUG=false
- [ ] Run migrations & seeders
- [ ] Storage link (php artisan storage:link)
- [ ] Cache config/routes/views
- [ ] Test login semua role
- [ ] Test upload file (Supabase)
- [ ] Test create class
- [ ] Test payment request workflow
- [ ] Ganti password default users
- [ ] Setup SSL certificate (HTTPS)
- [ ] Test di berbagai browser
- [ ] Test responsive di mobile
- [ ] Backup database pertama kali
- [ ] Monitor logs 24 jam pertama

---

**🎉 Sistem Alfa Bank siap production!**

*Dokumentasi ini dibuat pada 10 Maret 2026*
