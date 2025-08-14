<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeatureRulesSeeder extends Seeder
{
    public function run(): void
    {
        // Comprehensive plan feature rules using our SubscriptionFeature enum values
        $rules = [
            'free' => [
                // Limits
                'billboards.max' => 5,
                'contracts.max' => 2,
                'team.members.max' => 1, // Owner only
                'templates.max' => 1,
                
                // Features  
                'team.invitations' => false,
                'analytics.advanced' => false,
                'analytics.insights' => false,
                'notifications.email' => false,
                'notifications.sms' => false,
                'notifications.realtime' => false,
                'api.access' => false,
                'export.enabled' => false,
                'bulk.operations' => false,
                'support.priority' => false,
                'support.phone' => false,
            ],
            'pro' => [
                // Limits
                'billboards.max' => 25,
                'contracts.max' => 15,
                'team.members.max' => 3,
                'templates.max' => 5,
                
                // Features
                'team.invitations' => true,
                'analytics.advanced' => true,
                'analytics.insights' => false,
                'notifications.email' => true,
                'notifications.sms' => false,
                'notifications.realtime' => false,
                'api.access' => false,
                'export.enabled' => false,
                'bulk.operations' => false,
                'support.priority' => true,
                'support.phone' => false,
            ],
            'business' => [
                // Limits - unlimited
                'billboards.max' => 'unlimited',
                'contracts.max' => 'unlimited', 
                'team.members.max' => 'unlimited',
                'templates.max' => 'unlimited',
                
                // Features - all enabled
                'team.invitations' => true,
                'analytics.advanced' => true,
                'analytics.insights' => true,
                'notifications.email' => true,
                'notifications.sms' => true,
                'notifications.realtime' => true,
                'api.access' => true,
                'export.enabled' => true,
                'bulk.operations' => true,
                'support.priority' => true,
                'support.phone' => true,
            ],
        ];

        // Clear existing rules first
        DB::table('plan_feature_rules')->truncate();

        foreach ($rules as $plan => $kv) {
            foreach ($kv as $k => $v) {
                DB::table('plan_feature_rules')->insert([
                    'plan_id' => $plan,
                    'key' => $k,
                    'value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
