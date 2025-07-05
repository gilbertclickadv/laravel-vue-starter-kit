<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import ImageUpload from '@/components/ImageUpload.vue';

const props = defineProps<{
    section: any;
    products?: any[];
}>();

const { toast } = useToast();

const form = useForm({
    title: '',
    description: '',
    image: null as File | null,
    link_url: '',
    link_text: '',
    additional_data: {},
    is_active: true,
    sort_order: 0,
    product_id: null as number | null,
});

const submit = () => {
    form.post(route('admin.homepage-sections.items.store', props.section.id), {
        onSuccess: () => {
            toast({
                title: 'Success',
                description: 'Item created successfully',
            });
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Add Section Item" />

        <div class="container py-6">
            <Card>
                <CardHeader>
                    <CardTitle>Add Item to {{ section.title }}</CardTitle>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                :error="form.errors.title"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :error="form.errors.description"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Image</Label>
                            <ImageUpload
                                v-model="form.image"
                                :error="form.errors.image"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="link_url">Link URL</Label>
                            <Input
                                id="link_url"
                                v-model="form.link_url"
                                type="text"
                                :error="form.errors.link_url"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="link_text">Link Text</Label>
                            <Input
                                id="link_text"
                                v-model="form.link_text"
                                type="text"
                                :error="form.errors.link_text"
                            />
                        </div>

                        <template v-if="section.type === 'featured_products' && products">
                            <div class="space-y-2">
                                <Label for="product_id">Product</Label>
                                <Select v-model="form.product_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a product" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="product in products"
                                            :key="product.id"
                                            :value="product.id"
                                        >
                                            {{ product.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </template>

                        <div class="flex items-center space-x-2">
                            <Switch
                                id="is_active"
                                v-model="form.is_active"
                            />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="space-y-2">
                            <Label for="sort_order">Sort Order</Label>
                            <Input
                                id="sort_order"
                                v-model="form.sort_order"
                                type="number"
                                :error="form.errors.sort_order"
                            />
                        </div>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                        >
                            Add Item
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 