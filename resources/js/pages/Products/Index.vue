<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Icon from '@/components/Icon.vue';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Vendor {
    id: number;
    company_name: string;
    store_name: string;
    user: User;
}

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    description: string;
    base_price: number;
    sku: string;
    status: string;
    created_at: string;
    vendor: Vendor;
    category: Category;
    images_count?: number;
    reviews_count?: number;
    average_rating?: number;
}

interface ProductStats {
    total: number;
    active: number;
    inactive: number;
    this_month: number;
    total_value: number;
    average_price: number;
}

interface PaginatedProducts {
    data: Product[];
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
    vendor_id?: number;
    category_id?: number;
    min_price?: number;
    max_price?: number;
    sort_by?: string;
    sort_order?: string;
}

const props = defineProps<{
    products: PaginatedProducts;
    vendors: Vendor[];
    categories: Category[];
    stats: ProductStats;
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/products' },
];

// Reactive filters
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const vendorFilter = ref(props.filters.vendor_id || '');
const categoryFilter = ref(props.filters.category_id || '');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

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
    router.get('/products', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        vendor_id: vendorFilter.value || undefined,
        category_id: categoryFilter.value || undefined,
        min_price: minPrice.value || undefined,
        max_price: maxPrice.value || undefined,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, statusFilter, vendorFilter, categoryFilter, minPrice, maxPrice, sortBy, sortOrder], debouncedSearch);

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    vendorFilter.value = '';
    categoryFilter.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
};

const deleteDialogRef = ref();
const productToDelete = ref<number | null>(null);
const isDeleting = ref(false);

const deleteProduct = (productId: number) => {
    productToDelete.value = productId;
    deleteDialogRef.value?.open();
};

const handleConfirmDelete = () => {
    if (productToDelete.value) {
        isDeleting.value = true;
        router.delete(`/products/${productToDelete.value}`, {
            onSuccess: () => {
                deleteDialogRef.value?.close();
                productToDelete.value = null;
                isDeleting.value = false;
            },
            onError: () => {
                isDeleting.value = false;
            }
        });
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const getStatusColor = (status: string) => {
    return status === 'active' 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Products</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your marketplace products and inventory
                    </p>
                </div>
                <Link :href="route('products.create')">
                    <Button>
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        Add Product
                    </Button>
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Products</CardTitle>
                        <Icon name="Package" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Products</CardTitle>
                        <Icon name="CheckCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Inactive Products</CardTitle>
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

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Value</CardTitle>
                        <Icon name="DollarSign" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_value) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Average Price</CardTitle>
                        <Icon name="BarChart3" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.average_price) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                    <CardDescription>Filter and search products</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <Input
                                id="search"
                                v-model="search"
                                placeholder="Search products..."
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
                            <Label for="vendor">Vendor</Label>
                            <select
                                id="vendor"
                                v-model="vendorFilter"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Vendors</option>
                                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                                    {{ vendor.store_name || vendor.company_name || vendor.user.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="category">Category</Label>
                            <select
                                id="category"
                                v-model="categoryFilter"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="min_price">Min Price</Label>
                            <Input
                                id="min_price"
                                v-model="minPrice"
                                type="number"
                                placeholder="0.00"
                                class="w-full"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="max_price">Max Price</Label>
                            <Input
                                id="max_price"
                                v-model="maxPrice"
                                type="number"
                                placeholder="1000.00"
                                class="w-full"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="sort_by">Sort By</Label>
                            <select
                                id="sort_by"
                                v-model="sortBy"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="created_at">Date Created</option>
                                <option value="name">Product Name</option>
                                <option value="base_price">Price</option>
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

            <!-- Products Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Products List</CardTitle>
                    <CardDescription>
                        Showing {{ products.from }} to {{ products.to }} of {{ products.total }} products
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Vendor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                        Price
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
                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-muted/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-foreground">
                                                {{ product.name }}
                                            </div>
                                            <div class="text-sm text-muted-foreground">
                                                SKU: {{ product.sku || 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-foreground">
                                            {{ product.vendor.store_name || product.vendor.company_name || product.vendor.user.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-foreground">
                                            {{ product.category.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-foreground">
                                            {{ formatCurrency(product.base_price) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getStatusColor(product.status)
                                        ]">
                                            {{ product.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                        {{ formatDate(product.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <Link :href="route('products.show', product.id)">
                                            <Button variant="outline" size="sm">
                                                <Icon name="Eye" class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="route('products.edit', product.id)">
                                            <Button variant="outline" size="sm">
                                                <Icon name="Edit" class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            @click="deleteProduct(product.id)"
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
                            Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <Link 
                                v-for="page in Array.from({ length: products.last_page }, (_, i) => i + 1)"
                                :key="page"
                                :href="route('products.index')"
                                :data="{ ...filters, page }"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md border',
                                    page === products.current_page
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

    <!-- Add the confirmation dialog -->
    <ConfirmationDialog
        ref="deleteDialogRef"
        title="Delete Product"
        description="Are you sure you want to delete this product? This action cannot be undone."
        confirm-text="Delete"
        cancel-text="Cancel"
        variant="destructive"
        :is-loading="isDeleting"
        @confirm="handleConfirmDelete"
    />
</template> 