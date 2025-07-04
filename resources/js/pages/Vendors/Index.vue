<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role?: string;
}

interface Vendor {
    id: number;
    company_name: string;
    store_name: string;
    status: string;
    created_at: string;
    user: User;
    products_count?: number;
}

interface VendorDetails {
    id: number;
    company_name?: string;
    store_name?: string;
    status: string;
    created_at: string;
    updated_at: string;
    user?: User;
    users?: User[];
    products?: Array<{ id: number; name: string; price: number }>;
}

interface VendorStats {
    total: number;
    active: number;
    inactive: number;
    this_month: number;
}

interface PaginatedVendors {
    data: Vendor[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Filters {
    search?: string;
    status?: string;
    sort_by?: string;
    sort_order?: string;
}

const props = defineProps<{
    vendors: PaginatedVendors;
    stats: VendorStats;
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vendors', href: '/vendors' },
];

// Reactive filters
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

// Dialog state
const showVendorDialog = ref(false);
const selectedVendor = ref<VendorDetails | null>(null);

// Simple debounce function
const debounce = (func: Function, wait: number) => {
    let timeout: number;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
};

// Watch for filter changes and update URL
const debouncedSearch = debounce(() => {
    router.get('/vendors', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, statusFilter, sortBy, sortOrder], debouncedSearch);

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
};

const deleteVendor = (vendorId: number) => {
    if (confirm('Are you sure you want to delete this vendor?')) {
        router.delete(`/vendors/${vendorId}`);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

// Updated formatCurrency function - v2.0 to handle all edge cases
const formatCurrency = (amount: any) => {
    try {
        if (amount === null || amount === undefined || amount === '') return '$0.00';
        const numAmount = typeof amount === 'number' ? amount : parseFloat(String(amount));
        return !isNaN(numAmount) && isFinite(numAmount) ? `$${numAmount.toFixed(2)}` : '$0.00';
    } catch (error) {
        console.warn('formatCurrency error:', error, 'amount:', amount);
        return '$0.00';
    }
};

const getStatusColor = (status: string) => {
    return status === 'active' 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

const viewVendor = (vendor: Vendor) => {
    // Use the vendor data that's already available instead of making an API call
    selectedVendor.value = {
        id: vendor.id,
        company_name: vendor.company_name,
        store_name: vendor.store_name,
        status: vendor.status,
        created_at: vendor.created_at,
        updated_at: vendor.created_at, // Using created_at as fallback for updated_at
        user: vendor.user,
        users: [], // Will be empty for index view, but that's fine
        products: [] // Will be empty for index view, but that's fine
    };
    showVendorDialog.value = true;
};

const closeDialog = () => {
    showVendorDialog.value = false;
    selectedVendor.value = null;
};

// Watch for dialog close to clean up selectedVendor
watch(showVendorDialog, (isOpen) => {
    if (!isOpen) {
        selectedVendor.value = null;
    }
});

const removeUserFromVendor = (userId: number) => {
    if (selectedVendor.value && confirm('Are you sure you want to remove this user from the vendor?')) {
        router.delete(route('vendors.users.remove', { vendor: selectedVendor.value.id, user: userId }), {
            onSuccess: () => {
                // Close the dialog and let the page refresh naturally
                closeDialog();
            }
        });
    }
};
</script>

<template>
    <Head title="Vendors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Vendors</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your marketplace vendors and their stores
                    </p>
                </div>
                <Link :href="route('vendors.create')">
                    <Button>
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        Add Vendor
                    </Button>
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Vendors</CardTitle>
                        <Icon name="Users" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Vendors</CardTitle>
                        <Icon name="CheckCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Inactive Vendors</CardTitle>
                        <Icon name="XCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.inactive }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">New This Month</CardTitle>
                        <Icon name="TrendingUp" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.this_month }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                    <CardDescription>Filter and search vendors</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <Input
                                id="search"
                                v-model="search"
                                placeholder="Search vendors..."
                                class="w-full"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                v-model="statusFilter"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="sort_by">Sort By</Label>
                            <select
                                id="sort_by"
                                v-model="sortBy"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="created_at">Date Created</option>
                                <option value="company_name">Company Name</option>
                                <option value="store_name">Store Name</option>
                                <option value="status">Status</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="sort_order">Sort Order</Label>
                            <select
                                id="sort_order"
                                v-model="sortOrder"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="desc">Descending</option>
                                <option value="asc">Ascending</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <Button variant="outline" @click="clearFilters">
                            <Icon name="X" class="mr-2 h-4 w-4" />
                            Clear Filters
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Vendors Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Vendors List</CardTitle>
                    <CardDescription>
                        Showing {{ vendors.from }} to {{ vendors.to }} of {{ vendors.total }} vendors
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Vendor Info
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="vendor in vendors.data" :key="vendor.id" class="hover:bg-muted/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-foreground">
                                                {{ vendor.company_name || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-muted-foreground">
                                                {{ vendor.store_name || 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-foreground">
                                                {{ vendor.user.name }}
                                            </div>
                                            <div class="text-sm text-muted-foreground">
                                                {{ vendor.user.email }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getStatusColor(vendor.status)
                                        ]">
                                            {{ vendor.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                        {{ formatDate(vendor.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            @click="viewVendor(vendor)"
                                        >
                                            <Icon name="Eye" class="h-4 w-4" />
                                        </Button>
                                        <Link :href="route('vendors.edit', vendor.id)">
                                            <Button variant="outline" size="sm">
                                                <Icon name="Edit" class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            @click="deleteVendor(vendor.id)"
                                            class="text-red-600 hover:text-red-900 border-red-300 hover:border-red-500"
                                        >
                                            <Icon name="Trash2" class="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ vendors.from }} to {{ vendors.to }} of {{ vendors.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <Link 
                                v-for="page in Array.from({ length: vendors.last_page }, (_, i) => i + 1)"
                                :key="page"
                                :href="route('vendors.index')"
                                :data="{ ...filters, page }"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md border',
                                    page === vendors.current_page
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : 'bg-background text-foreground border-border hover:bg-muted'
                                ]"
                            >
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Vendor Dialog -->
    <Dialog :open="showVendorDialog" @update:open="(open) => showVendorDialog = open">
        <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Vendor Details</DialogTitle>
            </DialogHeader>
            
            <div v-if="selectedVendor" class="space-y-6">
                <!-- Vendor Information -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Vendor Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Company Name</label>
                                <p class="text-foreground">{{ selectedVendor.company_name || 'N/A' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Store Name</label>
                                <p class="text-foreground">{{ selectedVendor.store_name || 'N/A' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Status</label>
                                <span :class="[
                                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                    selectedVendor.status === 'active' 
                                        ? 'bg-green-100 text-green-800' 
                                        : 'bg-red-100 text-red-800'
                                ]">{{ selectedVendor.status }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Products</label>
                                <p class="text-foreground">{{ selectedVendor.products?.length || 0 }} products</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Users</label>
                                <p class="text-foreground">{{ selectedVendor.users?.length || 0 }} users</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Vendor ID</label>
                                <p class="text-foreground">#{{ selectedVendor.id }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Created</label>
                                <p class="text-foreground">{{ formatDate(selectedVendor.created_at) }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                                <p class="text-foreground">{{ formatDate(selectedVendor.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Vendor Users -->
                <Card>
                    <CardHeader>
                        <CardTitle>Vendor Users</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Owner User -->
                            <div v-if="selectedVendor.user" class="border rounded-lg p-4 bg-blue-50">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-foreground">{{ selectedVendor.user.name }} <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2">Owner</span></p>
                                        <p class="text-sm text-muted-foreground">{{ selectedVendor.user.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick view notice -->
                            <div class="text-sm text-muted-foreground p-4 bg-gray-50 rounded-lg">
                                <p>💡 This is a quick view. For detailed user and product management, click "Edit Vendor" below.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Products -->
                <Card v-if="selectedVendor.products && selectedVendor.products.length > 0">
                    <CardHeader>
                        <CardTitle>Products</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-for="product in selectedVendor.products.slice(0, 10)" :key="product.id" class="flex justify-between items-center py-2 border-b last:border-b-0">
                                <span class="text-foreground">{{ product.name }}</span>
                                <span class="text-muted-foreground">{{ formatCurrency(product.price) }}</span>
                            </div>
                            <p v-if="selectedVendor.products.length > 10" class="text-xs text-muted-foreground mt-2">
                                ...and {{ selectedVendor.products.length - 10 }} more products
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <Link v-if="selectedVendor?.id" :href="route('vendors.edit', selectedVendor.id)">
                    <Button variant="outline">
                        <Icon name="Edit" class="mr-2 h-4 w-4" />
                        Edit Vendor
                    </Button>
                </Link>
                <Button @click="closeDialog">Close</Button>
            </div>
        </DialogContent>
    </Dialog>
</template> 