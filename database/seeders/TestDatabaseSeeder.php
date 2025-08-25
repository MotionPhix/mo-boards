<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed plan feature rules for testing
        $planFeatures = [
            [
                'plan_id' => SubscriptionPlan::FREE->value,
                'key' => 'billboards.max',
                'value' => '5',
            ],
            [
                'plan_id' => SubscriptionPlan::PRO->value,
                'key' => 'billboards.max',
                'value' => '25',
            ],
            [
                'plan_id' => SubscriptionPlan::BUSINESS->value,
                'key' => 'billboards.max',
                'value' => 'unlimited',
            ],
            [
                'plan_id' => SubscriptionPlan::FREE->value,
                'key' => 'team.members.max',
                'value' => '2',
            ],
            [
                'plan_id' => SubscriptionPlan::PRO->value,
                'key' => 'team.members.max',
                'value' => '5',
            ],
            [
                'plan_id' => SubscriptionPlan::BUSINESS->value,
                'key' => 'team.members.max',
                'value' => 'unlimited',
            ],
        ];

        DB::table('plan_feature_rules')->insert($planFeatures);

        // Seed billing plans (prices & metadata) for tests
        $this->call(\Database\Seeders\BillingPlansSeeder::class);
    }
}
