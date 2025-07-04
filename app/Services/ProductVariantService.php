<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductVariantService
{
    /**
     * Generate, update, and delete variants for a variable product based on its attributes.
     */
    public function generate(Product $product): void
    {
        if ($product->product_type !== 'variable') {
            return;
        }

        $product->load('productAttributes.attributeValue.attribute');
        $attributes = $product->productAttributes->groupBy('attribute_id');

        if ($attributes->isEmpty()) {
            $product->variants()->delete();
            return;
        }

        $attributeValueIds = $attributes->map(fn ($group) => $group->pluck('attribute_value_id')->toArray())->values()->all();
        $combinations = $this->getCombinations($attributeValueIds);
        
        $this->syncVariants($product, $combinations);
    }

    /**
     * Create or update variants for the given combinations, and delete obsolete ones.
     */
    private function syncVariants(Product $product, array $combinations): void
    {
        $existingVariants = $product->variants()->with('variantAttributes')->get();
        $newVariantSignatures = [];

        foreach ($combinations as $combination) {
            sort($combination);
            $signature = implode('-', $combination);
            $newVariantSignatures[] = $signature;
            
            $variant = $this->findVariantByCombination($existingVariants, $combination);

            if (!$variant) {
                $this->createVariant($product, $combination);
            }
        }
        
        foreach ($existingVariants as $variant) {
            $variantCombination = $variant->variantAttributes->pluck('attribute_value_id')->toArray();
            sort($variantCombination);
            $signature = implode('-', $variantCombination);

            if (!in_array($signature, $newVariantSignatures)) {
                $variant->delete();
            }
        }
    }
    
    /**
     * Find a variant from a collection that matches a specific attribute combination.
     */
    private function findVariantByCombination($variants, array $combination)
    {
        return $variants->first(function ($variant) use ($combination) {
            $variantCombination = $variant->variantAttributes->pluck('attribute_value_id')->sort()->values()->all();
            return $combination === $variantCombination;
        });
    }

    /**
     * Create a new product variant and its associated attributes.
     */
    private function createVariant(Product $product, array $combination): void
    {
        $variantSku = $this->generateVariantSku($product, $combination);

        $variant = $product->variants()->create([
            'sku' => $variantSku,
            'stock_quantity' => 0,
        ]);

        $this->syncVariantAttributes($variant, $combination);
    }

    /**
     * Generate a unique SKU for the variant.
     */
    private function generateVariantSku(Product $product, array $combination): string
    {
        $values = \App\Models\AttributeValue::whereIn('id', $combination)->get()->pluck('value');
        $sku = ($product->sku ?? Str::slug($product->name)) . '-' . $values->map(fn($v) => Str::slug($v))->join('-');
        return Str::limit($sku, 100);
    }

    /**
     * Attach attribute values to a given variant.
     */
    private function syncVariantAttributes(ProductVariant $variant, array $combination): void
    {
        $productAttributeValues = \App\Models\ProductAttribute::where('product_id', $variant->product_id)
            ->whereIn('attribute_value_id', $combination)->get();
        
        $attributesToSync = [];
        foreach ($productAttributeValues as $productAttribute) {
            $attributesToSync[] = [
                'attribute_id' => $productAttribute->attribute_id,
                'attribute_value_id' => $productAttribute->attribute_value_id,
            ];
        }
        
        $variant->variantAttributes()->createMany($attributesToSync);
    }

    /**
     * Generate all possible combinations of a set of arrays (Cartesian product).
     */
    private function getCombinations(array $arrays): array
    {
        $result = [[]];
        foreach ($arrays as $property => $values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($values as $value) {
                    $tmp[] = array_merge($result_item, [$property => $value]);
                }
            }
            $result = $tmp;
        }
        return array_map('array_values', $result);
    }
} 