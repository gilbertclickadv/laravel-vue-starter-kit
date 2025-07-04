<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Check if we're updating an existing product
        $product = $this->route('product');
        $productId = $product ? $product->id : null;
        
        return [
            // Basic product information
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'product_type' => ['required', Rule::in(['simple', 'variable'])],
            'stock_quantity' => ['required_if:product_type,simple', 'integer', 'min:0'],
            
            // Product attributes (for variable products)
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'exists:attributes,id'],
            'attributes.*.attribute_value_ids' => ['required', 'array', 'min:1'],
            'attributes.*.attribute_value_ids.*' => ['required', 'exists:attribute_values,id'],
            
            // Product images
            'images' => ['nullable', 'array'],
            'images.*.id' => ['nullable', 'integer', 'exists:product_images,id'],
            'images.*.file' => ['nullable', 'image', 'max:10240'], // 10MB max
            'images.*.image_url' => ['nullable', 'string'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'images.*.attribute_combination' => ['nullable', 'array'],
            'images.*.attribute_combination.*.attribute_id' => ['required', 'exists:attributes,id'],
            'images.*.attribute_combination.*.attribute_value_id' => ['required', 'exists:attribute_values,id'],
            
            // Product variants (for variable products)
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.attributes' => ['required', 'array'],
            'variants.*.attributes.*.attribute_id' => ['required', 'exists:attributes,id'],
            'variants.*.attributes.*.attribute_value_id' => ['required', 'exists:attribute_values,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'vendor_id.required' => 'Please select a vendor.',
            'vendor_id.exists' => 'The selected vendor is invalid.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'base_price.required' => 'The base price is required.',
            'base_price.numeric' => 'The base price must be a number.',
            'base_price.min' => 'The base price must be at least 0.',
            'sku.unique' => 'This SKU is already taken.',
            'product_type.required' => 'Please select a product type.',
            'product_type.in' => 'The product type must be either simple or variable.',
            'stock_quantity.required_if' => 'Stock quantity is required for simple products.',
            'stock_quantity.integer' => 'Stock quantity must be an integer.',
            'stock_quantity.min' => 'Stock quantity must be at least 0.',
            'images.*.file.image' => 'The file must be an image.',
            'images.*.file.max' => 'The image size must not exceed 10MB.',
            'variants.*.sku.unique' => 'One of the variant SKUs is already taken.',
            'variants.*.price_override.numeric' => 'Variant price must be a number.',
            'variants.*.stock_quantity.integer' => 'Variant stock quantity must be an integer.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for variable products
            if ($this->input('product_type') === 'variable') {
                if (!$this->input('attributes') || count($this->input('attributes')) === 0) {
                    $validator->errors()->add('attributes', 'Variable products must have at least one attribute.');
                }
                
                if (!$this->input('variants') || count($this->input('variants')) === 0) {
                    $validator->errors()->add('variants', 'Variable products must have at least one variant.');
                }
            }
            
            // Validate that only one image is marked as primary
            if ($this->input('images')) {
                $primaryImages = collect($this->input('images'))->filter(function ($image) {
                    return isset($image['is_primary']) && $image['is_primary'] === true;
                });
                
                if ($primaryImages->count() > 1) {
                    $validator->errors()->add('images', 'Only one image can be marked as primary.');
                }
            }
            
            // Validate variant SKU uniqueness
            if ($this->input('variants')) {
                $product = $this->route('product');
                $productId = $product ? $product->id : null;
                
                foreach ($this->input('variants') as $index => $variant) {
                    if (!empty($variant['sku'])) {
                        $query = \App\Models\ProductVariant::where('sku', $variant['sku']);
                        
                        // If updating, exclude current variant
                        if (isset($variant['id']) && $variant['id']) {
                            $query->where('id', '!=', $variant['id']);
                        }
                        
                        // Also exclude variants from the same product if updating
                        if ($productId) {
                            $query->where('product_id', '!=', $productId);
                        }
                        
                        if ($query->exists()) {
                            $validator->errors()->add("variants.{$index}.sku", 'This variant SKU is already taken.');
                        }
                    }
                }
            }
        });
    }
}
