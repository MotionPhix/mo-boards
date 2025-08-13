<?php

namespace Database\Seeders;

use App\Models\BillingPlan;
use Illuminate\Database\Seeder;

class BillingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = config('billing.plans', []);

        foreach ($plans as $plan) {
            BillingPlan::updateOrCreate(['key' => $plan['key']], [
                'name' => $plan['name'],
                'price' => $plan['price'],
                'currency' => $plan['currency'] ?? 'MWK',
                'interval' => $plan['interval'] ?? 'month',
                'interval_count' => $plan['interval_count'] ?? 1,
                'features' => $plan['features'] ?? [],
                'active' => $plan['active'] ?? true,
            ]);
        }
    }
}
