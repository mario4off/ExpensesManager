<?php

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->actingAs(User::factory()->invoices()->create());
});

function createInvoiceIssuedOn(string $date)
{
    return Livewire::test(CreateInvoice::class)
        ->fillForm([
            'number' => 'F-0001',
            'client_name' => 'Talleres Pérez S.L.',
            'issued_on' => $date,
            'amount' => '120.50',
        ])
        ->call('create');
}

it('rejects an issue date in the future', function () {
    $this->travelTo(Carbon::parse('2026-10-26 12:00', 'Europe/Madrid'));

    createInvoiceIssuedOn('2026-10-27')->assertHasFormErrors(['issued_on']);

    expect(Invoice::count())->toBe(0);
});

it('accepts today as issue date, also close to midnight in Madrid', function (string $now) {
    $this->travelTo(Carbon::parse($now, 'Europe/Madrid'));

    createInvoiceIssuedOn(today()->toDateString())->assertHasNoFormErrors();

    expect(Invoice::count())->toBe(1);
})->with(['2026-10-26 23:59', '2026-10-27 00:01']);

it('rejects tomorrow at 23:59 and accepts it at 00:01 in Madrid', function () {
    $this->travelTo(Carbon::parse('2026-10-26 23:59', 'Europe/Madrid'));
    createInvoiceIssuedOn('2026-10-27')->assertHasFormErrors(['issued_on']);

    $this->travelTo(Carbon::parse('2026-10-27 00:01', 'Europe/Madrid'));
    createInvoiceIssuedOn('2026-10-27')->assertHasNoFormErrors();
});
