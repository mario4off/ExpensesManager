<?php

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('lets the admin see the users list', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->invoices()->create(['name' => 'Ana Facturas']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Ana Facturas');
});

it('does not offer the users resource to users without the admin role', function (string $state) {
    $this->actingAs(User::factory()->{$state}()->create());

    expect(UserResource::canAccess())->toBeFalse();
})->with(['invoices', 'insurancePolicies']);

it('offers the users resource to the admin', function () {
    $this->actingAs(User::factory()->admin()->create());

    expect(UserResource::canAccess())->toBeTrue();
});

it('denies direct access to the users resource to users without the admin role', function (string $state) {
    $user = User::factory()->{$state}()->create();

    $this->actingAs($user)->get('/admin/users')->assertForbidden();
})->with(['invoices', 'insurancePolicies']);
