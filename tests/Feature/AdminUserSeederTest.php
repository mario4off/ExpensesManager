<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Hash;

it('creates the initial administrator', function () {
    (new AdminUserSeeder)->run();

    $admin = User::where('email', 'admin@etecnia.es')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->is_admin)->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeTrue();
});

it('does not duplicate or change the administrator when run twice', function () {
    (new AdminUserSeeder)->run();
    $firstPasswordHash = User::where('email', 'admin@etecnia.es')->first()->password;

    (new AdminUserSeeder)->run();

    expect(User::where('email', 'admin@etecnia.es')->count())->toBe(1)
        ->and(User::where('email', 'admin@etecnia.es')->first()->password)->toBe($firstPasswordHash);
});
