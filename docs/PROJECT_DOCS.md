# PROJECT DOCS — KKN MANAGEMENT SYSTEM

Modul aktif: Sistem Absensi Digital KKN Kelompok 30 UNTARA.

Terakhir diperbarui: 2026-11-07 (akhir sesi 10).

> **Aturan baca.** Dokumen ini adalah snapshot. Kondisi repository, kode aktual, hasil terminal, dan hasil pengujian terbaru adalah sumber kebenaran. Jika bagian ini bertentangan dengan kode, ikuti kode lalu perbarui dokumen.
>
> **Repository PUBLIC.** Dilarang menulis password, token QR, isi `.env`, koordinat posko, alamat tunnel, nama pengguna cPanel, nama server atau IP hosting, alamat domain produksi, nama database proyek lain, dan data pribadi anggota (nama lengkap, username, NIM, nomor HP, alamat).
>
> Dokumen ini dirapikan pada sesi 10: bagian lama yang sudah tidak berlaku (misalnya "status Izin belum dibuat") sudah diganti. Riwayat lengkap ada di Git.

---

## 1. Identitas proyek

- Nama aplikasi: KKN MANAGEMENT SYSTEM. Modul aktif: Sistem Absensi Digital KKN Kelompok 30 UNTARA (tim "Karaksa").
- Pemilik, developer, dan admin utama: pemilik proyek (masih belajar Laravel). Pengguna: 18 anggota KKN dan 1 admin.
- Folder lokal: `C:\laragon\www\kkn-management-system`. Database lokal: `kkn_management_system` (server MySQL lokal dipakai bersama proyek lain, jangan menyentuh database lain).
- Repository: `https://github.com/elrafdinata/kkn-management-system` (PUBLIC), branch `main`.
- KARAKSA WASTE adalah aplikasi terpisah dan tidak dicampur dengan proyek ini.

## 2. Teknologi

| Komponen             | Versi                                                                             |
| -------------------- | --------------------------------------------------------------------------------- |
| Sistem operasi lokal | Windows, Laragon, VS Code                                                         |
| Laravel              | 13.40.0                                                                           |
| PHP lokal            | 8.5.8                                                                             |
| MySQL lokal          | 8.0.30                                                                            |
| Composer             | 2.10.3                                                                            |
| Node.js / NPM        | 22.23.1 / 10.9.8                                                                  |
| Frontend             | Blade + Bootstrap 5 lewat Vite (`resources/scss/app.scss`, `resources/js/app.js`) |
| Package ekspor       | `maatwebsite/excel` 4.0.6, `barryvdh/laravel-dompdf` v3.1.2                       |
| Hosting              | AnymHost Developer Hosting, paket Newbie (cPanel), PHP CLI 8.4.27                 |

Terminal lokal pemilik adalah CMD (Cmder), bukan PowerShell. Gunakan `type`, `dir`, `copy`. Laravel 13 butuh PHP 8.3 atau lebih baru.

## 3. Aturan bisnis (jangan diubah tanpa diskusi dan persetujuan pemilik)

**Role**

- Administrator: login dari mana saja, bebas geofence, tidak wajib absen, akses penuh.
- Anggota: login hanya di dalam radius posko, hanya melihat data miliknya sendiri.
- Otorisasi ditegakkan di backend (middleware `role` dan `aktif`).

**Geofence dan lokasi**

- Radius geofence: 50 meter, Haversine di backend, diperiksa saat login anggota dan saat kirim absensi.
- Batas akurasi GPS: 500 meter (diubah admin dari 300 pada 2026-11-06; nilai aktual dibaca dari pengaturan posko di database).
- Koordinat dan radius disimpan di database (tabel `settings`), diisi lewat halaman Pengaturan. Jangan ditulis di dokumentasi atau repo.
- GPS mati atau izin ditolak: proses ditolak. GPS spoofing masih mungkin (keterbatasan yang diketahui).

**Kategori jadwal** (hanya menentukan hari wajib, bukan jam)

- Stay dan Shift Malam: wajib Senin sampai Minggu. Weekend: wajib Sabtu dan Minggu.
- Hari tidak wajib tidak dihitung Tidak Hadir. Logika di `App\Services\ScheduleService`.

**Waktu dan status**

