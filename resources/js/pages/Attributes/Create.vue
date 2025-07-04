<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, computed } from 'vue';
import Icon from '@/components/Icon.vue';
import ColorPicker from '@/components/ColorPicker.vue';

interface AttributeValue {
    value: string;
    hex?: string;
    sort_order: number;
}

interface ColorValue {
    name: string;
    hex: string;
}

defineProps<{
    attributeTypes: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attributes', href: '/attributes' },
    { title: 'Create', href: '/attributes/create' },
];

const form = useForm({
    name: '',
    description: '',
    type: 'dropdown',
    is_required: false,
    sort_order: 0,
    values: [] as any[],
});

const newValue = ref('');
const colorValues = ref<ColorValue[]>([]);

const isColorType = computed(() => form.type === 'color');

const addValue = () => {
    if (newValue.value.trim()) {
        (form.values as AttributeValue[]).push({
            value: newValue.value.trim(),
            sort_order: form.values.length,
        });
        newValue.value = '';
    }
};

const removeValue = (index: number) => {
    (form.values as AttributeValue[]).splice(index, 1);
    (form.values as AttributeValue[]).forEach((value, idx) => {
        value.sort_order = idx;
    });
};

const updateColorValues = (colors: ColorValue[]) => {
    colorValues.value = colors;
    // Update form values from color picker
    form.values = colors.map((color, index) => ({
        value: color.name,
        hex: color.hex,
        sort_order: index,
    }));
};

const submit = () => {
    form.post(route('attributes.store'));
};
</script>

<template>
    <Head title="Create Attribute" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Create Attribute</h1>
                    <p class="text-sm text-muted-foreground">Create a new product attribute for variable products</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Attribute Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Color, Size, Material"
                                required
                            />
                            <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Optional description for this attribute"
                            />
                        </div>

                        <div>
                            <Label for="type">Type *</Label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            >
                                <option v-for="(label, value) in attributeTypes" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_required"
                                    v-model="form.is_required"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <Label for="is_required">Required attribute</Label>
                            </div>

                            <div class="flex items-center gap-2">
                                <Label for="sort_order">Sort Order:</Label>
                                <Input
                                    id="sort_order"
                                    v-model="form.sort_order"
                                    type="number"
                                    min="0"
                                    class="w-20"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Attribute Values</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Color picker for color type attributes -->
                        <div v-if="isColorType">
                            <ColorPicker
                                v-model="colorValues"
                                label="Color Options"
                                placeholder="Enter color name (e.g., Red, Blue)"
                                @update:model-value="updateColorValues"
                            />
                        </div>

                        <!-- Regular value input for non-color attributes -->
                        <div v-else>
                            <div class="flex gap-2">
                                <Input
                                    v-model="newValue"
                                    type="text"
                                    placeholder="Enter attribute value"
                                    @keydown.enter.prevent="addValue"
                                />
                                <Button type="button" @click="addValue">
                                    <Icon name="Plus" class="h-4 w-4" />
                                </Button>
                            </div>

                            <div v-if="form.values.length > 0" class="space-y-2">
                                <div v-for="(value, index) in form.values as AttributeValue[]" :key="index" 
                                     class="flex items-center gap-2 p-3 bg-muted rounded-lg">
                                    <div class="flex-1">
                                        <Input
                                            v-model="value.value"
                                            type="text"
                                            placeholder="Value"
                                        />
                                    </div>
                                    <Button 
                                        type="button" 
                                        variant="destructive" 
                                        size="sm"
                                        @click="removeValue(index)"
                                    >
                                        <Icon name="Trash2" class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <p v-if="form.values.length === 0" class="text-sm text-muted-foreground text-center py-4">
                                No values added yet. Add values above to create options for this attribute.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="$inertia.visit(route('attributes.index'))"
                    >
                        Cancel
                    </Button>
                    <Button 
                        type="submit" 
                        :disabled="form.processing"
                        :class="{ 'opacity-50': form.processing }"
                    >
                        <Icon v-if="form.processing" name="Loader2" class="mr-2 h-4 w-4 animate-spin" />
                        Create Attribute
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 