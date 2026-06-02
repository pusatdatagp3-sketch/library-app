<?php

declare(strict_types=1);

namespace App\Web\Auth\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(role: 'authUserKampus', table: 'auth_user_kampus')]
class AuthUserKampus
{
    #[Column(type: 'integer', name: 'user_id', primary: true)]
    public ?int $userId = null;

    #[Column(type: 'string(4)', name: 'kode_kampus', primary: true)]
    public ?string $kodeKampus = null;

    #[BelongsTo(target: User::class, innerKey: 'userId', fkAction: 'CASCADE', load: 'lazy')]
    public ?User $user = null;
}
