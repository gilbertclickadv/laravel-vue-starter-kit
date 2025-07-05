<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomepageSection;
use App\Models\HomepageSectionItem;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Hero Carousel
        $carousel = HomepageSection::create([
            'type' => HomepageSection::TYPE_CAROUSEL,
            'title' => 'Hero Carousel',
            'description' => 'Main carousel showcasing featured items and promotions',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Add carousel items
        $carousel->items()->createMany([
            [
                'title' => 'Summer Collection',
                'description' => 'Discover our latest summer collection with amazing discounts',
                'link_url' => '/collections/summer',
                'link_text' => 'Shop Now',
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'title' => 'New Arrivals',
                'description' => 'Check out our newest products just for you',
                'link_url' => '/new-arrivals',
                'link_text' => 'View Collection',
                'is_active' => true,
                'sort_order' => 1,
            ],
        ]);

        // Featured Products Section
        $featuredProducts = HomepageSection::create([
            'type' => HomepageSection::TYPE_FEATURED_PRODUCTS,
            'title' => 'Featured Products',
            'description' => 'Our handpicked selection of must-have items',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Categories Section
        HomepageSection::create([
            'type' => HomepageSection::TYPE_CATEGORIES,
            'title' => 'Shop by Category',
            'description' => 'Browse our wide range of categories',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Promotional Banner
        $banner = HomepageSection::create([
            'type' => HomepageSection::TYPE_BANNER,
            'title' => 'Special Offer',
            'description' => 'Limited time promotional banner',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Add banner item
        $banner->items()->create([
            'title' => 'Summer Sale',
            'description' => 'Get up to 50% off on selected items',
            'link_url' => '/sale',
            'link_text' => 'Shop Sale',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Promotions Section
        HomepageSection::create([
            'type' => HomepageSection::TYPE_PROMOTIONS,
            'title' => 'Current Promotions',
            'description' => 'Check out our ongoing promotions and deals',
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
} 