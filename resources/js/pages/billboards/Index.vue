<template>
  <AppLayout title="Billboards">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Billboard Management</h1>
            <p class="mt-2 text-gray-600">
              Manage your outdoor advertising inventory
            </p>
          </div>
          <Button @click="$inertia.visit(route('billboards.create'))">
            <Plus class="h-4 w-4 mr-2" />
            Add Billboard
          </Button>
        </div>

        <!-- Company Selector -->
        <CompanySelector
          :companies="$page.props.companies"
          :current-company="company"
          class="mb-6"
        />

        <!-- Filters -->
        <Card class="mb-6">
          <CardContent class="p-6">
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <Label htmlFor="search">Search</Label>
                <Input
                  id="search"
                  v-model="searchForm.search"
                  type="text"
                  placeholder="Search billboards..."
                  class="mt-1"
                  @input="debouncedSearch"
                />
              </div>
              <div class="w-full sm:w-48">
                <Label htmlFor="status">Status</Label>
                <Select
                  :model-value="searchForm.status"
                  @update:model-value="updateStatus"
                >
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="All Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All Status</SelectItem>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="inactive">Inactive</SelectItem>
                    <SelectItem value="maintenance">Maintenance</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Billboards Table -->
        <Card>
          <CardContent class="p-0">
            <BillboardsTable
              :billboards="billboards.data"
              @edit="editBillboard"
              @view="viewBillboard"
              @delete="deleteBillboard"
            />
          </CardContent>
        </Card>

        <!-- Pagination -->
        <div class="mt-6">
          <Pagination :links="billboards.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import { Plus } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySelector from '@/components/CompanySelector.vue'
import BillboardsTable from '@/components/BillboardsTable.vue'
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
import type { Billboard, Company } from '@/types'

interface Props {
  billboards: {
    data: Billboard[]
    links: any[]
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

const searchForm = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
})

const debouncedSearch = debounce(() => {
  router.get(route('billboards.index'), searchForm, {
    preserveState: true,
    replace: true,
  })
}, 300)

const updateStatus = (value: string) => {
  searchForm.status = value
  router.get(route('billboards.index'), searchForm, {
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
</script>
