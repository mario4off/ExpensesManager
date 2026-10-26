<?php

use App\Filament\Resources\InsurancePolicies\Pages\CreateInsurancePolicy;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Models\Document;
use App\Models\InsurancePolicy;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('s3');
});

dataset('areas', [
    'invoice' => [
        CreateInvoice::class,
        'invoices',
        Invoice::class,
        ['number' => 'F-0001', 'client_name' => 'Talleres Pérez S.L.', 'issued_on' => '2026-10-01', 'amount' => '120.50'],
    ],
    'policy' => [
        CreateInsurancePolicy::class,
        'insurancePolicies',
        InsurancePolicy::class,
        ['policy_number' => 'P-0001', 'holder_name' => 'María López', 'insurer' => 'Mapfre', 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31', 'premium' => '350.75'],
    ],
]);

function submitWithDocument(string $page, string $userState, array $data, UploadedFile $file)
{
    test()->actingAs(User::factory()->{$userState}()->create());

    return Livewire::test($page)
        ->fillForm([...$data, 'document_id' => $file])
        ->call('create');
}

function expectNothingStored(string $model): void
{
    expect($model::count())->toBe(0)
        ->and(Document::count())->toBe(0)
        ->and(Storage::disk('s3')->allFiles())->toBeEmpty();
}

it('rejects a document that is not a PDF or PNG', function (string $page, string $userState, string $model, array $data) {
    submitWithDocument($page, $userState, $data, UploadedFile::fake()->create('notas.txt', 10, 'text/plain'))
        ->assertHasFormErrors(['document_id']);

    expectNothingStored($model);
})->with('areas');

// Livewire rejects the oversized upload before the form sees it (config/livewire.php),
// so the field stays empty: no document is stored and no record keeps one.
it('does not store a document larger than 10 MB', function (string $page, string $userState, string $model, array $data) {
    submitWithDocument($page, $userState, $data, UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf'));

    expect(Document::count())->toBe(0)
        ->and($model::whereNotNull('document_id')->count())->toBe(0)
        ->and(Storage::disk('s3')->allFiles())->toBeEmpty();
})->with('areas');
