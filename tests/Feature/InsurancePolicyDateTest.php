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

function createPolicyBetween(string $startsOn, string $endsOn)
{
    return Livewire::test(CreateInsurancePolicy::class)
        ->fillForm([
            'policy_number' => 'P-0001',
            'holder_name' => 'María López',
            'insurer' => 'Mapfre',
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'premium' => '350.75',
        ])
        ->call('create');
}

it('rejects an end date before the start date with a Spanish message', function () {
    createPolicyBetween('2026-06-01', '2026-05-31')
        ->assertHasFormErrors(['ends_on' => 'after_or_equal']);

    expect(InsurancePolicy::count())->toBe(0)
        ->and(__('validation.after_or_equal'))->toStartWith('El campo');
});

it('accepts an end date equal to or after the start date', function (string $endsOn) {
    createPolicyBetween('2026-06-01', $endsOn)->assertHasNoFormErrors();

    expect(InsurancePolicy::count())->toBe(1);
})->with(['equal' => '2026-06-01', 'later' => '2026-06-02']);
