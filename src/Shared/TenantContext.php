<?php

declare(strict_types=1);

namespace App\Shared;

final class TenantContext
{
    private ?string $activeCampusCode = null;
    private array $allowedCampusCodes = [];

    public function getActiveCampusCode(): ?string
    {
        return $this->activeCampusCode;
    }

    public function setActiveCampusCode(?string $activeCampusCode): void
    {
        $this->activeCampusCode = $activeCampusCode;
    }

    public function getAllowedCampusCodes(): array
    {
        return $this->allowedCampusCodes;
    }

    public function setAllowedCampusCodes(array $allowedCampusCodes): void
    {
        $this->allowedCampusCodes = $allowedCampusCodes;
    }

    public function hasAccess(?string $campusCode): bool
    {
        if ($campusCode === null) {
            return false;
        }
        return in_array($campusCode, $this->allowedCampusCodes, true);
    }
}
