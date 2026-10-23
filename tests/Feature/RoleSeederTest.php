<?php

use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

it('creates the admin and user roles', function () {
    (new RoleSeeder)->run();

    expect(Role::whereIn('name', ['admin', 'user'])->count())->toBe(2);
});

it('does not duplicate roles when run twice', function () {
    (new RoleSeeder)->run();
    $countAfterFirstRun = Role::count();

    (new RoleSeeder)->run();

    expect(Role::count())->toBe($countAfterFirstRun);
});
