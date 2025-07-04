<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])
            ->when($request->search, function ($query, $search) {
                return $query->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->rating, function ($query, $rating) {
                return $query->where('rating', $rating);
            })
            ->when($request->product_id, function ($query, $productId) {
                return $query->where('product_id', $productId);
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $reviews = $query->paginate($request->per_page ?? 15);

        return Inertia::render('Reviews/Index', [
            'reviews' => $reviews,
            'products' => Product::all(),
            'users' => User::all(),
            'filters' => $request->only(['search', 'rating', 'product_id', 'user_id', 'date_from', 'date_to', 'sort_by', 'sort_order']),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Reviews/Create', [
            'products' => Product::all(),
            'users' => User::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create($request->all());

        return redirect()->route('reviews.index')
            ->with('success', 'Review created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        $review->load(['user', 'product']);

        return Inertia::render('Reviews/Show', [
            'review' => $review,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        return Inertia::render('Reviews/Edit', [
            'review' => $review,
            'products' => Product::all(),
            'users' => User::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($request->all());

        return redirect()->route('reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    /**
     * Get review statistics.
     */
    private function getStats()
    {
        return [
            'total' => Review::count(),
            'five_star' => Review::where('rating', 5)->count(),
            'four_star' => Review::where('rating', 4)->count(),
            'three_star' => Review::where('rating', 3)->count(),
            'two_star' => Review::where('rating', 2)->count(),
            'one_star' => Review::where('rating', 1)->count(),
            'this_month' => Review::whereMonth('created_at', now()->month)->count(),
            'average_rating' => Review::avg('rating'),
        ];
    }
}
