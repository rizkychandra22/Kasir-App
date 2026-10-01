<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'Admin')->first() ?? User::first();
        $userId = $admin ? $admin->id : 1;

        $mkb = Category::where('name_code', 'MKB')->first();
        $mkr = Category::where('name_code', 'MKR')->first();
        $mnp = Category::where('name_code', 'MNP')->first();
        $mns = Category::where('name_code', 'MNS')->first();
        $msd = Category::where('name_code', 'MSD')->first();

        $products = [
            [
                'category_id' => $mkb?->id, 
                'name_prd' => 'Nasi Goreng Spesial',
                'code_prd' => 'MKB-NGS01',
                'description_prd' => 'Nasi goreng dengan telur, ayam, dan sayur.',
                'price' => 20000,
                'price_offline' => 20000,
                'price_online' => 23000,
                'sales_type' => 'all',
                'user_id' => $userId,
            ],
            [
                'category_id' => $mkr?->id, 
                'name_prd' => 'Keripik Singkong Pedas',
                'code_prd' => 'MKR-KSP01',
                'description_prd' => 'Keripik singkong dengan bumbu pedas khas.',
                'price' => 15000,
                'price_offline' => 15000,
                'price_online' => 17000,
                'sales_type' => 'all',
                'user_id' => $userId,
            ],
            [
                'category_id' => $mnp?->id, 
                'name_prd' => 'Teh Hangat',
                'code_prd' => 'MNP-TH01',
                'description_prd' => 'Teh hangat dengan rasa alami.',
                'price' => 8000,
                'price_offline' => 8000,
                'price_online' => 10000,
                'sales_type' => 'all',
                'user_id' => $userId,
            ],
            [
                'category_id' => $mns?->id, 
                'name_prd' => 'Es Jeruk Segar',
                'code_prd' => 'MNS-EJS01',
                'description_prd' => 'Minuman es jeruk segar dengan gula alami.',
                'price' => 12000,
                'price_offline' => 12000,
                'price_online' => 14000,
                'sales_type' => 'all',
                'user_id' => $userId,
            ],
            [
                'category_id' => $msd?->id, 
                'name_prd' => 'Soda Gembira',
                'code_prd' => 'MSD-SG01',
                'description_prd' => 'Minuman soda dengan sirup manis dan susu kental.',
                'price' => 18000,
                'price_offline' => 18000,
                'price_online' => 20000,
                'sales_type' => 'all',
                'user_id' => $userId,
            ],
        ];

        foreach ($products as $product) {
            if (!empty($product['category_id'])) {
                Product::updateOrCreate(
                    ['code_prd' => $product['code_prd']],
                    $product
                );
            }
        }
    }
}
