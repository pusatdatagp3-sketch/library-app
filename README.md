# yii3-pmdg

Aplikasi manajemen data pesantren berbasis **Yii3** (yiisoft/app template) dengan Cycle ORM dan sistem RBAC custom. Mencakup pengelolaan data guru, santri, kamar, konsulat, dan pelanggaran.

---

## 🧱 Template & Tech Stack

| Komponen | Teknologi |
|---|---|
| **Framework** | [Yii3](https://github.com/yiisoft/app) — `yiisoft/app` template |
| **ORM** | [Cycle ORM v2](https://cycle-orm.dev) via `yiisoft/yii-cycle` |
| **Database** | MySQL 8+ / MariaDB |
| **Router** | `yiisoft/router-fastroute` |
| **Session** | `yiisoft/session` |
| **CSRF** | `yiisoft/csrf` |
| **DI Container** | `yiisoft/di` |
| **Asset Management** | `yiisoft/assets` |
| **Export Excel** | `phpoffice/phpspreadsheet` |
| **PHP** | >= 8.4.0 |

---

## 📁 Struktur Direktori

```
yii3-pmdg/
├── assets/                     # Sumber asset (CSS, JS, gambar)
│   └── main/                   # Asset bundle utama
├── config/
│   ├── common/                 # Konfigurasi bersama (web + console)
│   │   ├── aliases.php         # Path alias (@root, @baseUrl, @assets, dll)
│   │   ├── params.php          # Parameter global (DB, session, Cycle ORM)
│   │   ├── routes.php          # Definisi semua route aplikasi
│   │   └── di/                 # Dependency injection bindings
│   ├── web/                    # Konfigurasi khusus web
│   │   ├── params.php          # Session config (save_path, cookie)
│   │   └── di/
│   │       ├── subfolder.php   # Subfolder middleware (path prefix)
│   │       └── application.php # Middleware stack
│   └── environments/           # Config per environment (dev/prod/test)
├── public/
│   ├── index.php               # Entry point aplikasi
│   ├── .htaccess               # Apache rewrite rules
│   └── assets/                 # Asset yang sudah di-publish (auto-generated)
├── runtime/
│   └── sessions/               # Penyimpanan file session PHP
├── src/
│   ├── Environment.php         # Pembaca env vars (DB_HOST, APP_DEBUG, dll)
│   ├── bootstrap.php           # Bootstrap: autoload + dotenv + Environment
│   ├── Shared/                 # Komponen bersama
│   └── Web/                    # Modul-modul web
│       ├── Auth/               # Login, logout, session user
│       ├── Guru/               # CRUD data guru + import Excel
│       ├── Santri/             # CRUD data santri
│       ├── Kamar/              # CRUD data kamar
│       ├── Konsulat/           # CRUD data konsulat
│       ├── Perizinan/          # CRUD data perizinan
│       ├── Rayon/              # CRUD data rayon
│       ├── Pelanggaran/        # CRUD data pelanggaran
│       ├── Rbac/               # Manajemen user, role, permission, route
│       ├── Gii/                # Code generator (dev only)
│       ├── Middleware/         # Custom middleware (RBAC Access Control)
│       └── Shared/             # Layout, komponen view bersama
├── teqic_yii3.sql              # Dump database awal
└── .env                        # Konfigurasi environment lokal (buat sendiri)
```

---

## 🏗️ Arsitektur MVC

Setiap modul mengikuti pola **MVC** yang terisolasi per fitur:

```
src/Web/{Modul}/
├── Controller/    # HTTP handler — menerima request, return response
├── Model/         # Entity (Cycle ORM), Repository, DTO, Factory
├── Service/       # Business logic (jika ada)
└── View/          # Template PHP (.php)
```

### Contoh: Modul Guru

```
src/Web/Guru/
├── Controller/
│   └── GuruController.php     # index, create, update, delete, upload, downloadTemplate
├── Model/
│   ├── GuruEntity.php         # #[Entity] Cycle ORM — tabel `guru`
│   ├── GuruRepository.php     # Query custom (findAll, findById, dll)
│   ├── GuruDto.php            # Data Transfer Object (form input)
│   ├── GuruFactory.php        # Pembuatan/update entity dari DTO
│   └── PhoneHelper.php        # Helper format nomor telepon
├── Service/
│   └── GuruService.php        # Import Excel, validasi data
└── View/
    ├── index.php
    ├── create.php
    └── update.php
```

### Entity Cycle ORM (contoh)

```php
#[Entity(role: 'guru', table: 'guru', repository: GuruRepository::class)]
class GuruEntity
{
    #[Column(type: 'primary')]
    public ?int $kdg = null;

    #[Column(type: 'string(150)')]
    public string $nama = '';

    #[BelongsTo(target: Kamar::class, innerKey: 'kamarId', fkAction: 'SET NULL')]
    public ?Kamar $kamar = null;
}
```

Schema Cycle ORM di-generate **otomatis saat runtime** dari PHP Attributes (tidak perlu migration manual).

---

## 🔐 Sistem RBAC

Aplikasi menggunakan RBAC **custom berbasis database** (bukan RBAC bawaan Yii):

| Tabel | Fungsi |
|---|---|
| `rbac_roles` | Daftar role (admin, operator, dll) |
| `rbac_permissions` | Daftar permission/hak akses |
| `rbac_role_permissions` | Relasi role ↔ permission |
| `rbac_route_permissions` | Proteksi route berdasarkan permission |
| `users` | Akun pengguna + role |

Semua route (kecuali `/login`) diproteksi oleh `RbacAccessControlMiddleware`.

---

## 🗺️ Daftar Route

| Method | URL | Deskripsi |
|---|---|---|
| GET | `/` | Halaman utama |
| GET/POST | `/login` | Login |
| POST | `/logout` | Logout |
| GET | `/guru` | Daftar guru |
| GET/POST | `/guru/create` | Tambah guru |
| GET/POST | `/guru/update/{kdg}` | Edit guru |
| POST | `/guru/delete/{kdg}` | Hapus guru |
| GET | `/guru/download-template` | Download template Excel |
| POST | `/guru/upload` | Import guru dari Excel |
| GET | `/santri` | Daftar santri |
| GET/POST | `/santri/create` | Tambah santri |
| GET/POST | `/santri/update/{id}` | Edit santri |
| POST | `/santri/delete/{id}` | Hapus santri |
| GET | `/kamar` | Daftar kamar |
| GET | `/konsulat` | Daftar konsulat |
| GET | `/perizinan` | Daftar perizinan |
| GET | `/rayon` | Daftar rayon |
| GET | `/pelanggaran` | Daftar pelanggaran |
| GET | `/users` | Manajemen user |
| GET | `/roles` | Manajemen role |
| GET | `/permissions` | Manajemen permission |
| GET | `/routes` | Konfigurasi proteksi route |
| GET | `/gii` | Code generator (dev) |

---

## ⚙️ Instalasi (dari Awal)

### Prasyarat

- **PHP >= 8.4** (pastikan versi ini aktif di Laragon/XAMPP)
- **Composer**
- **MySQL 8** atau **MariaDB**
- **Apache** dengan `mod_rewrite` aktif

> ⚠️ Proyek ini **membutuhkan PHP 8.4**. Beberapa dependensi (Symfony 8.x, doctrine/instantiator 2.1) tidak kompatibel dengan PHP 8.3 ke bawah.

---

### 1. Clone Repository

```bash
git clone https://github.com/hamidalfarizi/yii3-pmdg.git
cd yii3-pmdg
```

### 2. Install Dependensi

```bash
composer install
```

### 3. Buat File `.env`

Salin dari contoh dan sesuaikan:

```bash
cp .env.example .env
```

Edit file `.env`:

```env
APP_ENV=dev
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=teqic_yii3
DB_USER=root
DB_PASSWORD=your_password
```

### 4. Buat Database & Import SQL

Buat database di MySQL:

```sql
CREATE DATABASE IF NOT EXISTS teqic_yii3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import schema awal:

```bash
# Linux/Mac
mysql -u root -p teqic_yii3 < teqic_yii3.sql

# Windows (PowerShell)
Get-Content teqic_yii3.sql | mysql -u root -pyour_password teqic_yii3
```

### 5. Buat Folder Session

```bash
mkdir runtime/sessions
```

> Di Windows:
> ```powershell
> New-Item -ItemType Directory -Path "runtime\sessions" -Force
> ```

### 6. Konfigurasi Apache (Jika Deploy di Subdirektori)

Jika aplikasi berjalan di `http://localhost/yii3-pmdg/public/`, sesuaikan 3 file berikut:

**`public/.htaccess`**
```apache
RewriteEngine On
RewriteBase /yii3-pmdg/public/

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^ index.php [L]
```

**`config/common/aliases.php`** — ubah `@baseUrl`:
```php
'@baseUrl' => '/yii3-pmdg/public',
```

**`config/web/di/subfolder.php`** — ubah prefix Subfolder:
```php
$prefix = (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') ? null : '/yii3-pmdg/public';
```

### 7. Akses Aplikasi

```
http://localhost/yii3-pmdg/public/
```

---

## 🔄 Cycle ORM — Schema Auto-Sync

Aplikasi menggunakan Cycle ORM dengan `SyncTables` generator, artinya:

- **Tidak perlu menjalankan migration** secara manual
- Schema database di-sync otomatis dari PHP Attributes saat pertama kali request
- Entity paths yang di-scan ada di `config/common/params.php`:

```php
'entity-paths' => [
    '@src/Web/Perizinan/Model',
    '@src/Web/Guru/Model',
    '@src/Web/Rayon/Model',
    '@src/Web/Auth/Model',
    '@src/Web/Kamar/Model',
    '@src/Web/Konsulat/Model',
    '@src/Web/Pelanggaran/Model',
    '@src/Web/Santri/Model',
],
```

---

## 🛠️ Development

### Jalankan dengan PHP Built-in Server

```bash
composer serve
# atau
php yii serve
```

Akses di: `http://localhost:8080`

### Generate Code (Gii)

Tersedia di `/gii` (hanya pada environment `dev`).

---

## 📦 Environment Variables

| Variable | Default | Deskripsi |
|---|---|---|
| `APP_ENV` | `prod` | Environment: `dev`, `test`, `prod` |
| `APP_DEBUG` | `false` | Tampilkan error detail |
| `DB_HOST` | `127.0.0.1` | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_NAME` | `teqic_yii3` | Nama database |
| `DB_USER` | `root` | Username database |
| `DB_PASSWORD` | `dummy1!` | Password database |

---

## 📝 Catatan untuk Developer Pemula

### 1. ⚠️ Jangan commit file `.env` ke Git

File `.env` berisi informasi **sensitif** seperti password database. File ini sudah didaftarkan di `.gitignore` sehingga Git otomatis mengabaikannya dan tidak akan ter-upload ke GitHub.

**Kenapa ini penting?**
Kalau file `.env` sampai ter-upload ke GitHub (apalagi repo publik), siapa pun bisa membaca password database kamu. Ini adalah celah keamanan yang sangat serius.

**Yang harus dilakukan setiap developer yang clone repo ini:**
1. Buat file `.env` baru dari template: `cp .env.example .env`
2. Isi dengan konfigurasi lokal masing-masing (password MySQL, dll)
3. **Jangan pernah** menghapus `.env` dari `.gitignore`

> 💡 Anggap `.env` seperti kunci rumah — setiap penghuni punya kuncinya sendiri, tidak dibagikan ke semua orang.

---

### 2. 🤖 Folder `runtime/` dan `public/assets/` di-generate otomatis

Kedua folder ini **tidak perlu di-commit ke Git** karena isinya dibuat otomatis oleh aplikasi:

**`runtime/sessions/`**
- Berisi file-file session PHP (data login user yang sedang aktif)
- Di-buat otomatis saat ada user yang login
- Isinya berbeda di setiap komputer, jadi tidak ada gunanya di-commit
- ⚠️ Folder ini **harus ada** secara fisik. Kalau belum ada, buat manual:
  ```powershell
  # Windows
  New-Item -ItemType Directory -Path "runtime\sessions" -Force
  ```

**`public/assets/`**
- Berisi file CSS dan JS yang sudah di-*publish* dan diberi nama unik (contoh: `site.8823ec3d.css`)
- Di-generate otomatis oleh Yii Assets saat pertama kali halaman diakses
- Nama file berubah setiap ada perubahan pada source asset — ini normal, namanya *cache busting*
- Karena di-generate otomatis, tidak perlu (dan sebaiknya tidak) di-commit ke git

> 💡 Kalau kamu clone repo ini dan `public/assets/` masih kosong, jangan panik — buka aplikasinya di browser dan folder itu akan terisi sendiri.

---

### 3. 🚀 Cara deploy ke production (tanpa file `.env`)

Di server production (VPS, hosting), **jangan gunakan file `.env`**. Sebaiknya set konfigurasi langsung di server agar lebih aman.

**Kenapa tidak pakai `.env` di production?**
File `.env` bisa terbaca orang lain jika ada salah konfigurasi server (misalnya Apache menampilkan file sebagai teks biasa). Set langsung di server jauh lebih aman.

**Cara set di Apache (`VirtualHost`):**
```apache
<VirtualHost *:80>
    ServerName namadomain.com
    DocumentRoot /var/www/yii3-pmdg/public

    SetEnv APP_ENV prod
    SetEnv APP_DEBUG false
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_NAME teqic_yii3
    SetEnv DB_USER root
    SetEnv DB_PASSWORD password_production_kamu
</VirtualHost>
```

**Cara set di Docker (`docker-compose.yml`):**
```yaml
environment:
  APP_ENV: prod
  APP_DEBUG: "false"
  DB_HOST: db
  DB_NAME: teqic_yii3
  DB_USER: root
  DB_PASSWORD: password_production_kamu
```

> 💡 Untuk development lokal di Laragon/XAMPP, tetap gunakan file `.env` seperti biasa — praktis dan mudah.
