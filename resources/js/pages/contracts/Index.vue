<template>
  <div class="p-6">
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-900">Contracts</h1>
        <Link
          :href="route('contracts.create')"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
        >
          Create Contract
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
          <div class="text-sm text-gray-600">Total</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
          <div class="text-sm text-gray-600">Active</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="text-2xl font-bold text-gray-600">{{ stats.draft }}</div>
          <div class="text-sm text-gray-600">Draft</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="text-2xl font-bold text-yellow-600">{{ stats.pending_approval }}</div>
          <div class="text-sm text-gray-600">Pending</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="text-2xl font-bold text-blue-600">{{ stats.approved }}</div>
          <div class="text-sm text-gray-600">Approved</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <div class="text-2xl font-bold text-red-600">{{ stats.expiring_soon }}</div>
          <div class="text-sm text-gray-600">Expiring</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <input
              v-model="searchForm.search"
              type="text"
              placeholder="Search contracts..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="search"
            />
          </div>
          <div class="w-full md:w-48">
            <select
              v-model="searchForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="search"
            >
              <option value="">All Status</option>
              <option value="draft">Draft</option>
              <option value="pending_approval">Pending Approval</option>
              <option value="approved">Approved</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <button
            @click="clearFilters"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Clear
          </button>
        </div>
      </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Contract
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Client
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Period
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Amount
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="contract in contracts.data" :key="contract.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <div class="text-sm font-medium text-gray-900">
                  {{ contract.contract_number }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ contract.billboards.length }} billboard(s)
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <div class="text-sm font-medium text-gray-900">
                  {{ contract.client_name }}
                </div>
                <div class="text-sm text-gray-500" v-if="contract.client_company">
                  {{ contract.client_company }}
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <div>{{ formatDate(contract.start_date) }}</div>
              <div class="text-gray-500">to {{ formatDate(contract.end_date) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                ${{ formatCurrency(contract.total_amount) }}
              </div>
              <div class="text-sm text-gray-500">
                ${{ formatCurrency(contract.monthly_amount) }}/month
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(contract.status)">
                  {{ getStatusLabel(contract.status) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <Link
                  :href="route('contracts.show', contract.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </Link>
                <Link
                  v-if="contract.status === 'draft'"
                  :href="route('contracts.edit', contract.id)"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Edit
                </Link>
                <button
                  v-if="canExport(contract)"
                  @click="exportPdf(contract)"
                  class="text-green-600 hover:text-green-900"
                >
                  Export
                </button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ contracts.from || 0 }} to {{ contracts.to || 0 }} of {{ contracts.total }} results
          </div>
          <div class="flex space-x-2">
            <Link
              v-for="link in contracts.links"
              :key="link.label"
              :href="link.url"
              :class="[
                'px-3 py-2 text-sm border rounded',
                link.active
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'

const props = defineProps({
  contracts: Object,
  filters: Object,
  stats: Object,
})

const searchForm = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
})

const search = debounce(() => {
  router.get(route('contracts.index'), {
    search: searchForm.search,
    status: searchForm.status,
  }, {
    preserveState: true,
    replace: true,
  })
}, 300)

const clearFilters = () => {
  searchForm.search = ''
  searchForm.status = ''
  router.get(route('contracts.index'))
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat().format(amount)
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800',
    pending_approval: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
    approved: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800',
    active: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
    completed: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800',
    cancelled: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800',
  }
  return classes[status] || classes.draft
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    pending_approval: 'Pending Approval',
    approved: 'Approved',
    active: 'Active',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }
  return labels[status] || 'Unknown'
}

const canExport = (contract) => {
  return ['approved', 'active', 'completed'].includes(contract.status)
}

const exportPdf = (contract) => {
  window.open(route('contracts.export-pdf', contract.id), '_blank')
}
</script>
