<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;

it('creates the initial set of categories', function () {
    (new CategorySeeder)->run();

    $names = Category::pluck('name');

    expect($names)->toContain('Alimentación', 'Transporte', 'Vivienda', 'Ocio', 'Salud', 'Otros');
});

it('does not duplicate categories when run twice', function () {
    (new CategorySeeder)->run();
    $countAfterFirstRun = Category::count();

    (new CategorySeeder)->run();

    expect(Category::count())->toBe($countAfterFirstRun);
});

it('seeds the administrator and the initial categories through the database seeder', function () {
    $this->seed();

    expect(Category::count())->toBeGreaterThan(0)
        ->and(User::where('email', 'admin@etecnia.es')->exists())->toBeTrue();
});
