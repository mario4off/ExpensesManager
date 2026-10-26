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
    Storage::fake('local');
});

dataset('areas', [
    'invoices' => fn () => [
        CreateInvoice::class,
        Invoice::class,
        User::factory()->invoices()->create(),
        [
            'number' => 'F-0001',
            'client_name' => 'Talleres Pérez S.L.',
            'issued_on' => '2026-10-01',
            'amount' => '120.50',
        ],
    ],
    'insurance_policies' => fn () => [
        CreateInsurancePolicy::class,
        InsurancePolicy::class,
        User::factory()->insurancePolicies()->create(),
        [
            'policy_number' => 'P-0001',
            'holder_name' => 'María López',
            'insurer' => 'Mapfre',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'premium' => '350.75',
        ],
    ],
]);

it('saves no record, no document and no file when s3 is down on upload', function (string $page, string $model, User $user, array $data) {
    $this->actingAs($user);
    failS3On('put');

    Livewire::test($page)
        ->fillForm($data + ['document_id' => UploadedFile::fake()->create('documento.pdf', 10)])
        ->call('create')
        ->assertNotified('No se pudo subir el documento. No se ha guardado el registro; inténtalo de nuevo.');

    expect($model::count())->toBe(0)
        ->and(Document::count())->toBe(0);
})->with('areas');

it('removes the uploaded file and document when the record cannot be saved', function (string $page, string $model, User $user, array $data) {
    $this->actingAs($user);
    $model::creating(fn () => throw new RuntimeException('Database unavailable'));

    expect(fn () => Livewire::test($page)
        ->fillForm($data + ['document_id' => UploadedFile::fake()->create('documento.pdf', 10)])
        ->call('create'))->toThrow(RuntimeException::class);

    expect($model::count())->toBe(0)
        ->and(Document::count())->toBe(0)
        ->and(Storage::disk('s3')->allFiles())->toBeEmpty();
})->with('areas');
