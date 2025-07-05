<template>
  <Head title="Homepage Sections" />

  <AppSidebarLayout>
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-foreground">Homepage Sections</h1>
          <p class="text-sm text-muted-foreground">
            Manage your website's homepage sections and content
          </p>
        </div>
        <Link :href="route('admin.homepage-sections.create')">
          <Button>
            <Icon name="Plus" class="mr-2 h-4 w-4" />
            Add Section
          </Button>
        </Link>
      </div>

      <!-- Main Content -->
      <Card>
        <CardHeader>
          <CardTitle>All Sections</CardTitle>
          <CardDescription>
            A list of all homepage sections for your website
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="sections.length === 0" class="text-center py-12">
            <h3 class="text-lg font-medium text-gray-900">No sections found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new section.</p>
            <div class="mt-6">
              <Link :href="route('admin.homepage-sections.create')" class="btn-primary">
                Create Section
              </Link>
            </div>
          </div>

          <div v-else>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Title</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Sort Order</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="section in sections" :key="section.id">
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ section.title }}</div>
                      <div v-if="section.description" class="text-sm text-muted-foreground">
                        {{ section.description }}
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>{{ availableTypes[section.type] }}</TableCell>
                  <TableCell>{{ section.sort_order }}</TableCell>
                  <TableCell>
                    <Badge :variant="section.is_active ? 'success' : 'secondary'">
                      {{ section.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <Button variant="ghost" size="icon" @click="editSection(section)">
                      <Icon name="Edit" class="h-4 w-4" />
                      <span class="sr-only">Edit</span>
                    </Button>
                    <Button variant="ghost" size="icon" @click="deleteSection(section)">
                      <Icon name="Trash" class="h-4 w-4" />
                      <span class="sr-only">Delete</span>
                    </Button>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Delete Confirmation Dialog -->
    <ConfirmationDialog
      ref="deleteDialogRef"
      title="Delete Section"
      description="Are you sure you want to delete this section? This action cannot be undone."
      :loading="isDeleting"
      @confirm="handleConfirmDelete"
    />
  </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import Icon from '@/components/Icon.vue'
import ConfirmationDialog from '@/components/ConfirmationDialog.vue'

interface HomepageSection {
  id: number
  type: string
  title: string
  description?: string
  is_active: boolean
  sort_order: number
  items?: HomepageSectionItem[]
}

interface HomepageSectionItem {
  id: number
  title?: string
  description?: string
  image_url?: string
  is_active: boolean
}

const props = defineProps<{
  sections: HomepageSection[]
  availableTypes: Record<string, string>
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Homepage Sections', href: '/admin/homepage-sections' },
]

const deleteDialogRef = ref()
const sectionToDelete = ref<HomepageSection | null>(null)
const isDeleting = ref(false)

function deleteSection(section: HomepageSection) {
  sectionToDelete.value = section
  deleteDialogRef.value?.open()
}

function handleConfirmDelete() {
  if (!sectionToDelete.value) return

  isDeleting.value = true
  router.delete(route('admin.homepage-sections.destroy', sectionToDelete.value.id), {
    onSuccess: () => {
      deleteDialogRef.value?.close()
      sectionToDelete.value = null
      isDeleting.value = false
    },
    onError: () => {
      isDeleting.value = false
    }
  })
}

function editSection(section: HomepageSection) {
  router.visit(route('admin.homepage-sections.edit', section.id))
}
</script> 