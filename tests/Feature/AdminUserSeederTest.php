<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => (new RoleSeeder)->run());

it('creates the initial administrator', function () {
    (new AdminUserSeeder)->run();

    $admin = User::where('email', 'admin@etecnia.es')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeTrue();
});

it('does not duplicate or change the administrator when run twice', function () {
    (new AdminUserSeeder)->run();
    $firstPasswordHash = User::where('email', 'admin@etecnia.es')->first()->password;

    (new AdminUserSeeder)->run();

    $admin = User::where('email', 'admin@etecnia.es')->first();

    expect(User::where('email', 'admin@etecnia.es')->count())->toBe(1)
        ->and($admin->password)->toBe($firstPasswordHash)
        ->and($admin->roles()->count())->toBe(1);
});
