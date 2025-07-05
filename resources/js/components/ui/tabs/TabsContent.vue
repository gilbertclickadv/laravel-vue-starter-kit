<script setup lang="ts">
defineProps<{
    value: string;
}>();
</script>

<template>
    <div
        role="tabpanel"
        class="mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
    >
        <slot />
    </div>
</template> 