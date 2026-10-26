<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * The development users and their roles (RF-28).
     *
     * @var array<string, array{name: string, role: string}>
     */
    private const USERS = [
        'facturas@etecnia.es' => ['name' => 'Facturas', 'role' => 'invoices'],
        'seguros@etecnia.es' => ['name' => 'Seguros', 'role' => 'insurance_policies'],
        'admin@etecnia.es' => ['name' => 'Administrador', 'role' => 'admin'],
    ];

    /**
     * Run the database seeds.
     *
     * These credentials are for local development only and must be changed
     * outside local. Existing users keep their password.
     *
     * Assumes the roles already exist (see RoleSeeder, which must run first).
     */
    public function run(): void
    {
        foreach (self::USERS as $email => $data) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $data['name'], 'password' => Hash::make('password')],
            );

            $user->assignRole($data['role']);
        }
    }
}
