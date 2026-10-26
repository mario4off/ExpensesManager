<?php

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('shows every invoice to users with the invoices permission', function (string $state) {
    $invoices = Invoice::factory()->count(3)->create();

    $this->actingAs(User::factory()->{$state}()->create());

    Livewire::test(ListInvoices::class)->assertCanSeeTableRecords($invoices);
})->with(['invoices', 'admin']);

it('does not offer the invoices resource without the invoices permission', function () {
    $this->actingAs(User::factory()->insurancePolicies()->create());

    expect(InvoiceResource::canAccess())->toBeFalse();

    Livewire::test(ListInvoices::class)->assertForbidden();
});

it('denies an invoice requested by URL without the invoices permission', function () {
    $invoice = Invoice::factory()->create();

    $this->actingAs(User::factory()->insurancePolicies()->create())
        ->get("/admin/invoices/{$invoice->id}/edit")
        ->assertForbidden();

    $this->actingAs(User::factory()->invoices()->create())
        ->get("/admin/invoices/{$invoice->id}/edit")
        ->assertOk();
});
