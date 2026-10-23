<?php

it('lets Livewire temporary uploads be at least 10 MB', function () {
    $rules = (array) config('livewire.temporary_file_upload.rules');

    $maxRule = collect($rules)->first(fn (string $rule) => str_starts_with($rule, 'max:'));

    expect($maxRule)->not->toBeNull();

    $maxKilobytes = (int) (string) str($maxRule)->after('max:');

    expect($maxKilobytes)->toBeGreaterThanOrEqual(10 * 1024);
});

it('declares PHP upload limits of at least 10 MB for the web server', function () {
    $userIni = parse_ini_file(public_path('.user.ini'));

    expect($userIni)->not->toBeFalse();

    $toBytes = function (string $value) {
        $value = trim($value);
        $unit = strtoupper(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'G' => $number * 1024 * 1024 * 1024,
            'M' => $number * 1024 * 1024,
            'K' => $number * 1024,
            default => $number,
        };
    };

    expect($toBytes($userIni['upload_max_filesize']))->toBeGreaterThanOrEqual(10 * 1024 * 1024)
        ->and($toBytes($userIni['post_max_size']))->toBeGreaterThanOrEqual(10 * 1024 * 1024);
});
