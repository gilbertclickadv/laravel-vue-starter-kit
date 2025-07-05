<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Form from './Form.vue'

defineProps<{
  availableTypes: Record<string, string>
}>()
</script>

<template>
  <Form :available-types="availableTypes" />
</template>