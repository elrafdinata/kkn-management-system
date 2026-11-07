# PROJECT DOCS — KKN MANAGEMENT SYSTEM

Modul aktif: Sistem Absensi Digital KKN Kelompok 30 UNTARA.

Terakhir diperbarui: 2026-11-07 (akhir sesi 7).

> **Aturan baca.** Dokumen ini adalah snapshot. Kondisi repository, kode aktual, hasil terminal, dan hasil pengujian terbaru adalah sumber kebenaran. Jika bagian ini bertentangan dengan kode, ikuti kode lalu perbarui dokumen.
>
> **Repository PUBLIC.** Dilarang menulis password, token QR, isi `.env`, koordinat posko, alamat tunnel, nama database proyek lain, dan data pribadi anggota (nama lengkap, username, NIM, nomor HP).
>
> Isi lama dokumen ini (analisis Phase 1, desain Phase 2, rincian Phase 0, dan dokumentasi database bawaan Laravel) sudah dihapus karena bertentangan dengan implementasi aktual. Riwayat lengkapnya masih ada di riwayat Git.

---

## 1. Identitas proyek

- Nama aplikasi: KKN MANAGEMENT SYSTEM.
- Modul aktif: Sistem Absensi Digital KKN Kelompok 30 UNTARA.
- Pemilik, developer, dan admin utama: pemilik proyek (masih belajar Laravel).
- Pengguna: 18 anggota KKN dan 1 admin.
- Folder proyek: `C:\laragon\www\kkn-management-system`.
- Database proyek: `kkn_management_system`. Server MySQL lokal dipakai bersama proyek lain, jangan menyentuh database lain.
- Repository: `https://github.com/elrafdinata/kkn-management-system` (PUBLIC), branch `main`.
- KARAKSA WASTE adalah aplikasi terpisah dan tidak dicampur dengan proyek ini.

## 2. Teknologi (terakhir terverifikasi)

| Komponen       | Versi                                                                             |
| -------------- | --------------------------------------------------------------------------------- |
| Sistem operasi | Windows, Laragon, VS Code                                                         |
| Laravel        | 13.40.0                                                                           |
| PHP            | 8.5.8                                                                             |
| MySQL          | 8.0.30                                                                            |
| Composer       | 2.10.3                                                                            |
| Node.js / NPM  | 22.23.1 / 10.9.8                                                                  |
| Frontend       | Blade + Bootstrap 5 lewat Vite (`resources/scss/app.scss`, `resources/js/app.js`) |

Terminal yang dipakai pemilik adalah CMD (Cmder), bukan PowerShell. Gunakan perintah CMD (`type`, `dir`, `copy`), bukan `Get-ChildItem` atau `Copy-Item`.

## 3. Aturan bisnis (jangan diubah tanpa diskusi dan persetujuan pemilik)

**Role**

- Administrator: login dari mana saja, bebas geofence, tidak wajib absen, akses penuh.
- Anggota: login hanya di dalam radius posko, hanya melihat data miliknya sendiri.
- Otorisasi ditegakkan di backend (middleware `role` dan `aktif`), bukan hanya menyembunyikan tombol.

**Geofence dan lokasi**

- Radius geofence: 50 meter, dihitung Haversine di backend, diperiksa saat login anggota dan saat kirim absensi.
- Batas akurasi GPS: **500 meter**. Nilai ini diubah dari 300 menjadi 500 oleh admin pada 2026-11-06 (tercatat di Riwayat Aktivitas). Nilai aktual dibaca dari pengaturan posko di database, jadi cek lagi jika ragu.
- Koordinat dan radius disimpan di database (tabel `settings`), bukan di kode. Jangan ditulis di dokumentasi.
- GPS mati atau izin ditolak: proses ditolak.
- GPS spoofing masih mungkin. Ini keterbatasan yang diketahui.

**Kategori jadwal** (hanya menentukan hari wajib, bukan jam)

- Stay: wajib Senin sampai Minggu.
- Shift Malam: wajib Senin sampai Minggu.
- Weekend: wajib Sabtu dan Minggu.
- Hari tidak wajib tidak dihitung Tidak Hadir. Logika ada di `App\Services\ScheduleService`.

**Waktu dan status**

- Zona waktu `Asia/Jakarta`, absensi 00.00 sampai 23.59 WIB.
- Satu baris absensi per anggota per tanggal (unique `user_id` + `attendance_date`).
- Status tersimpan: `hadir` dan `tidak_hadir`.
- `Menunggu` adalah status hitungan (hari wajib, belum ada baris), tidak disimpan di database.
- Tidak ada status Terlambat.
- Penetapan Tidak Hadir otomatis: command `absensi:tutup-hari`, dijadwalkan 00:05 setiap hari.

