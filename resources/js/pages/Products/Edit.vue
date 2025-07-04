<template>
  <Head title="Edit Product" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-foreground">Edit Product</h1>
          <p class="text-sm text-muted-foreground">Update your product information</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Product Information -->
        <Card>
          <CardHeader>
            <CardTitle>Basic Information</CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="name">Product Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Enter product name"
                  required
                />
                <InputError :message="form.errors.name" />
              </div>

              <div class="space-y-2">
                <Label for="sku">SKU</Label>
                <Input
                  id="sku"
                  v-model="form.sku"
                  type="text"
                  placeholder="Enter product SKU"
                />
                <InputError :message="form.errors.sku" />
              </div>
            </div>

            <div class="space-y-2">
              <Label for="description">Description</Label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="Enter product description"
              ></textarea>
              <InputError :message="form.errors.description" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div class="space-y-2">
                <Label for="vendor_id">Vendor</Label>
                <select
                  id="vendor_id"
                  v-model="form.vendor_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="">Select vendor</option>
                  <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                    {{ vendor.user.name }} ({{ vendor.company_name || vendor.store_name }})
                  </option>
                </select>
                <InputError :message="form.errors.vendor_id" />
              </div>

              <div class="space-y-2">
                <Label for="category_id">Category</Label>
                <select
                  id="category_id"
                  v-model="form.category_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="">Select category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <InputError :message="form.errors.category_id" />
              </div>

              <div class="space-y-2">
                <Label for="status">Status</Label>
                <select
                  id="status"
                  v-model="form.status"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <InputError :message="form.errors.status" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div class="space-y-2">
                <Label for="product_type">Product Type</Label>
                <select
                  id="product_type"
                  v-model="form.product_type"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  @change="handleProductTypeChange"
                  required
                >
                  <option value="simple">Simple Product</option>
                  <option value="variable">Variable Product</option>
                </select>
                <InputError :message="form.errors.product_type" />
              </div>

              <div class="space-y-2">
                <Label for="base_price">Base Price</Label>
                <Input
                  id="base_price"
                  v-model="form.base_price"
                  type="number"
                  step="0.01"
                  placeholder="0.00"
                  required
                />
                <InputError :message="form.errors.base_price" />
              </div>

              <div v-if="form.product_type === 'simple'" class="space-y-2">
                <Label for="stock_quantity">Stock Quantity</Label>
                <Input
                  id="stock_quantity"
                  v-model="form.stock_quantity"
                  type="number"
                  placeholder="0"
                  required
                />
                <InputError :message="form.errors.stock_quantity" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Product Attributes -->
        <Card v-if="form.product_type === 'variable'">
          <CardHeader>
            <CardTitle>Product Attributes</CardTitle>
            <p class="text-sm text-muted-foreground">
              Select attributes for this variable product. These will be used to generate variants.
            </p>
          </CardHeader>
          <CardContent>
            <AttributeSelector
              v-model="form.attributes"
              :attributes="attributes"
              label=""
              required
            />
            <InputError :message="form.errors.attributes" />
          </CardContent>
        </Card>

        <!-- Product Images -->
        <Card>
          <CardHeader>
            <CardTitle>Product Images</CardTitle>
            <p class="text-sm text-muted-foreground">
              Upload images for your product. You can assign images to specific attribute combinations.
            </p>
          </CardHeader>
          <CardContent>
            <ImageUpload
              v-model="form.images"
              :attributes="form.product_type === 'variable' ? filteredAttributes : []"
              label=""
              :max-images="10"
            />
            <InputError :message="form.errors.images" />
          </CardContent>
        </Card>

        <!-- Variants Preview -->
        <Card v-if="form.product_type === 'variable' && generatedVariants.length > 0">
          <CardHeader>
            <CardTitle>Generated Variants</CardTitle>
            <p class="text-sm text-muted-foreground">
              Preview of variants that will be created based on selected attributes.
            </p>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="divide-y divide-border">
                <!-- Table Header -->
                <div class="grid grid-cols-12 gap-4 font-medium px-4 py-2 bg-muted/50 rounded-t-lg">
                  <div class="col-span-5">Variant</div>
                  <div class="col-span-3">SKU</div>
                  <div class="col-span-2">Price Override</div>
                  <div class="col-span-2">Stock</div>
                </div>
                <!-- Table Body -->
                <div
                  v-for="(variant, index) in generatedVariants"
                  :key="variant.id || index"
                  class="grid grid-cols-12 gap-4 items-center px-4 py-3"
                >
                  <div class="col-span-5 font-medium">{{ variant.name }}</div>
                  <div class="col-span-3">
                    <Input
                      v-model="variant.sku"
                      type="text"
                      :placeholder="`${form.sku || 'SKU'}-${index + 1}`"
                    />
                  </div>
                  <div class="col-span-2">
                    <Input
                      v-model="variant.price_override"
                      type="number"
                      step="0.01"
                      :placeholder="form.base_price"
                    />
                  </div>
                  <div class="col-span-2">
                    <Input
                      v-model.number="variant.stock_quantity"
                      type="number"
                      placeholder="0"
                    />
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Form Actions -->
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Updating...' : 'Update Product' }}
              </Button>
              <Button
                variant="outline"
                type="button"
                @click="$inertia.visit(route('products.index'))"
              >
                Cancel
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import AttributeSelector from '@/components/AttributeSelector.vue';
import ImageUpload from '@/components/ImageUpload.vue';
import { computed, watch, ref, onMounted } from 'vue';

