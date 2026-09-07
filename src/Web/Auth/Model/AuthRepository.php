<?php

declare(strict_types=1);

namespace App\Web\Auth\Model;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;

/**
 * Repositori autentikasi yang mengelola data pengguna dari tabel `users`.
 * Tidak lagi bergantung pada tabel kampus; sistem multi-kampus telah dihapus.
 */
final class AuthRepository
{
    private $repository;

    public function __construct(
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
    ) {
        $this->repository = $orm->getRepository(User::class);
        $this->seedDefaultUsers();
    }

    /**
     * Menyuntikkan pengguna default jika tabel users kosong.
     * Hanya berjalan sekali saat deployment pertama.
     */
    private function seedDefaultUsers(): void
    {
        if ($this->repository->select()->count() > 0) {
            return;
        }

        $defaults = [
            ['username' => 'admin',    'email' => 'admin@kutubia.id',    'password' => 'admin123',    'role' => 'Admin'],
            ['username' => 'operator', 'email' => 'operator@kutubia.id', 'password' => 'operator123', 'role' => 'Operator'],
            ['username' => 'guru',     'email' => 'guru@kutubia.id',     'password' => 'guru123',     'role' => 'Guru'],
        ];

        foreach ($defaults as $d) {
            $user               = new User();
            $user->username     = $d['username'];
            $user->email        = $d['email'];
            $user->passwordHash = password_hash($d['password'], PASSWORD_BCRYPT);
            $user->role         = $d['role'];
            $user->createdAt    = new \DateTimeImmutable();
            $this->entityManager->persist($user);
        }
        $this->entityManager->run();
    }

    // ── Query ────────────────────────────────────────────────────────────────

    public function findByUsername(string $username): ?array
    {
        $user = $this->repository->select()->where('username', $username)->fetchOne();
        return $user ? $this->toArray($user) : null;
    }

    public function findByEmail(string $email): ?array
    {
        $user = $this->repository->select()->where('email', $email)->fetchOne();
        return $user ? $this->toArray($user) : null;
    }

    public function findById(int $id): ?array
    {
        $user = $this->repository->findByPK($id);
        return $user ? $this->toArray($user) : null;
    }

    public function getAllUsers(): array
    {
        $users = $this->repository->select()->orderBy('id', 'ASC')->fetchAll();
        return array_map([$this, 'toArray'], $users);
    }

    // ── Mutasi ───────────────────────────────────────────────────────────────

    /**
     * Membuat pengguna baru dengan password yang di-hash menggunakan BCRYPT.
     */
    public function createUser(string $username, string $email, string $password, string $role): bool
    {
        $user               = new User();
        $user->username     = $username;
        $user->email        = $email;
        $user->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $user->role         = $role;
        $user->createdAt    = new \DateTimeImmutable();

        $this->entityManager->persist($user)->run();
        return true;
    }

    /**
     * Memperbarui data pengguna.
     * Jika $password bernilai null atau string kosong, password lama dipertahankan.
     */
    public function updateUser(int $id, string $username, string $email, ?string $password, string $role): bool
    {
        $user = $this->repository->findByPK($id);
        if ($user === null) {
            return false;
        }

        $user->username = $username;
        $user->email    = $email;
        $user->role     = $role;

        if ($password !== null && $password !== '') {
            $user->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->entityManager->persist($user)->run();
        return true;
    }

    public function updateUserRole(int $userId, string $role): bool
    {
        $user = $this->repository->findByPK($userId);
        if ($user === null) {
            return false;
        }
        $user->role = $role;
        $this->entityManager->persist($user)->run();
        return true;
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->repository->findByPK($id);
        if ($user === null) {
            return false;
        }
        $this->entityManager->delete($user)->run();
        return true;
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    private function toArray(User $user): array
    {
        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'email'         => $user->email,
            'password_hash' => $user->passwordHash,
            'role'          => $user->role,
            'created_at'    => $user->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
