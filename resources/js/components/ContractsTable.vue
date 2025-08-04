<script setup lang="ts">
import {
  FileText,
  Eye,
  Pencil as Edit,
  X,
  Trash2,
  Download
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import type { Contract } from '@/types'

interface Props {
  contracts: Contract[]
}

defineProps<Props>()

defineEmits<{
  view: [contract: Contract]
  edit: [contract: Contract]
  delete: [contract: Contract]
  cancel: [contract: Contract]
  exportPdf: [contract: Contract]
}>()

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClasses = (color: string): string => {
  const classes = {
    green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    gray: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    orange: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
  }

  return classes[color as keyof typeof classes] || classes.gray
}

const getStatusDotClasses = (color: string): string => {
  const classes = {
    green: 'bg-green-400',
    blue: 'bg-blue-400',
    yellow: 'bg-yellow-400',
    red: 'bg-red-400',
    gray: 'bg-gray-400',
    orange: 'bg-orange-400',
    purple: 'bg-purple-400'
  }

  return classes[color as keyof typeof classes] || classes.gray
}
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Contract
          </th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Client
          </th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Period
          </th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Amount
          </th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Status
          </th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
        <tr
          v-for="contract in contracts"
          :key="contract.id"
          class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150"
        >
          <!-- Contract Info -->
          <td class="px-4 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                <FileText class="h-4 w-4 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="ml-3">
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ contract.contract_number }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ contract.billboards.length }} billboard{{ contract.billboards.length !== 1 ? 's' : '' }}
                </div>
              </div>
            </div>
          </td>

          <!-- Client Info -->
          <td class="px-4 py-4 whitespace-nowrap">
            <div>
              <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ contract.client.name }}
              </div>
              <div v-if="contract.client.company" class="text-xs text-gray-500 dark:text-gray-400">
                {{ contract.client.company }}
              </div>
              <div v-if="contract.client.email" class="text-xs text-gray-500 dark:text-gray-400">
                {{ contract.client.email }}
              </div>
            </div>
          </td>

          <!-- Period -->
          <td class="px-4 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900 dark:text-white">
              {{ formatDate(contract.dates.start_date) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              to {{ formatDate(contract.dates.end_date) }}
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-500">
              {{ Math.round(contract.dates.duration_months) }} months
            </div>
          </td>

          <!-- Amount -->
          <td class="px-4 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900 dark:text-white">
              {{ contract.financial.formatted_total }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              {{ contract.financial.formatted_monthly }}/month
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-500 capitalize">
              {{ contract.terms.payment_terms }}
            </div>
          </td>

          <!-- Status -->
          <td class="px-4 py-4 whitespace-nowrap">
            <span
              :class="getStatusClasses(contract.status.color)"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            >
              <div
                :class="getStatusDotClasses(contract.status.color)"
                class="w-1.5 h-1.5 rounded-full mr-1.5"
              ></div>
              {{ contract.status.label }}
            </span>
          </td>

          <!-- Actions -->
          <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center justify-end space-x-2">
              <Button
                v-if="contract.actions.can_view"
                variant="ghost"
                size="sm"
                @click="$emit('view', contract)"
                class="h-8 w-8 p-0"
                title="View Contract"
              >
                <Eye class="h-4 w-4" />
              </Button>

              <Button
                v-if="contract.actions.can_view"
                variant="ghost"
                size="sm"
                @click="$emit('exportPdf', contract)"
                class="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                title="Download PDF"
              >
                <Download class="h-4 w-4" />
              </Button>

              <Button
                v-if="contract.actions.can_edit"
                variant="ghost"
                size="sm"
                @click="$emit('edit', contract)"
                class="h-8 w-8 p-0"
                title="Edit Contract"
              >
                <Edit class="h-4 w-4" />
              </Button>

              <Button
                v-if="contract.actions.can_cancel"
                variant="ghost"
                size="sm"
                @click="$emit('cancel', contract)"
                class="h-8 w-8 p-0 text-orange-600 hover:text-orange-700"
                title="Cancel Contract"
              >
                <X class="h-4 w-4" />
              </Button>

              <Button
                v-if="contract.actions.can_delete"
                variant="ghost"
                size="sm"
                @click="$emit('delete', contract)"
                class="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                title="Delete Contract"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
