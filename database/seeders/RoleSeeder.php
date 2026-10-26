<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * The area permissions (RF-27).
     *
     * @var list<string>
     */
    private const PERMISSIONS = ['invoices', 'insurance_policies'];

    /**
     * The initial permissions of each role (RF-27).
     *
     * @var array<string, list<string>>
     */
    private const ROLES = [
        'admin' => ['invoices', 'insurance_policies'],
        'invoices' => ['invoices'],
        'insurance_policies' => ['insurance_policies'],
    ];

    /**
     * Run the database seeds.
     *
     * Permissions are only assigned when a role is created, so re-running the
     * seeder does not undo changes an administrator made to a role (RF-04).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        foreach (self::ROLES as $name => $permissions) {
            $role = Role::firstOrCreate(['name' => $name]);

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions($permissions);
            }
        }
    }
}
