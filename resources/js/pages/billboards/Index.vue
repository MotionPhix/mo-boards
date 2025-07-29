<template>
  <AppLayout title="Billboards">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-8 space-y-4 lg:space-y-0">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Billboard Management</h1>
            <p class="mt-2 text-gray-600">
              Manage your outdoor advertising inventory
            </p>
            <div v-if="billboards.total > 0" class="mt-3 flex flex-wrap gap-4 text-sm text-gray-500">
              <span class="flex items-center">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                {{ getActiveCount() }} Active
              </span>
              <span class="flex items-center">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                {{ getOccupiedCount() }} Occupied
              </span>
              <span class="flex items-center">
                <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                {{ billboards.total }} Total
              </span>
            </div>
          </div>
          <Button @click="router.visit(route('billboards.create'))" size="lg" class="shadow-sm">
            <Plus class="h-4 w-4 mr-2" />
            Add Billboard
          </Button>
        </div>

        <!-- Company Selector -->
        <CompanySelector
          :companies="(page.props.companies as Company[])"
          :current-company="company"
          class="mb-6"
        />

        <!-- Filters -->
        <Card class="mb-6 shadow-sm">
          <CardContent class="p-6">
            <div class="flex flex-col lg:flex-row gap-4">
              <div class="flex-1">
                <Label htmlFor="search" class="text-sm font-medium text-gray-700">Search Billboards</Label>
                <div class="relative mt-1">
                  <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                  <Input
                    id="search"
                    v-model="searchForm.search"
                    type="text"
                    placeholder="Search by name, code, or location..."
                    class="pl-10"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
              <div class="w-full lg:w-48">
                <Label htmlFor="status" class="text-sm font-medium text-gray-700">Filter by Status</Label>
                <Select
                  :model-value="searchForm.status"
                  @update:model-value="updateStatus"
                >
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="All Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="option in billboardStatusOptions"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="flex items-end">
                <Button
                  variant="outline"
                  @click="clearFilters"
                  class="h-10"
                  :disabled="!hasActiveFilters"
                >
                  <X class="h-4 w-4 mr-2" />
                  Clear
                </Button>
              </div>
            </div>

            <!-- Results Summary -->
            <div v-if="billboards.total > 0" class="mt-4 pt-4 border-t border-gray-100">
              <p class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ billboards.meta.from }}</span> to
                <span class="font-medium">{{ billboards.meta.to }}</span> of
                <span class="font-medium">{{ billboards.meta.total }}</span> billboards
                <span v-if="hasActiveFilters" class="text-blue-600"> (filtered)</span>
              </p>
            </div>
          </CardContent>
        </Card>

        <!-- Billboards Table -->
        <Card>
          <CardContent class="p-0">
            <div v-if="billboards.data.length > 0">
              <BillboardsTable
                :billboards="billboards.data"
                @edit="editBillboard"
                @view="viewBillboard"
                @delete="deleteBillboard"
              />
            </div>
            <div v-else class="p-6">
              <EmptyState
                title="No billboards found"
                :description="getEmptyStateDescription()"
                :icon="Building2"
                action-label="Add Your First Billboard"
                :action-icon="Plus"
                :action-handler="() => router.visit(route('billboards.create'))"
              >
                <template #actions>
                  <Button @click="router.visit(route('billboards.create'))" class="mb-3">
                    <Plus class="h-4 w-4 mr-2" />
                    Add Your First Billboard
                  </Button>
                  <div class="text-sm text-gray-500">
                    <p>Get started by adding billboard locations to your inventory.</p>
                  </div>
                </template>
              </EmptyState>
            </div>
          </CardContent>
        </Card>

        <!-- Pagination -->
        <div v-if="billboards.data.length > 0" class="mt-6">
          <Pagination :links="billboards.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import { Plus, Building2, Search, X } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySelector from '@/components/CompanySelector.vue'
import BillboardsTable from '@/components/BillboardsTable.vue'
import EmptyState from '@/components/EmptyState.vue'
import Pagination from '@/components/Pagination.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { useSelectOptions } from '@/composables/useSelectOptions'
import type { Billboard, Company } from '@/types'

interface Props {
  billboards: {
    data: Billboard[]
    links: any[]
    meta: {
      current_page: number
      from: number
      last_page: number
      links: any[]
      path: string
      per_page: number
      to: number
      total: number
    }
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  company: Company
  filters: {
    search?: string
    status?: string
  }
}

const props = defineProps<Props>()

const page = usePage()

const { billboardStatusOptions, toBackendValue, toFrontendValue } = useSelectOptions()

const searchForm = reactive({
  search: props.filters.search || '',
  status: toFrontendValue(props.filters.status),
})

const debouncedSearch = debounce(() => {
  const formData = {
    search: searchForm.search,
    status: toBackendValue(searchForm.status),
  }

  router.get(route('billboards.index'), formData, {
    preserveState: true,
    replace: true,
  })
}, 300)

const updateStatus = (value: any) => {
  const stringValue = value ? String(value) : ''
  searchForm.status = stringValue

  const formData = {
    search: searchForm.search,
    status: toBackendValue(stringValue),
  }

  router.get(route('billboards.index'), formData, {
    preserveState: true,
    replace: true,
  })
}

const editBillboard = (billboard: Billboard) => {
  router.visit(route('billboards.edit', billboard.id))
}

const viewBillboard = (billboard: Billboard) => {
  router.visit(route('billboards.show', billboard.id))
}

const deleteBillboard = (billboard: Billboard) => {
  if (confirm('Are you sure you want to delete this billboard?')) {
    router.delete(route('billboards.destroy', billboard.id))
  }
}

const getEmptyStateDescription = () => {
  const hasFilters = searchForm.search || searchForm.status

  if (hasFilters) {
    return "No billboards match your current search criteria. Try adjusting your filters or search terms."
  }

  return "Start building your billboard inventory by adding your first location. You can manage pricing, status, and track occupancy for each billboard."
}

const hasActiveFilters = computed(() => {
  return !!searchForm.search || !!searchForm.status
})

const clearFilters = () => {
  searchForm.search = ''
  searchForm.status = ''

  router.get(route('billboards.index'), {}, {
    preserveState: true,
    replace: true,
  })
}

const getActiveCount = () => {
  return props.billboards.data.filter((billboard: Billboard) => billboard.status.current === 'active').length
}

const getOccupiedCount = () => {
  return props.billboards.data.filter((billboard: Billboard) => billboard.contracts.is_occupied).length
}
</script>