**Alur absensi**

1. Anggota membuka link absensi (dari QR atau grup).
2. Login, geofence diperiksa.
3. Dashboard anggota, tombol Absen Sekarang.
4. Halaman absen membuka kamera depan, anggota mengambil selfie.
5. Selfie dikirim bersama lokasi. Server memvalidasi lokasi, selfie, dan duplikasi.
6. Jika valid, tersimpan sebagai Hadir.

QR hanya pintu masuk ke `/anggota/absen`. Token QR tidak diperiksa saat kirim absensi. Jangan menghidupkan kembali aturan QR wajib tanpa persetujuan pemilik.

**Selfie**

- Wajib dari kamera langsung, JPEG, maksimum 5 MB.
- Disimpan di storage privat, diakses lewat route `selfie.show` yang memeriksa hak akses (admin melihat semua, anggota hanya miliknya).
- Tidak ada face recognition.

**Koreksi absensi admin** (sesuai kode `AttendanceCorrectionController`)

- Status yang bisa dipilih: Hadir atau Tidak Hadir.
- Alasan wajib 5 sampai 255 karakter, dicatat di Riwayat Aktivitas (`koreksi_absensi`).
- Hadir boleh sampai hari ini. Tidak Hadir hanya untuk hari yang sudah berakhir dan merupakan hari wajib anggota itu.
- Status yang sama ditolak. Baris hasil koreksi tidak memiliki jam, lokasi, dan selfie.
- Proses memakai transaction dan `lockForUpdate`.

**Mode "Masuk sebagai anggota"** (permintaan pemilik, sesi 7)

- Hanya admin yang bisa memakainya, hanya ke anggota yang aktif.
- Tujuannya hanya melihat tampilan, karena GPS laptop sering ditolak.
- Mode ini melewati geofence login anggota. Ini disengaja dan hanya berlaku bagi admin yang sudah login.
- Selama mode aktif, **kirim absensi dan ganti password diblokir** (middleware `bukan.impersonasi`).
- Awal dan akhir sesi dicatat (`impersonasi_mulai`, `impersonasi_selesai`).
- Banner kuning dan tombol "Kembali ke admin" tampil di layout. Tombol Keluar di navbar tetap logout penuh.

**Status Izin: belum dibuat.** Rancangan awal: ditetapkan admin lewat formulir koreksi, alasan disimpan di kolom baru (butuh migration), boleh untuk tanggal ke depan, ditolak di hari tidak wajib, dibedakan dari Tidak Hadir di tampilan. Migration hanya setelah pemilik menulis "setuju migration". Perhitungan Izin di laporan diputuskan di Phase 12.

**Keputusan fitur**

- Pemilik pernah memutuskan tidak memerlukan fitur ganti password dan pembatasan percobaan login. Namun di kode aktual keduanya ada: route `ganti-password` dan pembatasan login 5 percobaan per 60 detik (kunci username + IP) di `LoginController`. Jangan dihapus atau diubah sepihak, tanyakan pemilik jika perlu keputusan.

## 4. Struktur kode aktual (terverifikasi dari isi file dan `route:list` pada sesi 7)

**Controller**

- `Auth/LoginController`, `Auth/PasswordController`.
- `Admin/DashboardController` (ringkasan hari ini), `Admin/MemberController`, `Admin/AttendanceController` (Data Absensi), `Admin/AttendanceCorrectionController`, `Admin/ImpersonationController`, `Admin/QrController`, `Admin/SettingController`, `Admin/ActivityLogController`.
- `Anggota/DashboardController`, `Anggota/AbsenController`.
- `SelfieController`.

**Middleware**

- `role` (EnsureRole), `aktif` (EnsureActive), `bukan.impersonasi` (BlockWhenImpersonating).
- `trustProxies` untuk `127.0.0.1` (disiapkan untuk uji lewat tunnel HTTPS).

**Service dan model**

