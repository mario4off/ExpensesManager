<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * The initial set of categories (RF-06), shown to users in Spanish.
     *
     * @var list<string>
     */
    private const CATEGORIES = [
        'Alimentación',
        'Transporte',
        'Vivienda',
        'Ocio',
        'Salud',
        'Otros',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
