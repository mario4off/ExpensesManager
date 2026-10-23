<?php

use App\Models\User;

it('defaults a new user to not being an administrator', function () {
    $user = User::factory()->create();

    expect($user->is_admin)->toBeFalse();
});

it('casts is_admin as a boolean', function () {
    $user = User::factory()->create();

    expect($user->is_admin)->toBeBool();
});

it('creates an administrator through the admin factory state', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->is_admin)->toBeTrue();
});