- Zona waktu `Asia/Jakarta`, absensi 00.00 sampai 23.59 WIB. Satu baris per anggota per tanggal (unique `user_id` + `attendance_date`).
- Status tersimpan: `hadir`, `tidak_hadir`, `izin`, `libur`. `Menunggu` adalah status hitungan (hari wajib, belum ada baris), tidak disimpan. Tidak ada status Terlambat.
- Penetapan Tidak Hadir otomatis: command `absensi:tutup-hari`, dijadwalkan `5 0 * * *` (00:05, Asia/Jakarta). Command hanya membuat baris untuk anggota yang belum punya baris, jadi Izin dan Libur tidak tertimpa.

**Izin** (ditetapkan admin)

- Lewat formulir Koreksi / Izin. Alasan wajib 5 sampai 255 karakter di kolom `reason`, dicatat di Riwayat Aktivitas (`koreksi_absensi`). Boleh tanggal ke depan. Ditolak di hari tidak wajib anggota. Anggota tidak mengajukan sendiri.

**Libur** (permintaan pemilik, per anggota, ditetapkan admin)

- Status `libur` lewat formulir yang sama, aturan alasan dan tanggal sama dengan Izin.
- Hari Libur dikeluarkan dari hari wajib: tidak Tidak Hadir, tidak masuk persentase, tidak Menunggu. Dashboard admin menampilkan "N libur" pada kartu wajib hadir. Anggota melihat badge Libur dan keterangan.

**Rumus kehadiran**

- Hadir ÷ (Hadir + Tidak Hadir). Izin, Libur, dan Menunggu tidak masuk persentase.
- Tidak Hadir dihitung dari jadwal: hari wajib yang sudah lewat tanpa baris Hadir/Izin/Libur. Hari ini tanpa baris = Menunggu. Hari sebelum akun dibuat dan masa depan tidak dihitung. Laporan maksimal 366 hari.
- Dashboard admin per kategori: Hadir ÷ (hari wajib − Izin), dengan Libur sudah dikeluarkan dari hari wajib.

**Alur absensi**

1. Anggota membuka link absensi (dari QR atau grup), login, geofence diperiksa.
2. Dashboard anggota, tombol Absen Sekarang, halaman absen membuka kamera depan, anggota mengambil selfie.
3. Selfie dikirim bersama lokasi. Server memvalidasi lokasi, selfie, dan duplikasi. Jika valid, tersimpan sebagai Hadir.

QR hanya pintu masuk ke `/anggota/absen`; token QR tidak diperiksa saat kirim absensi. Jangan menghidupkan kembali aturan QR wajib tanpa persetujuan pemilik.

**Selfie:** wajib dari kamera langsung, JPEG, maksimum 5 MB, storage privat, diakses lewat route `selfie.show` (admin melihat semua, anggota hanya miliknya). Tidak ada face recognition.

**Koreksi admin:** Hadir, Tidak Hadir, Izin, atau Libur dengan alasan wajib dan log. Hadir boleh sampai hari ini. Tidak Hadir hanya untuk hari yang sudah berakhir dan hari wajib anggota itu. Status yang sama ditolak. Hadir ke Izin atau Libur membiarkan selfie dan lokasi lama di baris itu. Proses memakai transaction dan `lockForUpdate`.

**Mode "Masuk sebagai anggota":** hanya admin, hanya ke anggota aktif, hanya untuk melihat tampilan. Kirim absensi dan ganti password diblokir (middleware `bukan.impersonasi`). Awal dan akhir sesi dicatat. Jangan dicabut tanpa persetujuan.

**Keputusan fitur:** kode memiliki route `ganti-password` dan pembatasan login 5 percobaan per 60 detik meski pemilik pernah bilang tidak perlu. Jangan dihapus sepihak.

## 4. Struktur kode

**Controller:** `Auth/LoginController`, `Auth/PasswordController`; `Admin/DashboardController` (statistik hari ini, 7 hari, per kategori, Tidak Hadir terbanyak), `Admin/MemberController`, `Admin/AttendanceController` (Data Absensi), `Admin/AttendanceCorrectionController`, `Admin/ImpersonationController`, `Admin/QrController`, `Admin/SettingController`, `Admin/ActivityLogController`, `Admin/ReportController` (Laporan, Excel, PDF); `Anggota/DashboardController`, `Anggota/AbsenController`; `SelfieController`.

**Middleware:** `role`, `aktif`, `bukan.impersonasi`, `trustProxies`.

