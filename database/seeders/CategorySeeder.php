<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'Admin')->first() ?? User::first();
        $userId = $admin ? $admin->id : 1;

        $categories = [
            ['name' => 'Makanan Berat',   'name_code' => 'MKB'],
            ['name' => 'Makanan Ringan',  'name_code' => 'MKR'],
            ['name' => 'Minuman Panas',   'name_code' => 'MNP'],
            ['name' => 'Minuman Segar',   'name_code' => 'MNS'],
            ['name' => 'Minuman Soda',    'name_code' => 'MSD'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name_code' => $category['name_code']],
                [
                    'name'      => $category['name'],
                    'user_id'   => $userId,
                ]
            );
        }
    }
}
