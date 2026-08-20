<?php

namespace App\Domain\Customer\Services;

use App\Domain\Customer\Models\Otp;
use App\Domain\Customer\Models\User;
use App\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    private const CODE_LENGTH = 6;
    private const EXPIRE_MINUTES = 2;
    private const RESEND_SECONDS = 60;
    private const MAX_VERIFY_ATTEMPTS = 5;

    public function __construct(private readonly SmsService $sms)
    {
    }

    public function send(User $user, ?string $ip = null, ?string $userAgent = null): void
    {
        if ($user->last_otp_sent_at && $user->last_otp_sent_at->diffInSeconds(now()) < self::RESEND_SECONDS) {
            throw new DomainException('لطفاً کمی صبر کنید و دوباره تلاش کنید.', 429, 'otp_throttled');
        }

        $code = (string) random_int(100000, 999999);

        DB::transaction(function () use ($user, $code, $ip, $userAgent) {
            // Invalidate any previous unused codes for this user.
            $user->otps()->whereNull('used_at')->update(['used_at' => now()]);

            $user->otps()->create([
                'code_hash'  => $this->hash($code),
                'expires_at' => now()->addMinutes(self::EXPIRE_MINUTES),
                'attempts'   => 0,
                'ip'         => $ip,
                'user_agent' => $userAgent,
            ]);

            $user->update(['last_otp_sent_at' => now()]);
        });

        $this->sms->send($user->phone, "کد تایید شما: {$code}");
    }

    public function verify(User $user, string $code): bool
    {
        $limiterKey = "otp-verify:{$user->id}";

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_VERIFY_ATTEMPTS)) {
            throw new DomainException('تعداد تلاش‌های شما بیش از حد مجاز است.', 429, 'otp_too_many_attempts');
        }

        $otp = $user->otps()->whereNull('used_at')->latest()->first();

        if (! $otp || $otp->isExpired()) {
            RateLimiter::hit($limiterKey, self::EXPIRE_MINUTES * 60);
            throw new DomainException('کد نامعتبر یا منقضی شده است.', 422, 'otp_invalid');
        }

        if (! hash_equals($otp->code_hash, $this->hash($code))) {
            RateLimiter::hit($limiterKey, self::EXPIRE_MINUTES * 60);
            $otp->increment('attempts');
            throw new DomainException('کد نامعتبر است.', 422, 'otp_invalid');
        }

        RateLimiter::clear($limiterKey);

        $otp->update(['used_at' => now()]);
        $user->update(['phone_verified_at' => now()]);

        return true;
    }

    private function hash(string $code): string
    {
        // HMAC keyed by app key: fast (unlike bcrypt) and appropriate for a
        // short-lived, 6-digit, single-use secret.
        return hash_hmac('sha256', $code, config('app.key'));
    }
}
