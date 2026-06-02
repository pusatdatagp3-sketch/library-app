<?php

declare(strict_types=1);

namespace App\Web\Auth\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\HasMany;

#[Entity(role: 'user', table: 'users')]
class User
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(50)', unique: true)]
    public string $username = '';

    #[Column(type: 'string(100)', unique: true)]
    public string $email = '';

    #[Column(type: 'string(255)', name: 'password_hash')]
    public string $passwordHash = '';

    #[Column(type: 'string(50)')]
    public string $role = '';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[HasMany(target: AuthUserKampus::class, innerKey: 'id', outerKey: 'userId', load: 'eager')]
    public array $allowedCampuses = [];
}
