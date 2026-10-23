<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * The two fixed roles of the application (RF-03).
     *
     * @var list<string>
     */
    private const ROLES = ['admin', 'user'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ROLES as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
