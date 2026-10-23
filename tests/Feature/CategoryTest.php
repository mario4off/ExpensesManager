<?php

use App\Models\Category;
use Illuminate\Database\QueryException;

it('creates a category through its factory', function () {
    $category = Category::factory()->create(['name' => 'Transporte']);

    expect($category->id)->not->toBeNull()
        ->and($category->name)->toBe('Transporte');
});

it('requires the category name to be unique', function () {
    Category::factory()->create(['name' => 'Transporte']);

    expect(fn () => Category::factory()->create(['name' => 'Transporte']))
        ->toThrow(QueryException::class);
});
