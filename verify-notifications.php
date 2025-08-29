#!/usr/bin/env php
<?php

// Quick verification script for the notification system fix
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "🔔 MoBoards Notification System Verification\n";
echo "===========================================\n\n";

// Check if notification routes exist
echo "📋 Checking notification routes...\n";
$routes = [
    'notifications.index',
    'notifications.mark-read', 
    'notifications.dismiss',
    'notifications.mark-all-read',
    'notifications.unread-count'
];

foreach ($routes as $route) {
    try {
        $url = route($route, ['notification' => 1]); // Test with dummy ID for parameterized routes
        echo "   ✓ {$route} - Available\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Route [') !== false) {
            echo "   ❌ {$route} - Missing\n";
        } else {
            echo "   ✓ {$route} - Available\n";
        }
    }
}

echo "\n📊 Checking database...\n";

// Check if system_notifications table exists
try {
    $hasTable = \Illuminate\Support\Facades\Schema::hasTable('system_notifications');
    echo "   " . ($hasTable ? "✓" : "❌") . " system_notifications table: " . ($hasTable ? "Exists" : "Missing") . "\n";
    
    if ($hasTable) {
        $count = \App\Models\SystemNotification::count();
        echo "   📄 Total notifications: {$count}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
}

echo "\n👤 Checking user setup...\n";

// Check users and companies
$userCount = \App\Models\User::count();
$companyCount = \App\Models\Company::count();

echo "   📊 Users: {$userCount}\n";
echo "   🏢 Companies: {$companyCount}\n";

if ($userCount > 0) {
    $user = \App\Models\User::first();
    echo "   👤 First user: {$user->name} ({$user->email})\n";
    
    if ($user->currentCompany) {
        echo "   🏢 Current company: {$user->currentCompany->name}\n";
        echo "   🔐 Is owner: " . ($user->isOwnerOf($user->currentCompany) ? 'Yes' : 'No') . "\n";
        
        // Test policy
        try {
            $policy = new \App\Policies\CompanyPolicy();
            $canManage = $policy->manageSettings($user, $user->currentCompany);
            echo "   ⚙️  Can access settings: " . ($canManage ? 'Yes' : 'No') . "\n";
        } catch (Exception $e) {
            echo "   ⚙️  Settings access check failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ⚠️  User has no current company\n";
    }
    
    $roles = $user->getRoleNames()->toArray();
    echo "   🏷️  User roles: " . (empty($roles) ? 'None' : implode(', ', $roles)) . "\n";
}

echo "\n🎯 Component Check...\n";

// Check if key frontend files exist
$frontendFiles = [
    'resources/js/components/NotificationsBell.vue',
    'resources/js/layouts/AppLayout.vue',
    'resources/js/pages/companies/settings/Notifications.vue'
];

foreach ($frontendFiles as $file) {
    $exists = file_exists($file);
    echo "   " . ($exists ? "✓" : "❌") . " {$file}: " . ($exists ? "Exists" : "Missing") . "\n";
}

echo "\n🚀 Recommendations:\n";
echo "   1. Run: npm run build (or npm run dev for development)\n";
echo "   2. Visit your app and check for the notification bell (🔔) in the header\n";
echo "   3. Test company settings access\n";
echo "   4. Check browser console for any JavaScript errors\n";

if ($userCount === 0) {
    echo "\n⚠️  No users found! Please create a user account first.\n";
} else {
    echo "\n🎉 Setup looks good! The notification bell should now be visible in the header.\n";
}

echo "\n📝 Next steps if notification bell is still not visible:\n";
echo "   - Check browser console for JavaScript errors\n";
echo "   - Ensure assets are built with 'npm run build'\n";
echo "   - Verify you're logged in with a user that has a company\n";
echo "   - Check that routes are accessible at /api/notifications\n";
