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
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@etecnia.es'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );
    }
}
