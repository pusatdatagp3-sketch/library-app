<?php

declare(strict_types=1);

namespace App\Web\Auth\Model;

use App\Web\Rbac\Model\RbacRepository;
use Yiisoft\Session\SessionInterface;
use Cycle\Database\DatabaseInterface;

final class UserSession
{
    private const SESSION_KEY_USER_ID = 'user_auth_id';
    private const SESSION_KEY_USERNAME = 'user_auth_username';
    private const SESSION_KEY_ROLE = 'user_auth_role';
    private const SESSION_KEY_ALLOWED_CAMPUSES = 'user_auth_allowed_campuses';
    private const SESSION_KEY_ACTIVE_CAMPUS_CODE = 'user_auth_active_campus_code';

    public function __construct(
        private SessionInterface $session,
        private RbacRepository $rbacRepository,
        private DatabaseInterface $db
    ) {
    }

    public function getCampusList(): array
    {
        $allowed = $this->getAllowedCampuses();
        if (empty($allowed)) {
            return [];
        }
        $rows = $this->db->select('kode', 'nama')
            ->from('list_kampus')
            ->where('kode', 'in', $allowed)
            ->fetchAll();
        $list = [];
        foreach ($rows as $row) {
            $list[$row['kode']] = $row['nama'];
        }
        return $list;
    }

    public function login(array $user): void
    {
        $this->session->regenerateID();
        $this->session->set(self::SESSION_KEY_USER_ID, (int) $user['id']);
        $this->session->set(self::SESSION_KEY_USERNAME, (string) $user['username']);
        $this->session->set(self::SESSION_KEY_ROLE, (string) $user['role']);
        
        $allowed = $user['allowed_campuses'] ?? [];
        $this->session->set(self::SESSION_KEY_ALLOWED_CAMPUSES, $allowed);
        if (!empty($allowed)) {
            $this->session->set(self::SESSION_KEY_ACTIVE_CAMPUS_CODE, $allowed[0]);
        } else {
            $this->session->set(self::SESSION_KEY_ACTIVE_CAMPUS_CODE, null);
        }
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_KEY_USER_ID);
        $this->session->remove(self::SESSION_KEY_USERNAME);
        $this->session->remove(self::SESSION_KEY_ROLE);
        $this->session->remove(self::SESSION_KEY_ALLOWED_CAMPUSES);
        $this->session->remove(self::SESSION_KEY_ACTIVE_CAMPUS_CODE);
        $this->session->destroy();
    }

    public function getAllowedCampuses(): array
    {
        return $this->session->get(self::SESSION_KEY_ALLOWED_CAMPUSES) ?? [];
    }

    public function getActiveCampus(): ?string
    {
        return $this->session->get(self::SESSION_KEY_ACTIVE_CAMPUS_CODE);
    }

    public function setActiveCampus(string $campusCode): void
    {
        if (in_array($campusCode, $this->getAllowedCampuses(), true)) {
            $this->session->set(self::SESSION_KEY_ACTIVE_CAMPUS_CODE, $campusCode);
        }
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
