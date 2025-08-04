<template>
  <AppLayout
    title="Template Marketplace"
    :breadcrumbs="[
      { label: 'Templates', href: route('contract-templates.index') },
      { label: 'Marketplace' }
    ]">

    <div class="pb-12">
      <div class="max-w-7xl">
        <!-- Marketplace Header -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 dark:from-blue-700 dark:to-blue-900 rounded-2xl mb-8 overflow-hidden">
          <div class="relative px-6 py-10 sm:px-10 sm:py-14">
            <!-- Subtle Background Pattern -->
            <div class="absolute inset-0 opacity-10 dark:opacity-5">
              <svg class="absolute bottom-0 right-0" viewBox="0 0 400 400" fill="none">
                <circle cx="300" cy="300" r="100" fill="white" opacity="0.1"/>
                <circle cx="350" cy="250" r="50" fill="white" opacity="0.15"/>
                <circle cx="250" cy="350" r="30" fill="white" opacity="0.2"/>
              </svg>
            </div>

            <div class="relative text-white max-w-4xl">
              <div class="mb-6">
                <h1 class="text-4xl sm:text-5xl font-bold mb-4 leading-tight">
                  Professional Contract
                  <span class="text-blue-200 dark:text-blue-300">Templates</span>
                </h1>
                <div class="w-20 h-1 bg-blue-300 dark:bg-blue-400 rounded-full mb-6"></div>
              </div>

              <p class="text-blue-100 dark:text-blue-200 text-xl leading-relaxed mb-8">
                Discover professionally crafted contract templates designed to streamline your business operations.
                Each template is carefully created by legal experts and industry professionals.
              </p>

              <!-- Enhanced feature highlights -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="flex items-center text-blue-600 dark:text-blue-200 bg-white bg-opacity-10 dark:bg-white dark:bg-opacity-5 backdrop-blur-sm rounded-lg p-3 border border-white border-opacity-20 dark:border-opacity-10">
                  <div class="flex-shrink-0 w-8 h-8 bg-green-400 dark:bg-green-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="font-medium">Legal Expert Reviewed</span>
                </div>
                <div class="flex items-center text-blue-600 dark:text-blue-200 bg-white bg-opacity-10 dark:bg-white dark:bg-opacity-5 backdrop-blur-sm rounded-lg p-3 border border-white border-opacity-20 dark:border-opacity-10">
                  <div class="flex-shrink-0 w-8 h-8 bg-yellow-400 dark:bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="font-medium">Instant Download</span>
                </div>
                <div class="flex items-center text-blue-600 dark:text-blue-200 bg-white bg-opacity-10 dark:bg-white dark:bg-opacity-5 backdrop-blur-sm rounded-lg p-3 border border-white border-opacity-20 dark:border-opacity-10">
                  <div class="flex-shrink-0 w-8 h-8 bg-purple-400 dark:bg-purple-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="font-medium">Fully Customizable</span>
                </div>
              </div>

              <!-- Quick stats -->
              <div class="flex flex-wrap gap-6 text-sm text-blue-200 dark:text-blue-300">
                <div>
                  <span class="font-semibold text-white dark:text-blue-100 text-lg">{{ allTemplates.length }}+</span>
                  <span class="ml-1">Templates Available</span>
                </div>
                <div>
                  <span class="font-semibold text-white dark:text-blue-100 text-lg">{{ categories.length }}</span>
                  <span class="ml-1">Categories</span>
                </div>
                <div>
                  <span class="font-semibold text-white dark:text-blue-100 text-lg">100%</span>
                  <span class="ml-1">Compliance Ready</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter and Search Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900/50 mb-6 p-6 border border-gray-100 dark:border-gray-700">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <label for="search" class="sr-only">Search templates</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>

                <Input
                  id="search"
                  v-model="searchQuery"
                  type="search"
                  placeholder="Search templates..."
                  class="pl-10 is-large"
                />
              </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
              <Select
                v-model="selectedCategory">
                <SelectTrigger class="w-full is-large">
                  <SelectValue
                    placeholder="Filter by categories"
                  />
                </SelectTrigger>

                <SelectContent>
                  <SelectItem
                    :value="null">
                    All Categories
                  </SelectItem>

                  <SelectItem
                    v-for="category in categories" :key="category" :value="category">
                    {{ category }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <Select
                v-model="sortBy">
                <SelectTrigger class="w-full is-large">
                  <SelectValue
                    placeholder="Sort by"
                  />
                </SelectTrigger>

                <SelectContent>
                <SelectItem value="name">Name</SelectItem>
                <SelectItem value="price-low">Price: Low to High</SelectItem>
                <SelectItem value="price-high">Price: High to Low</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <!-- Templates Grid -->
        <div v-if="filteredTemplates.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900/50 p-12 text-center border border-gray-100 dark:border-gray-700">
          <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No templates found</h3>
          <p class="text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria.</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <div
            v-for="template in filteredTemplates"
            :key="template.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl dark:shadow-gray-900/50 dark:hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col h-full group"
          >
            <!-- Template Header -->
            <div class="p-6 flex-1 flex flex-col">
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                    {{ template.name }}
                  </h3>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                    {{ template.category }}
                  </span>
                </div>
                <div class="text-right ml-4">
                  <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ formatCurrency(template.price) }}
                  </div>
                  <div v-if="template.is_purchased" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 mt-2">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Purchased
                  </div>
                </div>
              </div>

              <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-3 flex-1">{{ template.description }}</p>

              <!-- Features -->
              <div class="mb-4">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="feature in template.features?.slice(0, 3)"
                    :key="feature"
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700"
                  >
                    {{ feature }}
                  </span>
                  <span
                    v-if="template.features && template.features.length > 3"
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600"
                  >
                    +{{ template.features.length - 3 }} more
                  </span>
                </div>
              </div>

              <!-- Tags -->
              <div v-if="template.tags && template.tags.length > 0" class="mb-4">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="tag in template.tags.slice(0, 2)"
                    :key="tag"
                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600"
                  >
                    #{{ tag }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Template Actions - Always at bottom -->
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 border-t border-gray-100 dark:border-gray-600 mt-auto">
              <div class="flex gap-2">
                <Link
                  :href="route('contract-templates.preview', template.id)"
                  class="flex-1 inline-flex justify-center items-center px-3 py-2.5 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-400 dark:hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                >
                  <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Preview
                </Link>

                <button
                  v-if="!template.is_purchased"
                  @click="purchaseTemplate(template.id)"
                  type="button"
                  class="flex-1 inline-flex justify-center items-center px-3 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-500 dark:to-blue-600 hover:from-blue-700 hover:to-blue-800 dark:hover:from-blue-600 dark:hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 shadow-lg hover:shadow-xl transition-all duration-200"
                >
                  <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10l-1 4M9 19a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z" />
                  </svg>
                  Purchase
                </button>

                <div
                  v-else
                  class="flex-1 inline-flex justify-center items-center px-3 py-2.5 text-sm font-medium text-green-700 dark:text-green-300 bg-gradient-to-r from-green-100 to-green-200 dark:from-green-800 dark:to-green-700 rounded-lg border border-green-300 dark:border-green-600"
                >
                  <svg class="-ml-1 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  Owned
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination would go here if needed -->
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';

