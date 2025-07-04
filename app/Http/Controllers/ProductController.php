<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Attribute;
use App\Services\ProductVariantService;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['vendor.user', 'category', 'images', 'reviews'])
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->product_type, function ($query, $productType) {
                return $query->where('product_type', $productType);
            })
            ->when($request->vendor_id, function ($query, $vendorId) {
                return $query->where('vendor_id', $vendorId);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->min_price, function ($query, $minPrice) {
                return $query->where('base_price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                return $query->where('base_price', '<=', $maxPrice);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $products = $query->paginate($request->per_page ?? 10);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'vendors' => Vendor::with('user')->get(),
            'categories' => Category::all(),
            'filters' => $request->only(['search', 'status', 'product_type', 'vendor_id', 'category_id', 'min_price', 'max_price', 'sort_by', 'sort_order']),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'vendors' => Vendor::with('user')->get(),
            'categories' => Category::all(),
            'attributes' => Attribute::with(['values' => function ($query) {
                $query->ordered();
            }])->ordered()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, ProductVariantService $variantService)
    {
        try {
            \DB::beginTransaction();
            
            // Create the product
            $product = Product::create([
                'vendor_id' => $request->vendor_id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'sku' => $request->sku,
                'status' => $request->status,
                'product_type' => $request->product_type,
                'stock_quantity' => $request->product_type === 'simple' ? $request->stock_quantity : 0,
            ]);

            // Handle attributes for variable products
            if ($request->product_type === 'variable' && $request->input('attributes')) {
                $this->syncProductAttributes($product, $request->input('attributes'));
            }

            // Handle image uploads
            if ($request->input('images')) {
                $this->handleImageUploads($product, $request->input('images'));
            }

            // Handle variants for variable products
            if ($request->product_type === 'variable' && $request->input('variants')) {
                $this->createProductVariants($product, $request->input('variants'));
            }

            \DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully!');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating product: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating product. Please try again.');
        }
    }

    /**
     * Sync product attributes
     */
    private function syncProductAttributes(Product $product, array $attributes)
    {
        // Delete existing product attributes
        $product->productAttributes()->delete();
        
        // Add new product attributes
        foreach ($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'];
            $attributeValueIds = $attribute['attribute_value_ids'];
            
            foreach ($attributeValueIds as $attributeValueId) {
                $product->productAttributes()->create([
                    'attribute_id' => $attributeId,
                    'attribute_value_id' => $attributeValueId,
                ]);
            }
        }
    }

    /**
     * Handle image uploads and create product images
     */
    private function handleImageUploads(Product $product, array $images)
    {
        $hasPrimary = false;
        
        foreach ($images as $index => $imageData) {
            $imageUrl = null;
            
            // Handle file upload
            if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $imageData['file'];
                $filename = time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
                $imageUrl = $path;
            } elseif (isset($imageData['image_url']) && !empty($imageData['image_url'])) {
                // Handle base64 image data
                $imageUrl = $this->saveBase64Image($imageData['image_url'], $product->id, $index);
            }
            
            if ($imageUrl) {
                $isPrimary = isset($imageData['is_primary']) && $imageData['is_primary'] && !$hasPrimary;
                if ($isPrimary) {
                    $hasPrimary = true;
                }
                
                $product->images()->create([
                    'image_url' => $imageUrl,
                    'alt_text' => $imageData['alt_text'] ?? '',
                    'sort_order' => $imageData['sort_order'] ?? $index,
                    'is_primary' => $isPrimary,
                    'attribute_combination' => isset($imageData['attribute_combination']) 
                        ? json_encode($imageData['attribute_combination']) 
                        : null,
                ]);
            }
        }
        
        // If no image was marked as primary, mark the first one as primary
        if (!$hasPrimary && $product->images()->count() > 0) {
            $product->images()->first()->update(['is_primary' => true]);
        }
    }

    /**
     * Save base64 image data to storage
     */
    private function saveBase64Image(string $base64Data, int $productId, int $index): string
    {
        // Extract the base64 data and extension
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $extension = $matches[1];
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $base64Data = base64_decode($base64Data);
            
            $filename = "product_{$productId}_{$index}_{" . time() . "}.{$extension}";
            $path = "products/{$filename}";
            
            Storage::disk('public')->put($path, $base64Data);
            
            return $path;
        }
        
        throw new \Exception('Invalid image data format');
    }

    /**
     * Create product variants
     */
    private function createProductVariants(Product $product, array $variants)
    {
        foreach ($variants as $variantData) {
            $variant = $product->variants()->create([
                'sku' => $variantData['sku'] ?? null,
                'price_override' => !empty($variantData['price_override']) ? $variantData['price_override'] : null,
                'stock_quantity' => $variantData['stock_quantity'] ?? 0,
            ]);
            
            // Create variant attributes
            if (isset($variantData['attributes'])) {
                foreach ($variantData['attributes'] as $attribute) {
                    $variant->variantAttributes()->create([
                        'attribute_id' => $attribute['attribute_id'],
                        'attribute_value_id' => $attribute['attribute_value_id'],
                    ]);
                }
            }
        }
    }

    /**
     * Update product images
     */
    private function updateProductImages(Product $product, array $images)
    {
        // Get existing images
        $existingImages = $product->images()->get();
        $imageIdsToKeep = [];
        $hasPrimary = false;
        
        foreach ($images as $index => $imageData) {
            $imageUrl = null;
            
            if (isset($imageData['id'])) {
                // Update existing image
                $existingImage = $existingImages->find($imageData['id']);
                if ($existingImage) {
                    $isPrimary = isset($imageData['is_primary']) && $imageData['is_primary'] && !$hasPrimary;
                    if ($isPrimary) {
                        $hasPrimary = true;
                    }
                    
                    $existingImage->update([
                        'alt_text' => $imageData['alt_text'] ?? '',
                        'sort_order' => $imageData['sort_order'] ?? $index,
                        'is_primary' => $isPrimary,
                        'attribute_combination' => isset($imageData['attribute_combination']) 
                            ? json_encode($imageData['attribute_combination']) 
                            : null,
                    ]);
                    
                    $imageIdsToKeep[] = $imageData['id'];
                }
            } else {
                // Handle file upload for new image
                if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $imageData['file'];
                    $filename = time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('products', $filename, 'public');
                    $imageUrl = $path;
                } elseif (isset($imageData['image_url']) && !empty($imageData['image_url']) && str_starts_with($imageData['image_url'], 'data:')) {
                    // Handle base64 image data
                    $imageUrl = $this->saveBase64Image($imageData['image_url'], $product->id, $index);
                }
                
                if ($imageUrl) {
                    $isPrimary = isset($imageData['is_primary']) && $imageData['is_primary'] && !$hasPrimary;
                    if ($isPrimary) {
                        $hasPrimary = true;
                    }
                    
                    $newImage = $product->images()->create([
                        'image_url' => $imageUrl,
                        'alt_text' => $imageData['alt_text'] ?? '',
                        'sort_order' => $imageData['sort_order'] ?? $index,
                        'is_primary' => $isPrimary,
                        'attribute_combination' => isset($imageData['attribute_combination']) 
                            ? json_encode($imageData['attribute_combination']) 
                            : null,
                    ]);
                    
                    $imageIdsToKeep[] = $newImage->id;
                }
            }
        }
        
        // Delete images that are no longer present
        foreach ($existingImages as $imageToDelete) {
            if (!in_array($imageToDelete->id, $imageIdsToKeep)) {
                // Delete file from storage if it's a local file
                if (!str_starts_with($imageToDelete->image_url, 'http') && !str_starts_with($imageToDelete->image_url, 'data:')) {
                    Storage::disk('public')->delete($imageToDelete->image_url);
                }
                $imageToDelete->delete();
            }
        }
        
        // If no image was marked as primary, mark the first one as primary
        if (!$hasPrimary && $product->images()->count() > 0) {
            $product->images()->first()->update(['is_primary' => true]);
        }
    }

    /**
     * Update product variants
     */
    private function updateProductVariants(Product $product, array $variants)
    {
        // Get existing variants
        $existingVariants = $product->variants()->with('variantAttributes')->get();
        $variantIdsToKeep = [];
        
        foreach ($variants as $variantData) {
            if (isset($variantData['id']) && $variantData['id']) {
                // Update existing variant
                $existingVariant = $existingVariants->find($variantData['id']);
                if ($existingVariant) {
                    $existingVariant->update([
                        'sku' => $variantData['sku'] ?? null,
                        'price_override' => !empty($variantData['price_override']) ? $variantData['price_override'] : null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                    ]);
                    
                    // Update variant attributes
                    $existingVariant->variantAttributes()->delete();
                    if (isset($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attribute) {
                            $existingVariant->variantAttributes()->create([
                                'attribute_id' => $attribute['attribute_id'],
                                'attribute_value_id' => $attribute['attribute_value_id'],
                            ]);
                        }
                    }
                    
                    $variantIdsToKeep[] = $variantData['id'];
                }
            } else {
                // Create new variant
                $newVariant = $product->variants()->create([
                    'sku' => $variantData['sku'] ?? null,
                    'price_override' => !empty($variantData['price_override']) ? $variantData['price_override'] : null,
                    'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                ]);
                
                // Create variant attributes
                if (isset($variantData['attributes'])) {
                    foreach ($variantData['attributes'] as $attribute) {
                        $newVariant->variantAttributes()->create([
                            'attribute_id' => $attribute['attribute_id'],
                            'attribute_value_id' => $attribute['attribute_value_id'],
                        ]);
                    }
                }
                
                $variantIdsToKeep[] = $newVariant->id;
            }
        }
        
        // Delete variants that are no longer present
        $variantsToDelete = $existingVariants->whereNotIn('id', $variantIdsToKeep);
        foreach ($variantsToDelete as $variantToDelete) {
            // First delete all variant attributes
            $variantToDelete->variantAttributes()->delete();
            // Then delete the variant itself
            $variantToDelete->delete();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['vendor.user', 'category', 'images', 'variants.variantAttributes.attributeValue.attribute', 'reviews.user']);

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load([
            'vendor.user', 
            'category', 
            'images' => function($query) {
                $query->ordered();
            },
            'productAttributes.attribute.values' => function($query) {
                $query->ordered();
            },
            'productAttributes.attributeValue',
            'variants.variantAttributes.attribute',
            'variants.variantAttributes.attributeValue'
        ]);

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'vendors' => Vendor::with('user')->get(),
            'categories' => Category::all(),
            'attributes' => Attribute::with(['values' => function ($query) {
                $query->ordered();
            }])->ordered()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, Product $product, ProductVariantService $variantService)
    {
        try {
            \DB::beginTransaction();
            
            // Update the product
            $product->update([
                'vendor_id' => $request->vendor_id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'sku' => $request->sku,
                'status' => $request->status,
                'product_type' => $request->product_type,
                'stock_quantity' => $request->product_type === 'simple' ? $request->stock_quantity : 0,
            ]);

            // Handle attributes for variable products
            if ($request->product_type === 'variable' && $request->input('attributes')) {
                $this->syncProductAttributes($product, $request->input('attributes'));
            } else {
                // If product type changed to simple, remove all attributes
                $product->productAttributes()->delete();
            }

            // Handle image updates
            if ($request->input('images')) {
                $this->updateProductImages($product, $request->input('images'));
            }

            // Handle variants for variable products
            if ($request->product_type === 'variable' && $request->input('variants')) {
                $this->updateProductVariants($product, $request->input('variants'));
            } else {
                // If product type changed to simple, remove all variants
                $product->variants()->delete();
            }

            \DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully!');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating product: ' . $e->getMessage());
            
            // Load the product with its relationships for the form
            $product->load([
                'vendor.user', 
                'category', 
                'images' => function($query) {
                    $query->ordered();
                },
                'productAttributes.attribute.values' => function($query) {
                    $query->ordered();
                },
                'productAttributes.attributeValue',
                'variants.variantAttributes.attribute',
                'variants.variantAttributes.attributeValue'
            ]);

            // Return back to the edit page with the error and form data
            return Inertia::render('Products/Edit', [
                'product' => $product,
                'vendors' => Vendor::with('user')->get(),
                'categories' => Category::all(),
                'attributes' => Attribute::with(['values' => function ($query) {
                    $query->ordered();
                }])->ordered()->get(),
                'error' => 'Error updating product. Please try again.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $productName = $product->name;
        $product->delete();

            return redirect()->route('products.index')
                ->with('success', "Product '{$productName}' and all related data deleted successfully.");
        } catch (\Exception $e) {
            \Log::error('Error deleting product: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error deleting product. Please try again.');
        }
    }

    
    /**
     * Get product statistics.
     */
    private function getStats()
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'inactive' => Product::where('status', 'inactive')->count(),
            'simple' => Product::where('product_type', 'simple')->count(),
            'variable' => Product::where('product_type', 'variable')->count(),
            'in_stock' => Product::where('stock_quantity', '>', 0)->count(),
            'this_month' => Product::whereMonth('created_at', now()->month)->count(),
            'total_value' => Product::sum('base_price'),
            'average_price' => Product::avg('base_price'),
        ];
    }
}
