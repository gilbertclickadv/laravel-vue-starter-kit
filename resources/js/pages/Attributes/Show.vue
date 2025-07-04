<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface AttributeValue {
    id: number;
    value: string;
    hex?: string;
    sort_order: number;
    created_at: string;
    updated_at: string;
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
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    attribute: Attribute;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attributes', href: '/attributes' },
    { title: props.attribute.name, href: `/attributes/${props.attribute.id}` },
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

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        dropdown: 'Dropdown',
        color: 'Color',
        size: 'Size',
        text: 'Text',
        number: 'Number',
    };
    return labels[type] || 'Unknown';
};

const getContrastColor = (hex: string) => {
    if (!hex) return '#000000';
    
    // Remove # if present
    const cleanHex = hex.replace('#', '');
    
    // Convert to RGB
    const r = parseInt(cleanHex.substr(0, 2), 16);
    const g = parseInt(cleanHex.substr(2, 2), 16);
    const b = parseInt(cleanHex.substr(4, 2), 16);
    
    // Calculate luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    
    return luminance > 0.5 ? '#000000' : '#ffffff';
};
</script>

<template>
    <Head :title="attribute.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <Icon :name="getTypeIcon(attribute.type)" class="h-12 w-12 text-muted-foreground" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <h1 class="text-3xl font-bold text-foreground">{{ attribute.name }}</h1>
                            <span :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getTypeColor(attribute.type)}`">
                                {{ getTypeLabel(attribute.type) }}
                            </span>
                            <span v-if="attribute.is_required" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Required
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ attribute.description || 'No description provided' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('attributes.edit', attribute.id)">
                        <Button>
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Attribute
                        </Button>
                    </Link>
                    <Link :href="route('attributes.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Attributes
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Attribute Details -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Attribute Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Name</label>
                                    <p class="text-sm font-medium">{{ attribute.name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Slug</label>
                                    <p class="text-sm font-mono bg-muted px-2 py-1 rounded">{{ attribute.slug }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Type</label>
                                    <div class="flex items-center gap-2">
                                        <Icon :name="getTypeIcon(attribute.type)" class="h-4 w-4" />
                                        <span class="text-sm">{{ getTypeLabel(attribute.type) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Sort Order</label>
                                    <p class="text-sm">{{ attribute.sort_order }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Required</label>
                                    <span :class="`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${attribute.is_required ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'}`">
                                        {{ attribute.is_required ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Total Values</label>
                                    <p class="text-sm font-medium">{{ attribute.values.length }}</p>
                                </div>
                            </div>

                            <div v-if="attribute.description" class="pt-4 border-t">
                                <label class="text-sm font-medium text-muted-foreground">Description</label>
                                <p class="text-sm mt-1">{{ attribute.description }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Attribute Values -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle>Attribute Values</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="attribute.values.length > 0" class="space-y-3">
                                <!-- Color type values -->
                                <div v-if="attribute.type === 'color'" class="space-y-3">
                                    <div v-for="value in attribute.values" :key="value.id" 
                                         class="flex items-center justify-between p-3 rounded-lg border">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-muted flex items-center justify-center text-xs font-medium">
                                                {{ value.sort_order }}
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ value.value }}</p>
                                                <p class="text-xs text-muted-foreground">
                                                    {{ value.hex || 'No hex value' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="value.hex" 
                                             class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center"
                                             :style="{ backgroundColor: value.hex }">
                                            <div class="w-6 h-6 rounded-full border border-white/20" 
                                                 :style="{ backgroundColor: value.hex }"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Non-color type values -->
                                <div v-else class="space-y-3">
                                    <div v-for="value in attribute.values" :key="value.id" 
                                         class="flex items-center justify-between p-3 rounded-lg border">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-muted flex items-center justify-center text-xs font-medium">
                                                {{ value.sort_order }}
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ value.value }}</p>
                                                <p class="text-xs text-muted-foreground">
                                                    Added {{ formatDate(value.created_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <Icon name="List" class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 class="text-lg font-semibold mb-2">No values found</h3>
                                <p class="text-muted-foreground mb-4">This attribute doesn't have any values yet.</p>
                                <Link :href="route('attributes.edit', attribute.id)">
                                    <Button size="sm">
                                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                                        Add Values
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Color Preview (for color attributes) -->
                    <Card v-if="attribute.type === 'color' && attribute.values.length > 0" class="mt-6">
                        <CardHeader>
                            <CardTitle>Color Preview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-3 gap-2">
                                <div v-for="value in attribute.values.filter(v => v.hex)" :key="value.id" 
                                     class="aspect-square rounded-lg border-2 border-gray-300 flex items-center justify-center text-xs font-medium relative overflow-hidden cursor-pointer"
                                     :style="{ backgroundColor: value.hex, color: getContrastColor(value.hex || '#ffffff') }"
                                     :title="`${value.value} (${value.hex})`">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="bg-black/20 px-2 py-1 rounded text-white text-xs">
                                            {{ value.value }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Metadata -->
            <Card>
                <CardHeader>
                    <CardTitle>Metadata</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-muted-foreground">Created</label>
                            <p class="font-medium">{{ formatDate(attribute.created_at) }}</p>
                        </div>
                        <div>
                            <label class="text-muted-foreground">Last Updated</label>
                            <p class="font-medium">{{ formatDate(attribute.updated_at) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 