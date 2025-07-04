<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Icon from '@/components/Icon.vue';

interface DashboardStats {
    revenue?: number;
    orders?: number;
    products?: number;
    vendors?: number;
    customers?: number;
    reviews?: number;
    promotions?: number;
}

const props = defineProps<{
    stats?: DashboardStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatNumber = (num: number | undefined): string => {
    return new Intl.NumberFormat().format(num || 0);
};

const formatCurrency = (num: number | undefined): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(num || 0);
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="border-b border-border pb-5">
                <h3 class="text-2xl font-semibold leading-6 text-foreground">
                    Dashboard Overview
                </h3>
                <p class="mt-2 max-w-4xl text-sm text-muted-foreground">
                    Welcome to your multivendor ecommerce platform. Here's an overview of your business metrics.
                </p>
            </div>

            <!-- Main Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Revenue -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <Icon name="DollarSign" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats?.revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Link :href="route('orders.index')" class="hover:underline">
                                View orders
                            </Link>
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Orders -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Orders</CardTitle>
                        <Icon name="ShoppingCart" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatNumber(stats?.orders) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Link :href="route('orders.index')" class="hover:underline">
                                Manage orders
                            </Link>
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Products -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Products</CardTitle>
                        <Icon name="Package" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatNumber(stats?.products) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Link :href="route('products.index')" class="hover:underline">
                                View products
                            </Link>
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Vendors -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Vendors</CardTitle>
                        <Icon name="Users" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatNumber(stats?.vendors) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <Link :href="route('vendors.index')" class="hover:underline">
                                Manage vendors
                            </Link>
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Secondary Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Customer Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle>Customer Overview</CardTitle>
                        <CardDescription>Total registered customers</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ formatNumber(stats?.customers) }}</div>
                    </CardContent>
                </Card>

                <!-- Review Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle>Reviews & Ratings</CardTitle>
                        <CardDescription>Customer feedback and ratings</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ formatNumber(stats?.reviews) }}</div>
                    </CardContent>
                </Card>

                <!-- Promotion Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle>Active Promotions</CardTitle>
                        <CardDescription>Currently running promotions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ formatNumber(stats?.promotions) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Common management tasks</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Link 
                            :href="route('products.create')" 
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <Icon name="Plus" class="mr-2 h-4 w-4" />
                            Add Product
                        </Link>
                        
                        <Link 
                            :href="route('vendors.create')" 
                            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <Icon name="UserPlus" class="mr-2 h-4 w-4" />
                            Add Vendor
                        </Link>
                        
                        <Link 
                            :href="route('categories.create')" 
                            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <Icon name="Tag" class="mr-2 h-4 w-4" />
                            Add Category
                        </Link>
                        
                        <Link 
                            :href="route('promotions.create')" 
                            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <Icon name="Percent" class="mr-2 h-4 w-4" />
                            Add Promotion
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
