<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    title?: string;
    description?: string;
    open?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed bottom-4 right-4 z-50 flex w-full max-w-md animate-in slide-in-from-bottom-5"
    >
        <div class="w-full rounded-lg border bg-background p-6 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="grid gap-1">
                    <div v-if="title" class="text-sm font-semibold">
                        {{ title }}
                    </div>
                    <div v-if="description" class="text-sm opacity-90">
                        {{ description }}
                    </div>
                </div>
                <button
                    type="button"
                    class="ml-auto rounded-md p-1 text-foreground/50 opacity-70 transition-opacity hover:opacity-100 focus:opacity-100 focus:outline-none focus:ring-2"
                    @click="isOpen = false"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template> 