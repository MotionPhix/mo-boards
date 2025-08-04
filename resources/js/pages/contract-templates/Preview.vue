<template>
  <AppLayout
    title="Template Preview"
    :breadcrumbs="[
      { label: 'Templates', href: route('contract-templates.index') },
      { label: 'Marketplace', href: route('contract-templates.marketplace') },
      { label: 'Preview' }
    ]"
  >
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Template Preview
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ template.name }}
          </p>
        </div>

        <div class="flex gap-3">
          <button
            v-if="!isPurchased && canPurchase"
            @click="purchaseTemplate(template.id)"
            type="button"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10l-1 4M9 19a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z" />
            </svg>
            Buy
          </button>
        </div>
      </div>

    <div class="pb-12">
      <div class="max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Template Preview Content -->
          <div class="lg:col-span-2">
            <!-- Template Info -->
            <div class="bg-white rounded-lg mb-6 overflow-hidden">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="text-xl font-semibold text-gray-900">{{ template.name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ template.description }}</p>
                  </div>
                  <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                      {{ template.category }}
                    </span>
                    <div v-if="isPurchased" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-2">
                      <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      Owned
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Preview Warning for Non-Purchased Templates -->
            <div v-if="!isPurchased" class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-amber-800">
                    Preview Mode
                  </h3>
                  <div class="mt-2 text-sm text-amber-700">
                    <p>
                      This is a preview of the template. Some content may be redacted or limited.
                      Purchase the template to access the full content and customization options.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Template Content Preview -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-medium text-gray-900">Template Content</h3>
                  <div class="flex gap-2">
                    <button
                      @click="showRawContent = !showRawContent"
                      class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                      </svg>
                      {{ showRawContent ? 'Hide Raw' : 'Show Raw' }}
                    </button>
                    <button
                      @click="printPreview"
                      class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                      </svg>
                      Print
                    </button>
                  </div>
                </div>
              </div>

              <div class="p-6">
                <div v-if="!showRawContent" class="prose max-w-none">
                  <div
                    v-html="previewContent || template.content || 'No content available for preview.'"
                    class="contract-preview"
                    :class="{ 'preview-mode': !isPurchased }"
                  ></div>
                </div>
                <div v-else class="bg-gray-50 rounded-lg p-4">
                  <pre class="text-sm text-gray-800 whitespace-pre-wrap font-mono">{{ previewContent || template.content || 'No content available.' }}</pre>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Template Details -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Template Details</h3>
              </div>
              <div class="px-6 py-4 space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Category</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ template.category }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Price</dt>
                  <dd class="mt-1 text-sm font-gray-900">
                    <span class="text-lg font-semibold text-green-600">
                      {{ formatCurrency(template.price) }}
                    </span>
                  </dd>
                </div>
                <div v-if="template.features && template.features.length > 0">
                  <dt class="text-sm font-medium text-gray-500">Features</dt>
                  <dd class="mt-1">
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="feature in template.features"
                        :key="feature"
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200"
                      >
                        {{ feature }}
                      </span>
                    </div>
                  </dd>
                </div>
                <div v-if="template.tags && template.tags.length > 0">
                  <dt class="text-sm font-medium text-gray-500">Tags</dt>
                  <dd class="mt-1">
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="tag in template.tags"
                        :key="tag"
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200"
                      >
                        #{{ tag }}
                      </span>
                    </div>
                  </dd>
                </div>
                <div v-if="template.custom_fields && template.custom_fields.length > 0">
                  <dt class="text-sm font-medium text-gray-500">Custom Fields</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ template.custom_fields.length }} fields defined</dd>
                </div>
                <div v-if="template.default_terms && Object.keys(template.default_terms).length > 0">
                  <dt class="text-sm font-medium text-gray-500">Default Terms</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ Object.keys(template.default_terms).length }} terms defined</dd>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Actions</h3>
              </div>
              <div class="px-6 py-4 space-y-3">
                <button
                  v-if="!isPurchased && canPurchase"
                  @click="purchaseTemplate(template.id)"
                  type="button"
                  class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200"
                >
                  <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10l-1 4M9 19a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z" />
                  </svg>
                  Purchase Template
                </button>

                <div
                  v-else-if="isPurchased"
                  class="w-full inline-flex justify-center items-center px-4 py-3 text-sm font-medium text-green-700 bg-green-100 rounded-md border border-green-200"
                >
                  <svg class="-ml-1 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  Template Owned
                </div>

                <Link
                  v-if="isPurchased"
                  :href="route('contracts.create', { template: template.id })"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Create Contract
                </Link>

                <Link
                  :href="route('contract-templates.marketplace')"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Browse More Templates
                </Link>
              </div>
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800">
                    Need Help?
                  </h3>
                  <div class="mt-2 text-sm text-blue-700">
                    <p>
                      Contact our support team if you have questions about this template or need assistance with customization.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';

interface ContractTemplate {
  id: number
  name: string
  description?: string
  content?: string
  price: number
  category: string
  features?: string[]
  tags?: string[]
  default_terms?: object
  custom_fields?: any[]
  is_system_template: boolean
  created_at: string
  updated_at: string
}

interface Props {
  template: ContractTemplate
  previewContent?: string
  isPurchased: boolean
  canPurchase: boolean
}

defineProps<Props>()
const page = usePage()

const showRawContent = ref(false)

const formatCurrency = (amount: number): string => {
  // Get company currency from page props or default to MWK
  const companyCurrency = (page.props as any).auth?.user?.currentCompany?.currency || 'MWK'

  const currencyFormats: Record<string, { code: string; locale: string; symbol?: string }> = {
    'MWK': { code: 'MWK', locale: 'en-MW', symbol: 'MWK' },
    'USD': { code: 'USD', locale: 'en-US', symbol: '$' },
    'EUR': { code: 'EUR', locale: 'en-EU', symbol: '€' },
    'GBP': { code: 'GBP', locale: 'en-GB', symbol: '£' },
    'ZAR': { code: 'ZAR', locale: 'en-ZA', symbol: 'R' },
  }

  const format = currencyFormats[companyCurrency] || currencyFormats['MWK']

  try {
    const formatter = new Intl.NumberFormat(format.locale, {
      style: 'currency',
      currency: format.code,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
    return formatter.format(amount)
  } catch {
    const symbol = format.symbol || companyCurrency
    return `${symbol} ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
  }
}

const printPreview = () => {
  window.print()
}

const purchaseTemplate = (templateId: number) => {
  router.post(route('contract-templates.purchase', templateId));
}
</script>

<style scoped>
.contract-preview {
  line-height: 1.6;
}

.contract-preview h1,
.contract-preview h2,
.contract-preview h3 {
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  font-weight: 600;
}

.contract-preview p {
  margin-bottom: 1em;
}

.contract-preview ul,
.contract-preview ol {
  margin-bottom: 1em;
  padding-left: 1.5em;
}

.preview-mode::after {
  content: "\A\A--- Preview Mode ---\AThis is a limited preview. Purchase the template to access the full content.";
  white-space: pre;
  color: #6b7280;
  font-style: italic;
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
  margin-top: 1rem;
  display: block;
}

@media print {
  .no-print {
    display: none !important;
  }

  .contract-preview {
    font-size: 12pt;
    line-height: 1.5;
  }
}
</style>
