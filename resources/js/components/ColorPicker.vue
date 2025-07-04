<script setup lang="ts">
import { ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface ColorValue {
    name: string;
    hex: string;
}

interface Props {
    modelValue: ColorValue[];
    label?: string;
    placeholder?: string;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Colors',
    placeholder: 'Enter color name',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: ColorValue[]];
}>();

const colors = ref<ColorValue[]>([...props.modelValue]);

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
    colors.value = [...newValue];
}, { deep: true });

const addColor = () => {
    colors.value.push({ name: '', hex: '#ff0000' });
    emit('update:modelValue', colors.value);
};

const removeColor = (index: number) => {
    colors.value.splice(index, 1);
    emit('update:modelValue', colors.value);
};

const updateColor = (index: number, field: keyof ColorValue, value: string) => {
    colors.value[index][field] = value;
    emit('update:modelValue', colors.value);
};

const openColorPicker = (index: number) => {
    const colorInput = document.getElementById(`color-picker-${index}`) as HTMLInputElement;
    if (colorInput) {
        colorInput.click();
    }
};

const getContrastColor = (hex: string) => {
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

const isValidHex = (hex: string) => {
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
};

// Initialize with at least one color if empty
if (colors.value.length === 0) {
    addColor();
}
</script>

<template>
    <div class="space-y-4">
        <Label v-if="label" class="text-sm font-medium">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </Label>

        <div class="space-y-3">
            <div v-for="(color, index) in colors" :key="index" 
                 class="flex items-center gap-3 p-4 border rounded-lg bg-card">
                
                <!-- Color Preview & Picker -->
                <div class="flex flex-col items-center gap-2">
                    <div 
                        class="w-12 h-12 rounded-lg border-2 border-gray-300 cursor-pointer relative overflow-hidden shadow-sm hover:shadow-md transition-shadow"
                        :style="{ backgroundColor: color.hex }"
                        @click="openColorPicker(index)"
                        :title="'Click to choose color'"
                    >
                        <div 
                            class="absolute inset-0 flex items-center justify-center"
                            :style="{ color: getContrastColor(color.hex) }"
                        >
                            <Icon name="Palette" class="h-5 w-5" />
                        </div>
                    </div>
                    
                    <Button 
                        type="button"
                        variant="outline" 
                        size="sm"
                        @click="openColorPicker(index)"
                        class="text-xs px-2 py-1 h-6"
                    >
                        Pick Color
                    </Button>
                    
                    <input
                        :id="`color-picker-${index}`"
                        type="color"
                        :value="color.hex"
                        @input="updateColor(index, 'hex', ($event.target as HTMLInputElement).value)"
                        class="absolute opacity-0 pointer-events-none"
                    />
                </div>

                <!-- Color Details -->
                <div class="flex-1 space-y-2">
                    <!-- Color Name Input -->
                    <div>
                        <Label class="text-xs text-muted-foreground mb-1">Color Name</Label>
                        <Input
                            v-model="color.name"
                            @input="emit('update:modelValue', colors)"
                            :placeholder="placeholder"
                            class="w-full"
                        />
                    </div>

                    <!-- Hex Input -->
                    <div>
                        <Label class="text-xs text-muted-foreground mb-1">Hex Code</Label>
                        <Input
                            v-model="color.hex"
                            @input="emit('update:modelValue', colors)"
                            placeholder="#ff0000"
                            class="font-mono text-sm"
                            :class="{ 'border-red-500': !isValidHex(color.hex) }"
                        />
                    </div>
                </div>

                <!-- Remove Button -->
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="removeColor(index)"
                    :disabled="colors.length === 1"
                    class="text-red-600 hover:text-red-700 self-start"
                >
                    <Icon name="Trash2" class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Add Color Button -->
        <Button
            type="button"
            variant="outline"
            size="sm"
            @click="addColor"
            class="w-full"
        >
            <Icon name="Plus" class="mr-2 h-4 w-4" />
            Add Another Color
        </Button>

        <!-- Color Summary -->
        <div v-if="colors.length > 0" class="mt-4">
            <Label class="text-sm font-medium mb-2">Color Preview</Label>
            <div class="flex flex-wrap gap-2">
                <div v-for="(color, index) in colors" :key="index" 
                     class="flex items-center gap-2 px-3 py-2 rounded-full border text-sm shadow-sm"
                     :style="{ backgroundColor: color.hex, color: getContrastColor(color.hex) }">
                    <div class="w-3 h-3 rounded-full border border-white/30" :style="{ backgroundColor: color.hex }"></div>
                    <span class="font-medium">{{ color.name || 'Unnamed' }}</span>
                    <span class="text-xs opacity-75">{{ color.hex }}</span>
                </div>
            </div>
        </div>
    </div>
</template> 