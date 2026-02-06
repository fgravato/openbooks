<?php

declare(strict_types=1);

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'home' => 'dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],
    'views' => false,
    'features' => [
        Features::registration(),
        Features::emailVerification(),
        Features::resetPasswords(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        // Features::passwordConfirmation(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],
];
