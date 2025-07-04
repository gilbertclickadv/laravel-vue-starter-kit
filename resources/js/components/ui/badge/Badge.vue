<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'success' | 'warning';
  size?: 'sm' | 'md' | 'lg';
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
  class: '',
});

const badgeClasses = computed(() => {
  const baseClasses = 'inline-flex items-center justify-center rounded-full px-2 py-1 text-xs font-medium transition-colors';
  
  const variantClasses = {
    default: 'bg-primary text-primary-foreground',
    secondary: 'bg-secondary text-secondary-foreground',
    destructive: 'bg-destructive text-destructive-foreground',
    outline: 'border border-input bg-background text-foreground',
    success: 'bg-green-100 text-green-800 border border-green-200',
    warning: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
  };
  
  const sizeClasses = {
    sm: 'text-xs px-1.5 py-0.5',
    md: 'text-xs px-2 py-1',
    lg: 'text-sm px-3 py-1.5',
  };
  
  return [
    baseClasses,
    variantClasses[props.variant],
    sizeClasses[props.size],
    props.class,
  ].join(' ');
});
</script>

<template>
  <div :class="badgeClasses">
    <slot />
  </div>
</template> 