<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
  heroSection: any;
  featuredProducts: any;
  categories: any;
  latestProducts: any;
}>();

const currentSlide = ref(0);
const autoplayInterval = ref<any>(null);

const nextSlide = () => {
  if (heroSection.items) {
    currentSlide.value = (currentSlide.value + 1) % heroSection.items.length;
  }
};

const prevSlide = () => {
  if (heroSection.items) {
    currentSlide.value = currentSlide.value === 0 ? heroSection.items.length - 1 : currentSlide.value - 1;
  }
};

const startAutoplay = () => {
  autoplayInterval.value = setInterval(nextSlide, 5000);
};

const stopAutoplay = () => {
  if (autoplayInterval.value) {
    clearInterval(autoplayInterval.value);
  }
};

onMounted(() => {
  startAutoplay();
});
</script>

<template>
  <Head title="Welcome" />

  <!-- Hero Section -->
  <section 
    class="relative h-[600px] overflow-hidden"
    @mouseenter="stopAutoplay"
    @mouseleave="startAutoplay"
  >
    <div 
      v-for="(slide, index) in heroSection.items" 
      :key="index"
      class="absolute inset-0 transition-opacity duration-500"
      :class="{ 'opacity-0': currentSlide !== index }"
    >
      <img 
        :src="slide.image_url" 
        :alt="slide.title"
        class="w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-black/40">
        <div class="container mx-auto h-full flex items-center">
          <div class="max-w-2xl text-white p-8">
            <h1 class="text-5xl font-bold mb-4">{{ slide.title }}</h1>
            <p class="text-xl mb-8">{{ slide.description }}</p>
            <Button 
              v-if="slide.link_url"
              :href="slide.link_url" 
              size="lg"
              variant="default"
            >
              {{ slide.link_text }}
            </Button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Navigation Buttons -->
    <button 
      @click="prevSlide" 
      class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
      </svg>
    </button>
    <button 
      @click="nextSlide" 
      class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
      </svg>
    </button>
  </section>

  <!-- Featured Products -->
  <section class="py-16 bg-gray-50">
    <div class="container mx-auto">
      <h2 class="text-3xl font-bold text-center mb-12">Featured Products</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <Card v-for="product in featuredProducts" :key="product.id">
          <CardContent class="p-0">
            <img 
              :src="product.primary_image?.image_url" 
              :alt="product.name"
              class="w-full aspect-square object-cover"
            />
            <div class="p-4">
              <h3 class="font-semibold text-lg mb-2">{{ product.name }}</h3>
              <p class="text-gray-600 mb-4">{{ product.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-xl font-bold">${{ product.base_price }}</span>
                <Button>Add to Cart</Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </section>

  <!-- Categories -->
  <section class="py-16">
    <div class="container mx-auto">
      <h2 class="text-3xl font-bold text-center mb-12">Shop by Category</h2>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <a 
          v-for="category in categories" 
          :key="category.id"
          :href="`/categories/${category.id}`"
          class="group relative aspect-square overflow-hidden rounded-lg"
        >
          <img 
            :src="category.image_url" 
            :alt="category.name"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
          />
          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-semibold">{{ category.name }}</h3>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- Latest Products -->
  <section class="py-16 bg-gray-50">
    <div class="container mx-auto">
      <h2 class="text-3xl font-bold text-center mb-12">Latest Arrivals</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <Card v-for="product in latestProducts" :key="product.id">
          <CardContent class="p-0">
            <img 
              :src="product.primary_image?.image_url" 
              :alt="product.name"
              class="w-full aspect-square object-cover"
            />
            <div class="p-4">
              <h3 class="font-semibold text-lg mb-2">{{ product.name }}</h3>
              <p class="text-gray-600 mb-4">{{ product.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-xl font-bold">${{ product.base_price }}</span>
                <Button>Add to Cart</Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section class="py-16 bg-primary text-white">
    <div class="container mx-auto text-center">
      <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
      <p class="text-lg mb-8 max-w-2xl mx-auto">Subscribe to our newsletter and get exclusive offers, new product alerts, and insider-only discounts.</p>
      <form class="flex gap-4 max-w-md mx-auto">
        <input 
          type="email" 
          placeholder="Enter your email"
          class="flex-1 px-4 py-2 rounded-lg text-gray-900"
        />
        <Button type="submit">Subscribe</Button>
      </form>
    </div>
  </section>
</template>
