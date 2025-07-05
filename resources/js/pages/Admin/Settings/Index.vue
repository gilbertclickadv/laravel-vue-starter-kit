<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';

const props = defineProps<{
    settings: Record<string, any[]>;
    groups: string[];
}>();

const { toast } = useToast();
const activeGroup = ref(props.groups[0]);

const form = useForm({
    settings: {} as Record<string, any>,
});

// Initialize form with current settings
for (const group in props.settings) {
    for (const setting of props.settings[group]) {
        form.settings[setting.key] = setting.value;
    }
}

// Watch for form processing state
watch(() => form.processing, (processing) => {
    if (!processing && form.recentlySuccessful) {
        toast({
            title: 'Success',
            description: 'Settings updated successfully',
        });
    }
});

const updateSettings = () => {
    form.put(route('admin.settings.update-group', activeGroup.value));
};

const getSettingComponent = (type: string) => {
    switch (type) {
        case 'boolean':
            return Switch;
        case 'text':
            return Input;
        case 'number':
            return Input;
        default:
            return Textarea;
    }
};

const getSettingProps = (type: string) => {
    switch (type) {
        case 'number':
            return { type: 'number', step: '0.01' };
        default:
            return {};
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Site Settings" />

        <div class="container py-6">
            <Card>
                <CardHeader>
                    <CardTitle>Site Settings</CardTitle>
                </CardHeader>

                <CardContent>
                    <Tabs v-model="activeGroup" class="w-full">
                        <TabsList class="w-full justify-start">
                            <TabsTrigger
                                v-for="group in groups"
                                :key="group"
                                :value="group"
                                class="capitalize"
                            >
                                {{ group.replace('_', ' ') }}
                            </TabsTrigger>
                        </TabsList>

                        <form @submit.prevent="updateSettings">
                            <TabsContent
                                v-for="group in groups"
                                :key="group"
                                :value="group"
                                class="space-y-6 mt-6"
                            >
                                <div
                                    v-for="setting in settings[group]"
                                    :key="setting.key"
                                    class="space-y-2"
                                >
                                    <Label :for="setting.key">
                                        {{ setting.label }}
                                        <span
                                            v-if="setting.description"
                                            class="text-sm text-muted-foreground ml-2"
                                        >
                                            {{ setting.description }}
                                        </span>
                                    </Label>

                                    <component
                                        :is="getSettingComponent(setting.type)"
                                        v-model="form.settings[setting.key]"
                                        :id="setting.key"
                                        v-bind="getSettingProps(setting.type)"
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="mt-6"
                                >
                                    Save Changes
                                </Button>
                            </TabsContent>
                        </form>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 