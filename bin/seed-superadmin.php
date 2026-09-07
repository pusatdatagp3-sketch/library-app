#!/usr/bin/env php
<?php

/**
 * ============================================================
 * KUTUBIA — Super Admin Seeder
 * ============================================================
 * Skrip mandiri untuk menyuntikkan akun Super Admin default
 * ke database. Jalankan SATU KALI dari root proyek:
 *
 *   php bin/seed-superadmin.php
 *
 * Skrip ini aman dijalankan berulang kali; akan memberi tahu
 * jika akun atau role sudah ada tanpa menimpa data yang ada.
 * ============================================================
 */

declare(strict_types=1);

// ── Bootstrap ──────────────────────────────────────────────────────────────
$rootDir = dirname(__DIR__);

// Muat autoloader Composer
$autoloadPath = $rootDir . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    fwrite(STDERR, "[ERROR] vendor/autoload.php tidak ditemukan. Jalankan `composer install` terlebih dahulu.\n");
    exit(1);
}
require $autoloadPath;

// Muat variabel lingkungan dari .env
if (file_exists($rootDir . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($rootDir);
    $dotenv->load();
}

\App\Environment::prepare();

// ── Konfigurasi Akun Super Admin ──────────────────────────────────────────
// ⚠️  UBAH PASSWORD INI SEGERA SETELAH LOGIN PERTAMA!
$SUPERADMIN_USERNAME = 'superadmin';
$SUPERADMIN_EMAIL    = 'superadmin@kutubia.id';
$SUPERADMIN_PASSWORD = 'Kutubia@Sup3rAdm!n';  // Wajib ganti!
$SUPERADMIN_ROLE     = 'super-admin';

// ── Koneksi PDO langsung (tanpa DI Container, agar skrip mandiri) ─────────
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        \App\Environment::dbHost(),
        \App\Environment::dbPort(),
        \App\Environment::dbName()
    );
    $pdo = new PDO($dsn, \App\Environment::dbUser(), \App\Environment::dbPassword(), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[OK] Koneksi database berhasil.\n";
} catch (\PDOException $e) {
    fwrite(STDERR, "[ERROR] Gagal koneksi database: " . $e->getMessage() . "\n");
    exit(1);
}

// ── Pastikan role 'super-admin' ada di rbac_roles ─────────────────────────
$existingRole = $pdo->prepare("SELECT name FROM `rbac_roles` WHERE `name` = ?");
$existingRole->execute([$SUPERADMIN_ROLE]);
if ($existingRole->fetch() === false) {
    $pdo->prepare("INSERT INTO `rbac_roles` (`name`, `description`) VALUES (?, ?)")
        ->execute([$SUPERADMIN_ROLE, 'Super Administrator — akses penuh ke seluruh sistem tanpa pembatasan RBAC']);
    echo "[INSERTED] Role '{$SUPERADMIN_ROLE}' berhasil ditambahkan ke tabel rbac_roles.\n";
} else {
    echo "[SKIP] Role '{$SUPERADMIN_ROLE}' sudah ada di tabel rbac_roles.\n";
}

// ── Periksa apakah akun sudah ada (cek username dan email) ────────────────
$existingByUsername = $pdo->prepare("SELECT id, username FROM `users` WHERE `username` = ?");
$existingByUsername->execute([$SUPERADMIN_USERNAME]);
$foundByUsername = $existingByUsername->fetch();

$existingByEmail = $pdo->prepare("SELECT id, email FROM `users` WHERE `email` = ?");
$existingByEmail->execute([$SUPERADMIN_EMAIL]);
$foundByEmail = $existingByEmail->fetch();

if ($foundByUsername !== false) {
    echo "[SKIP] Akun dengan username '{$SUPERADMIN_USERNAME}' sudah ada (ID: {$foundByUsername['id']}).\n";
    echo "       Jika ingin mereset password, gunakan perintah SQL berikut:\n";
    $newHash = password_hash($SUPERADMIN_PASSWORD, PASSWORD_BCRYPT);
    echo "       UPDATE \`users\` SET \`password_hash\` = '{$newHash}' WHERE \`username\` = '{$SUPERADMIN_USERNAME}';\n";
    exit(0);
}

if ($foundByEmail !== false) {
    fwrite(STDERR, "[ERROR] Email '{$SUPERADMIN_EMAIL}' sudah digunakan oleh akun lain (ID: {$foundByEmail['id']}).\n");
    fwrite(STDERR, "        Ubah nilai \$SUPERADMIN_EMAIL di skrip ini sebelum melanjutkan.\n");
    exit(1);
}

// ── Insert akun super admin baru ──────────────────────────────────────────
$passwordHash = password_hash($SUPERADMIN_PASSWORD, PASSWORD_BCRYPT);

try {
    $pdo->prepare(
        "INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `created_at`) VALUES (?, ?, ?, ?, NOW())"
    )->execute([
        $SUPERADMIN_USERNAME,
        $SUPERADMIN_EMAIL,
        $passwordHash,
        $SUPERADMIN_ROLE,
    ]);
    $newId = (int) $pdo->lastInsertId();
    echo "[INSERTED] Akun super admin berhasil dibuat:\n";
    echo "           ID       : {$newId}\n";
    echo "           Username : {$SUPERADMIN_USERNAME}\n";
    echo "           Email    : {$SUPERADMIN_EMAIL}\n";
    echo "           Role     : {$SUPERADMIN_ROLE}\n";
    echo "\n";
    echo "  ⚠️  PENTING: Segera ubah password default setelah login pertama!\n";
    echo "  ⚠️  Default password: {$SUPERADMIN_PASSWORD}\n";
} catch (\PDOException $e) {
    fwrite(STDERR, "[ERROR] Gagal insert akun super admin: " . $e->getMessage() . "\n");
    exit(1);
}

echo "\n[DONE] Seeder selesai dijalankan.\n";
