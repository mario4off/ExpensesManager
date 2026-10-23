<?php

use Illuminate\Support\Facades\DB;

it('uses mysql as the default database connection', function () {
    expect(config('database.default'))->toBe('mysql');
});

it('can connect to the testing database', function () {
    expect(fn () => DB::connection()->getPdo())->not->toThrow(Throwable::class);
});

it('runs tests against a database different from the development one', function () {
    $devDatabase = str(file_get_contents(base_path('.env')))
        ->after('DB_DATABASE=')
        ->before("\n")
        ->trim()
        ->toString();

    expect(config('database.connections.mysql.database'))
        ->not->toBe($devDatabase)
        ->and(config('database.connections.mysql.database'))->not->toBeEmpty()
        ->and($devDatabase)->not->toBeEmpty();
});

it('declares the database variables in .env.example without real credentials', function () {
    $example = file_get_contents(base_path('.env.example'));

    expect($example)
        ->toMatch('/^DB_CONNECTION=mysql$/m')
        ->toMatch('/^DB_HOST=/m')
        ->toMatch('/^DB_DATABASE=/m')
        ->toMatch('/^DB_USERNAME=/m')
        ->toMatch('/^DB_PASSWORD=/m');
});

it('does not keep the leftover SQLite database file', function () {
    expect(file_exists(database_path('database.sqlite')))->toBeFalse();
});
