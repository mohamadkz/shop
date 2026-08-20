<?php

namespace Database\Seeders;

use App\Domain\Customer\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Customer accounts. Runs first — every other domain's seeded data
 * (comments, favorites, orders, payments) references a user_id, so users
 * must exist before anything else.
 */
class UserSeeder extends Seeder
{
    private const CUSTOMER_COUNT = 30;

    public function run(): void
    {
        // Idempotent: re-running `db:seed` shouldn't duplicate the fixed
        // demo account or balloon the customer count on every run.
        if (User::query()->exists()) {
            $this->command?->info('Users already seeded — skipping.');

            return;
        }

        DB::transaction(function () {
            // A fixed, known-credentials account for local login/demo/testing.
            User::factory()->verified()->create([
                'name'  => 'Demo Customer',
                'email' => 'demo@shop.test',
                'phone' => '09120000000',
            ]);

            User::factory()->count(self::CUSTOMER_COUNT)->create();
        });

        $this->command?->info(sprintf('Seeded %d users.', self::CUSTOMER_COUNT + 1));
    }
}
