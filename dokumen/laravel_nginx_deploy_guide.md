# Panduan Deploy Laravel 12 di Nginx (Subfolder `localhost/sidasapp`)

Nginx tidak membaca file `.htaccess`, sehingga seluruh konfigurasi subfolder (termasuk aturan rewrite Laravel) harus didefinisikan langsung di dalam file konfigurasi server block Nginx Anda.

Berikut adalah panduan lengkap konfigurasinya.

---

## Langkah 1: Konfigurasi Server Block Nginx
1. Buka file konfigurasi Nginx untuk domain/host Anda (biasanya di `/etc/nginx/sites-available/default` atau file konfigurasi situs Anda):
   ```bash
   sudo nano /etc/nginx/sites-available/default
   ```

2. Tambahkan blok konfigurasi `location` berikut di dalam blok `server` yang sudah ada:
   ```nginx
   server {
       listen 80;
       server_name localhost; # atau nama domain Anda
       root /var/www/html;    # root default web server Anda

       index index.html index.htm index.php;

       # --- KONFIGURASI SUBFOLDER LARAVEL (sidasapp) ---
       location /sidasapp {
           # Petakan request /sidasapp ke folder public Laravel Anda
           alias /var/www/html/sidasapp/public;
           
           # Coba langsung file/direktori, jika tidak ada kirim ke fallback laravel
           try_files $uri $uri/ @sidasapp_laravel;

           # Penanganan PHP untuk subfolder sidasapp
           location ~ \.php$ {
               # Gantilah socket PHP-FPM di bawah ini dengan versi PHP yang Anda gunakan (contoh: 8.2 / 8.3 / 8.4)
               fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; 
               
               fastcgi_split_path_info ^(.+\.php)(.*)$;
               include fastcgi_params;
               
               # PENTING: Gunakan $request_filename agar Nginx memetakan file php dengan benar saat menggunakan alias
               fastcgi_param SCRIPT_FILENAME $request_filename;
           }
       }

       # Fallback Routing Laravel untuk Subfolder sidasapp
       location @sidasapp_laravel {
           rewrite /sidasapp/(.*)$ /sidasapp/index.php?/$1 last;
       }
       # ------------------------------------------------
       
       # Konfigurasi PHP Global (untuk proyek lain di folder root jika ada)
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
       }
   }
   ```

3. Simpan perubahan dengan menekan `Ctrl + O`, lalu `Enter`, dan keluar dengan `Ctrl + X`.

---

## Langkah 2: Atur Izin Akses Folder (Permissions)
Agar Nginx dan PHP-FPM bisa menulis file log dan cache Laravel, pastikan izin folder diatur dengan benar:

Jalankan perintah ini di server:
```bash
# Masuk ke direktori Laravel Anda
cd /var/www/html/sidasapp

# Ubah grup folder storage dan bootstrap/cache ke group web server (www-data)
sudo chown -R $USER:www-data storage bootstrap/cache

# Berikan hak akses baca-tulis untuk Owner dan Group
chmod -R 775 storage bootstrap/cache
```

---

## Langkah 3: Ubah `.env` Laravel
Pastikan variabel `APP_URL` di dalam file `.env` Laravel Anda diarahkan ke alamat subfolder:
```ini
APP_URL=http://localhost/sidasapp
```

---

## Langkah 4: Uji Coba Konfigurasi dan Reload Nginx
Sebelum memuat ulang Nginx, selalu tes konfigurasinya terlebih dahulu untuk memastikan tidak ada kesalahan ketik (syntax error):

```bash
# 1. Tes konfigurasi Nginx
sudo nginx -t

# 2. Jika sukses (syntax is ok), reload Nginx
sudo systemctl reload nginx
```

---

## Selesai!
Sekarang Anda dapat membuka browser dan mengakses proyek Laravel Anda di:
👉 **`http://localhost/sidasapp`** (atau `http://IP_SERVER/sidasapp`)
