<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingPlansSeeder extends Seeder
{
    public function run(): void
    {
        // Seed core plans and pricing; editable later via admin panel
        $plans = [
            [
                'key' => 'free',
                'name' => 'Starter',
                'price' => 0,
                'currency' => 'USD',
                'interval' => 'month',
                'interval_count' => 1,
                'features' => json_encode([]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pro',
                'name' => 'Professional',
                'price' => 29,
                'currency' => 'USD',
                'interval' => 'month',
                'interval_count' => 1,
                'features' => json_encode([]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'business',
                'name' => 'Enterprise',
                'price' => 99,
                'currency' => 'USD',
                'interval' => 'month',
                'interval_count' => 1,
                'features' => json_encode([]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($plans as $plan) {
            // Upsert by key
            DB::table('billing_plans')->updateOrInsert(
                ['key' => $plan['key']],
                $plan
            );
        }
    }
}
