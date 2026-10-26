<?php

use App\Filament\Resources\InsurancePolicies\Pages\CreateInsurancePolicy;
use App\Models\InsurancePolicy;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->actingAs(User::factory()->insurancePolicies()->create());
});

function validPolicy(array $overrides = []): array
{
    return array_merge([
        'policy_number' => 'P-0001',
        'holder_name' => 'María López',
        'insurer' => 'Mapfre',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'premium' => '350,75',
    ], $overrides);
}

it('creates a policy with the six fields', function () {
    Livewire::test(CreateInsurancePolicy::class)
        ->fillForm(validPolicy())
        ->call('create')
        ->assertHasNoFormErrors();

    $policy = InsurancePolicy::firstWhere('policy_number', 'P-0001');

    expect($policy)->not->toBeNull()
        ->and($policy->holder_name)->toBe('María López')
        ->and($policy->insurer)->toBe('Mapfre')
        ->and($policy->starts_on->toDateString())->toBe('2026-01-01')
        ->and($policy->ends_on->toDateString())->toBe('2026-12-31')
        ->and($policy->premium)->toBe('350.75');
});

it('requires every field', function (string $field) {
    Livewire::test(CreateInsurancePolicy::class)
        ->fillForm(validPolicy([$field => null]))
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);

    expect(InsurancePolicy::count())->toBe(0);
})->with(['policy_number', 'holder_name', 'insurer', 'starts_on', 'ends_on', 'premium']);

it('rejects a repeated policy number', function () {
    InsurancePolicy::factory()->create(['policy_number' => 'P-0001']);

    Livewire::test(CreateInsurancePolicy::class)
        ->fillForm(validPolicy())
        ->call('create')
        ->assertHasFormErrors(['policy_number' => 'unique']);

    expect(InsurancePolicy::count())->toBe(1);
});
