<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import { ref } from 'vue';

interface AttributeValue {
    id: number;
    value: string;
    sort_order: number;
}

interface Attribute {
    id: number;
    name: string;
    slug: string;
    description?: string;
    type: string;
    is_required: boolean;
    sort_order: number;
    values: AttributeValue[];
    values_count: number;
    products_count: number;
    created_at: string;
    updated_at: string;
}

interface AttributeStats {
    total: number;
    with_values: number;
    required: number;
    dropdown_type: number;
    color_type: number;
    this_month: number;
}

defineProps<{
    attributes: {
        data: Attribute[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: AttributeStats;
    filters: Record<string, any>;
    attributeTypes: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attributes', href: '/attributes' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        dropdown: 'bg-blue-100 text-blue-800',
        color: 'bg-purple-100 text-purple-800',
        size: 'bg-green-100 text-green-800',
        text: 'bg-gray-100 text-gray-800',
        number: 'bg-orange-100 text-orange-800',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const getTypeIcon = (type: string) => {
    const icons: Record<string, string> = {
        dropdown: 'ChevronDown',
        color: 'Palette',
        size: 'Ruler',
        text: 'Type',
        number: 'Hash',
    };
    return icons[type] || 'Settings';
};

// Delete functionality
const deleteForm = useForm({});
const deletingAttribute = ref<Attribute | null>(null);
const confirmDialog = ref<InstanceType<typeof ConfirmationDialog>>();

const confirmDelete = (attribute: Attribute) => {
    deletingAttribute.value = attribute;
    confirmDialog.value?.open();
};

const handleDeleteConfirm = () => {
    if (deletingAttribute.value) {
        deleteForm.delete(route('attributes.destroy', deletingAttribute.value.id), {
            onSuccess: () => {
                deletingAttribute.value = null;
                confirmDialog.value?.close();
            },
            onError: () => {
                deletingAttribute.value = null;
                confirmDialog.value?.close();
            }
        });
    }
};

const handleDeleteCancel = () => {
    deletingAttribute.value = null;
};
</script>

<template>
    <Head title="Attributes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Product Attributes</h1>
                    <p class="text-sm text-muted-foreground">Manage product attributes and their values for variable products</p>
                </div>
                <Link :href="route('attributes.create')">
                    <Button>
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        Add Attribute
                    </Button>
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Attributes</CardTitle>
                        <Icon name="Settings" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">With Values</CardTitle>
                        <Icon name="List" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.with_values }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Required</CardTitle>
                        <Icon name="AlertCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.required }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Dropdown Type</CardTitle>
                        <Icon name="ChevronDown" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.dropdown_type }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Color Type</CardTitle>
                        <Icon name="Palette" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ stats.color_type }}</div>
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

            <!-- Attributes List -->
            <Card>
                <CardHeader>
                    <CardTitle>Attributes Management</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="attribute in attributes.data" :key="attribute.id" 
                             class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex-shrink-0">
                                    <Icon :name="getTypeIcon(attribute.type)" class="h-8 w-8 text-muted-foreground" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-semibold text-foreground truncate">{{ attribute.name }}</h3>
                                        <span :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getTypeColor(attribute.type)}`">
                                            {{ attributeTypes[attribute.type] }}
                                        </span>
                                        <span v-if="attribute.is_required" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Required
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-2">
                                        {{ attribute.description || 'No description provided' }}
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>{{ attribute.values_count }} values</span>
                                        <span>{{ attribute.products_count }} products</span>
                                        <span>Sort: {{ attribute.sort_order }}</span>
                                        <span>Created {{ formatDate(attribute.created_at) }}</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <span v-for="value in attribute.values.slice(0, 3)" :key="value.id" 
                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border">
                                            {{ value.value }}
                                        </span>
                                        <span v-if="attribute.values.length > 3" 
                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border">
                                            +{{ attribute.values.length - 3 }} more
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('attributes.show', attribute.id)">
                                    <Button variant="outline" size="sm">
                                        <Icon name="Eye" class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Link :href="route('attributes.edit', attribute.id)">
                                    <Button variant="outline" size="sm">
                                        <Icon name="Edit" class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Button 
                                    variant="destructive" 
                                    size="sm"
                                    @click="confirmDelete(attribute)"
                                    :disabled="deleteForm.processing"
                                >
                                    <Icon v-if="deleteForm.processing && deletingAttribute?.id === attribute.id" 
                                          name="Loader2" class="h-4 w-4 animate-spin" />
                                    <Icon v-else name="Trash2" class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="attributes.data.length === 0" class="text-center py-8">
                            <Icon name="Settings" class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold mb-2">No attributes found</h3>
                            <p class="text-muted-foreground mb-4">Create your first product attribute to get started with variable products.</p>
                            <Link :href="route('attributes.create')">
                                <Button>
                                    <Icon name="Plus" class="mr-2 h-4 w-4" />
                                    Add Attribute
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Confirmation Dialog -->
        <ConfirmationDialog
            ref="confirmDialog"
            :title="deletingAttribute ? `Delete ${deletingAttribute.name}` : 'Delete Attribute'"
            :description="deletingAttribute ? `Are you sure you want to delete the '${deletingAttribute.name}' attribute? This action cannot be undone and will remove all associated values.` : 'Are you sure you want to delete this attribute?'"
            confirm-text="Delete"
            cancel-text="Cancel"
            variant="destructive"
            :is-loading="deleteForm.processing"
            @confirm="handleDeleteConfirm"
            @cancel="handleDeleteCancel"
        />
    </AppLayout>
</template> 