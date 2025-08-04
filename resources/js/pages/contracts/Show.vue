<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
  FileText,
  Edit,
  MoreHorizontal,
  Download,
  Copy,
  X,
  Trash2,
  User,
  Calendar,
  MapPin,
  DollarSign,
  Building
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger
} from '@/components/ui/dropdown-menu'
import type { Contract } from '@/types'

interface Props {
  contract: {
    data: Contract
  }
}

const props = defineProps<Props>()

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStatusClasses = (color: string): string => {
  const classes = {
    green: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
    blue: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
    yellow: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
    red: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    gray: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
    orange: 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
    purple: 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200'
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

const exportContract = () => {
  // Open the contract PDF export endpoint
  window.open(route('contracts.export-pdf', props.contract.data.uuid), '_blank')
}

const duplicateContract = () => {
  router.visit(route('contracts.create'), {
    data: { duplicate_from: props.contract.data.uuid }
  })
}

const cancelContract = () => {
  if (confirm('Are you sure you want to cancel this contract?')) {
    router.patch(route('contracts.update', props.contract.data.uuid), {
      status: 'cancelled'
    })
  }
}

const deleteContract = () => {
  if (confirm('Are you sure you want to delete this contract? This action cannot be undone.')) {
    router.delete(route('contracts.destroy', props.contract.data.uuid))
  }
}

const viewDocument = () => {
  router.visit(route('contracts.document.show', props.contract.data.uuid))
}
</script>

<template>
  <AppLayout
    :title="`Contract ${contract.data.contract_number}`"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Contracts', href: route('contracts.index') },
      { label: contract.data.contract_number }
    ]">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-start sm:space-y-0">
        <div class="flex-1">
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
              <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>

            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contract {{ contract.data.contract_number }}</h1>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Created {{ formatDate(contract.data.created_at) }} by {{ contract.data.created_by.name }}
              </p>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <!-- Status Badge -->
          <span
            :class="getStatusClasses(contract.data.status.color)"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
          >
            <div
              :class="getStatusDotClasses(contract.data.status.color)"
              class="w-2 h-2 rounded-full mr-2"
            ></div>
            {{ contract.data.status.label }}
          </span>

          <!-- Action Buttons -->
          <Button
            v-if="contract.data.actions.can_edit"
            variant="outline"
            @click="router.visit(route('contracts.edit', contract.data.uuid))"
          >
            <Edit class="h-4 w-4 mr-2" />
            Edit Contract
          </Button>

          <Button
            variant="outline"
            @click="() => router.visit(route('contracts.document.show', contract.data.uuid), {
              preserveScroll: true
            })">
            <FileText class="h-4 w-4 mr-2" />
            View Document
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="sm">
                <MoreHorizontal class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem @click="exportContract">
                <Download class="mr-2 h-4 w-4" />
                Export PDF
              </DropdownMenuItem>
              <DropdownMenuItem @click="duplicateContract">
                <Copy class="mr-2 h-4 w-4" />
                Duplicate Contract
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                v-if="contract.data.actions.can_cancel"
                @click="cancelContract"
                class="text-orange-600"
              >
                <X class="mr-2 h-4 w-4" />
                Cancel Contract
              </DropdownMenuItem>
              <DropdownMenuItem
                v-if="contract.data.actions.can_delete"
                @click="deleteContract"
                class="text-red-600"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Delete Contract
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Client Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <User class="h-5 w-5 mr-2" />
                Client Information
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Client Name</label>
                  <p class="text-sm text-gray-900">{{ contract.data.client.name }}</p>
                </div>
                <div v-if="contract.data.client.company">
                  <label class="text-sm font-medium text-gray-500">Company</label>
                  <p class="text-sm text-gray-900">{{ contract.data.client.company }}</p>
                </div>
                <div v-if="contract.data.client.email">
                  <label class="text-sm font-medium text-gray-500">Email</label>
                  <p class="text-sm text-gray-900">{{ contract.data.client.email }}</p>
                </div>
                <div v-if="contract.data.client.phone">
                  <label class="text-sm font-medium text-gray-500">Phone</label>
                  <p class="text-sm text-gray-900">{{ contract.data.client.phone }}</p>
                </div>
              </div>
              <div v-if="contract.data.client.address">
                <label class="text-sm font-medium text-gray-500">Address</label>
                <p class="text-sm text-gray-900">{{ contract.data.client.address }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Contract Details -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Calendar class="h-5 w-5 mr-2" />
                Contract Details
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Start Date</label>
                  <p class="text-sm text-gray-900">{{ formatDate(contract.data.dates.start_date) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">End Date</label>
                  <p class="text-sm text-gray-900">{{ formatDate(contract.data.dates.end_date) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Duration</label>
                  <p class="text-sm text-gray-900">{{ Math.round(contract.data.dates.duration_months) }} months</p>
                </div>
                <div v-if="contract.data.dates.signed_at">
                  <label class="text-sm font-medium text-gray-500">Signed Date</label>
                  <p class="text-sm text-gray-900">{{ formatDate(contract.data.dates.signed_at) }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Billboards -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <MapPin class="h-5 w-5 mr-2" />
                Billboards ({{ contract.data.billboards.length }})
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div
                  v-for="billboard in contract.data.billboards"
                  :key="billboard.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <div class="flex items-center space-x-2">
                        <h4 class="text-sm font-medium text-gray-900">{{ billboard.name }}</h4>
                        <Badge variant="outline">{{ billboard.code }}</Badge>
                      </div>
                      <p class="text-xs text-gray-600 mt-1">{{ billboard.location }}</p>
                      <div v-if="billboard.dimensions" class="text-xs text-gray-500 mt-1">
                        Dimensions: {{ billboard.dimensions }}
                      </div>
                      <div v-if="billboard.notes" class="text-xs text-gray-600 mt-2">
                        <span class="font-medium">Notes:</span> {{ billboard.notes }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm font-medium text-gray-900">{{ billboard.formatted_rate }}</div>
                      <div class="text-xs text-gray-500">per month</div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Notes -->
          <Card v-if="contract.data.notes">
            <CardHeader>
              <CardTitle class="flex items-center">
                <FileText class="h-5 w-5 mr-2" />
                Notes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ contract.data.notes }}</p>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Financial Summary -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <DollarSign class="h-5 w-5 mr-2" />
                Financial Summary
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Monthly Amount:</span>
                  <span class="text-sm font-medium text-gray-900">{{ contract.data.financial.formatted_monthly }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Total Value:</span>
                  <span class="text-lg font-bold text-gray-900">{{ contract.data.financial.formatted_total }}</span>
                </div>
                <Separator />
                <div class="space-y-2">
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Payment Terms:</span>
                    <span class="text-gray-700 capitalize">{{ contract.data.terms.payment_terms }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Payment Days:</span>
                    <span class="text-gray-700">{{ contract.data.terms.payment_terms_days }} days</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Currency:</span>
                    <span class="text-gray-700">{{ contract.data.financial.currency }}</span>
                  </div>
                  <div v-if="contract.data.financial.currency_converted" class="flex justify-between text-xs">
                    <span class="text-gray-500">Exchange Rate:</span>
                    <span class="text-gray-700">{{ contract.data.financial.exchange_rate }}</span>
                  </div>
                  <div v-if="contract.data.terms.late_fee_percentage && parseFloat(contract.data.terms.late_fee_percentage) > 0" class="flex justify-between text-xs">
                    <span class="text-gray-500">Late Fee:</span>
                    <span class="text-gray-700">{{ contract.data.terms.late_fee_percentage }}%</span>
                  </div>
                  <div v-if="contract.data.terms.tax_rate && parseFloat(contract.data.terms.tax_rate) > 0" class="flex justify-between text-xs">
                    <span class="text-gray-500">Tax Rate:</span>
                    <span class="text-gray-700">{{ contract.data.terms.tax_rate }}%</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Company Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Building class="h-5 w-5 mr-2" />
                Company
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <p class="text-sm font-medium text-gray-900">{{ contract.data.company.name }}</p>
                <div class="text-xs text-gray-500 space-y-1">
                  <div>Currency: {{ contract.data.company.currency }}</div>
                  <div>Timezone: {{ contract.data.company.timezone }}</div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions -->
          <Card>
            <CardHeader>
              <CardTitle class="text-sm">Quick Actions</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Button
                v-if="contract.data.actions.can_edit"
                variant="outline"
                size="sm"
                class="w-full justify-start"
                @click="router.visit(route('contracts.edit', contract.data.uuid))"
              >
                <Edit class="h-4 w-4 mr-2" />
                Edit Contract
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="w-full justify-start"
                @click="viewDocument"
              >
                <FileText class="h-4 w-4 mr-2" />
                View Document
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="w-full justify-start"
                @click="exportContract"
              >
                <Download class="h-4 w-4 mr-2" />
                Export PDF
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="w-full justify-start"
                @click="duplicateContract"
              >
                <Copy class="h-4 w-4 mr-2" />
                Duplicate Contract
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
