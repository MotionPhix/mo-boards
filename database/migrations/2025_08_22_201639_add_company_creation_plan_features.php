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
        // Add company creation limits to plan feature rules
        $features = [
            // Free plan - 1 company only
            ['plan_id' => 'free', 'key' => 'companies.max', 'value' => '1'],

            // Pro plan - up to 3 companies
            ['plan_id' => 'pro', 'key' => 'companies.max', 'value' => '3'],

            // Business plan - unlimited companies
            ['plan_id' => 'business', 'key' => 'companies.max', 'value' => 'unlimited'],
        ];

        foreach ($features as $feature) {
            DB::table('plan_feature_rules')->insert(array_merge($feature, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plan_feature_rules')
            ->where('key', 'companies.max')
            ->delete();
    }
};
