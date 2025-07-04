<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Promotion {
    id: number;
    title: string;
    description?: string;
    code?: string;
    type: 'percentage' | 'fixed';
    value: number;
    min_order_amount?: number;
    max_discount_amount?: number;
    start_date: string;
    end_date: string;
    usage_limit?: number;
    usage_count: number;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
}

interface PromotionStats {
    total: number;
    active: number;
    inactive: number;
    expired: number;
    this_month: number;
    percentage_type: number;
    fixed_type: number;
}

defineProps<{
    promotions: {
        data: Promotion[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: PromotionStats;
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Promotions', href: '/promotions' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const formatCurrency = (amount: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
const formatDiscount = (promotion: Promotion) => {
    return promotion.type === 'percentage' ? `${promotion.value}%` : formatCurrency(promotion.value);
};
</script>

<template>
    <Head title="Promotions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Promotions</h1>
                    <p class="text-sm text-muted-foreground">Manage promotional campaigns</p>
                </div>
                <Link :href="route('promotions.create')">
                    <Button>
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        Create Promotion
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Promotions</CardTitle>
                        <Icon name="Percent" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active</CardTitle>
                        <Icon name="CheckCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Expired</CardTitle>
                        <Icon name="XCircle" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.expired }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <Icon name="Calendar" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.this_month }}</div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Promotions List</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="promotion in promotions.data" :key="promotion.id" class="hover:bg-muted/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ promotion.title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ promotion.code || 'No code' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ promotion.type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ formatDiscount(promotion) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        {{ formatDate(promotion.start_date) }} - {{ formatDate(promotion.end_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            promotion.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">{{ promotion.status }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <Link :href="route('promotions.show', promotion.id)">
                                            <Button variant="outline" size="sm"><Icon name="Eye" class="h-4 w-4" /></Button>
                                        </Link>
                                        <Link :href="route('promotions.edit', promotion.id)">
                                            <Button variant="outline" size="sm"><Icon name="Edit" class="h-4 w-4" /></Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 