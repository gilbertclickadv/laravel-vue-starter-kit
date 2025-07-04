<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

const props = defineProps<{
    users: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vendors', href: '/vendors' },
    { title: 'Create Vendor', href: '/vendors/create' },
];

const form = useForm({
    user_id: '',
    company_name: '',
    store_name: '',
    status: 'active',
});

const submit = () => {
    form.post(route('vendors.store'));
};
</script>

<template>
    <Head title="Create Vendor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Create Vendor</h1>
                    <p class="text-sm text-muted-foreground">Add a new vendor to your platform</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Vendor Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="user_id">Select User (Owner)</Label>
                            <select
                                id="user_id"
                                v-model="form.user_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Select a user...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }}) - {{ user.role }}
                                </option>
                            </select>
                            <InputError :message="form.errors.user_id" />
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="company_name">Company Name</Label>
                                <Input
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    placeholder="Enter company name"
                                />
                                <InputError :message="form.errors.company_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="store_name">Store Name</Label>
                                <Input
                                    id="store_name"
                                    v-model="form.store_name"
                                    type="text"
                                    placeholder="Enter store name"
                                />
                                <InputError :message="form.errors.store_name" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Vendor' }}
                            </Button>
                            <Button variant="outline" type="button" @click="$inertia.visit(route('vendors.index'))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 