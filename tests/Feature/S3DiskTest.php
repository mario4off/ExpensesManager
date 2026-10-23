<?php

use Illuminate\Support\Facades\Storage;

it('defines a private s3 disk that throws on failure', function () {
    $disk = config('filesystems.disks.s3');

    expect($disk['driver'])->toBe('s3')
        ->and($disk['visibility'])->toBe('private')
        ->and($disk['throw'])->toBeTrue();
});

it('can resolve the s3 disk because the S3 driver is installed', function () {
    expect(fn () => Storage::disk('s3'))->not->toThrow(Throwable::class);
});

it('declares the AWS variables in .env.example without values for secrets', function () {
    $example = file_get_contents(base_path('.env.example'));

    expect($example)
        ->toMatch('/^AWS_ACCESS_KEY_ID=$/m')
        ->toMatch('/^AWS_SECRET_ACCESS_KEY=$/m')
        ->toMatch('/^AWS_BUCKET=$/m')
        ->toMatch('/^AWS_DEFAULT_REGION=/m');
});

it('reads the s3 credentials only from the environment', function () {
    $sources = collect(['config', 'app', 'routes', 'database'])
        ->flatMap(fn (string $dir) => glob(base_path("$dir/{,*/,*/*/}*.php"), GLOB_BRACE))
        ->map(fn (string $file) => file_get_contents($file))
        ->implode("\n");

    expect($sources)->not->toMatch('/AKIA[0-9A-Z]{16}/');
});
