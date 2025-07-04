<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Icon from '@/components/Icon.vue';
import { Checkbox } from '@/components/ui/checkbox';

interface ImageFile {
    id?: number;
    file?: File;
    image_url: string;
    alt_text: string;
    sort_order: number;
    is_primary: boolean;
    attribute_combination: AttributeCombination[];
    preview?: string;
}

interface AttributeCombination {
    attribute_id: number;
    attribute_value_id: number;
    attribute_name?: string;
    attribute_value?: string;
}

interface Attribute {
    id: number;
    name: string;
    values: AttributeValue[];
}

interface AttributeValue {
    id: number;
    value: string;
    hex?: string;
}

interface Props {
    modelValue: ImageFile[];
    attributes?: Attribute[];
    label?: string;
    required?: boolean;
    maxImages?: number;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Product Images',
    required: false,
    maxImages: 10,
    attributes: () => [],
});

const emit = defineEmits<{
    'update:modelValue': [value: ImageFile[]];
}>();

const images = ref<ImageFile[]>([...props.modelValue]);
const fileInput = ref<HTMLInputElement>();

// Watch for changes to modelValue prop and update images
watch(() => props.modelValue, (newValue) => {
    images.value = [...newValue];
}, { deep: true, immediate: true });

const canAddMore = computed(() => images.value.length < props.maxImages);

const addImages = (files: FileList | null) => {
    if (!files) return;

    const updatedImages = [...images.value];
    for (let i = 0; i < files.length && updatedImages.length < props.maxImages; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const newImage: ImageFile = {
                    file,
                    image_url: e.target?.result as string || '',
                    preview: e.target?.result as string,
                    alt_text: file.name || '',
                    sort_order: updatedImages.length,
                    is_primary: updatedImages.length === 0,
                    attribute_combination: [],
                };
                
                updatedImages.push(newImage);
                
                // Only emit after all images are processed
                if (i === files.length - 1 || updatedImages.length === props.maxImages) {
                    // Update local state
                    images.value = updatedImages;
                    // Emit the update
                    emit('update:modelValue', updatedImages);
                }
            };
            reader.readAsDataURL(file);
        }
    }
};

const removeImage = (index: number) => {
    const updatedImages = [...images.value];
    updatedImages.splice(index, 1);
    
    // Update sort orders
    updatedImages.forEach((img, idx) => {
        img.sort_order = idx;
    });
    
    // Ensure we have a primary image
    if (!updatedImages.some(img => img.is_primary) && updatedImages.length > 0) {
        updatedImages[0].is_primary = true;
    }
    
    // Update local state
    images.value = updatedImages;
    
    // Emit the update
    emit('update:modelValue', updatedImages);
};

const setPrimary = (index: number) => {
    if (index >= 0 && index < images.value.length) {
        // Create a deep copy of the images array
        const updatedImages = JSON.parse(JSON.stringify(images.value));
        
        // Set all images as non-primary
        updatedImages.forEach((img: ImageFile) => {
            img.is_primary = false;
        });
        
        // Set the selected image as primary
        updatedImages[index].is_primary = true;
        
        // Update local state
        images.value = updatedImages;
        
        // Emit the update to parent
        emit('update:modelValue', updatedImages);
    }
};

const updateImage = (index: number, updatedImage: Partial<ImageFile>) => {
    if (index >= 0 && index < images.value.length) {
        // Create a deep copy of the images array
        const updatedImages: ImageFile[] = JSON.parse(JSON.stringify(images.value));
        
        // If setting a new primary image
        if (updatedImage.is_primary) {
            // Set all other images as non-primary
            updatedImages.forEach((img: ImageFile) => {
                img.is_primary = false;
            });
        }
        
        // Update the specific image with new values
        updatedImages[index] = {
            ...updatedImages[index],
            ...updatedImage,
            // Ensure is_primary is a boolean
            is_primary: updatedImage.is_primary !== undefined ? Boolean(updatedImage.is_primary) : updatedImages[index].is_primary
        };
        
        // If no primary image exists, set the first image as primary
        if (!updatedImages.some((img: ImageFile) => img.is_primary) && updatedImages.length > 0) {
            updatedImages[0].is_primary = true;
        }
        
        // Update the local state
        images.value = updatedImages;
        
        // Emit the update to parent
        emit('update:modelValue', updatedImages);
    }
};

const toggleAttributeCombination = (imageIndex: number, attributeId: number, attributeValueId: number) => {
    // Create a new array to avoid direct mutation
    const updatedImages = [...images.value];
    const image = updatedImages[imageIndex];
    const existingIndex = image.attribute_combination.findIndex(
        combo => combo.attribute_id === attributeId
    );

    if (existingIndex >= 0) {
        // Update existing combination
        image.attribute_combination[existingIndex].attribute_value_id = attributeValueId;
    } else {
        // Add new combination
        image.attribute_combination.push({
            attribute_id: attributeId,
            attribute_value_id: attributeValueId,
        });
    }

    // Update local state
    images.value = updatedImages;
    
    // Emit the new array to update parent component
    emit('update:modelValue', updatedImages);
};

const removeAttributeCombination = (imageIndex: number, attributeId: number) => {
    // Create a new array to avoid direct mutation
    const updatedImages = [...images.value];
    const image = updatedImages[imageIndex];
    image.attribute_combination = image.attribute_combination.filter(
        combo => combo.attribute_id !== attributeId
    );
    
    // Update local state
    images.value = updatedImages;
    
    // Emit the new array to update parent component
    emit('update:modelValue', updatedImages);
};

