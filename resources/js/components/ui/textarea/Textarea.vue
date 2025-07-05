<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    modelValue: string;
    id?: string;
    error?: string;
    disabled?: boolean;
    rows?: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const value = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
</script>

<template>
    <textarea
        :id="id"
        v-model="value"
        :rows="rows || 3"
        :disabled="disabled"
        :class="[
            'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
            error ? 'border-destructive' : '',
        ]"
    />
</template> 