- Service: `ScheduleService`, `GeofenceService`.
- Model: `User`, `Attendance` (konstanta `STATUS_HADIR`, `STATUS_TIDAK_HADIR`), `ActivityLog`.
- Kolom `users` yang dipakai: `name`, `username`, `password`, `role`, `schedule_category`, `is_active`.
- Kolom `attendances` yang terlihat di kode: `user_id`, `attendance_date`, `status`, `category_snapshot`, `attended_at`, `distance_m`, `accuracy_m`, `selfie_path`.
- `activity_logs`: `user_id` (nullable), `action` (string), `description` (text), `ip_address`, `created_at`. Kolom `action` bertipe string, jadi kode aksi baru tidak butuh migration.
- Struktur lengkap tabel `settings` dan `qr_sessions` belum dibaca ulang.

**Route (27 route, dari `route:list`)**

| Area                 | Route utama                                                                                                                                                                    |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Umum                 | `/`, `login`, `logout`, `ganti-password`, `kembali-ke-admin`, `selfie/{attendance}`                                                                                            |
| Admin (`/admin`)     | dashboard, `anggota` (daftar, tambah, ubah, status, reset password, masuk-sebagai), `riwayat-aktivitas`, `pengaturan-posko`, `qr`, `absensi`, `absensi/koreksi` (GET dan POST) |
| Anggota (`/anggota`) | dashboard, `absen`, `absen/lokasi` (POST, throttle 20 per menit)                                                                                                               |

**Tampilan**

- Layout `layouts/app.blade.php` memiliki navbar (menu berbeda untuk admin dan anggota), tombol Keluar, banner mode lihat tampilan, serta alert sukses dan error bersama. Halaman tidak perlu membuat alert sendiri.
- Dashboard admin: kartu ringkasan (anggota aktif, wajib hadir hari ini, sudah hadir, menunggu), daftar yang belum absen, aksi cepat.
- Dashboard anggota: status hari ini, tombol Absen Sekarang, statistik pribadi, riwayat 10 hari terakhir.

## 5. Status fitur dan bukti

Tingkat status: Direncanakan, Dibuat, Dijalankan, Diuji, Berhasil, Siap digunakan.

| Fitur                                                                 | Status              | Bukti                                                                                                                 |
| --------------------------------------------------------------------- | ------------------- | --------------------------------------------------------------------------------------------------------------------- |
| Login dengan geofence, ditolak jika akurasi buruk atau di luar radius | Berhasil            | Riwayat Aktivitas mencatat `login_ditolak_lokasi` (`poor_accuracy`, `outside_radius`, `no_gps`)                       |
| Absen dari HP (selfie + lokasi)                                       | Berhasil            | Riwayat Aktivitas mencatat `absen_berhasil` pada 2026-11-06                                                           |
| Pengaturan posko dan perubahan batas akurasi                          | Berhasil            | Riwayat Aktivitas mencatat `ubah_pengaturan_posko`                                                                    |
| Navbar dan layout baru (admin dan anggota)                            | Berhasil            | Tangkapan layar sesi 7                                                                                                |
| Dashboard admin (ringkasan hari ini)                                  | Berhasil (tampilan) | Tangkapan layar. Penghitungan Hadir dan Tidak Hadir baru terlihat pada nilai 0                                        |
| Dashboard anggota (status, statistik, riwayat)                        | Berhasil (tampilan) | Tangkapan layar lewat mode Masuk sebagai                                                                              |
| Mode Masuk sebagai: mulai, kembali, pencatatan log                    | Berhasil            | Tangkapan layar dan Riwayat Aktivitas                                                                                 |
| Blokir kirim absen dan ganti password saat mode aktif                 | Dibuat, belum diuji | Hanya kode                                                                                                            |
| Formulir koreksi absensi tampil                                       | Berhasil            | Tangkapan layar                                                                                                       |
| Kolom Aksi dan tombol Koreksi di Data Absensi                         | Dibuat              | Tombol atas terlihat. Tombol per baris belum terlihat karena tabel kosong, dan belum mengisi anggota/tanggal otomatis |
| Penyimpanan koreksi absensi                                           | Dibuat, belum diuji | Hanya kode                                                                                                            |
| `absensi:tutup-hari`                                                  | Dibuat              | Terdaftar di jadwal harian. Logika penetapan Tidak Hadir belum terbukti                                               |
| Status Izin                                                           | Direncanakan        | Belum dibuat                                                                                                          |
| Laporan dan ekspor Excel/PDF                                          | Direncanakan        | Belum dimulai                                                                                                         |
| Hosting                                                               | Direncanakan        | Belum ada keputusan                                                                                                   |

Pemilik proyek memutuskan melewati pengujian detail untuk mempercepat penyelesaian. Semua yang berstatus "Dibuat, belum diuji" harus dianggap belum terbukti.

