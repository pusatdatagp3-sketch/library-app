<?php

declare(strict_types=1);

namespace App\Web\Auth\Model;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\Database\DatabaseInterface;

final class AuthRepository
{
    private $repository;

    public function __construct(
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private DatabaseInterface $db
    ) {
        $this->repository = $orm->getRepository(User::class);
        $this->seedDefaultUsers();
    }

    private function seedDefaultUsers(): void
    {
        if ($this->repository->select()->count() > 0) {
            return;
        }

        $defaults = [
            ['username' => 'admin',    'email' => 'admin@teqic.com',    'password' => 'admin123',    'role' => 'Admin'],
            ['username' => 'operator', 'email' => 'operator@teqic.com', 'password' => 'operator123', 'role' => 'Operator'],
            ['username' => 'guru',     'email' => 'guru@teqic.com',     'password' => 'guru123',     'role' => 'Guru'],
        ];

        foreach ($defaults as $d) {
            $user = new User();
            $user->username     = $d['username'];
            $user->email        = $d['email'];
            $user->passwordHash = password_hash($d['password'], PASSWORD_DEFAULT);
            $user->role         = $d['role'];
            $user->createdAt    = new \DateTimeImmutable();
            $this->entityManager->persist($user);
        }
        $this->entityManager->run();

        // Seed campus mappings
        $adminUser = $this->repository->select()->where('username', 'admin')->fetchOne();
        if ($adminUser !== null) {
            $auc = new AuthUserKampus();
            $auc->userId = $adminUser->id;
            $auc->kodeKampus = 'G1';
            $this->entityManager->persist($auc);
            
            $auc2 = new AuthUserKampus();
            $auc2->userId = $adminUser->id;
            $auc2->kodeKampus = 'G2';
            $this->entityManager->persist($auc2);
        }
        $operatorUser = $this->repository->select()->where('username', 'operator')->fetchOne();
        if ($operatorUser !== null) {
            $auc = new AuthUserKampus();
            $auc->userId = $operatorUser->id;
            $auc->kodeKampus = 'G1';
            $this->entityManager->persist($auc);
        }
        $guruUser = $this->repository->select()->where('username', 'guru')->fetchOne();
        if ($guruUser !== null) {
            $auc = new AuthUserKampus();
            $auc->userId = $guruUser->id;
            $auc->kodeKampus = 'G2';
            $this->entityManager->persist($auc);
        }
        $this->entityManager->run();
    }

    public function findByUsername(string $username): ?array
    {
        $user = $this->repository->select()->where('username', $username)->fetchOne();
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

    public function createUser(string $username, string $email, string $password, string $role): bool
    {
        $user = new User();
        $user->username     = $username;
        $user->email        = $email;
        $user->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user->role         = $role;
        $user->createdAt    = new \DateTimeImmutable();

        $this->entityManager->persist($user)->run();
        return true;
    }

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
            $user->passwordHash = password_hash($password, PASSWORD_DEFAULT);
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

    /**
     * Return all rows from list_kampus as [kode => nama].
     */
    public function getAllCampuses(): array
    {
        $rows = $this->db->select('kode', 'nama')->from('list_kampus')->orderBy('kode')->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['kode']] = $row['nama'];
        }
        return $result;
    }

    /**
     * Replace the campus assignments for the given user.
     * @param array $campuses array of kode_kampus strings
     */
    public function updateUserCampuses(int $userId, array $campuses): void
    {
        // Delete existing
        $this->db->delete('auth_user_kampus')->where('user_id', $userId)->run();

        // Insert new
        $kampusRepo = $this->orm->getRepository(AuthUserKampus::class);
        foreach ($campuses as $kode) {
            $kode = trim((string) $kode);
            if ($kode === '') continue;
            $auc = new AuthUserKampus();
            $auc->userId    = $userId;
            $auc->kodeKampus = $kode;
            $this->entityManager->persist($auc);
        }
        $this->entityManager->run();
    }

    private function toArray(User $user): array
    {
        $allowedCampuses = [];
        foreach ($user->allowedCampuses as $uc) {
            $allowedCampuses[] = $uc->kodeKampus;
        }
        return [
            'id'               => $user->id,
            'username'         => $user->username,
            'email'            => $user->email,
            'password_hash'    => $user->passwordHash,
            'role'             => $user->role,
            'created_at'       => $user->createdAt?->format('Y-m-d H:i:s'),
            'allowed_campuses' => $allowedCampuses,
        ];
    }
}
