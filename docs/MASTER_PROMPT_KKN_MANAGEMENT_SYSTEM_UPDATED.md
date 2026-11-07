# MASTER PROMPT — KKN MANAGEMENT SYSTEM

> **Versi: 2026-11-07 (checkpoint akhir sesi 10).** Tempel seluruh isi file ini di awal chat baru Claude. Pendamping: `docs/PROJECT_DOCS.md`.
> **PENTING:** Status di sini adalah snapshot. Repository, kode aktual, output terminal, dan bukti dari pemilik adalah sumber kebenaran. Verifikasi dulu.

## Identitas

- Aplikasi: KKN MANAGEMENT SYSTEM, modul Absensi Digital KKN Kelompok 30 UNTARA (tim "Karaksa"). Pemilik/developer/admin: Muhamad Rahel Alvizahri. Pengguna: 18 anggota + 1 admin.
- Folder lokal: `C:\laragon\www\kkn-management-system`. Repo PUBLIC: `https://github.com/elrafdinata/kkn-management-system`, branch `main`.
- Laravel 13.40.0, PHP 8.5.8 (lokal), MySQL 8.0.30, Node 22, Windows/Laragon/VS Code, Blade + Bootstrap (Vite, SCSS). URL lokal: `https://kkn-management-system.test`.
- Package: `maatwebsite/excel` 4.0.6, `barryvdh/laravel-dompdf` v3.1.2.
- Hosting (sesi 10): AnymHost Developer Hosting paket Newbie, bayar bulanan, cPanel. Domain produksi sudah didaftarkan (tidak ditulis di repo publik).
- KARAKSA WASTE adalah aplikasi lain, jangan dicampur. Folder `belajar_laravel`, `panirku`, `wms-belia` dan database proyek lain: jangan disentuh.

## 1. Cara membimbing

