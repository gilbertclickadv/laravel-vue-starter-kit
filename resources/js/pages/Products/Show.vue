<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Product {
    id: number;
    name: string;
    description?: string;
    base_price: number;
    sku?: string;
    status: 'active' | 'inactive';
    product_type: 'simple' | 'variable';
    stock_quantity?: number;
    created_at: string;
    updated_at: string;
    category: { id: number; name: string };
    vendor: { 
        id: number; 
        company_name: string; 
        store_name: string; 
        user: { id: number; name: string };
    };
    images?: Array<{
        id: number;
        image_url: string;
        alt_text?: string;
        is_primary: boolean;
        sort_order: number;
    }>;
    average_rating?: number;
    total_reviews?: number;
    is_variable?: boolean;
    is_simple?: boolean;
    is_in_stock?: boolean;
    total_stock?: number;
}

interface Stats {
    total_variants: number;
    total_stock: number;
    total_reviews: number;
    average_rating: number;
}

defineProps<{
    product: Product;
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/products' },
    { title: 'Product Details', href: '#' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const formatCurrency = (amount: number | null | undefined) => {
    if (amount === null || amount === undefined) return '$0.00';
    const numAmount = typeof amount === 'number' ? amount : parseFloat(String(amount));
    return !isNaN(numAmount) ? `$${numAmount.toFixed(2)}` : '$0.00';
};
const formatRating = (rating: number | null | undefined) => {
    if (rating === null || rating === undefined) return '0.0';
    const numRating = typeof rating === 'number' ? rating : parseFloat(String(rating));
    return !isNaN(numRating) ? numRating.toFixed(1) : '0.0';
};
</script>

<template>
    <Head title="Product Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ product.name }}</h1>
                    <p class="text-sm text-muted-foreground">Product Details</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('products.edit', product.id)">
                        <Button variant="outline">
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Product
                        </Button>
                    </Link>
                    <Link :href="route('products.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Products
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Product Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Name</label>
                            <p class="text-foreground">{{ product.name }}</p>
                        </div>

                        <div v-if="product.sku">
                            <label class="text-sm font-medium text-muted-foreground">SKU</label>
                            <p class="text-foreground">{{ product.sku }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Description</label>
                            <p class="text-foreground">{{ product.description || 'No description provided' }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Price</label>
                            <p class="text-foreground font-bold">{{ formatCurrency(product.base_price) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Product Type</label>
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                product.product_type === 'simple' 
                                    ? 'bg-blue-100 text-blue-800' 
                                    : 'bg-purple-100 text-purple-800'
                            ]">{{ product.product_type === 'simple' ? 'Simple Product' : 'Variable Product' }}</span>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Stock Quantity</label>
                            <p class="text-foreground">{{ product.stock_quantity || 0 }} units</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                product.status === 'active' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]">{{ product.status === 'active' ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Category & Vendor</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Category</label>
                            <p class="text-foreground">{{ product.category.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Vendor</label>
                            <p class="text-foreground">{{ product.vendor.company_name || product.vendor.store_name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Vendor Contact</label>
                            <p class="text-foreground">{{ product.vendor.user.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Product ID</label>
                            <p class="text-foreground">#{{ product.id }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Created</label>
                            <p class="text-foreground">{{ formatDate(product.created_at) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                            <p class="text-foreground">{{ formatDate(product.updated_at) }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Statistics Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Product Statistics</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-foreground">{{ stats.total_variants }}</p>
                        <p class="text-sm text-muted-foreground">Total Variants</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-foreground">{{ stats.total_stock }}</p>
                        <p class="text-sm text-muted-foreground">Total Stock</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-foreground">{{ stats.total_reviews }}</p>
                        <p class="text-sm text-muted-foreground">Total Reviews</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-foreground">{{ formatRating(stats.average_rating) }}</p>
                        <p class="text-sm text-muted-foreground">Average Rating</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Images Card -->
            <Card v-if="product.images && product.images.length > 0">
                <CardHeader>
                    <CardTitle>Product Images</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div v-for="image in product.images.slice(0, 8)" :key="image.id" class="relative">
                            <img 
                                :src="image.image_url" 
                                :alt="image.alt_text || product.name"
                                class="w-full h-32 object-cover rounded-lg border"
                            />
                            <div v-if="image.is_primary" class="absolute top-2 left-2">
                                <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">Primary</span>
                            </div>
                        </div>
                    </div>
                    <p v-if="product.images.length > 8" class="text-sm text-muted-foreground mt-2">
                        ...and {{ product.images.length - 8 }} more images
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 