<?php

declare(strict_types=1);

use App\Console;

return [
    'hello' => Console\HelloCommand::class,
    'santri:sync' => Console\SyncSantriCommand::class,
];
