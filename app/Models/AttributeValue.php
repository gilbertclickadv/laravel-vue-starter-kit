<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'hex',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // Relationships
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('value');
    }

    // Accessors
    public function getDisplayValueAttribute()
    {
        // For color attributes, show the color name
        if ($this->attribute && $this->attribute->type === 'color') {
            return ucfirst($this->value);
        }
        
        return $this->value;
    }

    public function getIsColorAttribute()
    {
        return $this->attribute && $this->attribute->type === 'color' && $this->hex;
    }

    // Helper method to get color info
    public function getColorInfo()
    {
        if ($this->isColorAttribute) {
            return [
                'name' => $this->value,
                'hex' => $this->hex,
            ];
        }
        return null;
    }
}
