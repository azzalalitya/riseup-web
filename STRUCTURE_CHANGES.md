# RiseUp — Restrukturisasi Kode (per Menu / Role)

Ringkasan perubahan struktur agar kode terpisah rapi per **role** dan per **menu**,
bukan lagi menumpuk flat dalam satu folder.

## 1. Controller dipisah per role

**Sebelum** — semua numpuk flat di `app/Http/Controllers/`:
```
AdminUserController, ApiController, AuthController, CheckinController,
DashboardController, LearningController, OnboardingController,
QuestController, SaveUpController
```

**Sesudah** — dikelompokkan per role:
```
app/Http/Controllers/
├── Auth/
│   └── AuthController.php
├── Student/
│   ├── DashboardController.php    (dari DashboardController@student -> index)
│   ├── OnboardingController.php
│   ├── CheckinController.php
│   ├── SaveUpController.php
│   ├── LearningController.php
│   └── QuestController.php
├── Admin/
│   ├── DashboardController.php    (dari DashboardController@admin -> index)
│   └── UserController.php         (dari AdminUserController)
└── Api/
    └── SummaryController.php      (dari ApiController)
```

## 2. Middleware role (menggantikan cek manual berulang)

**Sebelum** — tiap method controller mengulang:
```php
if (session('auth_role') !== 'user') {
    return redirect()->route('login');
}
```

**Sesudah** — pengecekan dipindah ke middleware, dipasang sekali di route group:
```
app/Http/Middleware/
├── EnsureUserRole.php   (alias: 'user')
└── EnsureAdminRole.php  (alias: 'admin')
```
Alias didaftarkan di `bootstrap/app.php`. Middleware juga sadar konteks:
kalau request `expectsJson()` -> balas `401 JSON`, selain itu redirect ke login.

## 3. Route dipisah per file/menu

**Sebelum** — semua route campur di `routes/web.php`.

**Sesudah**:
```
routes/
├── web.php       -> root + Auth + API (lalu meng-include 2 file di bawah)
├── student.php   -> semua menu USER  (middleware 'user', prefix /student)
└── admin.php     -> semua menu ADMIN (middleware 'admin', prefix /admin)
```
**Nama route & URL tidak berubah** (mis. `student.dashboard`, `admin.users.show`,
`onboarding.create`, `api.student.summary`) sehingga seluruh Blade view tetap jalan
tanpa diubah.

## 4. Kebersihan project

Dihapus 38 file sampah di root (artefak shell seperti `count()`, `route('login')`,
`where('usr_id'`, dll) yang bukan bagian dari Laravel.

---

## 5. Database SQL siap-import

Ditambahkan `database/riseup_database.sql` — skema **lengkap** (14 tabel)
+ seed, hasil rekonstruksi dari seluruh model. Sudah diuji import ke MySQL
tanpa error. File `database/database.sqlite` bawaan dihapus (tidak dipakai;
aplikasi memakai MySQL sesuai `.env`). `.env.example` juga disamakan ke MySQL.

