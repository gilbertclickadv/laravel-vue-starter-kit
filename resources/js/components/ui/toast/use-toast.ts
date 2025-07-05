import { ref } from 'vue';

interface ToastOptions {
    title?: string;
    description?: string;
    duration?: number;
}

const toastState = ref<ToastOptions & { open: boolean }>({
    open: false,
    title: '',
    description: '',
});

let timeoutId: number | undefined;

export function useToast() {
    const toast = (options: ToastOptions) => {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        toastState.value = {
            ...options,
            open: true,
        };

        timeoutId = window.setTimeout(() => {
            toastState.value.open = false;
        }, options.duration || 3000);
    };

    return {
        toast,
        toastState,
    };
}

export type { ToastOptions }; 