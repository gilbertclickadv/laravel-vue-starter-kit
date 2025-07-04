<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build query for root categories with their children
        $categoriesQuery = Category::with(['children' => function ($query) {
            $query->withCount('products');
        }])->whereNull('parent_id')->withCount('products');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $categoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('children', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', '%' . $search . '%')
                           ->orWhere('description', 'like', '%' . $search . '%');
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $categoriesQuery->where('status', $request->status);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $categoriesQuery->orderBy($sortBy, $sortOrder);

        // Get paginated root categories
        $rootCategories = $categoriesQuery->paginate(10);

        // Get standalone subcategories for search results
        $standaloneSubcategories = collect();
        if ($request->filled('search')) {
            $search = $request->search;
            $rootCategoryIds = $rootCategories->pluck('id');
            
            $standaloneSubcategories = Category::with(['parent'])
                ->whereNotNull('parent_id')
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                })
                ->whereDoesntHave('parent', function ($q) use ($rootCategoryIds) {
                    $q->whereIn('id', $rootCategoryIds);
                })
                ->withCount('products')
                ->get();
        }

        // Calculate statistics
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('status', 'active')->count(),
            'inactive' => Category::where('status', 'inactive')->count(),
            'root_categories' => Category::whereNull('parent_id')->count(),
            'child_categories' => Category::whereNotNull('parent_id')->count(),
            'this_month' => Category::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count(),
        ];

        return Inertia::render('Categories/Index', [
            'rootCategories' => $rootCategories,
            'standaloneSubcategories' => $standaloneSubcategories,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all active categories to use as potential parents
        $parentCategories = Category::where('status', 'active')
                                  ->whereNull('parent_id') // Only show root categories as potential parents
                                  ->orderBy('name')
                                  ->get();

        return Inertia::render('Categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Additional validation: if parent_id is provided, ensure it's not creating a circular reference
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->parent_id) {
                // For now, we'll limit to 2 levels (root -> child only)
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Cannot create subcategory under another subcategory. Please select a root category as parent.']);
            }
        }

        Category::create($validated);

        return redirect()->route('categories.index')
                        ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products']);
        
        return Inertia::render('Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Get potential parent categories (exclude self and any descendants to prevent circular references)
        $descendantIds = $this->getDescendantIds($category);
        $excludeIds = array_merge([$category->id], $descendantIds);
        
        $parentCategories = Category::whereNotIn('id', $excludeIds)
                                  ->where('status', 'active')
                                  ->orderBy('name')
                                  ->get();

        return Inertia::render('Categories/Edit', [
            'category' => $category->load('parent'),
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Get all descendant IDs of a category (recursive)
     */
    private function getDescendantIds(Category $category, $ids = [])
    {
        $children = $category->children;
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = $this->getDescendantIds($child, $ids);
        }
        return $ids;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Additional validation: prevent circular references
        if ($validated['parent_id']) {
            if ($validated['parent_id'] == $category->id) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'A category cannot be its own parent.']);
            }

            // Check if the selected parent is a descendant of this category
            $descendantIds = $this->getDescendantIds($category);
            if (in_array($validated['parent_id'], $descendantIds)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Cannot set a descendant category as parent. This would create a circular reference.']);
            }

            // Limit to 2 levels (root -> child only)
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->parent_id) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Cannot create subcategory under another subcategory. Please select a root category as parent.']);
            }
        }

        $category->update($validated);

        return redirect()->route('categories.index')
                        ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                           ->with('error', 'Cannot delete category with associated products. Please move or delete the products first.');
        }

        // Check if any subcategories have products
        $subcategoriesWithProducts = $category->children()
            ->whereHas('products')
            ->count();
        
        if ($subcategoriesWithProducts > 0) {
            return redirect()->route('categories.index')
                           ->with('error', 'Cannot delete category because its subcategories have associated products. Please move or delete the products first.');
        }

        // If it's a parent category, delete all subcategories first (cascade delete)
        if ($category->children()->count() > 0) {
            $subcategoryCount = $category->children()->count();
            
            // Delete all subcategories
            $category->children()->delete();
            
            // Delete the parent category
            $category->delete();
            
            return redirect()->route('categories.index')
                           ->with('success', "Category '{$category->name}' and {$subcategoryCount} subcategories deleted successfully.");
        }

        // If it's a regular category without children
        $category->delete();

        return redirect()->route('categories.index')
                        ->with('success', "Category '{$category->name}' deleted successfully.");
    }

    /**
     * Handle bulk delete of categories
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $validated['category_ids'])
            ->withCount('products')
            ->with('children')
            ->get();

        $deletedCount = 0;
        $skippedCount = 0;
        $subcategoriesDeleted = 0;

        foreach ($categories as $category) {
            // Skip if category has products
            if ($category->products_count > 0) {
                $skippedCount++;
                continue;
            }

            // Skip if subcategories have products
            $subcategoriesWithProducts = $category->children()
                ->whereHas('products')
                ->count();
            
            if ($subcategoriesWithProducts > 0) {
                $skippedCount++;
                continue;
            }

            // Count subcategories before deletion
            $subcategoryCount = $category->children()->count();
            $subcategoriesDeleted += $subcategoryCount;

            // Delete subcategories and the category
            $category->children()->delete();
            $category->delete();
            $deletedCount++;
        }

        $message = "Deleted {$deletedCount} categories";
        if ($subcategoriesDeleted > 0) {
            $message .= " and {$subcategoriesDeleted} subcategories";
        }
        if ($skippedCount > 0) {
            $message .= ". Skipped {$skippedCount} categories with products.";
        }

        return redirect()->route('categories.index')
                        ->with('success', $message);
    }
}