Import:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS riseup CHARACTER SET utf8mb4;"
mysql -u root riseup < database/riseup_database.sql
```
atau via phpMyAdmin: buat DB `riseup` -> Import -> pilih file tersebut.

**Akun bawaan:**
| Role  | Email               | Password  |
| ----- | ------------------- | --------- |
| Admin | admin@riseup.test   | admin123  |
| User  | demo@riseup.test    | user123   |

Begitu user registrasi lewat form, datanya masuk `usr_user` dan langsung
tampil di Admin Dashboard (`/admin/dashboard`).

## 6. Fitur baru: Jurnal Harian + Konten Religius (multi-agama)

- Tabel baru: `jrn_journal`, `rel_religious_content` (sudah di SQL + seed).
- Model: `Journal`, `ReligiousContent`.
- Controller: `Student/JournalController` (prompt reflektif dipilih dari mood
  check-in terakhir; refleksi religius dipilih sesuai `prf_religion_pref`,
  fallback ke `umum`; isi pertama per hari memberi +15 XP).
- Route: `student.journal.index` (GET) & `student.journal.store` (POST).
- View: `student/journal/index.blade.php` + `public/css/journal.css`
  (memakai design token cream/orange + animasi halus).
- Konten religius mencakup: umum, islam, kristen, katolik, hindu, buddha
  (teks orisinal bernuansa nilai kebaikan universal).

## 8. Fitur baru: Badge & Leaderboard + Buddy/Maskot (jam rawan)

**Badge & Leaderboard**
- Tabel baru: `bdg_badge`, `ubd_user_badge` (di SQL + seed 8 badge).
- Model: `Badge`, `UserBadge`. Service: `App\Services\BadgeService`
  (auto-award lazy: dievaluasi saat halaman Pencapaian dibuka, tidak
  perlu mengubah controller XP mana pun).
- Controller: `Student/AchievementController` (badge + leaderboard XP mingguan).
- Route: `student.achievements.index`. View + `public/css/achievements.css`.

**Buddy / Maskot + reminder jam rawan**
- Kolom baru pada `bas_onboarding_baseline`: `bas_risk_hour_start`,
  `bas_risk_hour_end` (diisi di form onboarding, opsional).
- Partial: `student/partials/buddy.blade.php` (maskot SVG + bubble + JS).
  Muncul mengambang di semua halaman student utama. Saat jam sekarang masuk
  rentang jam rawan, Buddy otomatis memunculkan reminder untuk check-in /
  aktivitas positif; di luar itu memberi pesan motivasi acak.
- Data Buddy (nama + jam rawan) di-share otomatis via **View Composer** di
  `AppServiceProvider` — cukup `@include('student.partials.buddy')` tanpa
  ubah controller. Default jam rawan 20:00–23:00 bila user belum mengatur.
- CSS: `public/css/buddy.css`.

## 9. Phase 2 — Midtrans (Snap Sandbox) + Vault Withdrawal

**Skema**: Setoran U Save Up **real** via Midtrans Snap Sandbox (gratis, tanpa uang beneran). Uang yang masuk **tidak bisa ditarik sampai target tercapai** — vault behavior.

**Yang ditambah:**
- Library: `midtrans/midtrans-php ^2.6` di `composer.json` (jalankan `composer install`).
- Config: `config/midtrans.php` (baca `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` dari `.env`).
- Service: `App\Services\MidtransService` (buat Snap token, verifikasi signature SHA-512, pemetaan status).
- Kolom baru `sav_saveup_deposit`: `dep_status` (paid/pending/failed/manual), `dep_source`, `dep_order_id` (unique), `dep_payment_type`, `dep_paid_at`.
- Tabel baru `wdr_saveup_withdrawal` untuk pengajuan penarikan.
- Model: `SaveUpWithdrawal`, update `SaveUpDeposit`.

**Endpoint:**
- `POST /student/saveup/snap-token` — AJAX buat Snap token (user).
- `POST /midtrans/callback` — webhook dari Midtrans (**di-exclude dari CSRF**).
- `POST /student/saveup/withdraw` — pengajuan penarikan (guard: progres ≥ target).
- `GET /admin/withdrawals` + `POST /admin/withdrawals/{id}` — approve/reject oleh admin.

**Flow yang sudah diuji:**
1. User isi form → klik "Setor via Midtrans" → deposit tercatat `pending` + order_id unik.
2. Snap.js popup → user bayar (Sandbox: VA/QRIS/etc mode simulasi).
3. Midtrans hit `/midtrans/callback` → signature diverifikasi → status jadi `paid`.
4. Hanya deposit `manual` + `paid` yang dihitung ke progres dashboard (`pending`/`failed` diabaikan).
5. Kalau progres ≥ target → tombol "Ajukan Penarikan" aktif.
6. Admin di `/admin/withdrawals` menyetujui/menolak (dengan catatan opsional).

**Cara setup Midtrans (Sandbox):**
1. Signup gratis di https://dashboard.sandbox.midtrans.com/
2. Settings → Access Keys → salin **Server Key** & **Client Key**.
3. Isi ke `.env`:
   ```
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
4. **Webhook** (untuk demo lokal): pakai `ngrok` / `cloudflared` untuk expose `http://127.0.0.1:8000/midtrans/callback` ke internet, lalu daftarkan URL publiknya di Settings → Payment → Notification URL.
   - Kalau nggak pakai tunnel, status tetap bisa diperiksa manual via dashboard Midtrans, tapi otomatisasi status di app butuh webhook.

## Catatan SQL