## 6. Roadmap

| Phase      | Nama                                                                    | Status                                                              |
| ---------- | ----------------------------------------------------------------------- | ------------------------------------------------------------------- |
| 0 sampai 7 | Discovery, analisis, desain, fondasi, autentikasi, geofence, jadwal, QR | Selesai dan di-commit                                               |
| 8          | Selfie                                                                  | Dibuat dan terbukti dari absensi HP                                 |
| 9          | Status dan otomasi                                                      | Penyimpanan Hadir terbukti. `tutup-hari` dan koreksi belum terbukti |
| 10         | Dashboard admin                                                         | Sebagian: Data Absensi, ringkasan hari ini, koreksi                 |
| 11         | Dashboard anggota                                                       | Sebagian: status, statistik, riwayat tampil                         |
| 12         | Laporan dan ekspor                                                      | Belum dimulai                                                       |
| 13         | Keamanan dan pengujian                                                  | Belum dilakukan secara menyeluruh                                   |
| 14         | Deployment                                                              | Belum dimulai                                                       |
| 15         | Rilis dan dokumentasi                                                   | Belum dimulai                                                       |

## 7. Git

- Commit terakhir yang terbukti ter-push: `4c7e88b` (pembaruan dokumentasi sesi 5).
- Sesi 7: commit lokal `8ac8528` berisi navbar, dashboard admin dan anggota, koreksi absensi, dan mode Masuk sebagai. Commit ini belum di-push saat dokumen ini ditulis.
- Pembaruan dokumen ini belum di-commit.
- Git di Windows menampilkan peringatan CRLF ke LF. Itu normal dan tidak perlu tindakan.
- Tidak ada migration baru pada sesi 6 dan 7.

## 8. Catatan teknis dan masalah yang diketahui

- Bug diperbaiki: Beranda anggota dulu menentukan Hadir dari `attended_at`, sehingga absensi hasil koreksi admin (tanpa jam) tampil sebagai Tidak Hadir. Sekarang memakai kolom `status`.
- `AttendanceCorrectionController` memiliki satu baris `use ... DashboardController as AdminDashboardController;` yang tidak terpakai. Tidak berbahaya, sebaiknya dihapus.
- Nilai cadangan radius di `GeofenceService` kemungkinan masih 30 (belum disamakan dengan 50). Verifikasi.
- Pastikan blok alert sukses dan error di `admin/attendances/index.blade.php` tidak ganda dengan layout.
- GPS laptop sering ditolak (`poor_accuracy`). Gunakan mode Masuk sebagai untuk melihat tampilan anggota di laptop. Akurasi GPS asli hanya bisa diuji dari smartphone.
- Uji kamera dan GPS di HP butuh HTTPS. Cloudflare Quick Tunnel pernah dipakai untuk uji. Alamat tunnel bersifat sementara dan tidak boleh dicatat atau dipublikasikan.

## 9. Keputusan terbuka dan pekerjaan berikutnya

**Hosting.** Pemilik ingin hosting gratis selama sekitar satu bulan. Belum ada keputusan. Syarat dari kode: PHP 8.3 atau lebih baru, HTTPS, MySQL, cron tiap menit untuk `schedule:run`, akses SSH untuk composer dan migrate, penyimpanan selfie permanen yang tidak dibuka publik. Hasil pengecekan sebelumnya (belum diuji langsung, syarat bisa berubah): InfinityFree tanpa SSH dan cron sehingga Tidak Hadir otomatis tidak jalan, Vercel berfilesystem hanya-baca, VM gratis cloud besar memenuhi syarat tetapi perlu pemasangan manual.

**Persiapan sebelum hosting:** `APP_DEBUG=false`, `APP_ENV=production`, HTTPS, scheduler untuk `absensi:tutup-hari`, storage selfie privat, backup database dan file, ganti password admin yang kuat, uji singkat login, absen, dan lihat hasil dengan akun anggota.

**Urutan langkah yang disepakati (satu langkah kecil per giliran):**

1. Cek `git status`, lalu push commit `8ac8528` dan pembaruan dokumen setelah persetujuan eksplisit pemilik.
2. Isi otomatis anggota dan tanggal pada tombol Koreksi per baris (controller koreksi sudah menerima `?member=` dan `?date=`).
3. Statistik dashboard admin lanjutan dan perapian UI/UX halaman lain (login, form anggota, pengaturan, QR).
4. `php artisan absensi:tutup-hari --dry-run`. Jangan dijalankan tanpa `--dry-run` sebelum perilakunya dipastikan, karena akun pemilik adalah akun anggota sungguhan.
5. Laporan dan ekspor sederhana (Phase 12).
6. Persiapan dan uji hosting dengan 18 akun.
7. Status Izin setelah hosting, supaya tidak menahan rilis.