interface Template {
  id: number;
  name: string;
  description: string;
  price: number;
  category: string;
  features?: string[];
  tags?: string[];
  is_purchased: boolean;
  created_at: string;
}

interface Props {
  templatesByCategory: Record<string, Template[]>;
  categories: string[];
  companyCurrency?: string;
}

const props = withDefaults(defineProps<Props>(), {
  companyCurrency: 'MWK'
});

const searchQuery = ref('');
const selectedCategory = ref('');
const sortBy = ref('name');

// Flatten templates from categories
const allTemplates = computed(() => {
  return Object.values(props.templatesByCategory).flat();
});

const filteredTemplates = computed(() => {
  let templates = allTemplates.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    templates = templates.filter(template =>
      template.name.toLowerCase().includes(query) ||
      template.description.toLowerCase().includes(query) ||
      template.category.toLowerCase().includes(query) ||
      template.features?.some(feature => feature.toLowerCase().includes(query)) ||
      template.tags?.some(tag => tag.toLowerCase().includes(query))
    );
  }

  // Filter by category
  if (selectedCategory.value) {
    templates = templates.filter(template => template.category === selectedCategory.value);
  }

  // Sort templates
  templates.sort((a, b) => {
    switch (sortBy.value) {
      case 'price-low':
        return a.price - b.price;
      case 'price-high':
        return b.price - a.price;
      case 'name':
      default:
        return a.name.localeCompare(b.name);
    }
  });

  return templates;
});

const formatCurrency = (amount: number): string => {
  const currency = props.companyCurrency || 'MWK';

  // Define currency formatting options
  const currencyFormats: Record<string, { code: string; locale: string; symbol?: string }> = {
    'MWK': { code: 'MWK', locale: 'en-MW', symbol: 'MWK' },
    'USD': { code: 'USD', locale: 'en-US', symbol: '$' },
    'EUR': { code: 'EUR', locale: 'en-EU', symbol: '€' },
    'GBP': { code: 'GBP', locale: 'en-GB', symbol: '£' },
    'ZAR': { code: 'ZAR', locale: 'en-ZA', symbol: 'R' },
  };

  const format = currencyFormats[currency] || currencyFormats['MWK'];

  try {
    const formatter = new Intl.NumberFormat(format.locale, {
      style: 'currency',
      currency: format.code,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    return formatter.format(amount);
  } catch {
    // Fallback formatting if Intl.NumberFormat fails
    const symbol = format.symbol || currency;
    return `${symbol} ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }
};

const purchaseTemplate = (templateId: number) => {
  router.post(route('contract-templates.purchase', templateId));
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