- Import baru / fresh → `database/riseup_database.sql` (sudah termasuk semua tabel Phase 2).
- Sudah punya DB dari Phase 1 → jalankan `database/riseup_addon_phase2.sql`.
- Sudah punya DB versi paling lama → jalankan `riseup_addon_phase1b.sql` **lalu** `riseup_addon_phase2.sql`.

## 10. Phase 3 — Polish UI + Animasi (rubrik minggu-6)

Ditambahkan **1 layer polish** yang bekerja di semua halaman tanpa mengubah controller/backend, cukup di-*link* di head dan body:
- `public/css/animations.css` — CSS transitions & animations murni (tanpa framework).
- `public/js/animations.js` — trigger + IntersectionObserver.

Sudah di-inject otomatis ke **13 halaman blade** (student & admin). Halaman `welcome.blade.php` dan partial buddy tidak diinject supaya tidak dobel/kotor.

**Yang dianimasikan (semuanya menghormati `prefers-reduced-motion`):**
1. **Reveal on scroll (fade-up + stagger)** untuk kartu, list badge, entri jurnal, section head.
2. **Hover lift** halus di semua card interaktif (`translateY(-4px)` + shadow).
3. **Tombol** — press feedback, focus ring aksesibel, efek shine (`.btn-shine`).
4. **Nav link** underline animasi + auto-mark active berdasarkan URL saat ini.
5. **Kalender** — reveal per-sel dengan delay bertahap + hover scale.
6. **Progress bar** (SaveUp, badge) — animasi lebar dari 0 → target saat masuk viewport; shimmer bila 100%.
7. **Count-up angka** di dashboard (Hari Hijau, Estimasi Hemat, Total XP) — meng-easing dari 0 ke target.
8. **Toast** untuk flash `session('success')` / `session('error')` — slide in kanan-atas, auto hilang 4.8 detik.
9. **Input** — focus glow konsisten, transisi border halus.
10. **Micro details** — tabel row hover, selection color, `::selection` warm.

**Sesuai rubrik**: memakai `@keyframes`, `transition`, `transform`, `cubic-bezier`, plus `IntersectionObserver` untuk pemicu — cocok untuk poin CSS transitions & animations minggu-6.

## 11. Phase 3B — Auth split (User + Google OAuth, Admin URL tersembunyi)

Login dipecah jadi 2 pintu terpisah:

| Kanal | URL | Cara masuk |
|-------|-----|-----------|
| **User** | `/login` (root `/` redirect ke sini) | Email/password **atau** "Lanjutkan dengan Google" (Socialite) |
| **Admin** | `/admin-access` (URL tersembunyi, `noindex`) | Email/password saja |

**Yang berubah teknis:**
- `AuthController` lama dipisah jadi `Auth\UserAuthController` + `Auth\AdminAuthController`.
- Route baru:
  - User: `GET|POST /login`, `POST /register`, `POST /logout`, `GET /auth/google/redirect`, `GET /auth/google/callback`
  - Admin: `GET|POST /admin-access`, `POST /admin-access/logout`
- Middleware `EnsureAdminRole` sekarang redirect ke `admin.login` (bukan `login`) kalau gagal — konsisten sama pintu masuknya.
- Kolom baru `usr_user`: `usr_google_id` (unique), `usr_avatar_url`; `usr_password_hash` sekarang **NULLABLE** (buat akun Google-only).
- Config `config/services.php` + env keys `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
- Library: `laravel/socialite ^5.16` di `composer.json` (`composer install`).

**Flow yang tetap terjaga:**
- Register (email/password ATAU Google baru) → auto-login → **`/onboarding`** (baseline) dulu → baru boleh ke dashboard.
- Login lama (sudah punya onboarding) → langsung ke dashboard.
- Kalau email sama sudah ada di DB (dari daftar biasa), login Google auto-*link* — nggak bikin akun ganda.

**Setup Google Cloud Console (lokal & production):**
1. Buka https://console.cloud.google.com/ → APIs & Services → Credentials → Create Credentials → OAuth Client ID.
2. Application type: **Web application**.
3. Authorized redirect URIs — tambahkan **semua** yang akan dipakai:
   - `http://localhost:8000/auth/google/callback`
   - `http://127.0.0.1:8000/auth/google/callback`
   - Kalau sudah deploy: `https://<domain-lo>/auth/google/callback`
