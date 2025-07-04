<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'description',
        'base_price',
        'sku',
        'status',
        'product_type',
        'stock_quantity',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'product_type' => 'string',
        'stock_quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            // Delete all associated images from storage and database
            foreach ($product->images as $image) {
                // Delete file from storage if it's a local file
                if (!str_starts_with($image->image_url, 'http') && !str_starts_with($image->image_url, 'data:')) {
                    Storage::disk('public')->delete($image->image_url);
                }
                // Delete the image record
                $image->delete();
            }
            
            // Delete all associated variants and their attributes
            foreach ($product->variants as $variant) {
                $variant->variantAttributes()->delete();
                $variant->delete();
            }
            
            // Delete all product attributes
            $product->productAttributes()->delete();
            
            // Delete all reviews
            $product->reviews()->delete();
            
            // Delete all wishlists
            $product->wishlists()->delete();
            
            // Delete all product promotions
            $product->promotions()->detach();
        });
    }

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function generalImages()
    {
        return $this->hasMany(ProductImage::class)->general()->ordered();
    }

    public function attributeSpecificImages()
    {
        return $this->hasMany(ProductImage::class)->attributeSpecific()->ordered();
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'product_promotions');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Product attributes relationships
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
                    ->withPivot('attribute_value_id')
                    ->withTimestamps();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attributes')
                    ->withPivot('attribute_id')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSimple($query)
    {
        return $query->where('product_type', 'simple');
    }

    public function scopeVariable($query)
    {
        return $query->where('product_type', 'variable');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // Accessors
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getIsVariableAttribute()
    {
        return $this->product_type === 'variable';
    }

    public function getIsSimpleAttribute()
    {
        return $this->product_type === 'simple';
    }

    public function getIsInStockAttribute()
    {
        if ($this->is_variable) {
            return $this->variants()->where('stock_quantity', '>', 0)->exists();
        }
        return $this->stock_quantity > 0;
    }

    public function getTotalStockAttribute()
    {
        if ($this->is_variable) {
            return $this->variants()->sum('stock_quantity');
        }
        return $this->stock_quantity;
    }

    // New helper methods for attributes and images
    public function getAttributeCombinations()
    {
        return ProductAttribute::getProductAttributeCombinations($this->id);
    }

    public function getSelectedAttributeValues()
    {
        return $this->productAttributes()
                    ->with(['attribute', 'attributeValue'])
                    ->get()
                    ->groupBy('attribute_id')
                    ->map(function ($productAttributes) {
                        return $productAttributes->map(function ($productAttribute) {
                            return [
                                'attribute_id' => $productAttribute->attribute_id,
                                'attribute_name' => $productAttribute->attribute->name,
                                'attribute_value_id' => $productAttribute->attribute_value_id,
                                'attribute_value' => $productAttribute->attributeValue->value,
                                'attribute_value_hex' => $productAttribute->attributeValue->hex,
                            ];
                        });
                    });
    }

    public function getImagesForAttributeCombination($attributeCombination = [])
    {
        $images = $this->images;
        
        if (empty($attributeCombination)) {
            // Return general images when no specific combination is requested
            return $images->filter(function ($image) {
                return $image->isGeneral();
            });
        }

        // Find images that match the specific attribute combination
        $matchingImages = $images->filter(function ($image) use ($attributeCombination) {
            return $image->matchesAttributeCombination($attributeCombination);
        });

        // If no specific images found, fallback to general images
        if ($matchingImages->isEmpty()) {
            return $images->filter(function ($image) {
                return $image->isGeneral();
            });
        }

        return $matchingImages;
    }

    public function syncAttributes($attributesData)
    {
        // Delete existing product attributes
        $this->productAttributes()->delete();

        // Add new product attributes
        foreach ($attributesData as $attributeId => $attributeValueIds) {
            foreach ($attributeValueIds as $attributeValueId) {
                $this->productAttributes()->create([
                    'attribute_id' => $attributeId,
                    'attribute_value_id' => $attributeValueId,
                ]);
            }
        }
    }

    // Accessor methods for frontend
    public function getFormattedAttributesAttribute()
    {
        return $this->productAttributes()
                    ->with(['attribute', 'attributeValue'])
                    ->get()
                    ->groupBy('attribute_id')
                    ->map(function ($productAttributes, $attributeId) {
                        return [
                            'attribute_id' => $attributeId,
                            'attribute_value_ids' => $productAttributes->pluck('attribute_value_id')->toArray(),
                        ];
                    })
                    ->values()
                    ->toArray();
    }

    public function getFormattedImagesAttribute()
    {
        return $this->images->map(function ($image) {
            // For stored images, check if it's a full URL or a storage path
            $imageUrl = $image->image_url;
            if (!str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, 'data:')) {
                $imageUrl = asset('storage/' . $image->image_url);
            }
            
            return [
                'id' => $image->id,
                'image_url' => $imageUrl,
                'alt_text' => $image->alt_text ?? '',
                'sort_order' => $image->sort_order,
                'is_primary' => $image->is_primary,
                'attribute_combination' => $image->attribute_combination ? json_decode($image->attribute_combination, true) : [],
            ];
        })->toArray();
    }

    // Override the toArray method to include formatted attributes and images
    protected $appends = [
        'average_rating',
        'total_reviews',
        'is_variable',
        'is_simple',
        'is_in_stock',
        'total_stock',
        'formatted_attributes',
        'formatted_images',
    ];
}
