<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Order {
    id: number;
    user: { id: number; name: string; email: string };
    status: string;
    total_amount: number;
    notes?: string;
    created_at: string;
    updated_at: string;
}

defineProps<{
    order: Order;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
    { title: 'Order Details', href: '#' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const formatCurrency = (amount: number) => `$${amount.toFixed(2)}`;

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'processing': return 'bg-blue-100 text-blue-800';
        case 'shipped': return 'bg-purple-100 text-purple-800';
        case 'delivered': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Order Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Order #{{ order.id }}</h1>
                    <p class="text-sm text-muted-foreground">Order Details</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('orders.edit', order.id)">
                        <Button variant="outline">
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Order
                        </Button>
                    </Link>
                    <Link :href="route('orders.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Orders
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Order Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Order ID</label>
                            <p class="text-foreground">#{{ order.id }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize',
                                getStatusColor(order.status)
                            ]">{{ order.status }}</span>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Total Amount</label>
                            <p class="text-foreground font-bold">{{ formatCurrency(order.total_amount) }}</p>
                        </div>

                        <div v-if="order.notes">
                            <label class="text-sm font-medium text-muted-foreground">Notes</label>
                            <p class="text-foreground">{{ order.notes }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Created</label>
                            <p class="text-foreground">{{ formatDate(order.created_at) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                            <p class="text-foreground">{{ formatDate(order.updated_at) }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Customer Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Customer</label>
                            <div>
                                <p class="font-medium text-foreground">{{ order.user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ order.user.email }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Customer ID</label>
                            <p class="text-foreground">#{{ order.user.id }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 