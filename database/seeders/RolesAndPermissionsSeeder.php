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

    // Create permissions
    $permissions = [
      // Company management
      'view companies',
      'create companies',
      'edit companies',
      'delete companies',
      'manage company settings',

      // Billboard management
      'view billboards',
      'create billboards',
      'edit billboards',
      'delete billboards',
      'manage billboard media',

      // User management
      'view users',
      'invite users',
      'edit users',
      'remove users',
      'manage user roles',

      // Analytics and reports
      'view analytics',
      'export reports',

      // Billing and subscriptions
      'view billing',
      'manage subscriptions',

      // System admin
      'access admin panel',
      'manage system settings',
    ];

    foreach ($permissions as $permission) {
      Permission::create(['name' => $permission]);
    }

    // Create roles and assign permissions
    $ownerRole = Role::create(['name' => 'company_owner']);
    $ownerRole->givePermissionTo([
      'view companies',
      'create companies',
      'edit companies',
      'manage company settings',
      'view billboards',
      'create billboards',
      'edit billboards',
      'delete billboards',
      'manage billboard media',
      'view users',
      'invite users',
      'edit users',
      'remove users',
      'manage user roles',
      'view analytics',
      'export reports',
      'view billing',
      'manage subscriptions',
    ]);

    $managerRole = Role::create(['name' => 'manager']);
    $managerRole->givePermissionTo([
      'view companies',
      'edit companies',
      'view billboards',
      'create billboards',
      'edit billboards',
      'delete billboards',
      'manage billboard media',
      'view users',
      'invite users',
      'view analytics',
      'export reports',
    ]);

    $editorRole = Role::create(['name' => 'editor']);
    $editorRole->givePermissionTo([
      'view companies',
      'view billboards',
      'create billboards',
      'edit billboards',
      'manage billboard media',
      'view users',
      'view analytics',
    ]);

    $viewerRole = Role::create(['name' => 'viewer']);
    $viewerRole->givePermissionTo([
      'view companies',
      'view billboards',
      'view users',
      'view analytics',
    ]);

    // Create super admin role
    $superAdminRole = Role::create(['name' => 'super_admin']);
    $superAdminRole->givePermissionTo(Permission::all());
  }
}
