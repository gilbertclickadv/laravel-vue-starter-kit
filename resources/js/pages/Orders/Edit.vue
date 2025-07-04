<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Order {
    id: number;
    user_id: number;
    status: string;
    total_amount: number;
    notes?: string;
    user: { id: number; name: string; email: string };
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    order: Order;
    users: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
    { title: 'Edit Order', href: '#' },
];

const form = useForm({
    user_id: props.order.user_id,
    status: props.order.status,
    total_amount: props.order.total_amount,
    notes: props.order.notes || '',
});

const submit = () => {
    form.put(route('orders.update', props.order.id));
};
</script>

<template>
    <Head title="Edit Order" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Edit Order #{{ order.id }}</h1>
                    <p class="text-sm text-muted-foreground">Update order information</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Order Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="user_id">Customer</Label>
                                <select
                                    id="user_id"
                                    v-model="form.user_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Select a customer</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.user_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="total_amount">Total Amount</Label>
                            <Input
                                id="total_amount"
                                v-model="form.total_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="Enter total amount"
                                required
                            />
                            <InputError :message="form.errors.total_amount" />
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Enter order notes (optional)"
                            ></textarea>
                            <InputError :message="form.errors.notes" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Order' }}
                            </Button>
                            <Button variant="outline" type="button" @click="$inertia.visit(route('orders.show', order.id))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 