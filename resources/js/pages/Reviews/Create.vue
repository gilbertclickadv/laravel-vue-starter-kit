<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Product {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

defineProps<{
    products: Product[];
    users: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reviews', href: '/reviews' },
    { title: 'Create Review', href: '/reviews/create' },
];

const form = useForm({
    user_id: '',
    product_id: '',
    rating: 5,
    comment: '',
});

const submit = () => {
    form.post(route('reviews.store'));
};
</script>

<template>
    <Head title="Create Review" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Create Review</h1>
                    <p class="text-sm text-muted-foreground">Add a new product review</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Review Details</CardTitle>
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
                                <Label for="product_id">Product</Label>
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Select a product</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.product_id" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="rating">Rating</Label>
                            <select
                                id="rating"
                                v-model="form.rating"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="1">1 Star - Poor</option>
                                <option value="2">2 Stars - Fair</option>
                                <option value="3">3 Stars - Good</option>
                                <option value="4">4 Stars - Very Good</option>
                                <option value="5">5 Stars - Excellent</option>
                            </select>
                            <InputError :message="form.errors.rating" />
                        </div>

                        <div class="space-y-2">
                            <Label for="comment">Comment</Label>
                            <textarea
                                id="comment"
                                v-model="form.comment"
                                rows="4"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Write your review comment here..."
                            ></textarea>
                            <InputError :message="form.errors.comment" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Review' }}
                            </Button>
                            <Button variant="outline" type="button" @click="$inertia.visit(route('reviews.index'))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 