**Service dan model:** `ScheduleService`, `GeofenceService`, `ReportService` (menghitung hadir, izin, libur, tidak hadir, menunggu, persentase; dipakai Laporan, PDF, Excel, dan beranda anggota lewat parameter `$userId`). Model: `User`, `Attendance` (konstanta `STATUS_HADIR`, `STATUS_TIDAK_HADIR`, `STATUS_IZIN`, `STATUS_LIBUR`), `ActivityLog`. Command: `absensi:tutup-hari`.

**Database (ringkas):** `users` (`name`, `username`, `password`, `role`, `schedule_category`, `is_active`); `attendances` (`user_id`, `attendance_date`, `status` string(20), `reason` string(255) nullable, `category_snapshot`, `attended_at`, `distance_m`, `accuracy_m`, `selfie_path`); `activity_logs` (`action` bertipe string, kode aksi baru tidak butuh migration); `settings`, `qr_sessions`. Migration sesi 8: `2026_11_07_115610_add_reason_to_attendances_table`. Tidak ada migration baru pada sesi 9 dan 10.

**Route:** 27 route pada sesi 7; sesi 8 menambah route laporan, excel, dan pdf. Daftar terbaru lewat `php artisan route:list`.

**Tampilan:** layout `layouts/app.blade.php` (navbar admin dan anggota, tombol Keluar, banner mode lihat tampilan, alert bersama; menu horizontal di laptop, burger di HP kurang dari 768px). Dashboard admin dan anggota, Data Absensi, formulir Koreksi / Izin, Laporan (`admin/reports/index` dan `pdf`). Tema Karaksa: `resources/scss/app.scss`, logo `public/images/logo-karaksa30.jpg`.

## 5. Status fitur dan bukti

Tingkat: Direncanakan, Dibuat, Diuji, Berhasil (terbukti).

| Fitur                                                            | Status                 | Bukti                                                    |
| ---------------------------------------------------------------- | ---------------------- | -------------------------------------------------------- |
| Login dengan geofence                                            | Berhasil               | Riwayat Aktivitas (`login_ditolak_lokasi`)               |
| Absen dari HP (selfie + lokasi)                                  | Berhasil               | Riwayat Aktivitas, 2026-11-06                            |
| Pengaturan posko                                                 | Berhasil               | Riwayat Aktivitas                                        |
| Navbar, layout, dashboard anggota, Masuk sebagai                 | Berhasil               | Tangkapan layar sesi 7                                   |
| Status Izin tersimpan dan tampil (Data Absensi, beranda anggota) | Berhasil               | Tangkapan layar sesi 8 dan 9                             |
| Tombol Koreksi per baris mengisi anggota dan tanggal             | Berhasil               | Tangkapan layar sesi 9                                   |
| Dashboard admin statistik, halaman Laporan                       | Berhasil (tampilan)    | Tangkapan layar sesi 8 dan 9                             |
| Unduh Excel dan PDF Laporan                                      | Berhasil               | Konfirmasi pemilik                                       |
| Beranda anggota sama dengan Laporan                              | Berhasil               | Konfirmasi pemilik                                       |
| Scheduler `5 0 * * *` lokal                                      | Berhasil               | `schedule:list`                                          |
| `absensi:tutup-hari --dry-run`                                   | Berhasil               | Sesi 8; versi sungguhan belum dijalankan                 |
| Tema Karaksa tahap 1 dan `npm run build`                         | Berhasil               | Tangkapan layar dan output build 2026-11-07              |
| Status Libur                                                     | Dibuat, belum terbukti | Kode saja; belum ada tangkapan layar badge               |
| Kolom Libur di Laporan, PDF, Excel                               | Dibuat, belum terbukti | Commit `f147383`; belum diuji                            |
| Bar progres dashboard memakai `@style`                           | Dibuat, belum terbukti | Pemilik menulis "done"; belum ada bukti tampilan         |
| Burger HP dan menu horizontal laptop                             | Dibuat, belum terbukti | Belum dikonfirmasi                                       |
| Penyimpanan koreksi Hadir/Tidak Hadir lama                       | Dibuat, belum diuji    | Kode saja                                                |
| Blokir kirim absen saat Masuk sebagai                            | Dibuat, belum diuji    | Kode saja                                                |
| Hosting aktif, PHP 8.4.27, ekstensi, Terminal                    | Berhasil               | Hasil `php -v`, `php -m` di Terminal cPanel              |
| Cron `schedule:run` di hosting                                   | Belum diperiksa        | Halaman Cron Jobs dibuka; opsi interval belum dilihat    |
| Deploy aplikasi di hosting                                       | Belum dimulai          | Belum ada database, kode, `.env`, atau migrasi di server |

