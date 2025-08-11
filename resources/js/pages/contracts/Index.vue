<script setup lang="ts">
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import { Plus, FileText, Search, X } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import ContractsTable from '@/components/ContractsTable.vue'
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
import type { Contract } from '@/types'

interface Props {
  contracts: {
    data: Contract[]
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
  }
  filters?: {
    search?: string
    status?: string
  }
  stats: {
    total: number
    active: number
    draft: number
    expiring_soon: number
  }
}

const props = defineProps<Props>()

const searchForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
})

const debouncedSearch = debounce(() => {
  const formData = {
    search: searchForm.search,
    status: searchForm.status,
  }

  router.get(route('contracts.index'), formData, {
    preserveState: true,
    replace: true,
  })
}, 300)

const updateStatus = (value: any) => {
  const stringValue = value === 'all' ? '' : (value ? String(value) : '')
  searchForm.status = stringValue

  const formData = {
    search: searchForm.search,
    status: stringValue,
  }

  router.get(route('contracts.index'), formData, {
    preserveState: true,
    replace: true,
  })
}

const hasActiveFilters = computed(() => {
  return !!searchForm.search || !!searchForm.status
})

const clearFilters = () => {
  searchForm.search = ''
  searchForm.status = ''

  router.get(route('contracts.index'), {}, {
    preserveState: true,
    replace: true,
  })
}

const getEmptyStateDescription = () => {
  const hasFilters = searchForm.search || searchForm.status

  if (hasFilters) {
    return "No contracts match your current search criteria. Try adjusting your filters or search terms."
  }

  return "Start managing your advertising agreements by creating your first contract. Track clients, billboards, and payment terms all in one place."
}

const editContract = (contract: Contract) => {
  router.visit(route('contracts.edit', contract.uuid))
}

const viewContract = (contract: Contract) => {
  router.visit(route('contracts.show', contract.uuid))
}

const deleteContract = (contract: Contract) => {
  if (confirm('Are you sure you want to delete this contract?')) {
    router.delete(route('contracts.destroy', contract.uuid))
  }
}

const cancelContract = (contract: Contract) => {
  if (confirm('Are you sure you want to cancel this contract?')) {
    router.patch(route('contracts.update', contract.id), {
      status: 'cancelled'
    })
  }
}

const exportPdf = (contract: Contract) => {
  window.open(route('contracts.export-pdf', contract.uuid), '_blank')
}
</script>

<template>
  <AppLayout
    title="Contracts"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Contracts' }
    ]"
  >
    <div class="space-y-6 max-w-4xl">
      <!-- Header -->
      <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-start sm:space-y-0">
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Contract Management
          </h1>

          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage your advertising contracts and agreements
          </p>
          <div v-if="contracts.meta.total > 0" class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
            <span class="flex items-center">
              <div class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mr-1.5"></div>
              {{ stats.active }} Active
            </span>
            <span class="flex items-center">
              <div class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full mr-1.5"></div>
              {{ stats.draft }} Draft
            </span>
            <span class="flex items-center">
              <div class="w-2 h-2 bg-orange-500 dark:bg-orange-400 rounded-full mr-1.5"></div>
              {{ stats.expiring_soon }} Expiring Soon
            </span>
            <span class="flex items-center">
              <div class="w-2 h-2 bg-blue-500 dark:bg-blue-400 rounded-full mr-1.5"></div>
              {{ contracts.meta.total }} Total
            </span>
          </div>
        </div>
        <Button @click="router.visit(route('contracts.create'))" class="shadow-sm shrink-0">
          <Plus class="h-4 w-4" />
          New
        </Button>
      </div>

      <!-- Filters -->
      <Card class="shadow-sm">
        <CardContent class="p-4">
          <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
              <Label htmlFor="search" class="text-sm font-medium text-gray-700 dark:text-gray-300">Search Contracts</Label>
              <div class="relative mt-1">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 h-4 w-4" />
                <Input
                  id="search"
                  v-model="searchForm.search"
                  type="text"
                  placeholder="Search by contract number, client, or company..."
                  class="pl-10"
                  @input="debouncedSearch"
                />
              </div>
            </div>
            <div class="w-full sm:w-48">
              <Label htmlFor="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Status</Label>
              <Select
                :model-value="searchForm.status || 'all'"
                @update:model-value="updateStatus"
              >
                <SelectTrigger class="mt-1 w-full">
                  <SelectValue placeholder="All Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="draft">Draft</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                  <SelectItem value="expired">Expired</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="flex items-end">
              <Button
                variant="outline"
                @click="clearFilters"
                :disabled="!hasActiveFilters">
                <X class="h-4 w-4" />
                Clear
              </Button>
            </div>
          </div>

          <!-- Results Summary -->
          <div v-if="contracts.meta.total > 0" class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-600 dark:text-gray-400">
              Showing <span class="font-medium">{{ contracts.meta.from }}</span> to
              <span class="font-medium">{{ contracts.meta.to }}</span> of
              <span class="font-medium">{{ contracts.meta.total }}</span> contracts
              <span v-if="hasActiveFilters" class="text-blue-600 dark:text-blue-400"> (filtered)</span>
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Contracts Table -->
      <Card class="shadow-sm">
        <CardContent class="p-0">
          <div v-if="contracts.data.length > 0">
            <ContractsTable
              :contracts="contracts.data"
              @edit="editContract"
              @view="viewContract"
              @delete="deleteContract"
              @cancel="cancelContract"
              @export-pdf="exportPdf"
            />
          </div>
          <div v-else class="p-6">
            <EmptyState
              title="No contracts found"
              :description="getEmptyStateDescription()"
              :icon="FileText"
              action-label="Create Your First Contract"
              :action-icon="Plus"
              :action-handler="() => router.visit(route('contracts.create'))"
            >
              <template #actions>
                <Button @click="router.visit(route('contracts.create'))" class="mb-3">
                  <Plus class="h-4 w-4 mr-2" />
                  Create Your First Contract
                </Button>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  <p>Start managing your advertising agreements by creating your first contract.</p>
                </div>
              </template>
            </EmptyState>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="contracts.data.length > 0" class="flex justify-center">
        <Pagination :links="contracts.links" />
      </div>
    </div>
  </AppLayout>
</template>
