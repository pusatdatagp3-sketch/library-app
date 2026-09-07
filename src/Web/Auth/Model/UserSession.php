<?php

declare(strict_types=1);

namespace App\Web\Auth\Model;

use App\Web\Rbac\Model\RbacRepository;
use Yiisoft\Session\SessionInterface;

/**
 * Mengelola state autentikasi pengguna dalam session.
 */
final class UserSession
{
    private const SESSION_KEY_USER_ID          = 'user_auth_id';
    private const SESSION_KEY_USERNAME         = 'user_auth_username';
    private const SESSION_KEY_ROLE             = 'user_auth_role';
    private const SESSION_KEY_ALLOWED_CAMPUSES = 'user_auth_allowed_campuses';
    private const SESSION_KEY_ACTIVE_CAMPUS    = 'user_auth_active_campus';

    public function __construct(
        private SessionInterface $session,
        private RbacRepository $rbacRepository,
    ) {
    }

    public function login(array $user): void
    {
        $this->session->regenerateID();
        $this->session->set(self::SESSION_KEY_USER_ID, (int) $user['id']);
        $this->session->set(self::SESSION_KEY_USERNAME, (string) $user['username']);
        $this->session->set(self::SESSION_KEY_ROLE, (string) $user['role']);
        $this->session->set(self::SESSION_KEY_ALLOWED_CAMPUSES, (array) ($user['allowed_campuses'] ?? []));
        $this->session->set(self::SESSION_KEY_ACTIVE_CAMPUS, isset($user['active_campus']) ? (string) $user['active_campus'] : null);
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_KEY_USER_ID);
        $this->session->remove(self::SESSION_KEY_USERNAME);
        $this->session->remove(self::SESSION_KEY_ROLE);
        $this->session->remove(self::SESSION_KEY_ALLOWED_CAMPUSES);
        $this->session->remove(self::SESSION_KEY_ACTIVE_CAMPUS);
        $this->session->destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->session->has(self::SESSION_KEY_USER_ID);
    }

    public function getUserId(): ?int
    {
        $id = $this->session->get(self::SESSION_KEY_USER_ID);
        return $id !== null ? (int) $id : null;
    }

    public function getUsername(): ?string
    {
        $username = $this->session->get(self::SESSION_KEY_USERNAME);
        return $username !== null ? (string) $username : null;
    }

    public function getUserRole(): ?string
    {
        $role = $this->session->get(self::SESSION_KEY_ROLE);
        return $role !== null ? (string) $role : null;
    }

    /**
     * Mengembalikan daftar kode kampus yang diizinkan untuk pengguna ini.
     */
    public function getAllowedCampuses(): array
    {
        $campuses = $this->session->get(self::SESSION_KEY_ALLOWED_CAMPUSES);
        return is_array($campuses) ? $campuses : [];
    }

    /**
     * Mengembalikan kode kampus yang sedang aktif untuk pengguna ini.
     */
    public function getActiveCampus(): ?string
    {
        $campus = $this->session->get(self::SESSION_KEY_ACTIVE_CAMPUS);
        return $campus !== null ? (string) $campus : null;
    }

    /**
     * Memeriksa apakah pengguna yang sedang login memiliki izin tertentu.
     * Role 'super-admin' secara otomatis mendapatkan bypass penuh (return true).
     */
    public function hasPermission(string $permission): bool
    {
        $role = $this->getUserRole();
        if ($role === null) {
            return false;
        }
        // Bypass penuh untuk Super Admin
        if ($role === 'super-admin') {
            return true;
        }
        return $this->rbacRepository->hasPermission($role, $permission);
    }
}
