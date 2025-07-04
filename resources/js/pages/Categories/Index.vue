<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import Icon from '@/components/Icon.vue';

interface Category {
    id: number;
    name: string;
    description?: string;
    status: string;
    parent_id?: number;
    parent?: {
        id: number;
        name: string;
    };
    children?: Category[];
    products_count?: number;
    created_at: string;
    updated_at: string;
}

interface CategoryStats {
    total: number;
    active: number;
    inactive: number;
    root_categories: number;
    child_categories: number;
    this_month: number;
}

defineProps<{
    rootCategories: {
        data: Category[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    standaloneSubcategories: Category[];
    stats: CategoryStats;
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/categories' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();

const hasChildren = (category: Category) => category.children && category.children.length > 0;

const getTotalProducts = (category: Category) => {
    let total = category.products_count || 0;
    if (category.children) {
        total += category.children.reduce((sum, child) => sum + (child.products_count || 0), 0);
    }
    return total;
};

// Delete functionality
const deleteDialog = ref(false);
const categoryToDelete = ref<Category | null>(null);
const deleteForm = useForm({});

const confirmDelete = (category: Category) => {
    categoryToDelete.value = category;
    deleteDialog.value = true;
};

const deleteCategory = () => {
    if (categoryToDelete.value) {
        deleteForm.delete(route('categories.destroy', categoryToDelete.value.id), {
            onFinish: () => {
                deleteDialog.value = false;
                categoryToDelete.value = null;
            }
        });
    }
};

// Bulk delete functionality
const selectedCategories = ref<Set<number>>(new Set());
const bulkDeleteDialog = ref(false);
const bulkDeleteForm = useForm({
    category_ids: [] as number[]
});

const toggleCategorySelection = (categoryId: number) => {
    if (selectedCategories.value.has(categoryId)) {
        selectedCategories.value.delete(categoryId);
    } else {
        selectedCategories.value.add(categoryId);
    }
};

const selectAll = () => {
    // This would select all visible categories
    // For now, we'll keep it simple
};

const confirmBulkDelete = () => {
    if (selectedCategories.value.size > 0) {
        bulkDeleteDialog.value = true;
    }
};

const bulkDeleteCategories = () => {
    bulkDeleteForm.category_ids = Array.from(selectedCategories.value);
    bulkDeleteForm.post(route('categories.bulk-destroy'), {
        onFinish: () => {
            bulkDeleteDialog.value = false;
            selectedCategories.value.clear();
        }
    });
};

const getDeleteMessage = (category: Category) => {
    const hasProducts = getTotalProducts(category) > 0;
    const hasSubcategories = hasChildren(category);
    
    if (hasProducts && hasSubcategories) {
        return `This will permanently delete "${category.name}" and all its ${category.children?.length} subcategories. Note: This category and its subcategories have ${getTotalProducts(category)} products that will need to be moved first.`;
    } else if (hasSubcategories) {
        return `This will permanently delete "${category.name}" and all its ${category.children?.length} subcategories. This action cannot be undone.`;
    } else if (hasProducts) {
        return `This will permanently delete "${category.name}". Note: This category has ${category.products_count} products that will need to be moved first.`;
    } else {
        return `This will permanently delete "${category.name}". This action cannot be undone.`;
    }
};
</script>

<template>
    <Head title="Categories" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Categories</h1>
                    <p class="text-sm text-muted-foreground">Manage product categories with hierarchical structure</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button 
                        v-if="selectedCategories.size > 0"
                        variant="destructive" 
                        @click="confirmBulkDelete"
                    >
                        <Icon name="Trash2" class="mr-2 h-4 w-4" />
                        Delete Selected ({{ selectedCategories.size }})
                    </Button>
                    <Link :href="route('categories.create')">
                        <Button>
                            <Icon name="Plus" class="mr-2 h-4 w-4" />
                            Add Category
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Categories</CardTitle>
                        <Icon name="FolderTree" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Root Categories</CardTitle>
                        <Icon name="Folder" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.root_categories }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Subcategories</CardTitle>
                        <Icon name="FileText" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ stats.child_categories }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active</CardTitle>
                        <Icon name="CheckCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Inactive</CardTitle>
                        <Icon name="XCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.inactive }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <Icon name="Calendar" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-indigo-600">{{ stats.this_month }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Accordion Categories -->
            <Card>
                <CardHeader>
                    <CardTitle>Categories Hierarchy</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <!-- Root Categories with Accordion -->
                        <div v-for="category in rootCategories.data" :key="category.id">
                            <Collapsible>
                                <div class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50">
                                    <div class="flex items-center gap-3 flex-1">
                                        <Checkbox 
                                            :checked="selectedCategories.has(category.id)"
                                            @click="toggleCategorySelection(category.id)"
                                        />
                                        <CollapsibleTrigger 
                                            v-if="hasChildren(category)"
                                            class="p-0 border-0 bg-transparent hover:bg-transparent"
                                        >
                                            <Icon name="ChevronRight" class="h-5 w-5 text-muted-foreground transition-transform data-[state=open]:rotate-90" />
                                        </CollapsibleTrigger>
                                        <Icon v-else name="Folder" class="h-5 w-5 text-muted-foreground" />
                                        
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-semibold text-foreground">{{ category.name }}</h3>
                                                <span :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full',
                                                    category.status === 'active' 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-red-100 text-red-800'
                                                ]">{{ category.status }}</span>
                                            </div>
                                            <p class="text-sm text-muted-foreground">
                                                {{ category.description || 'No description' }}
                                            </p>
                                            <div class="flex items-center gap-4 text-xs text-muted-foreground mt-1">
                                                <span>{{ getTotalProducts(category) }} products total</span>
                                                <span v-if="hasChildren(category)">{{ category.children?.length }} subcategories</span>
                                                <span>Created {{ formatDate(category.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Link :href="route('categories.show', category.id)">
                                            <Button variant="outline" size="sm">
                                                <Icon name="Eye" class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="route('categories.edit', category.id)">
                                            <Button variant="outline" size="sm">
                                                <Icon name="Edit" class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button 
                                            variant="destructive" 
                                            size="sm"
                                            @click="confirmDelete(category)"
                                        >
                                            <Icon name="Trash2" class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                
                                <!-- Subcategories -->
                                <CollapsibleContent v-if="hasChildren(category)" class="ml-8 mt-2 space-y-2">
                                    <div 
                                        v-for="child in category.children" 
                                        :key="child.id"
                                        class="flex items-center justify-between p-3 rounded-lg border border-dashed hover:bg-muted/30"
                                    >
                                        <div class="flex items-center gap-3 flex-1">
                                            <Checkbox 
                                                :checked="selectedCategories.has(child.id)"
                                                @click="toggleCategorySelection(child.id)"
                                            />
                                            <Icon name="FileText" class="h-4 w-4 text-muted-foreground" />
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-medium text-foreground">{{ child.name }}</h4>
                                                    <span :class="[
                                                        'px-2 py-1 text-xs font-medium rounded-full',
                                                        child.status === 'active' 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-red-100 text-red-800'
                                                    ]">{{ child.status }}</span>
                                                </div>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ child.description || 'No description' }}
                                                </p>
                                                <div class="flex items-center gap-4 text-xs text-muted-foreground mt-1">
                                                    <span>{{ child.products_count || 0 }} products</span>
                                                    <span>Created {{ formatDate(child.created_at) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Link :href="route('categories.show', child.id)">
                                                <Button variant="outline" size="sm">
                                                    <Icon name="Eye" class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="route('categories.edit', child.id)">
                                                <Button variant="outline" size="sm">
                                                    <Icon name="Edit" class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button 
                                                variant="destructive" 
                                                size="sm"
                                                @click="confirmDelete(child)"
                                            >
                                                <Icon name="Trash2" class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>

                        <!-- Empty State -->
                        <div v-if="rootCategories.data.length === 0" class="text-center py-8">
                            <Icon name="FolderTree" class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold mb-2">No categories found</h3>
                            <p class="text-muted-foreground mb-4">Get started by creating your first category.</p>
                            <Link :href="route('categories.create')">
                                <Button>
                                    <Icon name="Plus" class="mr-2 h-4 w-4" />
                                    Add Category
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="deleteDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Category</DialogTitle>
                    <DialogDescription>
                        {{ categoryToDelete ? getDeleteMessage(categoryToDelete) : '' }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="deleteDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        @click="deleteCategory"
                        :disabled="deleteForm.processing"
                    >
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Bulk Delete Confirmation Dialog -->
        <Dialog v-model:open="bulkDeleteDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Selected Categories</DialogTitle>
                    <DialogDescription>
                        This will permanently delete {{ selectedCategories.size }} selected categories and all their subcategories. 
                        Categories with products will be skipped. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="bulkDeleteDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        @click="bulkDeleteCategories"
                        :disabled="bulkDeleteForm.processing"
                    >
                        {{ bulkDeleteForm.processing ? 'Deleting...' : 'Delete Selected' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
