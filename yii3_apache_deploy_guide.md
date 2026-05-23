# Panduan Deploy Yii 3 di Apache (Subfolder `localhost/teqic-yii3`)

Panduan ini berisi langkah-langkah yang diperlukan untuk menjalankan aplikasi Yii 3 di bawah subfolder **`http://localhost/teqic-yii3`** menggunakan web server Apache secara permanen, tanpa perlu menjalankan `php serve` secara manual.

---

## Langkah 1: Konfigurasi Alias di Apache
Kita perlu mengarahkan request URL `/teqic-yii3` langsung ke folder `public/` di dalam project Anda.

1. Buka file konfigurasi default Apache:
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```

2. Tambahkan konfigurasi `Alias` berikut di dalam tag `<VirtualHost *:80>`:
   ```apache
   Alias /teqic-yii3 /var/www/html/teqic-yii3/public

   <Directory "/var/www/html/teqic-yii3/public">
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. Simpan dan keluar (`Ctrl + O`, `Enter`, `Ctrl + X`).

---

## Langkah 2: Konfigurasi `.htaccess` (Mencegah Loop Redirect 500)
Modul `mod_rewrite` Apache membutuhkan instruksi `RewriteBase` agar tidak terjadi perulangan redirect (looping) pada alias subfolder.

1. Buka atau buat file `.htaccess` di dalam folder `public/` project Anda (`/var/www/html/teqic-yii3/public/.htaccess`).
2. Masukkan aturan penulisan ulang berikut:
   ```apache
   RewriteEngine On
   RewriteBase /teqic-yii3/

   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d

   RewriteRule ^ index.php [L]
   ```

---

## Langkah 3: Install Package SubFolder Middleware
Yii 3 menggunakan standar modern PSR-7 dan tidak memotong subfolder secara otomatis. Kita harus menginstal middleware pendukung untuk menangani prefix URL `/teqic-yii3`.

Jalankan perintah berikut di direktori root project:
```bash
composer require yiisoft/yii-middleware
```

---

## Langkah 4: Registrasikan Middleware `Subfolder`
Kita harus menaruh middleware `Subfolder` di dalam pipeline aplikasi sebelum router memproses request.

1. Buka file **`config/web/di/application.php`**.
2. Import class:
   ```php
   use Yiisoft\Yii\Middleware\Subfolder;
   ```
3. Tambahkan `Subfolder::class` di dalam array `withMiddlewares()` **sebelum** `Router::class`:
   ```php
   'withMiddlewares()' => [
       [
           ErrorCatcher::class,
           SessionMiddleware::class,
           CsrfTokenMiddleware::class,
           RequestCatcherMiddleware::class,
           Subfolder::class, // <-- Tambahkan di sini
           Router::class,
       ],
   ],
   ```

---

## Langkah 5: Buat File Dependency Injection untuk `Subfolder`
Kita perlu menginstansiasi middleware `Subfolder` dengan parameter prefix URL `/teqic-yii3`.

1. Buat file baru di **`config/web/di/subfolder.php`**.
2. Masukkan kode berikut:
   ```php
   <?php

   declare(strict_types=1);

   use Yiisoft\Aliases\Aliases;
   use Yiisoft\Router\UrlGeneratorInterface;
   use Yiisoft\Yii\Middleware\Subfolder;

   return [
       Subfolder::class => static function (Aliases $aliases, UrlGeneratorInterface $urlGenerator) {
           return new Subfolder(
               $urlGenerator,
               $aliases,
               '/teqic-yii3' // Prefix subfolder Anda
           );
       },
   ];
   ```

---

## Langkah 6: Ubah Base URL di Aliases
Ubah `@baseUrl` agar semua URL aset (CSS/JS) dan generator link otomatis menambahkan awalan `/teqic-yii3`.

1. Buka file **`config/common/aliases.php`**.
2. Cari baris `@baseUrl` dan ubah nilainya menjadi:
   ```php
   '@baseUrl' => '/teqic-yii3',
   ```

---

## Langkah 7: Atur Izin Folder (Permissions) & Bersihkan Session Lama
Ubah kepemilikan folder `runtime` ke user Apache (`www-data`) agar Apache dapat menulis session, serta hapus session lama yang sebelumnya dibuat saat menggunakan `php serve`.

Jalankan perintah berikut di terminal:
```bash
# 1. Hapus file session lama agar tidak bentrok (karena status write-nya beda user)
rm -rf /var/www/html/teqic-yii3/runtime/sessions/*

# 2. Ubah group folder runtime secara rekursif menjadi www-data
sudo chown -R hamidalfa:www-data /var/www/html/teqic-yii3/runtime

# 3. Berikan hak akses baca-tulis untuk Owner dan Group
find /var/www/html/teqic-yii3/runtime -type d -exec chmod 775 {} +
find /var/www/html/teqic-yii3/runtime -type f -exec chmod 664 {} +
```

---

## Langkah 8: Regenerasi Autoload & Restart Apache
1. Lakukan dump autoload agar file DI container yang baru dibuat ter-registrasi:
   ```bash
   composer dump-autoload
   ```
2. Restart Apache di terminal Anda:
   ```bash
   sudo systemctl restart apache2
   ```

---

## Selesai!
Sekarang Anda dapat membuka browser dan mengakses project Yii 3 Anda di:
👉 **[http://localhost/teqic-yii3/guru](http://localhost/teqic-yii3/guru)**