## 10. Cara kerja yang diminta pemilik

- Bahasa Indonesia sederhana, satu langkah utama per giliran, jawaban singkat.
- Awali jawaban dengan satu baris **YANG HARUS KAMU LAKUKAN SEKARANG: ...**.
- Beri isi file lengkap untuk file yang diganti, bukan potongan per bagian. Backup dulu file yang akan ditimpa.
- Jangan meminta isi `.env`, password, token, atau secret.
- Commit dan push hanya dengan persetujuan jelas. `git restore`, `reset`, dan force push hanya setelah "setuju restore". Migration hanya setelah "setuju migration". `migrate:fresh`, `db:wipe`, dan hapus tabel dilarang tanpa persetujuan khusus.
- Bedakan dengan jelas: Terbukti, Klaim pemilik, Dibuat, Dijalankan, Diuji, Berhasil, Direncanakan. Jangan menyatakan fitur selesai hanya karena kode, route, atau view sudah ditulis.

## 11. Langkah pertama sesi berikutnya

Jalankan dari `C:\laragon\www\kkn-management-system`:

```
git status
git log --oneline -3
```

Bandingkan dengan bagian 7. Jika ada perubahan yang belum di-commit, lindungi dulu dan jangan menyarankan `restore`, `reset`, atau `checkout`. Setelah itu lanjutkan sesuai bagian 9.

---

## Sesi 8 — 2026-11-07

### Dikerjakan

- Status **Izin** (keputusan: admin yang menetapkan, lewat formulir Koreksi / Izin).
- Dashboard admin dengan statistik lanjutan.
- Halaman **Laporan** dan ekspor Excel/PDF.
- Package ekspor dipasang setelah dry-run kompatibilitas.

### Database

- Migration baru: `2026_11_07_115610_add_reason_to_attendances_table` (kolom `reason`, string 255, nullable, setelah `status`). Dijalankan (DONE). Kolom `status` bertipe string(20), nilai baru `izin`.
- Cadangan sebelum migration: di folder temporer komputer pemilik (tidak di Git).

### Package baru

- `maatwebsite/excel` 4.0.6 (membawa `phpoffice/phpspreadsheet` 5.10.0) dan `barryvdh/laravel-dompdf` v3.1.2 (membawa `dompdf/dompdf` v3.1.6). Total 13 paket baru, tanpa paket lama berubah, tanpa advisory keamanan.

### File dibuat

- `app/Services/ReportService.php`, `app/Http/Controllers/Admin/ReportController.php`
- `resources/views/admin/reports/index.blade.php`, `resources/views/admin/reports/pdf.blade.php`
- `database/migrations/2026_11_07_115610_add_reason_to_attendances_table.php`

### File diubah

- `app/Models/Attendance.php` (`STATUS_IZIN`, `reason` di fillable)
- `app/Http/Controllers/Admin/AttendanceCorrectionController.php` (status Izin, tanggal ke depan khusus Izin, validasi hari wajib)
- `app/Http/Controllers/Admin/DashboardController.php` dan `resources/views/admin/dashboard.blade.php`
- `app/Http/Controllers/Anggota/DashboardController.php` dan `resources/views/anggota/dashboard.blade.php`
- `resources/views/admin/attendances/correct.blade.php`, `resources/views/admin/attendances/index.blade.php`
- `routes/web.php` (route laporan, excel, pdf), `resources/views/layouts/app.blade.php` (menu Laporan)
- `composer.json`, `composer.lock`

### Rumus (keputusan sesi 8)

Kehadiran = Hadir ÷ (Hadir + Tidak Hadir). Izin dan Menunggu tidak dihitung. Dashboard admin dan Laporan menghitung Tidak Hadir dari jadwal; dashboard anggota masih dari baris tersimpan (belum disamakan).

### Pengujian

