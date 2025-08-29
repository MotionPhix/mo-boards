<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix company_user pivot entries where role is null but is_owner is true
        DB::table('company_user')
            ->where('is_owner', true)
            ->whereNull('role')
            ->update(['role' => 'company_owner']);
        
        // For non-owners without a role, set a default role of viewer
        DB::table('company_user')
            ->where('is_owner', false)
            ->whereNull('role')
            ->update(['role' => 'viewer']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't reliably reverse this migration as we don't know
        // which roles were null before
    }
};
