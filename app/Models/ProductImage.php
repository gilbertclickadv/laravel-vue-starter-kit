<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        'alt_text',
        'sort_order',
        'attribute_combination',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'attribute_combination' => 'array',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }

    public function scopeGeneral($query)
    {
        return $query->whereNull('attribute_combination');
    }

    public function scopeAttributeSpecific($query)
    {
        return $query->whereNotNull('attribute_combination');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // Helper methods
    public function isAttributeSpecific()
    {
        return !empty($this->attribute_combination);
    }

    public function isGeneral()
    {
        return empty($this->attribute_combination);
    }

    public function matchesAttributeCombination($attributeCombination)
    {
        if (empty($this->attribute_combination)) {
            return true; // General images match any combination
        }

        if (empty($attributeCombination)) {
            return false; // Attribute-specific images don't match empty combination
        }

        // Check if all required attributes match
        foreach ($this->attribute_combination as $requiredAttribute) {
            $found = false;
            foreach ($attributeCombination as $selectedAttribute) {
                if ($requiredAttribute['attribute_id'] == $selectedAttribute['attribute_id'] &&
                    $requiredAttribute['attribute_value_id'] == $selectedAttribute['attribute_value_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        return true;
    }

    public function getAttributeCombinationDisplay()
    {
        if (empty($this->attribute_combination)) {
            return 'General';
        }

        $combinations = [];
        foreach ($this->attribute_combination as $attr) {
            $attribute = \App\Models\Attribute::find($attr['attribute_id']);
            $attributeValue = \App\Models\AttributeValue::find($attr['attribute_value_id']);
            
            if ($attribute && $attributeValue) {
                $combinations[] = $attribute->name . ': ' . $attributeValue->value;
            }
        }

        return implode(', ', $combinations);
    }
}
