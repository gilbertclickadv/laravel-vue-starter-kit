<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Get hero section data
        $heroSection = HomepageSection::with('items')
            ->where('type', 'carousel')
            ->where('is_active', true)
            ->first();

        // Get featured products
        $featuredProducts = Product::with(['images' => function($query) {
                $query->where('is_primary', true);
            }])
            ->whereHas('promotions', function($query) {
                $query->where('promotion_type', 'featured')
                      ->where('status', 'active')
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
            })
            ->where('status', 'active')
            ->take(8)
            ->get();

        // Get categories with images
        $categories = Category::with('products')
            ->active()
            ->ordered()
            ->take(8)
            ->get();

        // Get latest products
        $latestProducts = Product::with(['images' => function($query) {
                $query->where('is_primary', true);
            }])
            ->where('status', 'active')
            ->latest()
            ->take(8)
            ->get();

        return Inertia::render('Welcome', [
            'heroSection' => $heroSection,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'latestProducts' => $latestProducts,
        ]);
    }
} 