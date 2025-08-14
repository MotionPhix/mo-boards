<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security()->ignoring('md5');

// Test that controllers are classes
arch()
    ->expect('App\Http\Controllers')
    ->toBeClasses();

// Test that controllers are only used in appropriate places
arch()
    ->expect('App\Http\Controllers')
    ->toOnlyBeUsedIn([
        'App\Http\Controllers',
        'routes',
        'tests',
    ]);
