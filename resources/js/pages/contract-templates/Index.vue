<script setup lang="ts">
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { debounce } from 'lodash'
import { Plus, FileText, Search, X, Eye, Edit, Copy, Trash2, Download, DollarSign } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import EmptyState from '@/components/EmptyState.vue'
import Pagination from '@/components/Pagination.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import type { ContractTemplate } from '@/types'

interface Props {
  templates: {
    current_page: number
    data: ContractTemplate[]
    first_page_url: string
    from: number | null
    last_page: number
    last_page_url: string
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
    next_page_url: string | null
    path: string
    per_page: number
    prev_page_url: string | null
    to: number | null
    total: number
    meta: {
      current_page: number
      from: number | null
      last_page: number
      links: Array<{
        url: string | null
        label: string
        active: boolean
      }>
      path: string
      per_page: number
      to: number | null
      total: number
    }
  }
  stats: {
    total: number
    company: number
    purchased: number
    available: number
    active: number
    categories: string[]
  }
  filters: {
    search?: string
    category?: string
    status?: string
    type?: string
    sort?: string
    direction?: string
  }
  categories: string[]
}

const props = defineProps<Props>()

const searchForm = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  status: props.filters.status || '',
  type: props.filters.type || '',
  sort: props.filters.sort || 'created_at',
  direction: props.filters.direction || 'desc',
})

const debouncedSearch = debounce(() => {
  const formData = {
    search: searchForm.search,
    category: searchForm.category,
    status: searchForm.status,
    type: searchForm.type,
    sort: searchForm.sort,
    direction: searchForm.direction,
  }

  router.get(route('contract-templates.index'), formData, {
    preserveState: true,
    replace: true,
  })
}, 300)

const updateFilter = (field: string, value: any) => {
  const stringValue = value === 'all' ? '' : (value ? String(value) : '')
  ;(searchForm as any)[field] = stringValue

  const formData = {
    search: searchForm.search,
    category: searchForm.category,
    status: searchForm.status,
    type: searchForm.type,
    sort: searchForm.sort,
    direction: searchForm.direction,
  }

  router.get(route('contract-templates.index'), formData, {
    preserveState: true,
    replace: true,
  })
}

const hasActiveFilters = computed(() => {
  return !!searchForm.search || !!searchForm.category || !!searchForm.status || !!searchForm.type
})

const hasTemplates = computed(() => {
  return props.templates?.data && Array.isArray(props.templates.data) && props.templates.data.length > 0
})

const templateCount = computed(() => {
  return props.templates?.meta?.total || 0
})

const clearFilters = () => {
  searchForm.search = ''
  searchForm.category = ''
  searchForm.status = ''
  searchForm.type = ''
  searchForm.sort = 'created_at'
  searchForm.direction = 'desc'

  router.get(route('contract-templates.index'), {}, {
    preserveState: true,
    replace: true,
  })
}

const getEmptyStateDescription = () => {
  const hasFilters = hasActiveFilters.value

  if (hasFilters) {
    return "No templates match your current search criteria. Try adjusting your filters or search terms."
  }

  return "Start creating contract templates to streamline your contract creation process. Templates allow you to reuse common contract structures and terms."
}

const viewTemplate = (template: ContractTemplate) => {
  router.visit(route('contract-templates.show', template.uuid))
}

const editTemplate = (template: ContractTemplate) => {
  router.visit(route('contract-templates.edit', template.uuid))
}

const duplicateTemplate = (template: ContractTemplate) => {
  router.post(route('contract-templates.duplicate', template.uuid))
}

const deleteTemplate = (template: ContractTemplate) => {
  if (confirm('Are you sure you want to delete this template?')) {
    router.delete(route('contract-templates.destroy', template.uuid))
  }
}

