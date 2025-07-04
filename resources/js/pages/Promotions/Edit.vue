<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

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
}

const props = defineProps<{
    promotion: Promotion;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Promotions', href: '/promotions' },
    { title: 'Edit Promotion', href: '#' },
];

const form = useForm({
    name: props.promotion.name,
    description: props.promotion.description || '',
    discount_type: props.promotion.discount_type,
    discount_value: props.promotion.discount_value,
    start_date: props.promotion.start_date,
    end_date: props.promotion.end_date,
    usage_limit: props.promotion.usage_limit || '',
    is_active: props.promotion.is_active,
});

const submit = () => {
    form.put(route('promotions.update', props.promotion.id));
};
</script>

<template>
    <Head title="Edit Promotion" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Edit Promotion</h1>
                    <p class="text-sm text-muted-foreground">Update promotion information</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Promotion Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Promotion Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter promotion name"
                                    required
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="discount_type">Discount Type</Label>
                                <select
                                    id="discount_type"
                                    v-model="form.discount_type"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed_amount">Fixed Amount</option>
                                </select>
                                <InputError :message="form.errors.discount_type" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="discount_value">Discount Value</Label>
                                <Input
                                    id="discount_value"
                                    v-model="form.discount_value"
                                    type="number"
                                    :placeholder="form.discount_type === 'percentage' ? 'e.g., 10 for 10%' : 'e.g., 50 for $50'"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                                <InputError :message="form.errors.discount_value" />
                            </div>

                            <div class="space-y-2">
                                <Label for="usage_limit">Usage Limit</Label>
                                <Input
                                    id="usage_limit"
                                    v-model="form.usage_limit"
                                    type="number"
                                    placeholder="Leave empty for unlimited"
                                    min="1"
                                />
                                <InputError :message="form.errors.usage_limit" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="start_date">Start Date</Label>
                                <Input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="datetime-local"
                                    required
                                />
                                <InputError :message="form.errors.start_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date">End Date</Label>
                                <Input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="datetime-local"
                                    required
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Enter promotion description (optional)"
                            ></textarea>
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-input focus:ring-ring"
                            />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Promotion' }}
                            </Button>
                            <Button variant="outline" type="button" @click="$inertia.visit(route('promotions.show', promotion.id))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 