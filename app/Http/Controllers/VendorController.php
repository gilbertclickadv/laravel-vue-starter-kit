<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vendor::with(['user', 'products'])
            ->when($request->search, function ($query, $search) {
                return $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('store_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $vendors = $query->paginate($request->per_page ?? 10);

        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order']),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::whereDoesntHave('vendor')->get();
        
        return Inertia::render('Vendors/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:vendors,user_id',
            'company_name' => 'nullable|string|max:255',
            'store_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $vendor = Vendor::create($request->all());

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load([
            'user', 
            'users' => function ($query) {
                $query->select('id', 'name', 'email', 'role', 'vendor_id', 'created_at');
            },
            'products' => function ($query) {
                $query->select('id', 'name', 'price', 'vendor_id');
            }
        ]);

        $vendorData = [
            'vendor' => $vendor,
            'stats' => [
                'total_products' => $vendor->products->count(),
                'active_products' => $vendor->products->where('status', 'active')->count(),
                'total_reviews' => 0, // Simplified since we're not loading reviews
                'average_rating' => 0, // Simplified since we're not loading reviews
                'total_users' => $vendor->users->count(),
            ],
        ];

        // Handle JSON requests for dialog
        if (request()->wantsJson()) {
            return response()->json([
                'props' => $vendorData
            ]);
        }

        return Inertia::render('Vendors/Show', $vendorData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        $users = User::whereDoesntHave('vendor')
            ->orWhere('id', $vendor->user_id)
            ->get();

        return Inertia::render('Vendors/Edit', [
            'vendor' => $vendor,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:vendors,user_id,' . $vendor->id,
            'company_name' => 'nullable|string|max:255',
            'store_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $vendor->update($request->all());

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Add a user to the vendor.
     */
    public function addUser(Request $request, Vendor $vendor)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        // Check if user is already assigned to another vendor
        if ($user->vendor_id && $user->vendor_id !== $vendor->id) {
            return back()->withErrors(['user_id' => 'User is already assigned to another vendor.']);
        }

        // Assign user to vendor
        $user->update(['vendor_id' => $vendor->id]);

        return back()->with('success', 'User added to vendor successfully.');
    }

    /**
     * Remove a user from the vendor.
     */
    public function removeUser(Vendor $vendor, User $user)
    {
        // Check if user belongs to this vendor
        if ($user->vendor_id !== $vendor->id) {
            return back()->withErrors(['error' => 'User does not belong to this vendor.']);
        }

        // Don't allow removing the owner user
        if ($vendor->user_id === $user->id) {
            return back()->withErrors(['error' => 'Cannot remove the owner user from vendor.']);
        }

        // Remove user from vendor
        $user->update(['vendor_id' => null]);

        return back()->with('success', 'User removed from vendor successfully.');
    }

    /**
     * Get available users for assignment to vendor.
     */
    public function getAvailableUsers(Vendor $vendor)
    {
        $availableUsers = User::where(function ($query) use ($vendor) {
            $query->whereNull('vendor_id')
                  ->orWhere('vendor_id', $vendor->id);
        })
        ->where('id', '!=', $vendor->user_id) // Exclude owner user
        ->select('id', 'name', 'email', 'role')
        ->get();

        return response()->json($availableUsers);
    }

    /**
     * Get vendor statistics.
     */
    private function getStats()
    {
        return [
            'total' => Vendor::count(),
            'active' => Vendor::where('status', 'active')->count(),
            'inactive' => Vendor::where('status', 'inactive')->count(),
            'this_month' => Vendor::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
