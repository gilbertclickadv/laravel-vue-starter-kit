<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attribute::with(['values'])->withCount(['values']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Apply type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if ($sortBy === 'sort_order') {
            $query->ordered();
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Get paginated results
        $attributes = $query->paginate(15);

        // Add products count manually for each attribute
        foreach ($attributes as $attribute) {
            $attribute->products_count = 0; // For now, set to 0 until we implement variants properly
        }

        // Calculate statistics
        $stats = [
            'total' => Attribute::count(),
            'with_values' => Attribute::has('values')->count(),
            'required' => Attribute::where('is_required', true)->count(),
            'dropdown_type' => Attribute::where('type', 'dropdown')->count(),
            'color_type' => Attribute::where('type', 'color')->count(),
            'this_month' => Attribute::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->count(),
        ];

        return Inertia::render('Attributes/Index', [
            'attributes' => $attributes,
            'stats' => $stats,
            'filters' => $request->only(['search', 'type', 'sort_by', 'sort_order']),
            'attributeTypes' => [
                'dropdown' => 'Dropdown',
                'color' => 'Color',
                'size' => 'Size',
                'text' => 'Text',
                'number' => 'Number',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Attributes/Create', [
            'attributeTypes' => [
                'dropdown' => 'Dropdown',
                'color' => 'Color',
                'size' => 'Size',
                'text' => 'Text',
                'number' => 'Number',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes',
            'description' => 'nullable|string',
            'type' => 'required|in:dropdown,color,size,text,number',
            'is_required' => 'boolean',
            'sort_order' => 'integer|min:0',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:255',
            'values.*.hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'values.*.sort_order' => 'integer|min:0',
        ]);

        $attribute = Attribute::create($validated);

        // Create attribute values if provided
        if (isset($validated['values']) && is_array($validated['values'])) {
            foreach ($validated['values'] as $index => $valueData) {
                $attribute->values()->create([
                    'value' => $valueData['value'],
                    'hex' => $valueData['hex'] ?? null,
                    'sort_order' => $valueData['sort_order'] ?? $index,
                ]);
            }
        }

        return redirect()->route('attributes.index')
                        ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $attribute->load(['values' => function ($query) {
            $query->ordered();
        }]);

        return Inertia::render('Attributes/Show', [
            'attribute' => $attribute,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attribute $attribute)
    {
        $attribute->load(['values' => function ($query) {
            $query->ordered();
        }]);

        return Inertia::render('Attributes/Edit', [
            'attribute' => $attribute,
            'attributeTypes' => [
                'dropdown' => 'Dropdown',
                'color' => 'Color',
                'size' => 'Size',
                'text' => 'Text',
                'number' => 'Number',
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            'description' => 'nullable|string',
            'type' => 'required|in:dropdown,color,size,text,number',
            'is_required' => 'boolean',
            'sort_order' => 'integer|min:0',
            'values' => 'nullable|array',
            'values.*.id' => 'nullable|exists:attribute_values,id',
            'values.*.value' => 'required|string|max:255',
            'values.*.hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'values.*.sort_order' => 'integer|min:0',
        ]);

        $attribute->update($validated);

        // Handle attribute values update
        if (isset($validated['values']) && is_array($validated['values'])) {
            $existingValueIds = [];
            
            foreach ($validated['values'] as $index => $valueData) {
                if (isset($valueData['id']) && $valueData['id']) {
                    // Update existing value
                    $value = $attribute->values()->find($valueData['id']);
                    if ($value) {
                        $value->update([
                            'value' => $valueData['value'],
                            'hex' => $valueData['hex'] ?? null,
                            'sort_order' => $valueData['sort_order'] ?? $index,
                        ]);
                        $existingValueIds[] = $value->id;
                    }
                } else {
                    // Create new value
                    $newValue = $attribute->values()->create([
                        'value' => $valueData['value'],
                        'hex' => $valueData['hex'] ?? null,
                        'sort_order' => $valueData['sort_order'] ?? $index,
                    ]);
                    $existingValueIds[] = $newValue->id;
                }
            }
            
            // Delete values that were removed
            $attribute->values()->whereNotIn('id', $existingValueIds)->delete();
        } else {
            // If no values provided, delete all existing values
            $attribute->values()->delete();
        }

        return redirect()->route('attributes.index')
                        ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        // Check if attribute is being used by any products
        if ($attribute->products()->count() > 0) {
            return redirect()->route('attributes.index')
                           ->with('error', 'Cannot delete attribute that is being used by products.');
        }

        // Delete all attribute values first
        $attribute->values()->delete();
        
        // Delete the attribute
        $attribute->delete();

        return redirect()->route('attributes.index')
                        ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Get attributes for API/AJAX calls
     */
    public function getAttributesForProducts(Request $request)
    {
        $attributes = Attribute::with(['values' => function ($query) {
            $query->ordered();
        }])->ordered()->get();

        return response()->json($attributes);
    }
}
