<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\HomepageSectionItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HomepageSectionController extends Controller
{
    private array $availableTypes = [
        'carousel' => 'Image Carousel',
        'featured_products' => 'Featured Products',
        'categories' => 'Category Showcase',
        'banner' => 'Banner',
        'promotions' => 'Promotions',
    ];

    public function index()
    {
        $sections = HomepageSection::with('items')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/HomepageSections/Index', [
            'sections' => $sections,
            'availableTypes' => $this->availableTypes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/HomepageSections/Create', [
            'availableTypes' => $this->availableTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:' . implode(',', array_keys($this->availableTypes)),
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        HomepageSection::create($validated);

        return redirect()->route('admin.homepage-sections.index')
            ->with('success', 'Homepage section created successfully.');
    }

    public function edit(HomepageSection $homepageSection)
    {
        return Inertia::render('Admin/HomepageSections/Edit', [
            'section' => $homepageSection->load('items'),
            'availableTypes' => $this->availableTypes,
        ]);
    }

    public function update(Request $request, HomepageSection $homepageSection)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:' . implode(',', array_keys($this->availableTypes)),
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        $homepageSection->update($validated);

        return redirect()->route('admin.homepage-sections.index')
            ->with('success', 'Homepage section updated successfully.');
    }

    public function destroy(HomepageSection $homepageSection)
    {
        $homepageSection->delete();

        return redirect()->route('admin.homepage-sections.index')
            ->with('success', 'Homepage section deleted successfully.');
    }

    private function handleImageUpload($image)
    {
        if (str_starts_with($image, 'data:image')) {
            // Handle base64 image
            $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            $image = substr($image, strpos($image, ',') + 1);
            $imageName = 'homepage-sections/' . uniqid() . '.' . $extension;
            Storage::disk('public')->put($imageName, base64_decode($image));
            return $imageName;
        } elseif ($image instanceof \Illuminate\Http\UploadedFile) {
            // Handle file upload
            $path = $image->store('homepage-sections', 'public');
            return $path;
        }

        return null;
    }
} 