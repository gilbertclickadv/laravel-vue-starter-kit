<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsService
{
    private const CACHE_KEY = 'site_settings';
    private const CACHE_TTL = 3600; // 1 hour

    // Get all settings
    public function all()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SiteSetting::orderBy('sort_order')
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->typed_value];
                })
                ->all();
        });
    }

    // Get settings by group
    public function getGroup(string $group)
    {
        $cacheKey = self::CACHE_KEY . ":{$group}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group) {
            return SiteSetting::byGroup($group)
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->typed_value];
                })
                ->all();
        });
    }

    // Get a single setting
    public function get(string $key, $default = null)
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    // Set a single setting
    public function set(string $key, $value)
    {
        $setting = SiteSetting::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        $setting->value = $value;
        $setting->save();

        $this->clearCache();

        return true;
    }

    // Update multiple settings at once
    public function updateMany(array $settings)
    {
        foreach ($settings as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
        }

        $this->clearCache();
    }

    // Clear settings cache
    public function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
        
        // Clear group caches
        $groups = SiteSetting::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget(self::CACHE_KEY . ":{$group}");
        }
    }

    // Get default settings structure
    public static function getDefaultSettings()
    {
        return [
            // Site Information
            [
                'key' => 'site_name',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Site Name',
                'value' => 'My E-commerce Store',
                'is_public' => true,
            ],
            [
                'key' => 'site_description',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Site Description',
                'value' => 'Welcome to our online store',
                'is_public' => true,
            ],
            
            // Contact Information
            [
                'key' => 'contact_email',
                'group' => 'contact',
                'type' => 'text',
                'label' => 'Contact Email',
                'validation_rules' => ['email'],
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'group' => 'contact',
                'type' => 'text',
                'label' => 'Contact Phone',
                'is_public' => true,
            ],
            
            // Social Media
            [
                'key' => 'social_facebook',
                'group' => 'social',
                'type' => 'text',
                'label' => 'Facebook URL',
                'is_public' => true,
            ],
            [
                'key' => 'social_instagram',
                'group' => 'social',
                'type' => 'text',
                'label' => 'Instagram URL',
                'is_public' => true,
            ],
            
            // SEO Settings
            [
                'key' => 'meta_title_template',
                'group' => 'seo',
                'type' => 'text',
                'label' => 'Meta Title Template',
                'value' => '{page_title} | {site_name}',
                'is_public' => true,
            ],
            [
                'key' => 'meta_description',
                'group' => 'seo',
                'type' => 'text',
                'label' => 'Default Meta Description',
                'is_public' => true,
            ],
            
            // Store Settings
            [
                'key' => 'currency_code',
                'group' => 'store',
                'type' => 'text',
                'label' => 'Currency Code',
                'value' => 'USD',
                'is_public' => true,
            ],
            [
                'key' => 'currency_symbol',
                'group' => 'store',
                'type' => 'text',
                'label' => 'Currency Symbol',
                'value' => '$',
                'is_public' => true,
            ],
            [
                'key' => 'tax_rate',
                'group' => 'store',
                'type' => 'number',
                'label' => 'Default Tax Rate (%)',
                'value' => '0',
                'is_public' => true,
            ],
            
            // Homepage Settings
            [
                'key' => 'featured_products_count',
                'group' => 'homepage',
                'type' => 'number',
                'label' => 'Number of Featured Products',
                'value' => '8',
                'is_public' => true,
            ],
            [
                'key' => 'new_arrivals_count',
                'group' => 'homepage',
                'type' => 'number',
                'label' => 'Number of New Arrivals',
                'value' => '8',
                'is_public' => true,
            ],
        ];
    }
} 