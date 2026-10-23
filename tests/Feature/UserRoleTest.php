<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => (new RoleSeeder)->run());

it('defaults a new user to not having the admin role', function () {
    $user = User::factory()->create();

    expect($user->hasRole('admin'))->toBeFalse();
});

it('assigns the admin role through the admin factory state', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->hasRole('admin'))->toBeTrue();
});
