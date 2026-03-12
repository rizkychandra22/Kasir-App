<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mkb = Category::where('name_code', 'MKB')->first();
        $mkr = Category::where('name_code', 'MKR')->first();
        $mnp = Category::where('name_code', 'MNP')->first();
        $mns = Category::where('name_code', 'MNS')->first();
        $msd = Category::where('name_code', 'MSD')->first();

        $products = [
            [
                'category_id' => $mkb->id, 
                'name_prd' => 'Nasi Goreng Spesial',
                'code_prd' => $mkb->name_code . 'NGS01',
                'description_prd' => 'Nasi goreng dengan telur, ayam, dan sayur.',
                'stock' => 50,
                'price' => 20000
            ],
            [
                'category_id' => $mkr->id, 
                'name_prd' => 'Keripik Singkong Pedas',
                'code_prd' => $mkr->name_code . 'KSP01',
                'description_prd' => 'Keripik singkong dengan bumbu pedas khas.',
                'stock' => 100,
                'price' => 15000
            ],
            [
                'category_id' => $mnp->id, 
                'name_prd' => 'Teh Hangat',
                'code_prd' => $mnp->name_code . 'TH01',
                'description_prd' => 'Teh hangat dengan rasa alami.',
                'stock' => 200,
                'price' => 8000
            ],
            [
                'category_id' => $mns->id, 
                'name_prd' => 'Es Jeruk Segar',
                'code_prd' => $mns->name_code . 'EJS01',
                'description_prd' => 'Minuman es jeruk segar dengan gula alami.',
                'stock' => 150,
                'price' => 12000
            ],
            [
                'category_id' => $msd->id, 
                'name_prd' => 'Soda Gembira',
                'code_prd' => $msd->name_code . 'SG01',
                'description_prd' => 'Minuman soda dengan sirup manis dan susu kental.',
                'stock' => 80,
                'price' => 15000
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
