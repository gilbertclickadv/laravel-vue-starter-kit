<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'homepage_section_id',
        'title',
        'description',
        'image_url',
        'link_url',
        'link_text',
        'additional_data',
        'is_active',
        'sort_order',
        'product_id',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function section(): BelongsTo
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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

    // Helper methods
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http') || str_starts_with($value, 'data:')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    public function setImageUrlAttribute($value)
    {
        if (str_starts_with($value, 'http') || str_starts_with($value, 'data:')) {
            $this->attributes['image_url'] = $value;
        } else {
            $this->attributes['image_url'] = str_replace('storage/', '', $value);
        }
    }
} 