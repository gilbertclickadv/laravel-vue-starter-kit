<template>
  <Head :title="isEditing ? 'Edit Section' : 'Create Section'" />

  <AppSidebarLayout>
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-foreground">
            {{ isEditing ? 'Edit Section' : 'Create Section' }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ isEditing ? 'Update an existing homepage section' : 'Add a new section to your homepage' }}
          </p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <Card>
          <CardHeader>
            <CardTitle>Basic Information</CardTitle>
            <CardDescription>
              Configure the main settings for this homepage section
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="title">Title</Label>
                <Input
                  id="title"
                  v-model="form.title"
                  type="text"
                  placeholder="Enter section title"
                  required
                />
                <InputError :message="form.errors.title" />
              </div>

              <div class="space-y-2">
                <Label for="type">Section Type</Label>
                <select
                  id="type"
                  v-model="form.type"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="">Select type</option>
                  <option v-for="(label, value) in availableTypes" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <InputError :message="form.errors.type" />
              </div>
            </div>

            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                rows="4"
                placeholder="Enter section description"
              />
              <InputError :message="form.errors.description" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="sort_order">Sort Order</Label>
                <Input
                  id="sort_order"
                  v-model.number="form.sort_order"
                  type="number"
                  placeholder="0"
                />
                <InputError :message="form.errors.sort_order" />
              </div>

              <div class="space-y-2">
                <Label for="is_active">Status</Label>
                <div class="flex items-center space-x-2">
                  <Switch
                    id="is_active"
                    v-model="form.is_active"
                  />
                  <Label for="is_active">{{ form.is_active ? 'Active' : 'Inactive' }}</Label>
                </div>
                <InputError :message="form.errors.is_active" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Section Type Settings -->
        <Card v-if="form.type && sectionTypeSettings.length > 0">
          <CardHeader>
            <CardTitle>Section Settings</CardTitle>
            <CardDescription>
              Configure specific settings for this section type
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div v-for="setting in sectionTypeSettings" :key="setting.key" class="space-y-2">
                <Label :for="setting.key">{{ setting.label }}</Label>
                
                <!-- Switch Input -->
                <div v-if="setting.type === 'switch'" class="flex items-center space-x-2">
                  <Switch
                    :id="setting.key"
                    v-model="form.settings[setting.key]"
                  />
                  <Label :for="setting.key">{{ form.settings[setting.key] ? 'Enabled' : 'Disabled' }}</Label>
                </div>

                <!-- Number Input -->
                <Input
                  v-else-if="setting.type === 'number'"
                  :id="setting.key"
                  v-model.number="form.settings[setting.key]"
                  type="number"
                  :min="setting.min"
                  :max="setting.max"
                  :step="setting.step"
                />

                <!-- Select Input -->
                <select
                  v-else-if="setting.type === 'select'"
                  :id="setting.key"
                  v-model="form.settings[setting.key]"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option v-for="(label, value) in setting.options" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>

                <InputError :message="form.errors[`settings.${setting.key}`]" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Form Actions -->
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? (isEditing ? 'Saving...' : 'Creating...') : (isEditing ? 'Save Changes' : 'Create Section') }}
              </Button>
              <Button
                variant="outline"
                type="button"
                @click="$inertia.visit(route('admin.homepage-sections.index'))"
              >
                Cancel
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>
    </div>
  </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'

interface Props {
  section?: {
    id?: number
    title?: string
    description?: string
    type?: string
    is_active?: boolean
    sort_order?: number | string
    settings?: Record<string, any>
  }
  availableTypes: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  section: () => ({})
})

const form = useForm({
  title: props.section?.title || '',
  description: props.section?.description || '',
  type: props.section?.type || '',
  is_active: props.section?.is_active ?? true,
  sort_order: Number(props.section?.sort_order) || 0,
  settings: props.section?.settings || {
    autoplay: true,
    autoplaySpeed: 5000,
    showArrows: true,
    showDots: true,
    productsToShow: 4,
    imagePosition: 'left',
    imageWidth: '50%',
    showCategoryImage: true,
    showCategoryDescription: true,
    promotionStyle: 'grid',
    images: [],
    selectedProducts: [],
    selectedCategories: []
  }
})

const isEditing = computed(() => !!props.section?.id)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Homepage Sections', href: '/admin/homepage-sections' },
  { title: isEditing.value ? 'Edit Section' : 'Create Section', href: '#' }
]

// Section type settings
const sectionTypeSettings = computed(() => {
  const settings = [];
  
  switch (form.type) {
    case 'carousel':
      settings.push(
        { key: 'autoplay', type: 'switch', label: 'Auto Play' },
        { key: 'autoplaySpeed', type: 'number', label: 'Auto Play Speed (ms)', min: 1000, max: 10000, step: 500 },
        { key: 'showArrows', type: 'switch', label: 'Show Arrows' },
        { key: 'showDots', type: 'switch', label: 'Show Dots' }
      );
      break;
    case 'featured_products':
      settings.push(
        { key: 'productsToShow', type: 'number', label: 'Products to Show', min: 1, max: 12 }
      );
      break;
    case 'banner':
      settings.push(
        { key: 'imagePosition', type: 'select', label: 'Image Position', options: {
          left: 'Left',
          right: 'Right',
          top: 'Top',
          bottom: 'Bottom'
        }},
        { key: 'imageWidth', type: 'select', label: 'Image Width', options: {
          '25': '25%',
          '33': '33%',
          '50': '50%',
          '66': '66%',
          '75': '75%',
          '100': '100%'
        }}
      );
      break;
    case 'categories':
      settings.push(
        { key: 'showCategoryImage', type: 'switch', label: 'Show Category Image' },
        { key: 'showCategoryDescription', type: 'switch', label: 'Show Category Description' }
      );
      break;
    case 'promotions':
      settings.push(
        { key: 'promotionStyle', type: 'select', label: 'Promotion Style', options: {
          grid: 'Grid',
          list: 'List',
          carousel: 'Carousel'
        }}
      );
      break;
  }
  
  return settings;
})

function submit() {
  if (isEditing.value) {
    form.put(route('admin.homepage-sections.update', props.section?.id))
  } else {
    form.post(route('admin.homepage-sections.store'))
  }
}
</script> 