const exportPdf = (template: ContractTemplate) => {
  window.open(route('contract-templates.export-pdf', template.uuid), '_blank')
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClasses = (template: ContractTemplate): string => {
  if (!template.is_active) {
    return 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600'
  }
  if (template.is_premium) {
    return 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-700'
  }
  return 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-700'
}
</script>

<template>
  <AppLayout
    title="Contract Templates"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Contract Templates' }
    ]"
  >
    <!-- Full-width header/hero section -->
    <div class=" py-8">
      <div>
        <!-- Header -->
        <div class="flex flex-col gap-6 lg:flex-row lg:justify-between lg:items-center">
          <!-- Title Section -->
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Contract Templates</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">
                  Create and manage reusable contract templates for your business
                </p>
              </div>
            </div>
          </div>

          <!-- Stats & Actions Section -->
          <div class="flex flex-col lg:flex-row lg:items-center gap-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-3">
              <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 text-center">
                <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ stats.active }}</div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Active</div>
              </div>
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 text-center">
                <div class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ stats.company }}</div>
                <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Company</div>
              </div>
              <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-lg p-3 text-center">
                <div class="text-lg font-bold text-violet-700 dark:text-violet-300">{{ stats.purchased }}</div>
                <div class="text-xs text-violet-600 dark:text-violet-400 font-medium">Purchased</div>
              </div>
              <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 text-center">
                <div class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ stats.total }}</div>
                <div class="text-xs text-slate-600 dark:text-slate-400 font-medium">Total</div>
              </div>
            </div>

            <!-- Action Button -->
            <Button
              @click="router.visit(route('contract-templates.create'))"
              class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 lg:flex-shrink-0"
              size="lg">
              <Plus class="h-5 w-5" />
              New
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content with max-w-5xl constraint -->
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Filters -->
      <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
        <CardContent class="p-4 sm:p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <div class="sm:col-span-2 xl:col-span-2">
              <Label htmlFor="search" class="text-sm font-medium text-slate-700 dark:text-slate-300">Search Templates</Label>
              <div class="relative mt-1.5">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 dark:text-slate-500 h-4 w-4" />
                <Input
                  id="search"
                  v-model="searchForm.search"
                  type="text"
                  placeholder="Search by name or description..."
                  class="pl-10 border-slate-300 dark:border-slate-600 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-slate-900"
                  @input="debouncedSearch"
                />
              </div>
            </div>
            <div>
              <Label htmlFor="category" class="text-sm font-medium text-slate-700 dark:text-slate-300">Category</Label>
              <Select
                :model-value="searchForm.category || 'all'"
                @update:model-value="(value) => updateFilter('category', value)"
              >
                <SelectTrigger class="mt-1.5 w-full border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900">
                  <SelectValue placeholder="All Categories" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                  <SelectItem value="all">All Categories</SelectItem>
                  <SelectItem v-for="category in categories" :key="category" :value="category">
                    {{ category }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label htmlFor="status" class="text-sm font-medium text-slate-700 dark:text-slate-300">Status</Label>
              <Select
                :model-value="searchForm.status || 'all'"
                @update:model-value="(value) => updateFilter('status', value)"
              >
                <SelectTrigger class="mt-1.5 w-full border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900">
                  <SelectValue placeholder="All Status" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label htmlFor="type" class="text-sm font-medium text-slate-700 dark:text-slate-300">Type</Label>
              <Select
                :model-value="searchForm.type || 'all'"
                @update:model-value="(value) => updateFilter('type', value)"
              >
                <SelectTrigger class="mt-1.5 w-full border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900">
                  <SelectValue placeholder="All Types" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="company">Company</SelectItem>
                  <SelectItem value="purchased">Purchased</SelectItem>
                  <SelectItem value="available">Available</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Clear Filters and Results Summary -->
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 space-y-3 sm:space-y-0">
            <div v-if="templateCount > 0" class="order-2 sm:order-1">
              <p class="text-xs text-slate-600 dark:text-slate-400">
                Showing <span class="font-medium text-slate-900 dark:text-slate-100">{{ templates.meta.from }}</span> to
                <span class="font-medium text-slate-900 dark:text-slate-100">{{ templates.meta.to }}</span> of
                <span class="font-medium text-slate-900 dark:text-slate-100">{{ templates.meta.total }}</span> templates
                <span v-if="hasActiveFilters" class="text-blue-600 dark:text-blue-400"> (filtered)</span>
              </p>
            </div>

            <div v-if="hasActiveFilters" class="order-1 sm:order-2">
              <Button variant="outline" @click="clearFilters" class="border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 w-full sm:w-auto">
                <X class="h-4 w-4 mr-2" />
                Clear Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Templates Grid -->
      <div v-if="hasTemplates" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <Card
          v-for="template in templates.data"
          :key="template.id"
          class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 flex flex-col"
        >
          <CardHeader class="pb-3 flex-shrink-0">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1 min-w-0">
                <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100 truncate">
                  {{ template.name }}
                </CardTitle>
                <p v-if="template.description" class="text-sm text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">
                  {{ template.description }}
                </p>
              </div>
              <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2 flex-shrink-0">
                <Badge :class="getStatusClasses(template)" class="text-xs whitespace-nowrap">
                  {{ template.is_active ? 'Active' : 'Inactive' }}
                </Badge>
                <Badge v-if="template.is_premium" variant="outline" class="text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-600 text-xs whitespace-nowrap">
                  <DollarSign class="h-3 w-3 mr-1" />
                  Premium
                </Badge>
              </div>
            </div>
          </CardHeader>

          <CardContent class="pt-0 flex-1 flex flex-col justify-between">
            <div class="space-y-3 flex-1">
              <!-- Template Info -->
              <div class="space-y-2">
                <div v-if="template.category" class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                  <FileText class="h-4 w-4 mr-2 text-slate-400 dark:text-slate-500 flex-shrink-0" />
                  <span class="truncate">{{ template.category }}</span>
                </div>
                <div v-if="template.formatted_price" class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                  <DollarSign class="h-4 w-4 mr-2 text-slate-400 dark:text-slate-500 flex-shrink-0" />
                  <span class="truncate">{{ template.formatted_price }}</span>
                </div>
                <div class="flex items-center text-sm text-slate-500 dark:text-slate-400">
                  <span class="truncate">Used in {{ template.contracts_count || 0 }} contracts</span>
                </div>
                <div class="flex items-center text-xs text-slate-400 dark:text-slate-500">
                  <span class="truncate">Created {{ formatDate(template.created_at) }}</span>
                </div>
              </div>

              <!-- Tags -->
              <div v-if="template.tags && template.tags.length > 0" class="flex flex-wrap gap-1">
                <Badge v-for="tag in template.tags.slice(0, 2)" :key="tag" variant="secondary"
                       class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                  {{ tag }}
                </Badge>
                <Badge v-if="template.tags.length > 2" variant="secondary"
                       class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                  +{{ template.tags.length - 2 }} more
                </Badge>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700 mt-3">
              <div class="flex items-center space-x-1 overflow-x-auto">
                <Button
                  v-if="template.actions && template.actions.can_view"
                  variant="ghost"
                  size="sm"
                  @click="viewTemplate(template)"
                  class="h-8 w-8 p-0 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 flex-shrink-0"
                  title="View Template"
                >
                  <Eye class="h-4 w-4" />
                </Button>

                <Button
                  v-if="template.actions && template.actions.can_edit"
                  variant="ghost"
                  size="sm"
                  @click="editTemplate(template)"
                  class="h-8 w-8 p-0 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 flex-shrink-0"
                  title="Edit Template"
                >
                  <Edit class="h-4 w-4" />
                </Button>

                <Button
                  variant="ghost"
                  size="sm"
                  @click="duplicateTemplate(template)"
                  class="h-8 w-8 p-0 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 flex-shrink-0"
                  title="Duplicate Template"
                >
                  <Copy class="h-4 w-4" />
                </Button>

                <Button
                  variant="ghost"
                  size="sm"
                  @click="exportPdf(template)"
                  class="h-8 w-8 p-0 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex-shrink-0"
                  title="Export PDF"
                >
                  <Download class="h-4 w-4" />
                </Button>

                <Button
                  v-if="template.actions && template.actions.can_delete"
                  variant="ghost"
                  size="sm"
                  @click="deleteTemplate(template)"
                  class="h-8 w-8 p-0 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 flex-shrink-0"
                  title="Delete Template"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <Card v-else class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
        <CardContent class="p-8">
          <EmptyState
            title="No templates found"
            :description="getEmptyStateDescription()"
            :icon="FileText"
            action-label="Create Your First Template"
            :action-icon="Plus"
            :action-handler="() => router.visit(route('contract-templates.create'))"
          >
            <template #actions>
              <Button @click="router.visit(route('contract-templates.create'))"
                     class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white mb-3 w-full sm:w-auto">
                <Plus class="h-4 w-4 mr-2" />
                Create Your First Template
              </Button>
              <div class="text-sm text-slate-500 dark:text-slate-400 text-center sm:text-left">
                <p>Start by creating a template with common contract terms and placeholders.</p>
              </div>
            </template>
          </EmptyState>
        </CardContent>
      </Card>      <!-- Pagination -->
      <div v-if="hasTemplates" class="flex justify-center px-4">
        <div class="w-full max-w-sm sm:max-w-none">
          <Pagination :links="templates.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