interface Vendor {
  id: number;
  user: {
    id: number;
    name: string;
  };
  company_name?: string;
  store_name?: string;
}

interface Category {
  id: number;
  name: string;
}

interface AttributeValue {
  id: number;
  value: string;
  hex?: string;
  sort_order: number;
}

interface Attribute {
  id: number;
  name: string;
  type: string;
  is_required: boolean;
  values: AttributeValue[];
}

interface SelectedAttribute {
  attribute_id: number;
  attribute_value_ids: number[];
}

interface ImageFile {
  id?: number;
  file?: File;
  image_url: string;
  alt_text: string;
  sort_order: number;
  is_primary: boolean;
  attribute_combination: AttributeCombination[];
  preview?: string;
}

interface AttributeCombination {
  attribute_id: number;
  attribute_value_id: number;
  attribute_name?: string;
  attribute_value?: string;
}

interface GeneratedVariant {
  id?: number;
  name: string;
  sku: string;
  price_override: string;
  stock_quantity: number;
  attributes: Array<{
    attribute_id: number;
    attribute_value_id: number;
  }>;
}

interface Product {
  id: number;
  name: string;
  description?: string;
  vendor_id: number;
  category_id: number;
  base_price: number;
  sku?: string;
  status: string;
  product_type: string;
  stock_quantity: number;
  formatted_attributes: SelectedAttribute[];
  formatted_images: ImageFile[];
  variants: any[];
}

