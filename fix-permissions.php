<?php

/**
 * Quick script to fix user permissions and roles for company settings access
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== MoBoards Permission Fix Script ===\n\n";

// Check if roles exist, if not run seeder
$ownerRole = Role::firstWhere('name', 'company_owner');
if (!$ownerRole) {
    echo "Running roles and permissions seeder...\n";
    Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    echo Artisan::output();
}

// Get first user (assuming this is the user having issues)
$user = User::first();
if (!$user) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

echo "Found user: {$user->name} ({$user->email})\n";

// Get or create first company
$company = Company::first();
if (!$company) {
    echo "Creating a test company...\n";
    $company = Company::create([
        'name' => 'Test Company',
        'email' => $user->email,
        'subscription_plan' => 'pro', // Give pro plan to avoid limits
    ]);
}

echo "Found/created company: {$company->name}\n";

// Ensure user is attached to company as owner
$userCompanyRelation = $user->companies()->where('company_id', $company->id)->first();
if (!$userCompanyRelation) {
    echo "Attaching user to company as owner...\n";
    $user->companies()->attach($company->id, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
} else {
    // Update existing relation to ensure they're an owner
    echo "Updating user company relation to owner...\n";
    $user->companies()->updateExistingPivot($company->id, [
        'role' => 'company_owner',
        'is_owner' => true,
    ]);
}

// Set this as the user's current company
$user->update(['current_company_id' => $company->id]);

// Ensure user has company_owner role
$ownerRole = Role::firstWhere('name', 'company_owner');
if ($ownerRole && !$user->hasRole('company_owner')) {
    echo "Assigning company_owner role to user...\n";
    $user->assignRole('company_owner');
}

echo "\n=== Verification ===\n";
echo "User: {$user->name}\n";
echo "Current Company: {$user->currentCompany->name}\n";
echo "User Roles: " . $user->getRoleNames()->implode(', ') . "\n";
echo "Is Owner: " . ($user->isOwnerOf($user->currentCompany) ? 'Yes' : 'No') . "\n";
echo "Role in Company: " . $user->getRoleInCompany($user->currentCompany) . "\n";

// Test authorization
try {
    $policy = new App\Policies\CompanyPolicy();
    $canManage = $policy->manageSettings($user, $user->currentCompany);
    echo "Can Manage Settings: " . ($canManage ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Authorization check failed: " . $e->getMessage() . "\n";
}

echo "\n=== Creating Test Notifications ===\n";

// Create a test notification
$notification = \App\Models\SystemNotification::create([
    'type' => 'system_update',
    'level' => 'info',
    'title' => 'Welcome to MoBoards!',
    'message' => 'Your notification system is now working correctly.',
    'company_id' => $company->id,
    'user_id' => $user->id,
]);

echo "Created test notification: {$notification->title}\n";

echo "\n=== Fix Complete! ===\n";
echo "You should now be able to access company settings.\n";
echo "Please run 'npm run build' or 'npm run dev' to ensure frontend is compiled.\n";
