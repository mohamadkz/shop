<?php

namespace Database\Seeders;

use App\Domain\Cart\Models\DiscountCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Cart discount codes. No foreign keys — can run any time relative
 * to the other seeders — but is ordered before OrderSeeder in
 * DatabaseSeeder because OrderSeeder randomly applies existing codes to
 * some seeded orders and needs them to already exist.
 */
class DiscountCodeSeeder extends Seeder
{
    private const RANDOM_CODES_TO_CREATE = 10;

    public function run(): void
    {
        if (DiscountCode::query()->exists()) {
            $this->command?->info('Discount codes already seeded — skipping.');

            return;
        }

        DB::transaction(function () {
            // Fixed, memorable codes for manual/demo testing.
            DiscountCode::factory()->create([
                'code'         => 'WELCOME10',
                'type'         => \App\Domain\Cart\Enums\DiscountType::Percentage,
                'percent'      => 10,
                'fixed_amount' => null,
                'max_discount' => 200_000,
                'expired_at'   => now()->addYear(),
                'usage_limit'  => null,
            ]);

            DiscountCode::factory()->expired()->create([
                'code' => 'EXPIRED20',
            ]);

            DiscountCode::factory()->count(self::RANDOM_CODES_TO_CREATE)->create();
        });

        $this->command?->info(sprintf('Seeded %d discount codes.', self::RANDOM_CODES_TO_CREATE + 2));
    }
}
