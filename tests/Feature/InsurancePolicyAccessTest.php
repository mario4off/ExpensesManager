<?php

use App\Filament\Resources\InsurancePolicies\InsurancePolicyResource;
use App\Filament\Resources\InsurancePolicies\Pages\ListInsurancePolicies;
use App\Models\InsurancePolicy;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('shows every policy to users with the insurance_policies permission', function (string $state) {
    $policies = InsurancePolicy::factory()->count(3)->create();

    $this->actingAs(User::factory()->{$state}()->create());

    Livewire::test(ListInsurancePolicies::class)->assertCanSeeTableRecords($policies);
})->with(['insurancePolicies', 'admin']);

it('does not offer the policies resource without the insurance_policies permission', function () {
    $this->actingAs(User::factory()->invoices()->create());

    expect(InsurancePolicyResource::canAccess())->toBeFalse();

    Livewire::test(ListInsurancePolicies::class)->assertForbidden();
});
