<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageSection extends Model
{
    use HasFactory;

    // Section types
    const TYPE_CAROUSEL = 'carousel';
    const TYPE_FEATURED_PRODUCTS = 'featured_products';
    const TYPE_CATEGORIES = 'categories';
    const TYPE_BANNER = 'banner';
    const TYPE_PROMOTIONS = 'promotions';

    protected $fillable = [
        'type',
        'title',
        'description',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(HomepageSectionItem::class)->orderBy('sort_order');
    }

    // Active items only
    public function activeItems()
    {
        return $this->items()->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Get available section types
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_CAROUSEL => 'Carousel',
            self::TYPE_FEATURED_PRODUCTS => 'Featured Products',
            self::TYPE_BANNER => 'Banner',
            self::TYPE_CATEGORIES => 'Categories',
            self::TYPE_PROMOTIONS => 'Promotions',
        ];
    }
} 