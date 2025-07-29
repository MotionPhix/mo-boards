<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  SelectSeparator
} from '@/components/ui/select'
import type { Company } from '@/types'

interface Props {
  companies: Company[]
  currentCompany?: Company
}

const props = defineProps<Props>()

const handleCompanyChange = (value: string) => {
  if (value === 'new') {
    router.visit(route('companies.create'))
  } else {
    const companyId = parseInt(value)
    const company = props.companies.find(c => c.id === companyId)
    if (company) {
      router.post(route('companies.switch', company.id))
    }
  }
}
</script>

<template>
  <div class="flex flex-col space-y-2">
    <Label htmlFor="company-select" class="text-sm font-medium">
      Company
    </Label>
    <Select
      :model-value="currentCompany?.id?.toString()"
      @update:model-value="handleCompanyChange">
      <SelectTrigger class="w-[280px]">
        <SelectValue placeholder="Select a company" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem
          v-for="company in companies"
          :key="company.id"
          :value="company.id.toString()">
          {{ company.name }}
        </SelectItem>
        <SelectSeparator />
        <SelectItem value="new">
          <div class="flex items-center">
            <Plus class="h-4 w-4 mr-2" />
            Add New Company
          </div>
        </SelectItem>
      </SelectContent>
    </Select>
  </div>
</template>