| Item                                                   | Status                                     |
| ------------------------------------------------------ | ------------------------------------------ |
| Izin tersimpan dan tampil di Data Absensi              | Terbukti (tangkapan layar)                 |
| Dashboard admin baru                                   | Terbukti (tangkapan layar)                 |
| Halaman Laporan di layar                               | Terbukti (tangkapan layar)                 |
| `absensi:tutup-hari --dry-run` (06/11)                 | Terbukti; versi sungguhan tidak dijalankan |
| Unduh Excel dan PDF                                    | Klaim pemilik, belum ada bukti             |
| Izin di beranda anggota                                | Dibuat, belum diuji                        |
| Tombol Koreksi per baris (isi otomatis)                | Dibuat, belum diuji                        |
| Koreksi Hadir/Tidak Hadir; blokir saat "Masuk sebagai" | Dibuat, belum diuji                        |

### Git

- Terakhir terverifikasi: `main` = `origin/main` di `ea53378`.
- Perubahan sesi 8 BELUM di-commit (pemilik memilih "tunda"). Verifikasi dengan `git status`. Perubahan `docs/MASTER_PROMPT_KKN_MANAGEMENT_SYSTEM_UPDATED.md` juga belum di-commit.

### Pending

- Verifikasi ekspor Excel/PDF dan uji tampilan Izin sisi anggota.
- Samakan perhitungan beranda anggota dengan rumus berbasis jadwal.
- Rapikan pesan "sudah tercatat absen" untuk status Izin.
- Samakan nilai cadangan radius `GeofenceService` dengan 50.
- Phase 13 (uji keamanan) dan Phase 14 (hosting, scheduler, backup, uji 18 akun).
- Keputusan commit menunggu pemilik.

### Pembaruan akhir sesi 8

- Git: `main` = `origin/main` di commit `d94e6d6` (sudah di-push). Pernyataan "belum di-commit" di blok sebelumnya sudah tidak berlaku.
- Unduh Excel dan PDF di Laporan: berhasil (konfirmasi pemilik).

---

## Sesi 9 — 2026-11-07

### Dikerjakan

- Status **Libur** (admin menetapkan per anggota lewat Koreksi / Izin), tanpa migration karena `status` bertipe string.
- Beranda anggota dihitung lewat `ReportService` (parameter baru `$userId`), sama dengan Laporan.
- Pemeriksaan scheduler hosting. Tema **Karaksa** tahap 1 (SCSS, layout, logo, favicon) dan menu burger khusus HP.

### File dibuat/diubah

- Dibuat: `public/images/logo-karaksa30.jpg`.
- Diubah: `app/Models/Attendance.php` (`STATUS_LIBUR`), `Admin/AttendanceCorrectionController.php`, `Admin/DashboardController.php`, `Anggota/DashboardController.php`, `app/Services/ReportService.php` (kolom `libur`, `$userId`), `resources/scss/app.scss`, `resources/views/layouts/app.blade.php`, `admin/attendances/correct.blade.php`, `admin/attendances/index.blade.php`, `admin/dashboard.blade.php`, `anggota/dashboard.blade.php`, dua file dokumentasi.
- Tidak ada migration baru pada sesi 9.

### Pengujian

| Item                                      | Status                                         |
| ----------------------------------------- | ---------------------------------------------- |
| Unduh Excel dan PDF                       | Berhasil (konfirmasi pemilik)                  |
| Izin di beranda anggota                   | Terbukti (tangkapan layar)                     |
| Tombol Koreksi per baris                  | Terbukti (tangkapan layar)                     |
| Beranda anggota sama dengan Laporan       | Konfirmasi pemilik                             |
| Scheduler `5 0 * * *`, Asia/Jakarta       | Terbukti (`schedule:list`)                     |
| ParseError dashboard admin (`@if` inline) | Diperbaiki; dashboard tampil (tangkapan layar) |
| Tema Karaksa tahap 1                      | Tampil (tangkapan layar)                       |
| Libur                                     | Dibuat, belum terbukti                         |
| Burger HP dan menu horizontal laptop      | Dibuat, belum terbukti                         |
| Kolom Libur di Laporan/PDF/Excel          | Belum dibuat                                   |

### Git

- Sebelum sesi 9: `origin/main` = `d94e6d6`. Perubahan sesi 9 di-commit atas persetujuan pemilik; push belum disetujui. Verifikasi dengan `git log --oneline -3`.

### Pending

- Konfirmasi Libur, burger, dan `npm run build`.
- Kolom Libur di Laporan/PDF/Excel.
- Tema tahap 2 (Login) sampai 6 (PDF kop logo), font lokal.
- Cek nilai cadangan radius `GeofenceService` (belum dilihat).
- Hosting (cron `schedule:run` tiap menit, production, HTTPS, backup, uji 18 akun); pertimbangkan repo private.
