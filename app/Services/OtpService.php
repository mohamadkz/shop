<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class OtpService
{
    protected SmsService $smsService;

    /**
     * مدت اعتبار OTP (دقیقه)
     */
    private const OTP_EXPIRE_MINUTES = 2;

    /**
     * حداقل فاصله بین دو ارسال OTP (ثانیه)
     */
    private const OTP_RESEND_SECONDS = 60;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * ارسال OTP
     */
    public function send(string $phone, ?string $ip = null, ?string $userAgent = null): array {

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false, 'message' => 'کاربری با این شماره موبایل یافت نشد.', 'data' => null
                ];
        }

        if (!$this->canSend($user)) {
            return [
                'success' => false, 'message' => 'لطفاً 60 ثانیه دیگر مجدداً تلاش کنید.', 'data' => null
                ];
        }

        DB::transaction(function () use ($user, $ip, $userAgent) {

            /*
            |--------------------------------------------
            | منقضی کردن OTP های قبلی
            |--------------------------------------------
            */

            $this->invalidateOldOtps($user);

            /*
            |--------------------------------------------
            | تولید OTP
            |--------------------------------------------
            */

            $code = $this->generateCode();

            /*
            |--------------------------------------------
            | ذخیره در دیتابیس
            |--------------------------------------------
            */

            $this->store(user: $user, code: $code, ip: $ip, userAgent: $userAgent);

            /*
            |--------------------------------------------
            | بروزرسانی زمان آخرین ارسال
            |--------------------------------------------
            */
            
            $user->update([
                'last_otp_sent_at' => now()
            ]);

            /*
            |--------------------------------------------
            | ارسال پیامک
            |--------------------------------------------
            */

            $this->smsService->send( phone: $user->phone, message: "کد تایید شما: {$code}");

        });

        return [
            'success' => true,
            'message' => 'کد تایید با موفقیت ارسال شد.',
            'data' => null
        ];
    }

    /**
     * تولید کد شش رقمی
     */
    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * بررسی امکان ارسال OTP
     */
    private function canSend(User $user): bool
    {
        if (!$user->last_otp_sent_at) {
            return true;
        }

        return Carbon::parse($user->last_otp_sent_at)
            ->addSeconds(self::OTP_RESEND_SECONDS)
            ->isPast();
    }

    /**
     * منقضی کردن OTP های قبلی
     */
    private function invalidateOldOtps(User $user): void
    {
        Otp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update([
                'expires_at' => now()
            ]);
    }

    /**
     * ذخیره OTP
     */
    private function store(User $user, string $code, ?string $ip, ?string $userAgent): Otp {

        return Otp::create([
            'user_id' => $user->id,
            /*
            برای پروژه واقعی بهتر است Hash ذخیره شود.
            */
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRE_MINUTES),
            'used_at' => null,
            'ip' => $ip,
            'user_agent' => $userAgent
        ]);
    }
    /**
     * بررسی معتبر بودن OTP
     */
    public function verify(string $phone, string $code): array {

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'کاربر یافت نشد.',
                'data' => null
            ];
        }

        $otp = Otp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'کد تاییدی وجود ندارد.',
                'data' => null
            ];
        }

        if ($otp->used_at) {
            return [
                'success' => false,
                'message' => 'این کد قبلاً استفاده شده است.',
                'data' => null
            ];
        }

        if ($otp->expires_at->isPast()) {
            return [
                'success' => false,
                'message' => 'کد تایید منقضی شده است.',
                'data' => null
            ];
        }

        if (!Hash::check($code, $otp->code)) {
            return [
                'success' => false,
                'message' => 'کد تایید اشتباه است.',
                'data' => null
            ];
        }

        $otp->update([
            'used_at' => now()
        ]);

        return [
            'success' => true,
            'message' => 'کد تایید معتبر است.',
            'data' => $user
        ];
    }
    /**
     * حذف OTP های منقضی شده
     */
    public function deleteExpired(): int
    {
        return Otp::where('expires_at', '<', now())
            ->delete();
    }
    /**
     * حذف OTP های یک کاربر
     */
    public function deleteUserOtps(User $user): void
    {
        $user->otps()->delete();
    }
    /**
     * تولید OTP جدید (Resend)
     */
    public function resend(string $phone, ?string $ip, ?string $userAgent): array {

        return $this->send(
            phone: $phone,
            ip: $ip,
            userAgent: $userAgent
        );
    }
}