# Panduan Konfigurasi Server: Kompresi File Upload & Limit PHP

Aplikasi TEQIC Yii3 telah dilengkapi dengan fitur **kompresi otomatis** untuk file yang diunggah:
1. **Gambar (JPG, JPEG, PNG, GIF)**: Dikonversi secara otomatis ke format `.webp` dengan kualitas 75% untuk menghemat penyimpanan (menggunakan library PHP GD).
2. **Dokumen (PDF)**: Dikompresi ukurannya secara otomatis menggunakan program **Ghostscript (`gs`)**.

Agar fitur kompresi ini dapat berjalan optimal untuk file berukuran besar pada server deployment/produksi, ikuti panduan konfigurasi server berikut.

---

## 1. Konfigurasi Batas Maksimum Upload (PHP.ini)

Secara bawaan, PHP membatasi upload file maksimal sebesar **2MB**. Jika file melebihi batas ini, server akan langsung menolaknya sebelum sempat dikompresi.

### Langkah-langkah:
1. Temukan file konfigurasi `php.ini` yang aktif pada server web (biasanya untuk PHP-FPM di Ubuntu terletak di `/etc/php/8.5/fpm/php.ini` atau `/etc/php/8.x/fpm/php.ini`).
2. Cari dan ubah baris pengaturan berikut (misalkan dinaikkan menjadi **20MB**):
   ```ini
   upload_max_filesize = 20M
   post_max_size = 25M
   ```
3. Simpan perubahan dan restart service PHP-FPM serta Web Server Anda:
   * **Ubuntu/Debian**:
     ```bash
     sudo systemctl restart php8.5-fpm
     sudo systemctl restart apache2
     ```
   * **CentOS/RHEL/Nginx**:
     ```bash
     sudo systemctl restart php-fpm
     sudo systemctl restart nginx
     ```

---

## 2. Instalasi Dependensi Gambar (PHP GD WebP Support)

Fitur konversi gambar membutuhkan ekstensi **PHP GD** dengan dukungan **WebP**.

### Instalasi di Ubuntu/Debian:
```bash
# Ganti 8.5 dengan versi PHP yang Anda gunakan di server
sudo apt-get update
sudo apt-get install php8.5-gd
sudo systemctl restart php8.5-fpm
```

### Verifikasi di Server:
Jalankan perintah berikut untuk memastikan dukungan WebP aktif:
```bash
php -r "var_dump(function_exists('imagewebp'));"
# Output harus bernilai: bool(true)
```

---

## 3. Instalasi Dependensi PDF (Ghostscript)

Untuk melakukan kompresi otomatis pada file PDF, server membutuhkan tool **Ghostscript** terinstal di path `/usr/bin/gs`.

### Instalasi di Ubuntu/Debian:
```bash
sudo apt-get update
sudo apt-get install ghostscript
```

### Instalasi di CentOS/RHEL:
```bash
sudo yum install ghostscript
```

### Verifikasi di Server:
Jalankan perintah berikut untuk memastikan Ghostscript terinstal dan dapat diakses:
```bash
/usr/bin/gs --version
# Contoh output: 10.02.1 (atau versi yang terinstal)
```

---

## 4. Konfigurasi Khusus di ISPConfig

Jika Anda menggunakan control panel **ISPConfig** untuk mengelola server web Anda, langkah-langkah di atas tetap relevan, tetapi Anda dapat mengonfigurasi batas maksimum upload (`php.ini`) langsung dari panel kontrol web tanpa perlu mengedit file konfigurasi via SSH:

### A. Mengubah Limit PHP via Panel ISPConfig:
1. Masuk ke panel kontrol ISPConfig Anda.
2. Masuk ke menu **Sites** dan klik nama domain/website aplikasi Anda.
3. Klik tab **Options** di bagian atas menu website tersebut.
4. Pada kolom **Custom php.ini settings**, ketikkan konfigurasi berikut:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 25M
   ```
5. Klik **Save**. ISPConfig akan menerapkan konfigurasi baru ini secara otomatis ke dalam PHP-FPM pool website tersebut dalam waktu 1-2 menit.

### B. Dependensi Sistem (Ghostscript & GD):
Karena ISPConfig berjalan di atas sistem operasi dasar (seperti Debian atau Ubuntu), penginstalan paket sistem (Ghostscript dan ekstensi PHP-GD) tetap harus dilakukan melalui akses SSH terminal server utama menggunakan perintah `apt-get` (seperti yang dijelaskan pada bagian **2** dan **3** di atas).

---

## Cara Kerja Layanan di Aplikasi
Layanan kompresi ini didefinisikan secara modular di dalam class `App\Web\Shared\Service\FileCompressionService` dan dipanggil secara otomatis oleh controller saat proses unggahan file Notulensi dan Dokumentasi berlangsung.

* Jika file yang diunggah berupa gambar, akan diubah menjadi `.webp` yang berukuran jauh lebih kecil.
* Jika berupa dokumen PDF, Ghostscript akan mengompresnya ke tingkat dpi standar layar (*screen level*).
* Jika file berupa format lain (atau jika kompresi menghasilkan file yang lebih besar), aplikasi secara otomatis akan menyimpan file asli tanpa modifikasi untuk menjaga integritas data.
