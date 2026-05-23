<?php

declare(strict_types=1);

namespace App\Web\Auth;

use App\Environment;
use PDO;

final class AuthRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $host = Environment::dbHost();
        $port = Environment::dbPort();
        $dbname = Environment::dbName();
        $user = Environment::dbUser();
        $password = Environment::dbPassword();

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->initializeTable();
    }

    private function initializeTable(): void
    {
        // Buat tabel users jika belum ada
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(50) NOT NULL UNIQUE,
                `email` VARCHAR(100) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `role` VARCHAR(50) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Seed default users jika tabel kosong
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `users`");
        if ($stmt->fetchColumn() == 0) {
            $defaultUsers = [
                [
                    'username' => 'admin',
                    'email' => 'admin@teqic.com',
                    'password' => 'admin123',
                    'role' => 'Admin',
                ],
                [
                    'username' => 'operator',
                    'email' => 'operator@teqic.com',
                    'password' => 'operator123',
                    'role' => 'Operator',
                ],
                [
                    'username' => 'guru',
                    'email' => 'guru@teqic.com',
                    'password' => 'guru123',
                    'role' => 'Guru',
                ]
            ];

            $insertStmt = $this->pdo->prepare("
                INSERT INTO `users` (`username`, `email`, `password_hash`, `role`)
                VALUES (:username, :email, :password_hash, :role)
            ");

            foreach ($defaultUsers as $u) {
                $insertStmt->execute([
                    'username' => $u['username'],
                    'email' => $u['email'],
                    'password_hash' => password_hash($u['password'], PASSWORD_DEFAULT),
                    'role' => $u['role']
                ]);
            }
        }
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `username` = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `id` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createUser(string $username, string $email, string $password, string $role): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO `users` (`username`, `email`, `password_hash`, `role`)
            VALUES (:username, :email, :password_hash, :role)
        ");
        return $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role
        ]);
    }

    public function updateUserRole(int $userId, string $role): bool
    {
        $stmt = $this->pdo->prepare("UPDATE `users` SET `role` = :role WHERE `id` = :id");
        return $stmt->execute([
            'role' => $role,
            'id' => $userId
        ]);
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM `users` ORDER BY `id` ASC");
        return $stmt->fetchAll();
    }
}
