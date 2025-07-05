<template>
  <Form
    :section="section"
    :available-types="availableTypes"
  />
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Form from './Form.vue'

defineProps<{
  section: {
    id: number
    title: string
    description?: string
    type: string
    is_active: boolean
    sort_order: number
  }
  availableTypes: Record<string, string>
}>()
</script> 