const props = defineProps<{
  product: Product;
  vendors: Vendor[];
  categories: Category[];
  attributes: Attribute[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Products', href: '/products' },
  { title: 'Edit Product', href: `/products/${props.product.id}/edit` },
];

const form = useForm({
  name: props.product.name,
  description: props.product.description || '',
  vendor_id: props.product.vendor_id,
  category_id: props.product.category_id,
  base_price: props.product.base_price,
  sku: props.product.sku || '',
  status: props.product.status as 'active' | 'inactive',
  product_type: props.product.product_type as 'simple' | 'variable',
  stock_quantity: props.product.stock_quantity,
  attributes: props.product.formatted_attributes as any,
  images: props.product.formatted_images as any,
  variants: [] as any,
});

const generatedVariants = ref<GeneratedVariant[]>([]);

// Computed property to filter attributes that have selected values
const filteredAttributes = computed(() => {
  return props.attributes.filter(attr => {
    const selectedAttr = (form.attributes as SelectedAttribute[]).find((sa: SelectedAttribute) => sa.attribute_id === attr.id);
    return selectedAttr && selectedAttr.attribute_value_ids.length > 0;
  });
});

// Initialize variants from existing product data
onMounted(() => {
  if (props.product.product_type === 'variable' && props.product.variants) {
    generatedVariants.value = props.product.variants.map((variant: any) => {
      const variantName = variant.variant_attributes.map((attr: any) => attr.attribute_value.value).join(' / ');
      
      return {
        id: variant.id,
        name: variantName,
        sku: variant.sku || '',
        price_override: variant.price_override || '',
        stock_quantity: variant.stock_quantity || 0,
        attributes: variant.variant_attributes.map((attr: any) => ({
          attribute_id: attr.attribute_id,
          attribute_value_id: attr.attribute_value_id,
        })),
      };
    });
    
    // Sync with form
    form.variants = generatedVariants.value as any;
  }
});

// Watch for changes in attributes to generate variants
watch(
  () => form.attributes,
  (newAttributes) => {
    if (form.product_type === 'variable') {
      generateVariants(newAttributes as SelectedAttribute[]);
    }
  },
  { deep: true }
);

// Watch for changes in form.images to preserve form state
watch(() => form.images, (newImages) => {
    // Instead of reassigning individual properties, we'll use form.transform
    form.transform((data) => ({
        ...data,
        images: newImages,
    }));
}, { deep: true });

const handleProductTypeChange = () => {
  if (form.product_type === 'simple') {
    form.attributes = [];
    generatedVariants.value = [];
  } else {
    form.stock_quantity = 0;
  }
};

const generateVariants = (attributes: SelectedAttribute[]) => {
  if (attributes.length === 0) {
    generatedVariants.value = [];
    return;
  }

  // Generate all possible combinations
  const combinations = getAttributeCombinations(attributes);
  
  // Keep existing variants that match new combinations
  const existingVariants = generatedVariants.value;
  const newVariants: GeneratedVariant[] = [];

  combinations.forEach((combination, index) => {
    const name = combination.map(attr => {
      const attribute = props.attributes.find(a => a.id === attr.attribute_id);
      const value = attribute?.values.find(v => v.id === attr.attribute_value_id);
      return value?.value || '';
    }).join(' / ');

    // Check if this combination already exists in current variants
    const existingVariant = existingVariants.find(v => {
      return v.attributes.length === combination.length &&
        v.attributes.every(attr => 
          combination.some(combo => 
            combo.attribute_id === attr.attribute_id && 
            combo.attribute_value_id === attr.attribute_value_id
          )
        );
    });

    if (existingVariant) {
      // Keep existing variant data
      newVariants.push(existingVariant);
    } else {
      // Create new variant
      newVariants.push({
        name,
        sku: '',
        price_override: '',
        stock_quantity: 0,
        attributes: combination,
      });
    }
  });

  generatedVariants.value = newVariants;
  form.variants = generatedVariants.value as any;
};

const getAttributeCombinations = (attributes: SelectedAttribute[]) => {
  const combinations: Array<{ attribute_id: number; attribute_value_id: number }[]> = [];

  const generateCombinations = (currentIndex: number, currentCombination: Array<{ attribute_id: number; attribute_value_id: number }>) => {
    if (currentIndex === attributes.length) {
      combinations.push([...currentCombination]);
      return;
    }

    const attribute = attributes[currentIndex];
    for (const valueId of attribute.attribute_value_ids) {
      currentCombination.push({
        attribute_id: attribute.attribute_id,
        attribute_value_id: valueId,
      });
      generateCombinations(currentIndex + 1, currentCombination);
      currentCombination.pop();
    }
  };

  generateCombinations(0, []);
  return combinations;
};

const submit = () => {
    // Sync variants with form data
    form.variants = generatedVariants.value as any;
    
    // Transform the form data to handle files
    const data = form.data();
    
    // Transform images to include files and ensure proper formatting
    if (data.images) {
        data.images = data.images.map((image: any) => {
            // Ensure is_primary is a boolean
            const isPrimary = Boolean(image.is_primary);
            
            // Format attribute combinations
            const attributeCombination = Array.isArray(image.attribute_combination) 
                ? image.attribute_combination 
                : [];
            
            return {
                id: image.id,
                file: image.file instanceof File ? image.file : undefined,
                image_url: image.image_url,
                alt_text: image.alt_text || '',
                sort_order: typeof image.sort_order === 'number' ? image.sort_order : 0,
                is_primary: isPrimary,
                attribute_combination: attributeCombination,
            };
        });
    }
    
    // Submit the form
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('products.update', props.product.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};
</script> 