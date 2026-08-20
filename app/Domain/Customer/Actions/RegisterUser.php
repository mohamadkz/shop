<?php

namespace App\Domain\Customer\Actions;

use App\Domain\Customer\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function __invoke(string $name, string $email, string $phone, string $password): User
    {
        return User::create([
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'password' => Hash::make($password),
        ]);
    }
}
