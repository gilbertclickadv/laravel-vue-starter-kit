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

defineProps<{
    review: Review;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reviews', href: '/reviews' },
    { title: 'Review Details', href: '#' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const renderStars = (rating: number) => '★'.repeat(rating) + '☆'.repeat(5 - rating);
</script>

<template>
    <Head title="Review Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Review Details</h1>
                    <p class="text-sm text-muted-foreground">View review information</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('reviews.edit', review.id)">
                        <Button variant="outline">
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Review
                        </Button>
                    </Link>
                    <Link :href="route('reviews.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Reviews
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Review Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Rating</label>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ renderStars(review.rating) }}</span>
                                <span class="text-sm text-muted-foreground">({{ review.rating }}/5)</span>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Comment</label>
                            <p class="text-foreground">{{ review.comment || 'No comment provided' }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Created</label>
                            <p class="text-foreground">{{ formatDate(review.created_at) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                            <p class="text-foreground">{{ formatDate(review.updated_at) }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Customer & Product</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Customer</label>
                            <div>
                                <p class="font-medium text-foreground">{{ review.user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ review.user.email }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Product</label>
                            <p class="text-foreground">{{ review.product.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Review ID</label>
                            <p class="text-foreground">#{{ review.id }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 