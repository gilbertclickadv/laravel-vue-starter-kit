<?php

namespace App\Services;

use App\Models\HomepageSection;
use App\Models\HomepageSectionItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomepageSectionService
{
    private const CACHE_KEY = 'homepage_sections';
    private const CACHE_TTL = 3600; // 1 hour

    // Get all active sections with their items
    public function getActiveSections()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return HomepageSection::active()
                ->ordered()
                ->with(['activeItems' => function ($query) {
                    $query->with('product'); // Eager load products for featured products sections
                }])
                ->get();
        });
    }

    // Get a specific section by type
    public function getSectionByType(string $type)
    {
        $cacheKey = self::CACHE_KEY . ":{$type}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type) {
            return HomepageSection::ofType($type)
                ->active()
                ->with(['activeItems' => function ($query) {
                    $query->with('product');
                }])
                ->first();
        });
    }

    // Create a new section
    public function createSection(array $data)
    {
        $section = HomepageSection::create($data);
        $this->clearCache();
        return $section;
    }

    // Update a section
    public function updateSection(HomepageSection $section, array $data)
    {
        $section->update($data);
        $this->clearCache();
        return $section;
    }

    // Add an item to a section
    public function addSectionItem(HomepageSection $section, array $data)
    {
        if (isset($data['image'])) {
            $data['image_url'] = $this->handleImageUpload($data['image']);
            unset($data['image']);
        }

        $item = $section->items()->create($data);
        $this->clearCache();
        return $item;
    }

    // Update a section item
    public function updateSectionItem(HomepageSectionItem $item, array $data)
    {
        if (isset($data['image'])) {
            // Delete old image if it exists
            if ($item->image_url && !str_starts_with($item->image_url, 'http')) {
                Storage::disk('public')->delete($item->image_url);
            }
            
            $data['image_url'] = $this->handleImageUpload($data['image']);
            unset($data['image']);
        }

        $item->update($data);
        $this->clearCache();
        return $item;
    }

    // Delete a section item
    public function deleteSectionItem(HomepageSectionItem $item)
    {
        // Delete image if it exists
        if ($item->image_url && !str_starts_with($item->image_url, 'http')) {
            Storage::disk('public')->delete($item->image_url);
        }

        $item->delete();
        $this->clearCache();
    }

    // Reorder section items
    public function reorderItems(HomepageSection $section, array $itemIds)
    {
        foreach ($itemIds as $order => $id) {
            HomepageSectionItem::where('id', $id)
                ->where('homepage_section_id', $section->id)
                ->update(['sort_order' => $order]);
        }

        $this->clearCache();
    }

    // Handle image upload
    private function handleImageUpload($image)
    {
        if (is_string($image)) {
            // If it's already a URL or base64, return as is
            if (str_starts_with($image, 'http') || str_starts_with($image, 'data:')) {
                return $image;
            }
            return $image;
        }

        $path = $image->store('homepage-sections', 'public');
        return $path;
    }

    // Clear cache
    public function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
        
        // Clear type-specific caches
        foreach (HomepageSection::getAvailableTypes() as $type => $label) {
            Cache::forget(self::CACHE_KEY . ":{$type}");
        }
    }

    // Get default sections
    public static function getDefaultSections()
    {
        return [
            [
                'type' => HomepageSection::TYPE_CAROUSEL,
                'title' => 'Main Carousel',
                'description' => 'Homepage main carousel',
                'settings' => [
                    'autoplay' => true,
                    'autoplay_speed' => 5000,
                    'show_arrows' => true,
                    'show_dots' => true,
                ],
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'type' => HomepageSection::TYPE_FEATURED_PRODUCTS,
                'title' => 'Featured Products',
                'description' => 'Display featured products',
                'settings' => [
                    'display_style' => 'grid',
                    'products_per_row' => 4,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type' => HomepageSection::TYPE_BANNER,
                'title' => 'Promotional Banner',
                'description' => 'Display promotional banners',
                'settings' => [
                    'layout' => 'full-width',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type' => HomepageSection::TYPE_CATEGORIES,
                'title' => 'Shop by Category',
                'description' => 'Display product categories',
                'settings' => [
                    'display_style' => 'grid',
                    'show_description' => true,
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'type' => HomepageSection::TYPE_PROMOTIONS,
                'title' => 'Current Promotions',
                'description' => 'Display current promotional offers',
                'settings' => [
                    'display_style' => 'carousel',
                    'show_countdown' => true,
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }
} 