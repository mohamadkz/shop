<?php

namespace App\Domain\Customer\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Shared\Traits\HasUuid;
use Database\Factories\UserFactory;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use APP\Domain\Cart\Models\Basket;
use App\Domain\Catalog\Models\Comment;
use App\Domain\Catalog\Models\Favorite;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUuid;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'phone_verified_at',
        'last_otp_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_otp_sent_at'  => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
