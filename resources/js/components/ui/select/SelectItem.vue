<script setup lang="ts">
import { Check } from 'lucide-vue-next';

const props = defineProps<{
    value: any;
    disabled?: boolean;
    selected?: boolean;
}>();

const emit = defineEmits<{
    (e: 'select', value: any): void;
}>();

const handleSelect = () => {
    if (!props.disabled) {
        console.log('SelectItem: emitting value', props.value);
        emit('select', props.value);
    }
};
</script>

<template>
    <div
        role="option"
        :aria-disabled="disabled"
        :aria-selected="selected"
        class="relative flex w-full cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
        @click="handleSelect"
    >
        <span v-if="selected" class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
            <Check class="h-4 w-4" />
        </span>
        <slot />
    </div>
</template> 