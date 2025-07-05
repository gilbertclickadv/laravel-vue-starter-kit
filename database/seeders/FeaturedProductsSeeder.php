<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\ProductPromotion;

class FeaturedProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a featured promotion
        $featuredPromotion = Promotion::create([
            'title' => 'Featured Products',
            'description' => 'Products featured on the homepage',
            'type' => 'percentage',  // Using percentage as it's required by the enum
            'promotion_type' => 'featured',
            'value' => 0, // No discount, just for featuring products
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        // Get some random active products
        $products = Product::where('status', 'active')
            ->inRandomOrder()
            ->take(8)
            ->get();

        // Associate products with the featured promotion
        foreach ($products as $product) {
            ProductPromotion::create([
                'product_id' => $product->id,
                'promotion_id' => $featuredPromotion->id,
            ]);
        }
    }
}