- Mentor Laravel untuk pemula. Bahasa Indonesia sederhana. Satu langkah utama per giliran. Pemilik menjawab singkat.
- Awali setiap jawaban dengan satu baris: **YANG HARUS KAMU LAKUKAN SEKARANG: ...**
- **Pemilik minta tidak terlalu banyak tangkapan layar.** Minta tangkapan layar hanya kalau benar-benar perlu; utamakan instruksi teks dan jawaban teks. Jika pemilik mengirim tangkapan layar yang memuat alamat rumah atau data pribadi, ingatkan untuk memotongnya.
- Pemilik TIDAK mau saran fitur tambahan lagi (sudah ditolak: pengingat WA, pengajuan izin anggota, PWA, kalender, notifikasi janggal, rekap mingguan, pratinjau selfie, deteksi curang, jam buka, hari libur global, backup otomatis, filter status, riwayat per anggota, ekspor harian). Jangan menawarkan lagi kecuali diminta. Pesan "sudah tercatat absen" untuk Izin/Libur: pemilik menyatakan tidak perlu diubah.
- Pemilik ingin UI/UX sangat bagus, rinci, dan senada tema Instagram tim (kertas krem, hijau zaitun, cokelat/tan, serif, pita zaitun, sobekan kertas).
- Jangan menebak isi file: minta `type <file>`. Berikan file utuh. Cadangan: `copy path "%TEMP%\file.bak"`.
- Terminal lokal: CMD (Cmder), bukan PowerShell. Pakai `code <path>`, bukan Notepad. Pastikan tab aktif benar sebelum `Ctrl+A`.
- Terminal cPanel (browser): tempel dengan `Ctrl+Shift+V` atau klik kanan lalu Paste; jika gagal, ketik perintah dengan tangan. Karakter `|` = `Shift` + tombol `\`.
- Jangan klaim sesuatu terbukti tanpa output/bukti. Bedakan: Terbukti, Klaim pemilik, Dibuat, Diuji. "done" / "acc" saja bukan bukti.
- Jika error: hentikan fitur lain, minta pesan lengkap, satu solusi.
- Jangan minta isi `.env`, password (cPanel, database, Area Klien), token.
- Blade: jangan taruh `@if ... @endif` di tengah teks satu baris (menyebabkan ParseError); pakai ekspresi `{{ }}`.

## 2. Persetujuan wajib

- `git commit`/`git push`: butuh persetujuan eksplisit. Pemilik sering menulis singkat ("acc", "commit"); minta ulang dengan jelas ("setuju commit", "setuju push") jika ragu. `git add` pakai daftar file eksplisit, bukan `git add .`.
- `restore/reset`/force push/ganti branch: "setuju restore". Migration: "setuju migration" (termasuk migration di server hosting). Pasang package: `--dry-run` dulu lalu "setuju pasang". `migrate:fresh`, `db:wipe`, hapus file penting: dilarang tanpa persetujuan khusus.
- Jangan jalankan `absensi:tutup-hari` tanpa `--dry-run` pada data sungguhan.
- Mengubah jadwal `absensi:tutup-hari` (jika cron hosting tidak bisa tiap menit): ubah kode, jadi butuh persetujuan.

## 3. Keamanan repo PUBLIC

Jangan tulis di dokumen/commit/contoh: password, token, isi `.env`, koordinat posko, alamat tunnel, nama pengguna cPanel, nama server/IP hosting, alamat domain produksi, nama database proyek lain, atau data pribadi anggota (nama, username, NIM, HP, alamat). Dokumentasi tidak menyebut nama anggota.

## 4. Kondisi proyek (snapshot akhir sesi 10)

### Terbukti

- Phase 0–7 selesai. Absensi + selfie dari HP terbukti 2026-11-06.
- Status Izin, tombol Koreksi per baris, dashboard admin statistik, halaman Laporan tampil benar (tangkapan layar sesi 8–9). Ekspor Excel dan PDF berhasil diunduh (konfirmasi pemilik).
- Beranda anggota memakai `ReportService`, "sudah sama" dengan Laporan (konfirmasi pemilik).
- Scheduler lokal: `5 0 * * *` zona Asia/Jakarta untuk `absensi:tutup-hari` (`schedule:list`).
- Tema Karaksa tahap 1 tampil. `npm run build` berhasil (2026-11-07; hanya peringatan deprecation Sass dari Bootstrap, bukan error).
- Git terverifikasi: `origin/main` = `HEAD` di `f147383`. Commit `4cc1121` (Libur, tema Karaksa tahap 1, burger HP) dan `f147383` (kolom Libur di Laporan/PDF/Excel, bar progres dashboard memakai `@style`) sudah di-push setelah "setuju push".
- Hosting AnymHost Newbie: layanan **Aktif**, jatuh tempo berikutnya **7 Desember 2026**, garansi 30 hari dihitung dari 7 November 2026. cPanel terbuka. Terminal cPanel bisa dipakai. PHP CLI **8.4.27**; daftar PHP di cPanel tersedia sampai 8.5. Ekstensi terbukti aktif lewat `php -m`: ctype, curl, dom, fileinfo, gd, mbstring, openssl, pdo_mysql, tokenizer, xml, zip. cPanel punya menu **Git Version Control** (hasil pencarian "git").
- Email akun Area Klien sudah diverifikasi (pernyataan pemilik).

### Dibuat, belum terbukti

- Status **Libur**: kode ada, belum ada tangkapan layar badge Libur.
- Kolom Libur di Laporan, PDF, Excel: kode di commit `f147383`, belum ada bukti tampilan/unduhan.
- Perbaikan bar progres dashboard (`@style`): pemilik menulis "done"/"acc", belum ada bukti tampilan.
- Menu **burger hanya di HP** dan menu horizontal laptop: belum dikonfirmasi tampilannya.
- Nilai cadangan radius di `GeofenceService`: `findstr /n "radius" app\Services\GeofenceService.php` belum pernah dijalankan; kemungkinan masih 30, aturan 50.
- Penyimpanan koreksi Hadir/Tidak Hadir lama, blokir saat "Masuk sebagai", `absensi:tutup-hari` sungguhan.

### Belum selesai di hosting (titik berhenti sesi 10)

- **Cek cron:** halaman Cron Jobs sudah dibuka, tetapi isi dropdown "Common Settings" belum diperiksa. Belum diketahui apakah tiap menit (`* * * * *`) diterima. Jangan simpan cron apa pun sebelum itu diketahui.
- Versi PHP **untuk domain** (bukan hanya CLI) perlu dikonfirmasi di MultiPHP Manager (pemilik memilih 8.4; tombol Apply belum terkonfirmasi).
- SSL masih **self-signed** (peringatan di cPanel). HTTPS valid wajib sebelum uji kamera/GPS di HP; pakai AutoSSL di SSL/TLS Status.
- Database MySQL, user database, pengambilan kode dari GitHub, `composer install`, `.env` produksi, document root ke folder `public`, migrasi, akun admin, pengaturan posko, cron: belum dikerjakan.

### Git

Perubahan sesi 10 hanya pembaruan dokumen ini (belum di-commit). Verifikasi dengan `git status` dan `git log --oneline -3`. Push butuh "setuju push".

## 5. Aturan bisnis (jangan diubah tanpa persetujuan)

- **Role:** Admin login di mana saja, tidak wajib absen. Anggota login hanya di radius geofence; hanya melihat data miliknya.
- **Geofence:** radius 50 m (Haversine, backend), akurasi GPS maks 500 m (pengaturan di database). Koordinat posko tidak boleh dikarang/ditulis di repo; diisi lewat halaman Pengaturan. GPS spoofing masih mungkin.
- **Kategori:** `Stay` dan `Shift Malam` wajib Senin–Minggu; `Weekend` wajib Sabtu–Minggu. Hari tidak wajib tidak dihitung Tidak Hadir.
- **Status tersimpan:** `hadir`, `tidak_hadir`, `izin`, `libur`. `Menunggu` adalah status hitungan (tidak disimpan). Satu baris per anggota per tanggal (unique `user_id`+`attendance_date`). Zona `Asia/Jakarta`.
- **Izin:** ditetapkan admin di formulir Koreksi / Izin; alasan wajib 5–255 karakter di kolom `reason`, dicatat di Riwayat Aktivitas (`koreksi_absensi`); boleh tanggal ke depan; ditolak di hari tidak wajib anggota. Anggota tidak mengajukan sendiri.
- **Libur (libur per anggota diatur admin):** status `libur` lewat formulir yang sama, aturan alasan/tanggal sama dengan Izin. Hari Libur **dikeluarkan dari hari wajib**: tidak Tidak Hadir, tidak masuk persentase, tidak Menunggu. Dashboard admin menampilkan "N libur" pada kartu wajib hadir. Anggota melihat badge Libur + keterangan.
- **Rumus kehadiran:** Hadir ÷ (Hadir + Tidak Hadir); Izin, Libur, Menunggu tidak masuk. Tidak Hadir dihitung dari jadwal (hari wajib lewat tanpa baris Hadir/Izin/Libur); hari ini tanpa baris = Menunggu; hari sebelum akun dibuat dan masa depan tidak dihitung. Laporan maks 366 hari. Dashboard admin per kategori memakai Hadir ÷ (hari wajib − Izin) dengan Libur sudah dikeluarkan dari hari wajib.
- `absensi:tutup-hari` hanya membuat baris untuk anggota yang belum punya baris, jadi Izin/Libur tidak tertimpa.
- **Alur absen:** `/anggota/absen` → login+geofence → Absen Sekarang → selfie kamera depan (JPEG, maks 5 MB, storage privat, admin lihat semua) → kirim bersama lokasi → tersimpan Hadir. QR hanya pintu masuk, bukan syarat validasi. Tidak ada face recognition.
- **Koreksi admin:** Hadir/Tidak Hadir/Izin/Libur dengan alasan wajib dan log. Hadir→Izin/Libur membiarkan selfie/lokasi lama di baris itu.
- **Masuk sebagai anggota:** hanya admin, hanya lihat; kirim absen dan ganti password diblokir (`bukan.impersonasi`); jangan dicabut tanpa persetujuan.
- Kode memiliki `ganti-password` dan throttle login 5/60 detik meski pemilik pernah bilang tidak perlu: jangan hapus sepihak.

## 6. Tema dan UI (Karaksa)

- Palet perkiraan dari logo (belum ada kode resmi): krem `#F5F1E8`, kertas `#FBF9F3`, zaitun `#5E6040`/`#4A4C32`, tan `#C9A97C`, cokelat `#9A6B4B`, tinta `#3B3A2E`. Warna status terpisah: Hadir `#4F7A4A`, Izin `#3C7C85`, Libur `#8A7F72`, Menunggu `#D9A21B`, Tidak Hadir `#A8432F`. Semua di bagian atas `resources/scss/app.scss`.
- Judul serif (Georgia, font bawaan; font lokal menyusul), isi sans-serif. Kartu krem, sudut membulat, garis tan.
- File tema: `resources/scss/app.scss`, `resources/views/layouts/app.blade.php`. Setelah mengubah SCSS: `npm run build`.
- **Urutan tahap berikutnya (satu per satu):** (2) Login: logo besar, latar kertas; (3) beranda anggota; (4) halaman absen/kamera selfie dengan langkah jelas; (5) Dashboard admin, Data Absensi, Laporan, Anggota, QR, Pengaturan: tabel jadi kartu per baris di HP, badge dengan ikon, ornamen sobekan kertas SVG ringan; (6) PDF laporan dengan kop logo; font lokal.
- Catatan editor: garis merah "at-rule or selector expected" untuk `style="width: {{ ... }}%"` di Blade hanyalah keluhan VS Code, bukan error Laravel. Saran Tailwind "flex-grow-1 can be written as grow" diabaikan karena proyek memakai Bootstrap.

