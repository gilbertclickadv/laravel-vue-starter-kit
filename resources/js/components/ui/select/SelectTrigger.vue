<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';

defineProps<{
    disabled?: boolean;
    isOpen?: boolean;
}>();

const emit = defineEmits<{
    (e: 'click'): void;
}>();
</script>

<template>
    <button
        type="button"
        role="combobox"
        :disabled="disabled"
        :aria-expanded="isOpen"
        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        @click="emit('click')"
    >
        <slot />
        <ChevronDown class="h-4 w-4 opacity-50" :class="{ 'rotate-180': isOpen }" />
    </button>
</template> 