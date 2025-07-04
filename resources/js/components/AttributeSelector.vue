<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface AttributeValue {
    id: number;
    value: string;
    hex?: string;
    sort_order: number;
}

interface Attribute {
    id: number;
    name: string;
    type: string;
    is_required: boolean;
    values: AttributeValue[];
}

interface SelectedAttribute {
    attribute_id: number;
    attribute_value_ids: number[];
}

interface Props {
    modelValue: SelectedAttribute[];
    attributes: Attribute[];
    label?: string;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Product Attributes',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: SelectedAttribute[]];
}>();

const selectedAttributes = ref<SelectedAttribute[]>([...props.modelValue]);

// Watch for changes to modelValue prop and update selectedAttributes
watch(() => props.modelValue, (newValue) => {
    selectedAttributes.value = JSON.parse(JSON.stringify(newValue || []));
}, { deep: true, immediate: true });

// When attributes change, make sure we're not referencing any attributes that no longer exist
watch(() => props.attributes, () => {
    // Filter out any attributes that no longer exist in the available attributes
    selectedAttributes.value = selectedAttributes.value.filter(attr => 
        props.attributes.some(a => a.id === attr.attribute_id)
    );
    emit('update:modelValue', JSON.parse(JSON.stringify(selectedAttributes.value)));
}, { deep: true });

const availableAttributes = computed(() => {
    return props.attributes.filter(attr => 
        !selectedAttributes.value.some(selected => selected.attribute_id === attr.id)
    );
});

const addAttribute = (attributeId: number) => {
    if (!selectedAttributes.value.some(attr => attr.attribute_id === attributeId)) {
        selectedAttributes.value.push({
            attribute_id: attributeId,
            attribute_value_ids: [],
        });
        emit('update:modelValue', JSON.parse(JSON.stringify(selectedAttributes.value)));
    }
};

const removeAttribute = (attributeId: number) => {
    const index = selectedAttributes.value.findIndex(attr => attr.attribute_id === attributeId);
    if (index >= 0) {
        selectedAttributes.value.splice(index, 1);
        emit('update:modelValue', JSON.parse(JSON.stringify(selectedAttributes.value)));
    }
};

const toggleAttributeValue = (attributeId: number, valueId: number) => {
    const attribute = selectedAttributes.value.find(attr => attr.attribute_id === attributeId);
    if (attribute) {
        const valueIndex = attribute.attribute_value_ids.indexOf(valueId);
        if (valueIndex >= 0) {
            attribute.attribute_value_ids.splice(valueIndex, 1);
        } else {
            attribute.attribute_value_ids.push(valueId);
        }
        emit('update:modelValue', JSON.parse(JSON.stringify(selectedAttributes.value)));
    }
};

const getAttributeById = (attributeId: number) => {
    return props.attributes.find(attr => attr.id === attributeId);
};

const getSelectedValueNames = (attributeId: number) => {
    const attribute = getAttributeById(attributeId);
    const selectedAttr = selectedAttributes.value.find(attr => attr.attribute_id === attributeId);
    
    if (!attribute || !selectedAttr) return [];
    
    return selectedAttr.attribute_value_ids.map(valueId => {
        const value = attribute.values.find(val => val.id === valueId);
        return value?.value || '';
    }).filter(Boolean);
};

