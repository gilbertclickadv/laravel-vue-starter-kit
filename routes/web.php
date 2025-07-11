<?php

use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductPromotionController;
use App\Http\Controllers\ProductVariantAttributeController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'revenue' => \App\Models\Order::where('status', 'delivered')->sum('total_amount'),
            'orders' => \App\Models\Order::count(),
            'products' => \App\Models\Product::count(),
            'vendors' => \App\Models\Vendor::count(),
            'customers' => \App\Models\User::where('role', 'customer')->count(),
            'reviews' => \App\Models\Review::count(),
            'promotions' => \App\Models\Promotion::where('status', 'active')->count(),
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Core E-commerce Management Routes
    Route::resource('vendors', VendorController::class);
Route::post('vendors/{vendor}/users', [VendorController::class, 'addUser'])->name('vendors.users.add');
Route::delete('vendors/{vendor}/users/{user}', [VendorController::class, 'removeUser'])->name('vendors.users.remove');
Route::get('vendors/{vendor}/available-users', [VendorController::class, 'getAvailableUsers'])->name('vendors.users.available');
    Route::resource('categories', CategoryController::class);
    Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
    Route::resource('attributes', \App\Http\Controllers\AttributeController::class);
    Route::get('attributes-for-products', [\App\Http\Controllers\AttributeController::class, 'getAttributesForProducts'])->name('attributes.for-products');
    Route::resource('products', ProductController::class);
    Route::put('products/{product}/variants', [ProductController::class, 'updateVariants'])->name('products.variants.update');
    Route::resource('orders', OrderController::class);
    Route::resource('reviews', ReviewController::class);
    Route::resource('promotions', PromotionController::class);
});

// Admin routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Homepage Sections
    Route::resource('homepage-sections', HomepageSectionController::class);
    
    // ... other admin routes ...
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
