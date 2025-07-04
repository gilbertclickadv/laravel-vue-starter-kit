<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Icon from '@/components/Icon.vue';
import axios from 'axios';

interface AttributeValue {
    id: number;
    value: string;
}

interface Attribute {
    id: number;
    name: string;
}

interface VariantAttribute {
    id: number;
    attribute_id: number;
    attribute_value_id: number;
    attribute: Attribute;
    attribute_value: AttributeValue;
}

interface ProductVariant {
    id: number;
    sku: string;
    price_override: number | null;
    stock_quantity: number;
    variant_attributes: VariantAttribute[];
}

interface Props {
    productId: number;
    variants: ProductVariant[];
    basePrice: number;
}

const props = defineProps<Props>();

const localVariants = ref<ProductVariant[]>([...props.variants]);
const isLoading = ref(false);
const error = ref<string | null>(null);

const editableVariants = computed(() => {
    return localVariants.value.map(variant => ({
        ...variant,
        get price_override_edit() {
            return variant.price_override === null ? '' : variant.price_override;
        },
        set price_override_edit(value: string | number) {
            variant.price_override = value === '' || value === null ? null : Number(value);
        }
    }));
});

watch(() => props.variants, (newVariants) => {
    localVariants.value = JSON.parse(JSON.stringify(newVariants));
}, { deep: true, immediate: true });

const getVariantName = (variant: ProductVariant) => {
    return variant.variant_attributes.map(attr => attr.attribute_value.value).join(' / ');
};

const getVariantPrice = (variant: ProductVariant) => {
    return variant.price_override ?? props.basePrice;
};

const handlePriceInput = (event: Event, variant: ProductVariant) => {
    const value = (event.target as HTMLInputElement).value;
    variant.price_override = value === '' ? null : Number(value);
};

const saveVariants = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        const payload = {
            variants: localVariants.value.map(v => ({
                id: v.id,
                sku: v.sku,
                price_override: v.price_override,
                stock_quantity: v.stock_quantity,
            })),
        };
        await axios.put(`/products/${props.productId}/variants`, payload);
        // Optionally, you can emit an event to notify parent about the successful update
    } catch (err) {
        error.value = 'Failed to save variants. Please try again.';
        console.error(err);
    } finally {
        isLoading.value = false;
    }
};

</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Icon name="Box" class="h-5 w-5" />
                    Product Variants
                </div>
                <Button @click="saveVariants" :disabled="isLoading">
                    <Icon v-if="isLoading" name="Loader" class="h-4 w-4 animate-spin mr-2" />
                    {{ isLoading ? 'Saving...' : 'Save Variants' }}
                </Button>
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="error" class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded-md">
                {{ error }}
            </div>
            <div class="space-y-4">
                <div v-if="localVariants.length === 0" class="text-center text-muted-foreground py-8">
                    <p>No variants generated. Add attributes to the product to create variants.</p>
                </div>
                <div v-else class="divide-y divide-border">
                    <!-- Table Header -->
                    <div class="grid grid-cols-12 gap-4 font-medium px-4 py-2 bg-muted/50">
                        <div class="col-span-4">Variant</div>
                        <div class="col-span-3">SKU</div>
                        <div class="col-span-2">Price</div>
                        <div class="col-span-2">Stock</div>
                    </div>
                    <!-- Table Body -->
                    <div v-for="(variant, index) in editableVariants" :key="variant.id" class="grid grid-cols-12 gap-4 items-center px-4 py-3">
                        <div class="col-span-4 font-medium">{{ getVariantName(variant) }}</div>
                        <div class="col-span-3">
                            <Input v-model="variant.sku" type="text" placeholder="Variant SKU" />
                        </div>
                        <div class="col-span-2">
                            <Input v-model="variant.price_override_edit" type="number" :placeholder="basePrice.toString()" step="0.01" />
                        </div>
                        <div class="col-span-2">
                            <Input v-model.number="variant.stock_quantity" type="number" placeholder="0" />
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template> 