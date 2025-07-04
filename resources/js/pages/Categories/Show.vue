<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Category {
    id: number;
    name: string;
    description?: string;
    status: string;
    parent_id?: number;
    parent?: { name: string };
    children?: Array<{ id: number; name: string }>;
    products?: Array<{ id: number; name: string }>;
    created_at: string;
    updated_at: string;
}

defineProps<{
    category: Category;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/categories' },
    { title: 'Category Details', href: '#' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
</script>

<template>
    <Head title="Category Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ category.name }}</h1>
                    <p class="text-sm text-muted-foreground">Category Details</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('categories.edit', category.id)">
                        <Button variant="outline">
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Category
                        </Button>
                    </Link>
                    <Link :href="route('categories.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Categories
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Category Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Name</label>
                            <p class="text-foreground">{{ category.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Description</label>
                            <p class="text-foreground">{{ category.description || 'No description provided' }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                category.status === 'active' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]">{{ category.status }}</span>
                        </div>

                        <div v-if="category.parent">
                            <label class="text-sm font-medium text-muted-foreground">Parent Category</label>
                            <p class="text-foreground">{{ category.parent.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Created</label>
                            <p class="text-foreground">{{ formatDate(category.created_at) }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Related Data</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Products Count</label>
                            <p class="text-foreground">{{ category.products?.length || 0 }} products</p>
                        </div>

                        <div v-if="category.children && category.children.length > 0">
                            <label class="text-sm font-medium text-muted-foreground">Subcategories</label>
                            <div class="space-y-1">
                                <p v-for="child in category.children" :key="child.id" class="text-sm text-foreground">
                                    • {{ child.name }}
                                </p>
                            </div>
                        </div>

                        <div v-if="category.products && category.products.length > 0">
                            <label class="text-sm font-medium text-muted-foreground">Recent Products</label>
                            <div class="space-y-1">
                                <p v-for="product in category.products.slice(0, 5)" :key="product.id" class="text-sm text-foreground">
                                    • {{ product.name }}
                                </p>
                                <p v-if="category.products.length > 5" class="text-xs text-muted-foreground">
                                    ...and {{ category.products.length - 5 }} more
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 