<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Props {
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Confirm Action',
    description: 'Are you sure you want to proceed?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'default',
    isLoading: false,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

const isOpen = ref(false);

const open = () => {
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
};

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    close();
    emit('cancel');
};

// Expose methods for parent component
defineExpose({
    open,
    close,
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon v-if="variant === 'destructive'" name="AlertTriangle" class="h-5 w-5 text-red-600" />
                    <Icon v-else name="AlertCircle" class="h-5 w-5 text-blue-600" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex gap-2 sm:space-x-0">
                <Button variant="outline" @click="handleCancel" :disabled="isLoading">
                    {{ cancelText }}
                </Button>
                <Button 
                    :variant="variant === 'destructive' ? 'destructive' : 'default'" 
                    @click="handleConfirm"
                    :disabled="isLoading"
                >
                    <Icon v-if="isLoading" name="Loader2" class="mr-2 h-4 w-4 animate-spin" />
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template> 