4. Copy Client ID + Client Secret ke `.env`:
   ```
   GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxx
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```
5. Kalau `GOOGLE_CLIENT_ID` kosong, tombol tetap render tapi klik-nya kasih pesan "belum dikonfigurasi" (aman untuk lingkungan yang belum diset).

**Catatan SQL:**
- Fresh import → `riseup_database.sql` (sudah termasuk kolom Google).
- DB dari phase sebelumnya → jalankan `riseup_addon_phase3b.sql`.

## 12. Phase 4 — Revamp flow, halaman terpisah, footer, badge notif, Railway-ready

**Halaman baru (input user dipisah dari dashboard):**
- `GET /student/checkin` — halaman Check-in penuh (form + kalender bulanan + riwayat).
- `GET /student/saveup` — halaman U Save Up penuh (target, setoran manual + Midtrans, vault/tarik dana, riwayat).
- **Dashboard sekarang ringkasan saja**: hero, akses cepat (6 kartu berikon), progress aktivitas, statistik, Ringkasan API, dan 2 kartu ringkas (Check-in hari ini + progres Save Up) dengan tombol menuju halaman masing-masing.
- Navbar student ditambah menu **Check-in** dan **Save Up**.

**Perbaikan logic:**
- Progress bar: dulu gradien `hijau→oranye` sehingga progress kecil terlihat hijau dan 100% terlihat oranye (terbalik). Sekarang bar **oranye**, dan otomatis **hijau hanya saat 100%** (class `is-full` dipasang animations.js).
- Nav "Positive Quest" tidak lagi hardcode hijau — menu aktif ditandai otomatis dari URL.

**Notifikasi badge:**
- Setiap aksi (check-in, selesai materi, selesai quest, isi jurnal) memicu `BadgeService::syncWithMessage()`:
  - Dapat badge baru → toast: `🏅 Badge baru: <nama>!`
  - Belum → toast progres badge terdekat: `🏅 Pembelajar: 2/3`

**UI:**
- **Footer wavy** (`partials/footer.blade.php` + `css/footer.css`) di semua halaman student & admin (termasuk tambah/detail/edit user) — mengisi ruang kosong di halaman quest/learning.
- Akses cepat dashboard: 6 kartu dengan **ikon SVG** + judul + kalimat singkat.
- Landing: pills navbar dirapikan (Check-in harian/Progress tersimpan/Aman & privat dihapus).

**Admin:**
- Tabel user: baris user dengan **check-in merah dalam 7 hari terakhir** di-highlight merah + badge "⚠ Perlu perhatian" berdenyut.

**Catatan "Pengajuan Penarikan Dana" (admin):**
Menu itu memproses pengajuan dari user. Di sisi user, tombol **"Ajukan Penarikan"** hanya muncul di halaman U Save Up ketika **progres ≥ target** (vault terbuka). Kalau tidak muncul, artinya user belum membuat target atau targetnya belum tercapai — itu memang desain vault-nya.

## 13. Deploy ke Railway (siap pakai)

File deploy sudah disiapkan: `Procfile`, `nixpacks.toml`, dan trust proxy sudah diaktifkan di `bootstrap/app.php` (wajib untuk https di balik proxy Railway).

**Langkah:**
1. Push project ke GitHub (tanpa folder `vendor/`).
2. railway.app → New Project → **Deploy from GitHub repo**.
3. Tambah plugin **MySQL** di project Railway → salin kredensialnya.
4. Isi Variables di service web:
   ```
   APP_NAME=RiseUp
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=            <- hasil `php artisan key:generate --show` di lokal
   APP_URL=https://<domain-railway-kamu>

   DB_CONNECTION=mysql
   DB_HOST=<dari plugin MySQL>
   DB_PORT=<dari plugin MySQL>
   DB_DATABASE=<dari plugin MySQL>
   DB_USERNAME=<dari plugin MySQL>
   DB_PASSWORD=<dari plugin MySQL>

   SESSION_DRIVER=database

   MIDTRANS_SERVER_KEY=...
   MIDTRANS_CLIENT_KEY=...
   MIDTRANS_IS_PRODUCTION=false   <- Sandbox cukup untuk demo UAS

   GOOGLE_CLIENT_ID=...
   GOOGLE_CLIENT_SECRET=...
   GOOGLE_REDIRECT_URI=https://<domain-railway>/auth/google/callback
   ```
