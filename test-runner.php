<?php

/**
 * Simple Test Runner for Team Invitation Tests
 * Run this with: php test-runner.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;

echo "=== Team Invitation Test Runner ===\n";

// Basic environment check
echo "1. Checking PHP version: " . PHP_VERSION . "\n";

// Check if Laravel is available
if (file_exists(__DIR__ . '/artisan')) {
    echo "2. Laravel installation found ✓\n";
} else {
    echo "2. Laravel installation NOT found ✗\n";
    exit(1);
}

// Check database configuration
echo "3. Checking database configuration...\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "   .env file found ✓\n";
} else {
    echo "   .env file NOT found ✗\n";
    echo "   Please copy .env.example to .env and configure it\n";
}

// Check if test database exists
if (file_exists(__DIR__ . '/database/database.sqlite')) {
    echo "   SQLite database found ✓\n";
} else {
    echo "   SQLite database NOT found - will be created during migration\n";
}

echo "\n=== Manual Test Execution Steps ===\n";
echo "If the automated tests aren't running, try these commands manually:\n\n";

echo "1. Setup environment:\n";
echo "   php artisan key:generate\n";
echo "   php artisan migrate:fresh --seed\n\n";

echo "2. Run individual test files:\n";
echo "   php artisan test tests/Feature/TeamInvitationTest.php\n";
echo "   php artisan test tests/Feature/InvitationAcceptanceTest.php\n";
echo "   php artisan test tests/Feature/TeamInvitationFlowTest.php\n\n";

echo "3. Run with verbose output:\n";
echo "   php artisan test --verbose\n\n";

echo "4. Run specific test methods:\n";
echo "   php artisan test --filter=\"team owner can send invitation\"\n\n";

echo "=== Troubleshooting ===\n";
echo "If tests are still hanging:\n";
echo "1. Check your .env file has correct database settings\n";
echo "2. Ensure SQLite is installed: php -m | grep sqlite\n";
echo "3. Check Laravel log files in storage/logs/\n";
echo "4. Try running: php artisan config:clear\n";
echo "5. Try running: php artisan cache:clear\n\n";

echo "=== Test Coverage Summary ===\n";
echo "Your team invitation system tests cover:\n";
echo "✓ Invitation sending and email notifications\n";
echo "✓ Invitation acceptance by existing users\n";
echo "✓ New user registration through invitations\n";
echo "✓ Token security and validation\n";
echo "✓ Invitation expiration handling\n";
echo "✓ Permission-based invitation controls\n";
echo "✓ Multiple company invitation scenarios\n\n";

echo "Tests are ready to run once environment issues are resolved!\n";
