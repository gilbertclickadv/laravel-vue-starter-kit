<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.productVariant.product', 'payment', 'shippingAddress'])
            ->when($request->search, function ($query, $search) {
                return $query->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
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
            ->when($request->min_amount, function ($query, $minAmount) {
                return $query->where('total_amount', '>=', $minAmount);
            })
            ->when($request->max_amount, function ($query, $maxAmount) {
                return $query->where('total_amount', '<=', $maxAmount);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $orders = $query->paginate($request->per_page ?? 10);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'users' => User::all(),
            'filters' => $request->only(['search', 'status', 'user_id', 'date_from', 'date_to', 'min_amount', 'max_amount', 'sort_by', 'sort_order']),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Orders/Create', [
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
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $order = Order::create($request->all());

        return redirect()->route('orders.index')
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.productVariant.product', 'payment', 'shippingAddress']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'stats' => [
                'total_items' => $order->items->sum('quantity'),
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment->status ?? 'pending',
                'items_count' => $order->items->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        return Inertia::render('Orders/Edit', [
            'order' => $order,
            'users' => User::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $order->update($request->all());

        return redirect()->route('orders.index')
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Get order statistics.
     */
    private function getStats()
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'this_month' => Order::whereMonth('created_at', now()->month)->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
            'average_order_value' => Order::avg('total_amount'),
        ];
    }
}