5. Import data: buka MySQL plugin → tab Query/Connect → jalankan isi `database/riseup_database.sql` (atau connect via MySQL client dan `mysql ... < riseup_database.sql`). Tabel `sessions` dll dibuat otomatis oleh `php artisan migrate --force` saat start.
6. **Google Cloud Console** → OAuth Client → tambahkan redirect URI production: `https://<domain-railway>/auth/google/callback`.
7. **Midtrans Dashboard** → Settings → Payment → Notification URL: `https://<domain-railway>/midtrans/callback` (tidak perlu ngrok lagi karena sudah punya URL publik).

## 14. Phase 5 — Setoran full Midtrans (manual dihapus)

- Form "Catat Setoran (Manual)" **dihapus** — satu-satunya jalur setor sekarang **Midtrans Snap** (nominal + catatan → popup pembayaran).
- Method `storeDeposit` + route `saveup.deposit.store` dihapus. `destroyDeposit` dipertahankan untuk membersihkan transaksi `pending`/`failed` (yang `paid` tetap tidak bisa dihapus).
- Data manual lama (jika ada) tetap dihitung ke progres agar tidak merusak data existing.
- Kanal pembayaran Snap kini bisa diatur via env `MIDTRANS_ENABLED_PAYMENTS`
  (default QRIS-first: `qris,gopay,shopeepay,bank_transfer,echannel,permata_va,bca_va,bni_va,bri_va`).
  Kosongkan untuk menampilkan semua kanal aktif di akun.

### Panduan Midtrans lengkap: dari sandbox sampai dana masuk rekening

**Tahap 1 — Sandbox (untuk development & demo UAS):**
1. Daftar di https://dashboard.sandbox.midtrans.com (gratis).
2. Settings → Access Keys → salin Server Key & Client Key → isi `.env` (`MIDTRANS_IS_PRODUCTION=false`).
3. Tes bayar: pilih QRIS di popup → scan pakai https://simulator.sandbox.midtrans.com (menu QRIS) → status otomatis jadi `settlement` → webhook menandai deposit `paid`.
4. Webhook lokal: expose `http://127.0.0.1:8000` via ngrok, daftarkan `https://<ngrok>/midtrans/callback` di Settings → Payment → Notification URL.

**Tahap 2 — Production (uang beneran):**
1. Daftar/login di https://dashboard.midtrans.com → klik banner aktivasi → isi data bisnis + unggah dokumen (KTP jelas, data rekening bank atas nama yang SAMA dengan KTP, link web/medsos/katalog yang bisa diakses).
2. Setelah disetujui, akun production langsung bisa menerima: Transfer Bank VA (Permata, BNI, BRI, Mandiri Bill), GoPay, dan QRIS. Merchant yang bisa GoPay otomatis bisa QRIS.
3. Ganti `.env` production: Server/Client Key production + `MIDTRANS_IS_PRODUCTION=true` (Snap.js otomatis pindah ke URL production).
4. Notification URL production: `https://<domain>/midtrans/callback`.

**Tahap 3 — Dana masuk ke rekening kamu (Withdrawal):**
1. Dana customer masuk dulu ke saldo merchant Midtrans saat transaksi berstatus `settlement`.
2. Di dashboard MAP → menu **Withdrawal** → isi **Detail Bank** (rekening tujuan).
3. Syarat penarikan pertama: dokumen registrasi lengkap + minimal 1 transaksi settlement.
4. Penarikan bisa dilakukan mulai **±3 hari kerja** setelah settlement, **tanpa biaya**. Tersedia payout manual atau otomatis.

## Cara menjalankan

```bash
composer install          # vendor tidak disertakan di zip ini
cp .env.example .env       # kalau .env belum ada
php artisan key:generate   # kalau APP_KEY kosong
# pastikan DB MySQL 'riseup' sudah ada (lihat .env)
php artisan route:list     # verifikasi semua route ter-registrasi
php artisan serve
```

> Catatan: struktur tabel memakai skema kustom (prefix `usr_`, `chk_`, `gms_`, dst)
> dan model memakai `$table`/`$primaryKey` eksplisit, jadi tidak bergantung pada
> migration bawaan untuk tabel domain.
