<?php

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->actingAs(User::factory()->invoices()->create());

    Storage::fake('s3');
    Storage::fake('local');
});

function invoiceData(array $overrides = []): array
{
    return array_merge([
        'number' => 'F-0001',
        'client_name' => 'Talleres Pérez S.L.',
        'issued_on' => '2026-10-01',
        'amount' => '120.50',
    ], $overrides);
}

it('creates an invoice without a document', function () {
    Livewire::test(CreateInvoice::class)
        ->fillForm(invoiceData())
        ->call('create')
        ->assertHasNoFormErrors();

    $invoice = Invoice::firstWhere('number', 'F-0001');

    expect($invoice)->not->toBeNull()
        ->and($invoice->document_id)->toBeNull()
        ->and(Storage::disk('s3')->allFiles())->toBeEmpty();
});

it('creates an invoice with a document stored in s3 for the invoices area', function () {
    Livewire::test(CreateInvoice::class)
        ->fillForm(invoiceData(['document_id' => UploadedFile::fake()->create('factura.pdf', 10)]))
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Invoice::firstWhere('number', 'F-0001')->document;

    expect($document)->not->toBeNull()
        ->and($document->area)->toBe('invoices')
        ->and($document->original_name)->toBe('factura.pdf')
        ->and($document->path)->toStartWith('docs/invoices/');

    Storage::disk('s3')->assertExists($document->path);

    $persistentLocalFiles = collect(Storage::disk('local')->allFiles())
        ->reject(fn (string $file) => str_starts_with($file, 'livewire-tmp'));

    expect($persistentLocalFiles)->toBeEmpty();
});
