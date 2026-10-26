<?php

use App\Filament\Resources\InsurancePolicies\Pages\CreateInsurancePolicy;
use App\Models\InsurancePolicy;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->actingAs(User::factory()->insurancePolicies()->create());

    Storage::fake('s3');
    Storage::fake('local');
});

function policyDataWithDocument(array $overrides = []): array
{
    return array_merge([
        'policy_number' => 'P-0001',
        'holder_name' => 'María López',
        'insurer' => 'Mapfre',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'premium' => '350.75',
    ], $overrides);
}

it('creates a policy without a document', function () {
    Livewire::test(CreateInsurancePolicy::class)
        ->fillForm(policyDataWithDocument())
        ->call('create')
        ->assertHasNoFormErrors();

    $policy = InsurancePolicy::firstWhere('policy_number', 'P-0001');

    expect($policy)->not->toBeNull()
        ->and($policy->document_id)->toBeNull()
        ->and(Storage::disk('s3')->allFiles())->toBeEmpty();
});

it('creates a policy with a document stored in s3 for the insurance_policies area', function () {
    Livewire::test(CreateInsurancePolicy::class)
        ->fillForm(policyDataWithDocument(['document_id' => UploadedFile::fake()->create('poliza.pdf', 10)]))
        ->call('create')
        ->assertHasNoFormErrors();

    $document = InsurancePolicy::firstWhere('policy_number', 'P-0001')->document;

    expect($document)->not->toBeNull()
        ->and($document->area)->toBe('insurance_policies')
        ->and($document->original_name)->toBe('poliza.pdf')
        ->and($document->path)->toStartWith('docs/insurance_policies/');

    Storage::disk('s3')->assertExists($document->path);

    $persistentLocalFiles = collect(Storage::disk('local')->allFiles())
        ->reject(fn (string $file) => str_starts_with($file, 'livewire-tmp'));

    expect($persistentLocalFiles)->toBeEmpty();
});
