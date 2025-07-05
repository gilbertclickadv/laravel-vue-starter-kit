<script setup lang="ts">
defineProps<{
    value: string;
    disabled?: boolean;
}>();
</script>

<template>
    <button
        type="button"
        role="tab"
        :disabled="disabled"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm"
    >
        <slot />
    </button>
</template> 