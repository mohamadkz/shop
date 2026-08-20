<?php

namespace App\Domain\Customer\Actions;

use App\Domain\Customer\Models\User;

class Logout
{
    public function __invoke(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
