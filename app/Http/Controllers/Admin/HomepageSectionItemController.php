<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\HomepageSectionItem;
use App\Models\Product;
use App\Services\HomepageSectionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomepageSectionItemController extends Controller
{
    protected $sectionService;

    public function __construct(HomepageSectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    public function create(HomepageSection $section)
    {
        $products = [];
        if ($section->type === HomepageSection::TYPE_FEATURED_PRODUCTS) {
            $products = Product::select('id', 'name', 'base_price')
                ->with('primaryImage')
                ->get();
        }

        return Inertia::render('Admin/HomepageSections/Items/Create', [
            'section' => $section,
            'products' => $products,
        ]);
    }

    public function store(Request $request, HomepageSection $section)
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'link_text' => ['nullable', 'string', 'max:255'],
            'additional_data' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];

        if ($section->type === HomepageSection::TYPE_FEATURED_PRODUCTS) {
            $rules['product_id'] = ['required', 'exists:products,id'];
        }

        $validated = $request->validate($rules);

        $this->sectionService->addSectionItem($section, $validated);

        return redirect()
            ->route('admin.homepage-sections.edit', $section)
            ->with('success', 'Item added successfully.');
    }

    public function edit(HomepageSection $section, HomepageSectionItem $item)
    {
        $products = [];
        if ($section->type === HomepageSection::TYPE_FEATURED_PRODUCTS) {
            $products = Product::select('id', 'name', 'base_price')
                ->with('primaryImage')
                ->get();
        }

        return Inertia::render('Admin/HomepageSections/Items/Edit', [
            'section' => $section,
            'item' => $item,
            'products' => $products,
        ]);
    }

    public function update(Request $request, HomepageSection $section, HomepageSectionItem $item)
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'link_text' => ['nullable', 'string', 'max:255'],
            'additional_data' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];

        if ($section->type === HomepageSection::TYPE_FEATURED_PRODUCTS) {
            $rules['product_id'] = ['required', 'exists:products,id'];
        }

        $validated = $request->validate($rules);

        $this->sectionService->updateSectionItem($item, $validated);

        return back()->with('success', 'Item updated successfully.');
    }

    public function destroy(HomepageSection $section, HomepageSectionItem $item)
    {
        $this->sectionService->deleteSectionItem($item);

        return back()->with('success', 'Item deleted successfully.');
    }

    public function reorder(Request $request, HomepageSection $section)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['integer'],
        ]);

        $this->sectionService->reorderItems($section, $validated['items']);

        return back()->with('success', 'Items reordered successfully.');
    }
} 