## 6. Roadmap

| Phase      | Nama                                                                    | Status                                                          |
| ---------- | ----------------------------------------------------------------------- | --------------------------------------------------------------- |
| 0 sampai 7 | Discovery, analisis, desain, fondasi, autentikasi, geofence, jadwal, QR | Selesai dan di-commit                                           |
| 8          | Selfie                                                                  | Selesai (terbukti dari absensi HP)                              |
| 9          | Status dan otomasi                                                      | Hadir, Izin, Libur ada; `tutup-hari` sungguhan belum dijalankan |
| 10         | Dashboard admin                                                         | Selesai (tampilan terbukti)                                     |
| 11         | Dashboard anggota                                                       | Selesai (tampilan terbukti)                                     |
| 12         | Laporan dan ekspor                                                      | Selesai; kolom Libur belum diuji                                |
| 13         | Keamanan dan pengujian                                                  | Belum menyeluruh                                                |
| 14         | Deployment                                                              | Berjalan: hosting aktif, aplikasi belum dipasang                |
| 15         | Rilis dan dokumentasi                                                   | Belum dimulai                                                   |

## 7. Git

- Terverifikasi (2026-11-07): `main` = `origin/main` di `f147383`.
- Urutan commit terbaru: `d94e6d6` (Izin, dashboard admin, Laporan), `4cc1121` (Libur, tema Karaksa tahap 1, burger HP), `f147383` (kolom Libur di Laporan/PDF/Excel, rapikan bar progres). Semua sudah di-push.
- Pembaruan dokumen sesi 10 belum di-commit saat dokumen ini ditulis. Verifikasi dengan `git status`.
- Peringatan CRLF ke LF di Windows itu normal.
- Cek keamanan sebelum push: `git ls-files | findstr /i ".env"` hanya menampilkan `.env.example` (terverifikasi).

## 8. Catatan teknis dan masalah yang diketahui

- Nilai cadangan radius di `GeofenceService` kemungkinan masih 30 (aturan 50). Belum diverifikasi.
- `AttendanceCorrectionController` mungkin masih memiliki `use ... DashboardController as AdminDashboardController;` yang tidak terpakai. Tidak berbahaya.
- GPS laptop sering ditolak (`poor_accuracy`); gunakan mode Masuk sebagai untuk melihat tampilan anggota. Uji kamera dan GPS butuh HTTPS dan smartphone.
- Peringatan "Deprecation Warning" Sass dari Bootstrap saat `npm run build` bukan kesalahan kode proyek.
- VS Code menandai `style="width: {{ ... }}%"` sebagai CSS rusak dan menyarankan `grow` (Tailwind). Keduanya diabaikan; proyek memakai Bootstrap. Bar progres dashboard kini memakai `@style`.
- Blade: jangan taruh `@if ... @endif` di tengah teks satu baris; pakai ekspresi `{{ }}`.

## 9. Hosting

**Keputusan:** AnymHost Developer Hosting paket Newbie (Rp15.000/bulan, bulanan, cPanel). Dipilih karena halaman resminya menyebut SSH, Git, dan Composer; paket Cheap Hosting tidak menyebutnya. Domain `.my.id` didaftarkan (Rp25.000 tahun pertama, perpanjangan Rp50.000 per tahun; anggap tidak ikut garansi). Total bayar hari ini Rp40.000.

**Tanggal penting:** dibeli 2026-11-07; garansi 30 hari dihitung dari tanggal itu; jatuh tempo layanan berikutnya 2026-12-07. Putuskan lanjut atau batal sebelum tanggal itu.

**Terbukti:** layanan Aktif; cPanel dan Terminal bisa dipakai; PHP CLI 8.4.27 (daftar PHP di cPanel sampai 8.5); ekstensi ctype, curl, dom, fileinfo, gd, mbstring, openssl, pdo_mysql, tokenizer, xml, zip aktif; menu Git Version Control ada; email Area Klien terverifikasi.

**Belum diketahui atau belum dikerjakan:**

- Interval terkecil cron. Penilaian: tiap menit ideal; minimal 5 menit aman karena 00:05 kelipatan 5; 10 atau 15 menit atau lebih bisa melewatkan jadwal 00:05 dan jadwal harus diubah (perlu persetujuan).
- Konfirmasi PHP 8.4 untuk domain di MultiPHP Manager (bukan hanya CLI).
- SSL masih self-signed; pasang AutoSSL di SSL/TLS Status sebelum uji HP.
- Database, kode, `composer install --no-dev`, `.env` produksi, document root ke `public`, migrasi, akun admin, pengaturan posko, cron.