const getContrastColor = (hex: string) => {
    if (!hex) return '#000000';
    
    const cleanHex = hex.replace('#', '');
    const r = parseInt(cleanHex.substr(0, 2), 16);
    const g = parseInt(cleanHex.substr(2, 2), 16);
    const b = parseInt(cleanHex.substr(4, 2), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    
    return luminance > 0.5 ? '#000000' : '#ffffff';
};
</script>

<template>
    <div class="space-y-4">
        <Label v-if="label" class="text-sm font-medium">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </Label>

        <!-- Selected Attributes -->
        <div v-if="selectedAttributes.length > 0" class="space-y-4">
            <div v-for="selectedAttr in selectedAttributes" :key="selectedAttr.attribute_id"
                 class="border rounded-lg p-4 space-y-3">
                
                <div class="flex items-center justify-between">
                    <h4 class="font-medium">{{ getAttributeById(selectedAttr.attribute_id)?.name }}</h4>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="removeAttribute(selectedAttr.attribute_id)"
                    >
                        <Icon name="X" class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Attribute Values -->
                <div class="space-y-2">
                    <Label class="text-xs text-muted-foreground">Select Values:</Label>
                    
                    <div v-if="getAttributeById(selectedAttr.attribute_id)?.type === 'color'" 
                         class="flex flex-wrap gap-2">
                        <div v-for="value in getAttributeById(selectedAttr.attribute_id)?.values" 
                             :key="value.id"
                             class="cursor-pointer"
                             @click="toggleAttributeValue(selectedAttr.attribute_id, value.id)">
                            
                            <div 
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border transition-all"
                                :class="selectedAttr.attribute_value_ids.includes(value.id) 
                                    ? 'border-blue-500 bg-blue-50 shadow-sm' 
                                    : 'border-gray-300 hover:border-gray-400'"
                            >
                                <div v-if="value.hex" 
                                     class="w-5 h-5 rounded-full border border-gray-300"
                                     :style="{ backgroundColor: value.hex }">
                                </div>
                                <span class="text-sm font-medium">{{ value.value }}</span>
                                <Icon v-if="selectedAttr.attribute_value_ids.includes(value.id)" 
                                      name="Check" class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <!-- Non-color attributes -->
                    <div v-else class="flex flex-wrap gap-2">
                        <div v-for="value in getAttributeById(selectedAttr.attribute_id)?.values" 
                             :key="value.id"
                             class="cursor-pointer"
                             @click="toggleAttributeValue(selectedAttr.attribute_id, value.id)">
                            
                            <div 
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border transition-all"
                                :class="selectedAttr.attribute_value_ids.includes(value.id) 
                                    ? 'border-blue-500 bg-blue-50 shadow-sm' 
                                    : 'border-gray-300 hover:border-gray-400'"
                            >
                                <span class="text-sm font-medium">{{ value.value }}</span>
                                <Icon v-if="selectedAttr.attribute_value_ids.includes(value.id)" 
                                      name="Check" class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <!-- Selected values summary -->
                    <div v-if="selectedAttr.attribute_value_ids.length > 0" 
                         class="text-xs text-muted-foreground">
                        Selected: {{ getSelectedValueNames(selectedAttr.attribute_id).join(', ') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Attribute -->
        <div v-if="availableAttributes.length > 0" class="space-y-2">
            <Label class="text-sm font-medium">Add Attributes:</Label>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-for="attribute in availableAttributes"
                    :key="attribute.id"
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addAttribute(attribute.id)"
                    class="flex items-center gap-2"
                >
                    <Icon :name="attribute.type === 'color' ? 'Palette' : 'Tag'" class="h-4 w-4" />
                    {{ attribute.name }}
                    <span v-if="attribute.is_required" class="text-red-500 text-xs">*</span>
                </Button>
            </div>
        </div>

        <!-- No attributes message -->
        <div v-if="props.attributes.length === 0" class="text-center py-8 text-muted-foreground">
            <Icon name="Tag" class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p class="text-sm">No attributes available.</p>
            <p class="text-xs">Create attributes first to use them with products.</p>
        </div>

        <!-- All attributes selected -->
        <div v-else-if="availableAttributes.length === 0 && selectedAttributes.length > 0" 
             class="text-center py-4 text-muted-foreground">
            <Icon name="CheckCircle" class="h-8 w-8 mx-auto mb-2 text-green-500" />
            <p class="text-sm">All available attributes have been added.</p>
        </div>
    </div>
</template> 