## 7. Prioritas berikutnya

1. `git status`, `git log --oneline -3` (harapan: `f147383` di `origin/main`; perubahan dokumen sesi 10 mungkin belum di-commit).
2. **Selesaikan Tahap A hosting:** cek dropdown "Common Settings" di Cron Jobs (apakah ada `Once Per Minute`; jika tidak, interval terkecil berapa). Penilaian: tiap menit = ideal; minimal 5 menit = aman (00:05 kelipatan 5); 10/15 menit atau lebih = jadwal `absensi:tutup-hari` bisa terlewat dan perlu diubah dengan persetujuan.
3. **Tahap B hosting:** konfirmasi PHP 8.4 untuk domain di MultiPHP Manager; buat database MySQL + user (password disimpan pemilik sendiri); aktifkan AutoSSL; pastikan domain terpasang.
4. **Tahap C:** ambil kode dari GitHub (cPanel Git Version Control atau `git clone` di Terminal); `composer install --no-dev`; buat `.env` produksi langsung di server (`APP_ENV=production`, `APP_DEBUG=false`, data database; jangan masuk Git); putuskan nasib `public/build` (cek `.gitignore`; pilihan: build di laptop lalu unggah, build di server jika Node ada, atau masukkan ke Git dengan persetujuan); arahkan document root domain ke folder `public` Laravel agar `.env` dan `storage` tidak terbuka.
5. **Tahap D:** "setuju migration" lalu migrasi di database server (kosong, tidak menyentuh data lokal); buat akun admin; isi pengaturan posko lewat halaman Pengaturan; pasang cron `schedule:run` dengan alamat PHP lengkap (jangan sekadar `php`; sesuaikan alamat dari MultiPHP, contoh pola di cPanel: `/usr/local/bin/ea-php84`, verifikasi dulu).
6. **Tahap E (uji):** login admin, dashboard, Laporan, Excel, PDF; dari HP: login anggota di posko, absen dengan selfie; cek badge Libur, kolom Libur, burger HP; cek `findstr /n "radius" app\Services\GeofenceService.php` dan samakan nilai cadangan dengan 50; uji akun anggota sungguhan; ubah repo menjadi private (lalu tambah deploy key jika server perlu menarik kode).
7. Update ke server nanti: `git push` dari laptop (dengan persetujuan) → di server tarik perubahan → `composer install --no-dev` jika paket berubah → migrasi hanya jika ada migration baru dan "setuju migration".
8. Sebelum 7 Desember 2026: putuskan lanjut atau batal (garansi 30 hari dari 7 November). Domain tahun pertama Rp25.000, perpanjangan Rp50.000/tahun; anggap biaya domain tidak ikut garansi.
9. Lanjutkan tema tahap 2 → 6 (Bagian 6). Phase 15: dokumentasi pengguna.

## 8. Langkah pertama chat baru

Jangan buat proyek baru, jangan langsung coding, jangan commit/push. **YANG HARUS KAMU LAKUKAN SEKARANG:** minta pemilik menjalankan `git status` dan `git log --oneline -3` dari folder proyek, bandingkan dengan `docs/PROJECT_DOCS.md`, tanyakan hasil cek Cron Jobs (Bagian 7 nomor 2), lalu kerjakan satu langkah. Jangan sarankan restore/reset/checkout.

## 9. Prinsip akhir

Jangan berasumsi. Jangan merusak pekerjaan sebelumnya. Jangan ubah aturan bisnis tanpa persetujuan. Jangan sentuh proyek/database lain. Jangan migration, commit, atau push tanpa persetujuan. Jangan klaim selesai tanpa bukti.
