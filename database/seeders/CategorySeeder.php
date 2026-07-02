<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Makanan Ringan',
            'Minuman',
            'Makanan Berat',
            'Sembako',
            'Perlengkapan Rumah',
            'Peralatan Mandi',
            'Alat Tulis',
            'Rokok & Linting',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
