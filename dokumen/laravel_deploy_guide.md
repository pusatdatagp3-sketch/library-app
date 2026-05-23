# Panduan Deploy Laravel 12 di Apache (Subfolder `localhost/sidasapp`)

Panduan ini menjelaskan cara mengonfigurasi proyek Laravel 12 agar dapat diakses melalui URL **`http://localhost/sidasapp`** di Apache port 80 secara permanen, tanpa perlu menjalankan `php artisan serve`.

---

## Langkah 1: Pindahkan/Pastikan Proyek Laravel Berada di `/var/www/html/`
Pastikan direktori proyek Laravel Anda berada di:
`/var/www/html/sidasapp`

---

## Langkah 2: Konfigurasi Alias di Apache
Kita perlu memberi tahu Apache agar mengarahkan alamat `/sidasapp` langsung ke folder `public` dari proyek Laravel Anda.

1. Buka file konfigurasi default Apache:
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```

2. Tambahkan konfigurasi `Alias` berikut di dalam tag `<VirtualHost *:80>` (bisa diletakkan di bagian bawah sebelum tag penutup `</VirtualHost>`):
   ```apache
   Alias /sidasapp /var/www/html/sidasapp/public

   <Directory "/var/www/html/sidasapp/public">
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. Simpan perubahan dengan menekan `Ctrl + O`, lalu `Enter`, dan keluar dengan `Ctrl + X`.

---

## Langkah 3: Konfigurasi `.htaccess` Laravel (Mencegah Redirect Loop)
Karena kita menggunakan `Alias`, Apache membutuhkan instruksi `RewriteBase` agar tidak terjadi error 500 (Internal Server Error akibat loop redirect).

1. Buka file `.htaccess` yang ada di dalam folder **`public/`** proyek Laravel Anda (`/var/www/html/sidasapp/public/.htaccess`):
   ```bash
   nano /var/www/html/sidasapp/public/.htaccess
   ```

2. Tambahkan baris `RewriteBase /sidasapp/` tepat di bawah `RewriteEngine On`. Contoh konfigurasinya menjadi seperti ini:
   ```apache
   <IfModule mod_rewrite.c>
       <IfModule mod_negotiation.c>
           Options -MultiViews -Indexes
       </IfModule>

       RewriteEngine On
       RewriteBase /sidasapp/

       # Handle Authorization Header
       RewriteCond %{HTTP:Authorization} .
       RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

       # Redirect Trailing Slashes If Not A Folder...
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteCond %{REQUEST_URI} (.+)/$
       RewriteRule ^ %1 [L,R=301]

       # Send Requests To Front Controller...
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [L]
   </IfModule>
   ```

3. Simpan dan keluar (`Ctrl + O`, `Enter`, `Ctrl + X`).

---

## Langkah 4: Set Izin Akses Folder (Permissions)
Laravel membutuhkan akses tulis ke folder `storage` dan `bootstrap/cache` agar tidak memunculkan error *Permission Denied*.

Jalankan perintah berikut di terminal Anda:
```bash
# Masuk ke direktori Laravel Anda
cd /var/www/html/sidasapp

# Ubah group folder storage dan cache menjadi www-data (group Apache) secara rekursif
sudo chown -R $USER:www-data storage bootstrap/cache

# Berikan hak akses baca-tulis untuk Owner dan Group
chmod -R 775 storage bootstrap/cache
```

---

## Langkah 5: Sesuaikan `.env` Laravel
Agar link dan aset (CSS/JS/Gambar) yang di-generate oleh Laravel mengarah ke subfolder dengan benar, buka file `.env` Laravel Anda:
```bash
nano /var/www/html/sidasapp/.env
```
Ubah nilai `APP_URL` menjadi:
```ini
APP_URL=http://localhost/sidasapp
```

---

## Langkah 6: Restart Apache
Restart Apache agar seluruh konfigurasi baru dibaca oleh server:
```bash
sudo systemctl restart apache2
```

---

## Selesai!
Sekarang Anda dapat mengakses proyek Laravel Anda langsung melalui browser di:
👉 **[http://localhost/sidasapp](http://localhost/sidasapp)**
