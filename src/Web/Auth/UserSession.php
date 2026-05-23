<?php

declare(strict_types=1);

namespace App\Web\Auth;

use App\Web\Rbac\RbacRepository;
use Yiisoft\Session\SessionInterface;

final class UserSession
{
    private const SESSION_KEY_USER_ID = 'user_auth_id';
    private const SESSION_KEY_USERNAME = 'user_auth_username';
    private const SESSION_KEY_ROLE = 'user_auth_role';

    public function __construct(
        private SessionInterface $session,
        private RbacRepository $rbacRepository
    ) {
    }

    public function login(array $user): void
    {
        $this->session->regenerateID();
        $this->session->set(self::SESSION_KEY_USER_ID, (int) $user['id']);
        $this->session->set(self::SESSION_KEY_USERNAME, (string) $user['username']);
        $this->session->set(self::SESSION_KEY_ROLE, (string) $user['role']);
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_KEY_USER_ID);
        $this->session->remove(self::SESSION_KEY_USERNAME);
        $this->session->remove(self::SESSION_KEY_ROLE);
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

    public function hasPermission(string $permission): bool
    {
        $role = $this->getUserRole();
        if ($role === null) {
            return false;
        }
        return $this->rbacRepository->hasPermission($role, $permission);
    }
}
