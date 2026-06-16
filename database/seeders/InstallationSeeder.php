<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstallationSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.ban',

            // Roles
            'roles.view',
            'roles.manage',

            // Plans
            'plans.view',
            'plans.create',
            'plans.edit',
            'plans.delete',

            // Orders
            'orders.view.own',
            'orders.view.any',

            'orders.create',

            'orders.edit.own',
            'orders.edit.any',

            'orders.cancel.own',
            'orders.cancel.any',

            'orders.refund',

            // Payments
            'payments.view.own',
            'payments.view.any',

            'payments.refund',

            // Subscriptions
            'subscriptions.view.own',
            'subscriptions.view.any',

            'subscriptions.create',
            'subscriptions.edit',
            'subscriptions.cancel',

            // Reports
            'reports.view',

            // Settings
            'settings.view',
            'settings.manage',

            // Profile
            'profile.view',
            'profile.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $superAdminRole = Role::firstOrCreate([
            'name'       => config('roles.super_admin'),
            'guard_name' => 'web',
        ]);

        $superAdminRole->syncPermissions([]);

        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        $managerRole = Role::firstOrCreate([
            'name'       => config('roles.manager'),
            'guard_name' => 'web',
        ]);

        $managerRole->syncPermissions([

            'users.view',

            'roles.view',

            'plans.view',
            'plans.edit',

            'orders.view.any',
            'orders.edit.any',
            'orders.cancel.any',
            'orders.refund',

            'payments.view.any',
            'payments.refund',

            'subscriptions.view.any',
            'subscriptions.cancel',

            'reports.view',

            'settings.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cashier
        |--------------------------------------------------------------------------
        */

        $cashierRole = Role::firstOrCreate([
            'name'       => config('roles.cashier'),
            'guard_name' => 'web',
        ]);

        $cashierRole->syncPermissions([

            'profile.view',
            'profile.edit',
            
            'plans.view',
            

            'orders.view.any',
            'orders.create',
            'orders.edit.any',

            'payments.view.any',

            'subscriptions.view.any',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default User
        |--------------------------------------------------------------------------
        */

        $userRole = Role::firstOrCreate([
            'name'       => config('roles.default'),
            'guard_name' => 'web',
        ]);

        $userRole->syncPermissions([

            'profile.view',
            'profile.edit',

            'orders.view.own',
            'orders.create',
            'orders.edit.own',
            'orders.cancel.own',

            'payments.view.own',

            'subscriptions.view.own',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Admin Account
        |--------------------------------------------------------------------------
        */

        $admin = User::firstOrCreate(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('admin'),
                'status'   => 'active',
            ]
        );

        Profile::updateOrCreate(
            [
                'user_id' => $admin->id,
            ],
            [
                'first_name' => 'Admin',
                'last_name'  => 'User',
            ]
        );

        $admin->syncRoles([$superAdminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✅ Installation completed successfully.');
    }
}