<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

interface Vendor {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    description?: string;
    is_active: boolean;
    user?: { id: number; name: string; email: string };
    users?: Array<{ id: number; name: string; email: string; role: string; created_at: string }>;
    products?: Array<{ id: number; name: string; price: number }>;
    created_at: string;
    updated_at: string;
    company_name?: string;
    store_name?: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

const props = defineProps<{
    vendor: Vendor;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vendors', href: '/vendors' },
    { title: 'Vendor Details', href: '#' },
];

const formatDate = (date: string) => new Date(date).toLocaleDateString();
const formatCurrency = (amount: number) => `$${amount.toFixed(2)}`;

const showAddUserDialog = ref(false);
const availableUsers = ref<User[]>([]);
const userForm = ref({
    user_id: '',
    processing: false
});

const removeUser = (userId: number) => {
    if (confirm('Are you sure you want to remove this user from the vendor?')) {
        router.delete(route('vendors.users.remove', { vendor: props.vendor.id, user: userId }));
    }
};

const addUser = () => {
    userForm.value.processing = true;
    router.post(route('vendors.users.add', props.vendor.id), {
        user_id: userForm.value.user_id
    }, {
        onSuccess: () => {
            showAddUserDialog.value = false;
            userForm.value.user_id = '';
        },
        onFinish: () => {
            userForm.value.processing = false;
        }
    });
};

const loadAvailableUsers = async () => {
    try {
        const response = await fetch(route('vendors.users.available', props.vendor.id));
        availableUsers.value = await response.json();
    } catch (error) {
        console.error('Failed to load available users:', error);
    }
};

// Load available users when dialog opens
const openDialog = () => {
    showAddUserDialog.value = true;
    loadAvailableUsers();
};
</script>

<template>
    <Head title="Vendor Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ vendor.company_name || vendor.store_name }}</h1>
                    <p class="text-sm text-muted-foreground">Vendor Details</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('vendors.edit', vendor.id)">
                        <Button variant="outline">
                            <Icon name="Edit" class="mr-2 h-4 w-4" />
                            Edit Vendor
                        </Button>
                    </Link>
                    <Link :href="route('vendors.index')">
                        <Button variant="outline">
                            <Icon name="ArrowLeft" class="mr-2 h-4 w-4" />
                            Back to Vendors
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Vendor Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Name</label>
                            <p class="text-foreground">{{ vendor.company_name || vendor.store_name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Email</label>
                            <p class="text-foreground">{{ vendor.email }}</p>
                        </div>

                        <div v-if="vendor.phone">
                            <label class="text-sm font-medium text-muted-foreground">Phone</label>
                            <p class="text-foreground">{{ vendor.phone }}</p>
                        </div>

                        <div v-if="vendor.address">
                            <label class="text-sm font-medium text-muted-foreground">Address</label>
                            <p class="text-foreground">{{ vendor.address }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Status</label>
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                vendor.is_active 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]">{{ vendor.is_active ? 'Active' : 'Inactive' }}</span>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Company Name</label>
                            <p class="text-foreground">{{ vendor.company_name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Store Name</label>
                            <p class="text-foreground">{{ vendor.store_name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">User</label>
                            <p class="text-foreground">{{ vendor.user?.name || 'Not assigned' }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Additional Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="vendor.description">
                            <label class="text-sm font-medium text-muted-foreground">Description</label>
                            <p class="text-foreground">{{ vendor.description }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Products</label>
                            <p class="text-foreground">{{ vendor.products?.length || 0 }} products</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Users</label>
                            <p class="text-foreground">{{ vendor.users?.length || 0 }} users</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Vendor ID</label>
                            <p class="text-foreground">#{{ vendor.id }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Created</label>
                            <p class="text-foreground">{{ formatDate(vendor.created_at) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                            <p class="text-foreground">{{ formatDate(vendor.updated_at) }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <CardTitle>Vendor Users</CardTitle>
                        <Button variant="outline" size="sm" @click="openDialog">
                            <Icon name="Plus" class="mr-2 h-4 w-4" />
                            Add User
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <!-- Owner User -->
                        <div v-if="vendor.user" class="border rounded-lg p-4 bg-blue-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-foreground">{{ vendor.user.name }} <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2">Owner</span></p>
                                    <p class="text-sm text-muted-foreground">{{ vendor.user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Associated Users -->
                        <div v-if="vendor.users && vendor.users.length > 0" class="space-y-2">
                            <h4 class="text-sm font-medium text-muted-foreground">Associated Users</h4>
                            <div v-for="user in vendor.users" :key="user.id" class="flex justify-between items-center py-2 border-b last:border-b-0">
                                <div>
                                    <span class="text-foreground">{{ user.name }}</span>
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full ml-2">{{ user.role }}</span>
                                    <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                </div>
                                <Button variant="destructive" size="sm" @click="removeUser(user.id)">
                                    <Icon name="Trash2" class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div v-else-if="!vendor.users || vendor.users.length === 0">
                            <p class="text-sm text-muted-foreground">No additional users assigned to this vendor.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="vendor.products && vendor.products.length > 0">
                <CardHeader>
                    <CardTitle>Products</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div v-for="product in vendor.products.slice(0, 10)" :key="product.id" class="flex justify-between items-center py-2 border-b last:border-b-0">
                            <span class="text-foreground">{{ product.name }}</span>
                            <span class="text-muted-foreground">{{ formatCurrency(product.price) }}</span>
                        </div>
                        <p v-if="vendor.products.length > 10" class="text-xs text-muted-foreground mt-2">
                            ...and {{ vendor.products.length - 10 }} more products
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Add User Dialog -->
    <Dialog v-model:open="showAddUserDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add User to Vendor</DialogTitle>
            </DialogHeader>
            <div class="space-y-4">
                <p class="text-sm text-muted-foreground">
                    Add an existing user to this vendor. Users can only be assigned to one vendor at a time.
                </p>
                <form @submit.prevent="addUser" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="user_id">Select User</Label>
                        <select
                            id="user_id"
                            v-model="userForm.user_id"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required
                        >
                            <option value="">Choose a user...</option>
                            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-4 justify-end">
                        <Button type="button" variant="outline" @click="showAddUserDialog = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="userForm.processing">
                            {{ userForm.processing ? 'Adding...' : 'Add User' }}
                        </Button>
                    </div>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>