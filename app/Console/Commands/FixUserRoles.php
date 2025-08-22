<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user roles where is_owner=1 but role is null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing user roles...');

        // Ensure roles exist
        $roles = ['super_admin', 'company_owner', 'manager', 'editor', 'viewer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Find users who are owners but have null role
        $usersToFix = \DB::table('company_user')
            ->where('is_owner', 1)
            ->whereNull('role')
            ->get();

        $this->info("Found {$usersToFix->count()} users to fix");

        foreach ($usersToFix as $companyUser) {
            // Update the pivot table
            \DB::table('company_user')
                ->where('id', $companyUser->id)
                ->update(['role' => 'company_owner']);

            // Get the user and assign the Spatie role
            $user = User::find($companyUser->user_id);
            if ($user && !$user->hasRole('company_owner')) {
                $user->assignRole('company_owner');
            }

            $this->info("Fixed user {$companyUser->user_id} in company {$companyUser->company_id}");
        }

        $this->info('User roles fixed successfully!');
    }
}
