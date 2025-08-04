<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
  public function run(): void
  {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create granular permissions
    $permissions = [
      // Company management - granular permissions
      'companies.view_any',           // View list of companies
      'companies.view',               // View specific company details
      'companies.create',             // Create new companies
      'companies.update',             // Edit company information
      'companies.delete',             // Delete companies
      'companies.switch',             // Switch between companies
      'companies.manage_settings',    // Manage company-specific settings
      'companies.view_billing',       // View company billing information
      'companies.manage_billing',     // Manage company billing and subscriptions

      // Billboard management - granular permissions
      'billboards.view_any',          // View billboard listings
      'billboards.view',              // View specific billboard details
      'billboards.create',            // Create new billboards
      'billboards.update',            // Edit billboard information
      'billboards.delete',            // Delete billboards
      'billboards.duplicate',         // Duplicate billboards
      'billboards.bulk_update',       // Perform bulk operations on billboards
      'billboards.upload_media',      // Upload billboard media/images
      'billboards.manage_media',      // Manage billboard media files
      'billboards.view_analytics',    // View billboard performance analytics
      'billboards.export_data',       // Export billboard data

      // Contract management - granular permissions
      'contracts.view_any',           // View contract listings
      'contracts.view',               // View specific contract details
      'contracts.create',             // Create new contracts
      'contracts.update',             // Edit contract information
      'contracts.delete',             // Delete contracts
      'contracts.approve',            // Approve contract changes
      'contracts.cancel',             // Cancel contracts
      'contracts.manage_payments',    // Manage contract payments
      'contracts.view_financial',     // View contract financial details

      // Team/User management - granular permissions
      'team.view_any',                // View team member listings
      'team.view',                    // View specific team member details
      'team.invite',                  // Invite new team members
      'team.update_roles',            // Update team member roles
      'team.update_permissions',      // Update team member permissions
      'team.remove',                  // Remove team members
      'team.manage_invitations',      // Manage pending invitations
      'team.view_activity',           // View team member activity logs

      // User profile management
      'users.update_own_profile',     // Update own user profile
      'users.update_any_profile',     // Update any user profile
      'users.change_password',        // Change user passwords
      'users.manage_sessions',        // Manage user sessions
      'users.view_activity',          // View user activity logs

      // Analytics and reporting - granular permissions
      'analytics.view_dashboard',     // View analytics dashboard
      'analytics.view_billboard',     // View billboard-specific analytics
      'analytics.view_contract',      // View contract analytics
      'analytics.view_financial',     // View financial analytics
      'analytics.export_reports',     // Export various reports
      'analytics.view_advanced',      // View advanced analytics

      // Financial management
      'finance.view_revenue',         // View revenue information
      'finance.view_expenses',        // View expense information
      'finance.manage_payments',      // Manage payment processing
      'finance.view_invoices',        // View invoices
      'finance.create_invoices',      // Create invoices
      'finance.export_financial',     // Export financial reports

      // System administration
      'admin.access_panel',           // Access admin panel
      'admin.manage_system',          // Manage system-wide settings
      'admin.view_logs',              // View system logs
      'admin.manage_permissions',     // Manage system permissions
      'admin.manage_roles',           // Manage system roles
    ];

    foreach ($permissions as $permission) {
      Permission::create(['name' => $permission]);
    }

    // Create roles and assign granular permissions
    $ownerRole = Role::create(['name' => 'company_owner']);
    $ownerRole->givePermissionTo([
      // Full company access
      'companies.view_any',
      'companies.view',
      'companies.create',
      'companies.update',
      'companies.delete',
      'companies.switch',
      'companies.manage_settings',
      'companies.view_billing',
      'companies.manage_billing',

      // Full billboard access
      'billboards.view_any',
      'billboards.view',
      'billboards.create',
      'billboards.update',
      'billboards.delete',
      'billboards.duplicate',
      'billboards.bulk_update',
      'billboards.upload_media',
      'billboards.manage_media',
      'billboards.view_analytics',
      'billboards.export_data',

      // Full contract access
      'contracts.view_any',
      'contracts.view',
      'contracts.create',
      'contracts.update',
      'contracts.delete',
      'contracts.approve',
      'contracts.cancel',
      'contracts.manage_payments',
      'contracts.view_financial',

      // Full team management
      'team.view_any',
      'team.view',
      'team.invite',
      'team.update_roles',
      'team.update_permissions',
      'team.remove',
      'team.manage_invitations',
      'team.view_activity',

      // User management
      'users.update_own_profile',
      'users.update_any_profile',
      'users.change_password',
      'users.manage_sessions',
      'users.view_activity',

      // Full analytics access
      'analytics.view_dashboard',
      'analytics.view_billboard',
      'analytics.view_contract',
      'analytics.view_financial',
      'analytics.export_reports',
      'analytics.view_advanced',

      // Full financial access
      'finance.view_revenue',
      'finance.view_expenses',
      'finance.manage_payments',
      'finance.view_invoices',
      'finance.create_invoices',
      'finance.export_financial',
    ]);

    $managerRole = Role::create(['name' => 'manager']);
    $managerRole->givePermissionTo([
      // Company viewing and basic management
      'companies.view_any',
      'companies.view',
      'companies.update',
      'companies.switch',

      // Billboard management (no delete)
      'billboards.view_any',
      'billboards.view',
      'billboards.create',
      'billboards.update',
      'billboards.duplicate',
      'billboards.bulk_update',
      'billboards.upload_media',
      'billboards.manage_media',
      'billboards.view_analytics',
      'billboards.export_data',

      // Contract management (limited)
      'contracts.view_any',
      'contracts.view',
      'contracts.create',
      'contracts.update',
      'contracts.view_financial',

      // Team management (limited)
      'team.view_any',
      'team.view',
      'team.invite',
      'team.view_activity',

      // User profile
      'users.update_own_profile',
      'users.change_password',

      // Analytics access
      'analytics.view_dashboard',
      'analytics.view_billboard',
      'analytics.view_contract',
      'analytics.export_reports',

      // Limited financial access
      'finance.view_revenue',
      'finance.view_invoices',
    ]);

    $editorRole = Role::create(['name' => 'editor']);
    $editorRole->givePermissionTo([
      // Company viewing
      'companies.view_any',
      'companies.view',
      'companies.switch',

      // Billboard editing
      'billboards.view_any',
      'billboards.view',
      'billboards.create',
      'billboards.update',
      'billboards.upload_media',
      'billboards.manage_media',
      'billboards.view_analytics',

      // Contract viewing and basic editing
      'contracts.view_any',
      'contracts.view',
      'contracts.create',
      'contracts.update',

      // Team viewing
      'team.view_any',
      'team.view',

      // User profile
      'users.update_own_profile',
      'users.change_password',

      // Basic analytics
      'analytics.view_dashboard',
      'analytics.view_billboard',
    ]);

    $viewerRole = Role::create(['name' => 'viewer']);
    $viewerRole->givePermissionTo([
      // Company viewing only
      'companies.view_any',
      'companies.view',
      'companies.switch',

      // Billboard viewing only
      'billboards.view_any',
      'billboards.view',

      // Contract viewing only
      'contracts.view_any',
      'contracts.view',

      // Team viewing only
      'team.view_any',
      'team.view',

      // User profile
      'users.update_own_profile',
      'users.change_password',

      // Basic analytics viewing
      'analytics.view_dashboard',
    ]);

    // Create super admin role with all permissions
    $superAdminRole = Role::create(['name' => 'super_admin']);
    $superAdminRole->givePermissionTo(Permission::all());
  }
}
