<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // 1) Temporarily allow both legacy and new values to avoid truncation during mapping
            DB::statement("ALTER TABLE `companies` MODIFY `subscription_plan` ENUM('starter','professional','enterprise','free','pro','business') NOT NULL DEFAULT 'starter'");
        }

        // 2) Map legacy values to new keys
        DB::table('companies')->where('subscription_plan', 'starter')->update(['subscription_plan' => 'free']);
        DB::table('companies')->where('subscription_plan', 'professional')->update(['subscription_plan' => 'pro']);
        DB::table('companies')->where('subscription_plan', 'enterprise')->update(['subscription_plan' => 'business']);

        if ($driver === 'mysql') {
            // 3) Shrink enum to the new set
            DB::statement("ALTER TABLE `companies` MODIFY `subscription_plan` ENUM('free','pro','business') NOT NULL DEFAULT 'free'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // 1) Temporarily allow both new and legacy values to avoid truncation during mapping back
            DB::statement("ALTER TABLE `companies` MODIFY `subscription_plan` ENUM('starter','professional','enterprise','free','pro','business') NOT NULL DEFAULT 'free'");
        }

        // 2) Map new keys back to legacy names
        DB::table('companies')->where('subscription_plan', 'free')->update(['subscription_plan' => 'starter']);
        DB::table('companies')->where('subscription_plan', 'pro')->update(['subscription_plan' => 'professional']);
        DB::table('companies')->where('subscription_plan', 'business')->update(['subscription_plan' => 'enterprise']);

        if ($driver === 'mysql') {
            // 3) Shrink enum back to legacy set
            DB::statement("ALTER TABLE `companies` MODIFY `subscription_plan` ENUM('starter','professional','enterprise') NOT NULL DEFAULT 'starter'");
        }
    }
};
