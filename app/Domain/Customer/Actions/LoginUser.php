<?php

namespace App\Domain\Customer\Actions;

use App\Domain\Customer\Models\User;
use App\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\Hash;

class LoginUser
{
    /**
     * @return array{user: User, token: string}
     */
    public function __invoke(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new DomainException('ایمیل یا رمز عبور اشتباه است.', 401, 'invalid_credentials');
        }

        $token = $user->createToken($deviceName ?? 'api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
