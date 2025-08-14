<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing billing plans with detailed features
        $plans = [
            [
                'key' => 'free',
                'name' => 'Free',
                'features' => [
                    'basic_billboards',
                    'basic_contracts', 
                    'owner_only',
                    'basic_templates',
                    'basic_reporting',
                    'email_support',
                    'max_billboards_5',
                    'max_contracts_2',
                ]
            ],
            [
                'key' => 'pro',
                'name' => 'Pro',
                'features' => [
                    'basic_billboards',
                    'basic_contracts',
                    'team_invitations',
                    'basic_templates',
                    'advanced_analytics',
                    'priority_support',
                    'email_notifications',
                    'max_billboards_25',
                    'max_contracts_15',
                    'max_team_members_3',
                    'max_templates_5',
                ]
            ],
            [
                'key' => 'business',
                'name' => 'Business',
                'features' => [
                    'unlimited_billboards',
                    'unlimited_contracts',
                    'unlimited_team_members',
                    'unlimited_templates',
                    'advanced_analytics',
                    'advanced_insights',
                    'phone_support',
                    'email_notifications',
                    'sms_notifications',
                    'real_time_notifications',
                    'api_access',
                    'export_functionality',
                    'bulk_operations',
                ]
            ]
        ];

        foreach ($plans as $plan) {
            DB::table('billing_plans')
                ->where('key', $plan['key'])
                ->update([
                    'features' => json_encode($plan['features']),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to simple feature descriptions
        $oldFeatures = [
            'free' => ['Basic features'],
            'pro' => ['Everything in Free', 'Priority support', 'Advanced features'],
            'business' => ['Everything in Pro', 'Team features', 'Billing automation']
        ];

        foreach ($oldFeatures as $key => $features) {
            DB::table('billing_plans')
                ->where('key', $key)
                ->update([
                    'features' => json_encode($features),
                    'updated_at' => now()
                ]);
        }
    }
};
