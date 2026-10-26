<?php

use App\Models\Document;
use App\Models\InsurancePolicy;
use App\Models\Invoice;
use App\Services\DocumentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    Storage::fake('local');
});

dataset('record models', [
    'invoices' => [Invoice::class, 'invoices'],
    'insurance_policies' => [InsurancePolicy::class, 'insurance_policies'],
]);

function storedDocument(string $area): Document
{
    return app(DocumentStorage::class)->store(UploadedFile::fake()->create('documento.pdf', 10), $area);
}

it('deletes the file and the document row when the record is deleted', function (string $model, string $area) {
    $document = storedDocument($area);
    $record = $model::factory()->create(['document_id' => $document->getKey()]);

    $record->delete();

    Storage::disk('s3')->assertMissing($document->path);
    expect(Document::count())->toBe(0);
})->with('record models');

it('deletes the files when several records are destroyed at once', function (string $model, string $area) {
    $documents = collect([1, 2])->map(function () use ($model, $area) {
        $document = storedDocument($area);
        $model::factory()->create(['document_id' => $document->getKey()]);

        return $document;
    });

    $model::destroy($model::query()->pluck('id')->all());

    $documents->each(fn (Document $document) => Storage::disk('s3')->assertMissing($document->path));
    expect(Document::count())->toBe(0);
})->with('record models');
