<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixNotificationSystem extends Command
{
    protected $signature = 'fix:notifications {--user-email= : Email of the user to fix permissions for}';
    protected $description = 'Fix notification system and user permissions';

    public function handle(): int
    {
        $this->info('🔧 Fixing notification system and permissions...');

        // Step 1: Ensure roles and permissions exist
        $this->info('📋 Checking roles and permissions...');
        $this->ensureRolesAndPermissions();

        // Step 2: Fix user permissions
        $userEmail = $this->option('user-email');
        if ($userEmail) {
            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                $this->error("User with email {$userEmail} not found");
                return 1;
            }
        } else {
            $user = User::first();
        }

        if (!$user) {
            $this->error('No user found. Please create a user first.');
            return 1;
        }

        $this->info("👤 Fixing permissions for user: {$user->name} ({$user->email})");
        $this->fixUserPermissions($user);

        // Step 3: Create test notification
        $this->info('🔔 Creating test notification...');
        $this->createTestNotification($user);

        // Step 4: Verify everything is working
        $this->info('✅ Verification...');
        $this->verifySetup($user);

        $this->newLine();
        $this->info('🎉 All fixes applied successfully!');
        $this->info('💡 Next steps:');
        $this->line('   1. Run: npm run build (or npm run dev)');
        $this->line('   2. Visit your company settings page');
        $this->line('   3. Check the notification bell in the header');

        return 0;
    }

    private function ensureRolesAndPermissions(): void
    {
        // Check if roles exist
        if (Role::count() === 0) {
            $this->info('   Creating roles and permissions...');
            $this->call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        } else {
            $this->info('   ✓ Roles and permissions already exist');
        }
    }

    private function fixUserPermissions(User $user): void
    {
        // Get or create a company
        $company = $user->currentCompany ?: Company::first();
        
        if (!$company) {
            $this->info('   Creating test company...');
            $company = Company::create([
                'name' => 'Test Company',
                'email' => $user->email,
                'subscription_plan' => 'pro', // Pro plan to avoid limits
                'timezone' => 'UTC',
                'currency' => 'USD',
            ]);
        }

        // Ensure user is company owner
        $relation = $user->companies()->where('company_id', $company->id)->first();
        if (!$relation) {
            $this->info('   Attaching user to company as owner...');
            $user->companies()->attach($company->id, [
                'role' => 'company_owner',
                'is_owner' => true,
            ]);
        } else {
            $this->info('   Updating user to company owner...');
            $user->companies()->updateExistingPivot($company->id, [
                'role' => 'company_owner',
                'is_owner' => true,
            ]);
        }

        // Set as current company
        $user->update(['current_company_id' => $company->id]);

        // Assign company_owner role
        $ownerRole = Role::where('name', 'company_owner')->first();
        if ($ownerRole && !$user->hasRole('company_owner')) {
            $this->info('   Assigning company_owner role...');
            $user->assignRole('company_owner');
        }

        $this->info("   ✓ User is now owner of company: {$company->name}");
    }

    private function createTestNotification(User $user): void
    {
        $company = $user->currentCompany;
        
        // Create a welcome notification
        SystemNotification::create([
            'type' => 'system_welcome',
            'level' => 'info',
            'title' => 'Welcome to MoBoards!',
            'message' => 'Your notification system is working correctly. You can now receive real-time updates about your billboards, contracts, and team activities.',
            'data' => ['source' => 'system_fix'],
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        // Create a settings access notification
        SystemNotification::create([
            'type' => 'settings_access',
            'level' => 'success', 
            'title' => 'Settings Access Granted',
            'message' => 'You now have full access to company settings. Configure your notification preferences, billing, and team management.',
            'data' => ['action' => 'access_granted'],
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        $this->info('   ✓ Created test notifications');
    }

    private function verifySetup(User $user): void
    {
        $company = $user->currentCompany;
        
        $this->line("   User: {$user->name}");
        $this->line("   Company: {$company->name}");
        $this->line("   Roles: " . $user->getRoleNames()->implode(', '));
        $this->line("   Is Owner: " . ($user->isOwnerOf($company) ? 'Yes' : 'No'));
        $this->line("   Company Role: " . $user->getRoleInCompany($company));

        // Test policy
        $policy = new \App\Policies\CompanyPolicy();
        $canManageSettings = $policy->manageSettings($user, $company);
        $this->line("   Can Access Settings: " . ($canManageSettings ? 'Yes' : 'No'));

        // Check notifications
        $notificationCount = SystemNotification::where('company_id', $company->id)->count();
        $this->line("   Notifications Count: {$notificationCount}");
    }
}
