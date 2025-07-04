<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }

    // Scopes
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForAttribute($query, $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    // Helper methods
    public static function getProductAttributeCombinations($productId)
    {
        return static::with(['attribute', 'attributeValue'])
            ->where('product_id', $productId)
            ->get()
            ->groupBy('attribute_id')
            ->map(function ($attributes) {
                return $attributes->map(function ($productAttribute) {
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
}
