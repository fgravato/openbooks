<?php

declare(strict_types=1);

use App\Domains\Identity\Models\Organization;

return [
    'organization_scope' => [
        'except' => [
            Organization::class,
        ],
    ],
];
