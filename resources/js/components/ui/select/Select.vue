<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    modelValue: any;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
}>();

const value = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const isOpen = ref(false);

const toggleOpen = () => {
    isOpen.value = !isOpen.value;
};

const updateValue = (newValue: any) => {
    console.log('Select: updating value to', newValue);
    emit('update:modelValue', newValue);
    isOpen.value = false;
};
</script>

<template>
    <div class="relative">
        <slot :value="value" :is-open="isOpen" :toggle-open="toggleOpen" :update-value="updateValue" />
    </div>
</template> 