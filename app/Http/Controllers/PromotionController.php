<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Promotion::query()
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('start_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('end_date', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $promotions = $query->paginate($request->per_page ?? 15);

        return Inertia::render('Promotions/Index', [
            'promotions' => $promotions,
            'filters' => $request->only(['search', 'status', 'type', 'date_from', 'date_to', 'sort_by', 'sort_order']),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Promotions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:promotions',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        Promotion::create($request->all());

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        $promotion->load(['products']);

        return Inertia::render('Promotions/Show', [
            'promotion' => $promotion,
            'stats' => [
                'total_products' => $promotion->products->count(),
                'usage_count' => $promotion->usage_count,
                'remaining_uses' => $promotion->usage_limit ? $promotion->usage_limit - $promotion->usage_count : 'Unlimited',
                'is_active' => $promotion->status === 'active' && now()->between($promotion->start_date, $promotion->end_date),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        return Inertia::render('Promotions/Edit', [
            'promotion' => $promotion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:promotions,code,' . $promotion->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $promotion->update($request->all());

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion deleted successfully.');
    }

    /**
     * Get promotion statistics.
     */
    private function getStats()
    {
        return [
            'total' => Promotion::count(),
            'active' => Promotion::where('status', 'active')->count(),
            'inactive' => Promotion::where('status', 'inactive')->count(),
            'expired' => Promotion::where('end_date', '<', now())->count(),
            'this_month' => Promotion::whereMonth('created_at', now()->month)->count(),
            'percentage_type' => Promotion::where('type', 'percentage')->count(),
            'fixed_type' => Promotion::where('type', 'fixed')->count(),
        ];
    }
}
