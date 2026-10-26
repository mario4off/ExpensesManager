<?php

use App\Models\Document;
use App\Services\DocumentStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function pdf(string $name = 'factura.pdf'): UploadedFile
{
    return UploadedFile::fake()->create($name, 10);
}

it('stores a document in s3 under docs/{area}/{uuid}.{ext} and nothing in local disk', function () {
    Storage::fake('s3');
    Storage::fake('local');

    $document = app(DocumentStorage::class)->store(pdf(), 'invoices');

    expect($document->path)->toMatch('/^docs\/invoices\/[0-9a-f-]{36}\.pdf$/')
        ->and($document->area)->toBe('invoices')
        ->and($document->original_name)->toBe('factura.pdf')
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();

    Storage::disk('s3')->assertExists($document->path);
});

it('writes the area as metadata of the s3 object', function () {
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('put')
        ->once()
        ->withArgs(fn (string $path, $contents, array $options) => str_starts_with($path, 'docs/insurance_policies/')
            && $options['Metadata'] === ['area' => 'insurance_policies'])
        ->andReturnTrue();
    Storage::set('s3', $disk);

    app(DocumentStorage::class)->store(pdf('poliza.pdf'), 'insurance_policies');
});

it('does not create a document row when s3 fails on write', function () {
    failS3On('put');

    expect(fn () => app(DocumentStorage::class)->store(pdf(), 'invoices'))
        ->toThrow(RuntimeException::class);

    expect(Document::count())->toBe(0);
});

it('deletes the file from s3 and the document row', function () {
    Storage::fake('s3');
    $document = app(DocumentStorage::class)->store(pdf(), 'invoices');

    app(DocumentStorage::class)->delete($document);

    Storage::disk('s3')->assertMissing($document->path);
    expect(Document::count())->toBe(0);
});

it('keeps the document row when s3 fails on delete', function () {
    $document = Document::factory()->create();
    failS3On('delete');

    expect(fn () => app(DocumentStorage::class)->delete($document))
        ->toThrow(RuntimeException::class);

    expect(Document::count())->toBe(1);
});