**Rencana pemasangan (satu langkah kecil per giliran):**

1. Selesaikan cek cron (Tahap A).
2. Buat database MySQL dan user di cPanel; password disimpan pemilik sendiri, tidak dikirim ke siapa pun.
3. Ambil kode dari GitHub (cPanel Git Version Control atau `git clone` di Terminal).
4. `composer install --no-dev`.
5. Buat `.env` produksi di server: `APP_ENV=production`, `APP_DEBUG=false`, data database. Tidak masuk Git.
6. Putuskan `public/build` (hasil `npm run build`): cek `.gitignore`; pilihan unggah manual, build di server jika Node ada, atau masukkan ke Git dengan persetujuan.
7. Arahkan document root domain ke folder `public` Laravel supaya `.env` dan `storage` tidak bisa dibuka dari browser.
8. "setuju migration", lalu migrasi di database server (kosong).
9. Buat akun admin, isi pengaturan posko lewat halaman Pengaturan.
10. Pasang cron `schedule:run` dengan alamat PHP lengkap (pola contoh di cPanel: `/usr/local/bin/ea-phpXX`; verifikasi versi dan alamat dulu).
11. Uji: login admin, dashboard, Laporan, Excel, PDF; dari HP: login anggota di posko, absen dengan selfie; cek badge Libur, kolom Libur, burger HP; samakan nilai cadangan radius dengan 50; uji akun anggota sungguhan.
12. Ubah repo ke private setelah dipakai sungguhan (tambah deploy key jika server perlu menarik kode).

**Alur update nanti:** `git push` dari laptop (dengan persetujuan) → di server tarik perubahan → `composer install --no-dev` jika paket berubah → migrasi hanya jika ada migration baru dan "setuju migration". Update otomatis lewat webhook tidak dipasang dulu (menambah risiko keamanan).

## 10. Cara kerja yang diminta pemilik

- Bahasa Indonesia sederhana, satu langkah utama per giliran, jawaban singkat.
- Awali jawaban dengan satu baris **YANG HARUS KAMU LAKUKAN SEKARANG: ...**.
- Jangan terlalu banyak meminta tangkapan layar; utamakan instruksi teks. Ingatkan pemilik memotong data pribadi pada tangkapan layar.
- Beri isi file lengkap untuk file yang diganti, bukan potongan. Backup dulu file yang akan ditimpa.
- Jangan meminta isi `.env`, password, token, atau secret.
- Commit dan push hanya dengan persetujuan jelas. `git restore`, `reset`, dan force push hanya setelah "setuju restore". Migration hanya setelah "setuju migration". `migrate:fresh`, `db:wipe`, dan hapus tabel dilarang tanpa persetujuan khusus.
- Bedakan dengan jelas: Terbukti, Klaim pemilik, Dibuat, Diuji, Berhasil, Direncanakan. "done" atau "acc" tanpa bukti adalah klaim pemilik.
- Pemilik tidak mau saran fitur tambahan (daftar penolakan ada di master prompt).

## 11. Langkah pertama sesi berikutnya

Dari `C:\laragon\www\kkn-management-system`:

```
git status
git log --oneline -3
```

Bandingkan dengan bagian 7. Lindungi perubahan yang belum di-commit; jangan menyarankan `restore`, `reset`, atau `checkout`. Lalu tanyakan hasil cek Cron Jobs (isi dropdown Common Settings), dan lanjutkan bagian 9.

---

## Riwayat sesi singkat

**Sesi 8 (2026-11-07):** status Izin (kolom `reason`, migration), dashboard admin dengan statistik, halaman Laporan dan ekspor Excel/PDF, package ekspor dipasang setelah dry-run.

**Sesi 9 (2026-11-07):** status Libur, beranda anggota memakai `ReportService`, pemeriksaan scheduler, tema Karaksa tahap 1, burger HP. Commit `4cc1121`.

**Sesi 10 (2026-11-07):** kolom Libur di Laporan, PDF, Excel dan perbaikan bar progres dashboard (`f147383`); push `4cc1121` dan `f147383`; `npm run build` berhasil; memilih dan membeli AnymHost Newbie; cek PHP, ekstensi, Terminal lulus; cek cron belum selesai; dokumen ini dirapikan.
