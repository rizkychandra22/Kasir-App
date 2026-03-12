<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan Berat',   'name_code' => 'MKB'],
            ['name' => 'Makanan Ringan',  'name_code' => 'MKR'],
            ['name' => 'Minuman Panas',   'name_code' => 'MNP'],
            ['name' => 'Minuman Segar',   'name_code' => 'MNS'],
            ['name' => 'Minuman Soda',    'name_code' => 'MSD'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name'      => $category['name'],
                'name_code' => $category['name_code'],
            ]);
        }
    }
}
