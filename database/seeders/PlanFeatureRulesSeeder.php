<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeatureRulesSeeder extends Seeder
{
    public function run(): void
    {
        // A simple key-value store for plan feature limits
        // Table will be created via migration below
        $rules = [
            // plan_id => [feature_key => value]
            'free' => [
                'billboards.max' => 5,
                'contracts.max' => 10,
                'team.members.max' => 2,
                'analytics.access' => false,
                'export.enabled' => false,
            ],
            'pro' => [
                'billboards.max' => 100,
                'contracts.max' => 500,
                'team.members.max' => 10,
                'analytics.access' => true,
                'export.enabled' => true,
            ],
            'business' => [
                'billboards.max' => 500,
                'contracts.max' => 2000,
                'team.members.max' => 25,
                'analytics.access' => true,
                'export.enabled' => true,
                'priority.support' => true,
            ],
        ];

        foreach ($rules as $plan => $kv) {
            foreach ($kv as $k => $v) {
                DB::table('plan_feature_rules')->updateOrInsert([
                    'plan_id' => $plan,
                    'key' => $k,
                ], [
                    'value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v,
                ]);
            }
        }
    }
}
