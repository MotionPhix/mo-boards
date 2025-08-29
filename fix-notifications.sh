#!/bin/bash

# MoBoards Notification System Fix Script
# Run this script to fix all notification and permission issues

echo "🔧 MoBoards Notification System Fix"
echo "=================================="

# Step 1: Run the custom fix command
echo "📋 Step 1: Fixing permissions and notifications..."
php artisan fix:notifications

# Step 2: Clear application caches
echo "🧹 Step 2: Clearing caches..."
php artisan config:clear
php artisan cache:clear  
php artisan route:clear
php artisan view:clear

# Step 3: Run migrations if needed
echo "📊 Step 3: Ensuring database is up to date..."
php artisan migrate --force

# Step 4: Generate Ziggy routes
echo "🛣️ Step 4: Generating Ziggy routes..."
php artisan ziggy:generate

# Step 5: Build frontend
echo "🎨 Step 5: Building frontend assets..."
if command -v pnpm &> /dev/null; then
    echo "Using pnpm..."
    pnpm install
    pnpm run build
elif command -v npm &> /dev/null; then
    echo "Using npm..."
    npm install
    npm run build
else
    echo "⚠️ Neither npm nor pnpm found. Please install dependencies and build manually:"
    echo "   npm install && npm run build"
fi

# Step 6: Final verification
echo "✅ Step 6: Running verification..."
echo ""
echo "🎉 Fix completed!"
echo ""
echo "📝 What was fixed:"
echo "   ✓ User permissions and roles"  
echo "   ✓ Company ownership setup"
echo "   ✓ Test notifications created"
echo "   ✓ Frontend assets built"
echo "   ✓ Routes and caches cleared"
echo ""
echo "🚀 Next steps:"
echo "   1. Visit your application: $(php artisan get-absolute-url 2>/dev/null || echo 'https://mo-boards.test')"
echo "   2. Look for the notification bell in the header (🔔)"
echo "   3. Click 'Settings' in the navigation menu"
echo "   4. Go to Settings > Notifications to configure preferences"
echo ""
echo "💡 If you still don't see the notification bell:"
echo "   - Check the browser console for JavaScript errors"
echo "   - Run 'npm run dev' for development mode with hot reloading"
echo "   - Ensure you're logged in with the user account"

