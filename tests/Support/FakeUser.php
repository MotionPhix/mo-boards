<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Notifications\Notifiable;

final class FakeUser implements AuthenticatableContract
{
    use Authenticatable, Notifiable;

    public $id = 1;
    public $email = 'owner@example.com';
    public $name = 'Owner';
    public $currentCompany;

    public function can($ability, $arguments = []): bool
    {
        return true;
    }
}
