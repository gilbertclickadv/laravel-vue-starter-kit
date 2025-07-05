<script setup lang="ts">
defineProps<{
    placeholder?: string;
}>();
</script>

<template>
    <span class="block truncate">
        <slot>{{ placeholder }}</slot>
    </span>
</template> 