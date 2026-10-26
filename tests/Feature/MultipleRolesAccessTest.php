<?php

use App\Filament\Resources\InsurancePolicies\InsurancePolicyResource;
use App\Filament\Resources\InsurancePolicies\Pages\ListInsurancePolicies;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\InsurancePolicy;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('gives a user with several roles access to the areas of all of them', function () {
    $invoices = Invoice::factory()->count(2)->create();
    $policies = InsurancePolicy::factory()->count(2)->create();

    $this->actingAs(User::factory()->invoices()->insurancePolicies()->create());

    expect(InvoiceResource::canAccess())->toBeTrue()
        ->and(InsurancePolicyResource::canAccess())->toBeTrue();

    Livewire::test(ListInvoices::class)->assertCanSeeTableRecords($invoices);
    Livewire::test(ListInsurancePolicies::class)->assertCanSeeTableRecords($policies);
});
