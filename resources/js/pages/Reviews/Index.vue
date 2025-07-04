<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';

interface Review {
    id: number;
    rating: number;
    comment: string;
    user: { id: number; name: string; email: string };
    product: { id: number; name: string };
    created_at: string;
    updated_at: string;
}

interface ReviewStats {
    total: number;
    average_rating: number | null;
    five_star: number;
    four_star: number;
    three_star: number;
    two_star: number;
    one_star: number;
    this_month: number;
}

defineProps<{
    reviews: {
        data: Review[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: ReviewStats;
    products: any[];
    users: any[];
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reviews', href: '/reviews' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const renderStars = (rating: number) => '★'.repeat(rating) + '☆'.repeat(5 - rating);
const formatRating = (rating: any) => {
    if (rating === null || rating === undefined) return '0.0';
    const numRating = typeof rating === 'number' ? rating : parseFloat(rating);
    return !isNaN(numRating) ? numRating.toFixed(1) : '0.0';
};
</script>

<template>
    <Head title="Reviews" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Reviews</h1>
                    <p class="text-sm text-muted-foreground">Manage customer reviews</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Reviews</CardTitle>
                        <Icon name="Star" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Average Rating</CardTitle>
                        <Icon name="TrendingUp" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatRating(stats.average_rating) }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">5-Star Reviews</CardTitle>
                        <Icon name="Star" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.five_star }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">1-Star Reviews</CardTitle>
                        <Icon name="Star" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.one_star }}</div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Reviews List</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Rating</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Comment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="review in reviews.data" :key="review.id" class="hover:bg-muted/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ review.product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ review.user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ renderStars(review.rating) }}</td>
                                    <td class="px-6 py-4 text-sm text-foreground max-w-xs truncate">{{ review.comment || 'No comment' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(review.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <Link :href="route('reviews.show', review.id)">
                                            <Button variant="outline" size="sm"><Icon name="Eye" class="h-4 w-4" /></Button>
                                        </Link>
                                        <Link :href="route('reviews.edit', review.id)">
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