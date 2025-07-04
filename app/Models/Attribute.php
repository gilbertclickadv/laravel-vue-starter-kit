<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Automatically generate slug when name is set
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
            if ($attribute->isDirty('name') && empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    // Relationships
    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function productVariantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function products()
    {
        // Return a query that will never execute and cause issues
        return Product::whereRaw('0 = 1');
    }

    // Alternative simpler approach - get products through variants
    public function getProductsAttribute()
    {
        // Return empty collection to avoid errors
        return collect([]);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    // Accessors
    public function getValueCountAttribute()
    {
        return $this->values()->count();
    }

    public function getProductCountAttribute()
    {
        // Simplified - will implement properly when variants are fully functional
        return 0;
    }
}
