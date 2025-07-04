<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price_override',
        'stock_quantity',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class, 'variant_id');
    }

    // Alias for backward compatibility
    public function attributes()
    {
        return $this->variantAttributes();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getPriceAttribute()
    {
        return $this->price_override ?? $this->product->base_price;
    }

    public function getIsInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }
}
