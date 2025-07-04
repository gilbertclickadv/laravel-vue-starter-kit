<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Promotion {
    id: number;
    name: string;
    description?: string;
    discount_type: string;
    discount_value: number;
    start_date: string;
    end_date: string;
    usage_limit?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

defineProps<{
    promotion: Promotion;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Promotions', href: '/promotions' },
    { title: 'Promotion Details', href: '#' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const formatDiscount = (type: string, value: number) => {
    return type === 'percentage' ? `${value}%` : `$${value}`;
};
</script>

<template>
    <Head title="Promotion Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ promotion.name }}</h1>
                    <p class="text-sm text-muted-foreground">Promotion Details</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('promotions.edit', promotion.id)">
                        <Button variant="outline">
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Promotion
                        </Button>
                    </Link>
                    <Link :href="route('promotions.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Promotions
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Promotion Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Name</label>
                            <p class="text-foreground">{{ promotion.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Description</label>
                            <p class="text-foreground">{{ promotion.description || 'No description provided' }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Discount</label>
                            <p class="text-foreground">{{ formatDiscount(promotion.discount_type, promotion.discount_value) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                promotion.is_active 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]">{{ promotion.is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Promotion Period & Limits</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Start Date</label>
                            <p class="text-foreground">{{ formatDate(promotion.start_date) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">End Date</label>
                            <p class="text-foreground">{{ formatDate(promotion.end_date) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Usage Limit</label>
                            <p class="text-foreground">{{ promotion.usage_limit || 'Unlimited' }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Created</label>
                            <p class="text-foreground">{{ formatDate(promotion.created_at) }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 