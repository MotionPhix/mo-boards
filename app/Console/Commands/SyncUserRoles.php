<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class SyncUserRoles extends Command
{
    protected $signature = 'roles:sync';
    protected $description = 'Synchronize user roles with their company role settings';

    public function handle()
    {
        $this->info('Starting role synchronization...');
        
        // First sync all company owners
        $companies = Company::with('users')->get();
        $ownerCount = 0;
        
        foreach ($companies as $company) {
            foreach ($company->users()->wherePivot('is_owner', true)->get() as $owner) {
                // Set company_owner role in pivot table
                $company->users()->updateExistingPivot($owner->id, [
                    'role' => 'company_owner'
                ]);
                
                // Assign role using Spatie Permission if not already assigned
                if (!$owner->hasRole('company_owner')) {
                    $owner->assignRole('company_owner');
                    $ownerCount++;
                }
            }
        }
        
        $this->info("Synchronized {$ownerCount} company owners");
        
        // Find any super admin users (typically user ID 1)
        $admin = User::find(1);
        if ($admin && !$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
            $this->info("Assigned super_admin role to user ID 1");
        }
        
        $this->info('Role synchronization complete!');
        
        return Command::SUCCESS;
    }
}
