<?php

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->actingAs(User::factory()->invoices()->create());
});

function validInvoice(array $overrides = []): array
{
    return array_merge([
        'number' => 'F-0001',
        'client_name' => 'Talleres Pérez S.L.',
        'issued_on' => '2026-10-01',
        'amount' => '120,50',
    ], $overrides);
}

it('creates an invoice with number, client name, issue date and amount', function () {
    Livewire::test(CreateInvoice::class)
        ->fillForm(validInvoice())
        ->call('create')
        ->assertHasNoFormErrors();

    $invoice = Invoice::firstWhere('number', 'F-0001');

    expect($invoice)->not->toBeNull()
        ->and($invoice->client_name)->toBe('Talleres Pérez S.L.')
        ->and($invoice->issued_on->toDateString())->toBe('2026-10-01')
        ->and($invoice->amount)->toBe('120.50');
});

it('requires number, client name, issue date and amount', function (string $field) {
    Livewire::test(CreateInvoice::class)
        ->fillForm(validInvoice([$field => null]))
        ->call('create')
        ->assertHasFormErrors([$field => 'required']);

    expect(Invoice::count())->toBe(0);
})->with(['number', 'client_name', 'issued_on', 'amount']);

it('rejects a repeated invoice number', function () {
    Invoice::factory()->create(['number' => 'F-0001']);

    Livewire::test(CreateInvoice::class)
        ->fillForm(validInvoice())
        ->call('create')
        ->assertHasFormErrors(['number' => 'unique']);

    expect(Invoice::count())->toBe(1);
});

it('reads the amount in Spanish format and stores it with a decimal point', function () {
    Livewire::test(CreateInvoice::class)
        ->fillForm(validInvoice(['amount' => '1.234,50']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Invoice::firstWhere('number', 'F-0001')->amount)->toBe('1234.50');
});
