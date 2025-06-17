<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'ProPak',
                'description' => 'ProPak is a comprehensive packaging solution designed for businesses that need reliable, efficient packaging management. It offers advanced features for inventory tracking, custom packaging designs, and automated packaging workflows.',
            ],
            [
                'name' => 'ProRapid',
                'description' => 'ProRapid is a high-speed processing platform built for businesses that demand quick turnaround times. It features lightning-fast data processing, real-time analytics, and seamless integration with existing systems.',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
