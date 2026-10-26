<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates the initial development administrator (RF-25). These credentials
     * are for local development only and must be changed outside local.
     *
     * Assumes the `admin` role already exists (see RoleSeeder, which must run first).
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@etecnia.es'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ],
        );

        $admin->assignRole('admin');
    }
}