const getAttributeValueName = (attributeId: number, attributeValueId: number) => {
    const attribute = props.attributes.find(attr => attr.id === attributeId);
    if (!attribute) return '';
    
    const value = attribute.values.find(val => val.id === attributeValueId);
    return value?.value || '';
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const moveImage = (fromIndex: number, toIndex: number) => {
    if (
        fromIndex >= 0 && 
        fromIndex < images.value.length && 
        toIndex >= 0 && 
        toIndex < images.value.length
    ) {
        // Create a deep copy of the images array
        const updatedImages = JSON.parse(JSON.stringify(images.value));
        
        // Remove the image from its current position
        const [movedImage] = updatedImages.splice(fromIndex, 1);
        
        // Insert it at the new position
        updatedImages.splice(toIndex, 0, movedImage);
        
        // Update sort_order for all images
        updatedImages.forEach((img: ImageFile, idx: number) => {
            img.sort_order = idx;
        });
        
        // Update local state
        images.value = updatedImages;
        
        // Emit the update to parent
        emit('update:modelValue', updatedImages);
    }
};
</script>

<template>
    <div class="space-y-4">
        <Label v-if="label" class="text-sm font-medium">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </Label>

        <!-- Upload Area -->
        <div 
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
            @click="triggerFileInput"
            @dragover.prevent
            @drop.prevent="addImages($event.dataTransfer?.files || null)"
        >
            <Icon name="Upload" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <p class="text-sm text-gray-600 mb-2">
                <span class="font-medium">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500">
                PNG, JPG, GIF up to 10MB ({{ images.length }}/{{ maxImages }} images)
            </p>
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                class="hidden"
                @change="addImages(($event.target as HTMLInputElement).files)"
            />
        </div>

        <!-- Image List -->
        <div v-if="images.length > 0" class="space-y-4">
            <div v-for="(image, index) in images" :key="index" 
                 class="border rounded-lg p-4 space-y-4">
                
                <!-- Image Preview -->
                <div class="flex items-start gap-4">
                    <div class="relative">
                        <img 
                            :src="image.preview || image.image_url" 
                            :alt="image.alt_text"
                            class="w-24 h-24 object-cover rounded-lg border"
                        />
                        <div v-if="image.is_primary" 
                             class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                            Primary
                        </div>
                    </div>
                    
                    <div class="flex-1 space-y-3">
                        <!-- Alt Text -->
                        <div>
                            <Label class="text-xs text-muted-foreground">Alt Text</Label>
                            <Input
                                :value="image.alt_text"
                                @input="updateImage(index, { alt_text: ($event.target as HTMLInputElement).value })"
                                placeholder="Describe this image"
                                class="text-sm"
                            />
                        </div>

                        <!-- Primary image toggle -->
                        <div class="flex items-center gap-2 mt-2">
                            <Checkbox
                                :checked="image.is_primary"
                                @update:checked="updateImage(index, { is_primary: $event })"
                                id="is-primary"
                            />
                            <Label for="is-primary">Set as primary image</Label>
                        </div>

                        <!-- Move image up -->
                        <Button
                            v-if="index > 0"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            @click="moveImage(index, index - 1)"
                        >
                            <Icon name="arrow-up" class="h-4 w-4" />
                        </Button>

                        <!-- Move image down -->
                        <Button
                            v-if="index < images.length - 1"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8"
                            @click="moveImage(index, index + 1)"
                        >
                            <Icon name="arrow-down" class="h-4 w-4" />
                        </Button>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <Button 
                                type="button"
                                variant="destructive" 
                                size="sm"
                                @click="removeImage(index)"
                            >
                                <Icon name="Trash2" class="h-4 w-4" />
                                Remove
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Attribute Assignment -->
                <div v-if="attributes.length > 0" class="border-t pt-4">
                    <Label class="text-sm font-medium mb-3">Attribute Assignment</Label>
                    <div class="space-y-3">
                        <div v-for="attribute in attributes" :key="attribute.id" class="flex items-center gap-3">
                            <Label class="text-sm min-w-0 w-20">{{ attribute.name }}:</Label>
                            <select
                                :value="image.attribute_combination.find(combo => combo.attribute_id === attribute.id)?.attribute_value_id || ''"
                                @change="toggleAttributeCombination(index, attribute.id, parseInt(($event.target as HTMLSelectElement).value))"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            >
                                <option value="">Any {{ attribute.name }}</option>
                                <option v-for="value in attribute.values" :key="value.id" :value="value.id">
                                    {{ value.value }}
                                </option>
                            </select>
                            <Button
                                v-if="image.attribute_combination.find(combo => combo.attribute_id === attribute.id)"
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="removeAttributeCombination(index, attribute.id)"
                            >
                                <Icon name="X" class="h-4 w-4" />
                            </Button>
                        </div>

                        <!-- Current Combination Display -->
                        <div v-if="image.attribute_combination.length > 0" 
                             class="mt-2 text-xs text-muted-foreground">
                            <span class="font-medium">This image shows:</span>
                            <span v-for="(combo, comboIndex) in image.attribute_combination" :key="combo.attribute_id">
                                {{ comboIndex > 0 ? ', ' : '' }}
                                {{ props.attributes.find(attr => attr.id === combo.attribute_id)?.name }}:
                                {{ getAttributeValueName(combo.attribute_id, combo.attribute_value_id) }}
                            </span>
                        </div>
                        <div v-else class="mt-2 text-xs text-muted-foreground">
                            <span class="font-medium">General image</span> - shown for all product variations
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add More Button -->
        <Button
            v-if="canAddMore"
            type="button"
            variant="outline"
            @click="triggerFileInput"
            class="w-full"
        >
            <Icon name="Plus" class="mr-2 h-4 w-4" />
            Add More Images
        </Button>
    </div>
</template> 