<script setup lang="ts">
import { ModalLink } from '@inertiaui/modal-vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Building2, List, Grid } from 'lucide-vue-next'
import { Card, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { Button } from '@/components/ui/button'
import CompanyCard from '@/components/companies/CompanyCard.vue'
import { useStorage } from '@vueuse/core'
import debounce from 'lodash/debounce'
import { Label } from '@/components/ui/label'

const props = defineProps<{ companies: any; currentCompany: any | null; canCreateCompany: boolean }>()
const ziggyRoute = (window as any).route as (...args: any[]) => string

// UI state: view mode, search and simple plan filter
const viewMode = useStorage<'grid' | 'list'>('company_view_mode', 'grid')
const q = ref(route().params.q ?? '')
const planFilter = ref<'all' | string>(route().params.plan ?? 'all')
const sort = ref<string>(route().params.sort ?? 'name')
const direction = ref<'asc' | 'desc'>(route().params.direction ?? 'asc')
const perPage = ref<number>(Number(route().params.per_page ?? 12))

// Selection for bulk actions
const selected = ref<Record<string, boolean>>({})
const selectAll = ref(false)

function toggleSelect(id: string) {
  selected.value[id] = !selected.value[id]
}

const filteredCompanies = computed(() => {
  // When server-side pagination is used, props.companies.data contains items
  const list = Array.isArray(props.companies?.data) ? props.companies.data : (props.companies || [])
  const term = q.value.trim().toLowerCase()

  return list.filter((c: any) => {
    if (planFilter.value !== 'all' && (c.subscription_plan || 'free') !== planFilter.value) {
      return false
    }

    if (!term) return true
    return (
      (c.name || '').toLowerCase().includes(term) ||
      (c.industry || '').toLowerCase().includes(term)
    )
  })
})

function toggleSelectAll() {
  selectAll.value = !selectAll.value
  // Use filteredCompanies computed property to respect current filters
  filteredCompanies.value.forEach((c: any) => {
    selected.value[c.id] = selectAll.value
  })
}

function selectedIds() {
  return Object.keys(selected.value).filter(k => selected.value[k])
}

function runBulkDelete() {
  const ids = selectedIds()
  if (ids.length === 0) return

  // submit via form POST to bulk-destroy route
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = ziggyRoute('companies.bulk-destroy')

  const method = document.createElement('input')
  method.type = 'hidden'
  method.name = '_method'
  method.value = 'DELETE'
  form.appendChild(method)

  const csrf = document.createElement('input')
  csrf.type = 'hidden'
  csrf.name = '_token'
  csrf.value = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
  form.appendChild(csrf)

  const idsField = document.createElement('input')
  idsField.type = 'hidden'
  idsField.name = 'ids[]'
  ids.forEach(id => {
    const f = idsField.cloneNode() as HTMLInputElement
    f.value = id
    form.appendChild(f)
  })

  document.body.appendChild(form)
  form.submit()
}

// Watch sort/direction/perPage/filters and reload with new query params

function buildQuery() {
  const params: Record<string, any> = {}
  if (q.value) params.q = q.value
  if (planFilter.value && planFilter.value !== 'all') params.plan = planFilter.value
  if (sort.value) params.sort = sort.value
  if (direction.value) params.direction = direction.value
  if (perPage.value) params.per_page = perPage.value
  return new URLSearchParams(params).toString()
}

watch([sort, direction, perPage, planFilter], () => {
  const qs = buildQuery()
  router.visit(window.location.pathname + (qs ? `?${qs}` : ''), {
    preserveState: true,
    preserveScroll: true
  })
})

// Debounced search watcher (lodash)
const debouncedSearch = debounce(() => {
  const qs = buildQuery()
  router.visit(window.location.pathname + (qs ? `?${qs}` : ''), {
    preserveState: true,
    preserveScroll: true
  })
}, 400)

watch(q, () => {
  debouncedSearch()
})

// Watch selected state to update selectAll
watch(selected, () => {
  if (!filteredCompanies.value.length) {
    selectAll.value = false
    return
  }

  // Check if all filtered companies are selected
  selectAll.value = filteredCompanies.value.every((c: any) => selected.value[c.id])
}, { deep: true })
</script>

<template>
  <AppLayout
    title="Companies"
    :breadcrumbs="[
      { label: 'Dashboard', href: ziggyRoute('dashboard') },
      { label: 'Companies', href: '#' }
    ]">
    <!-- Hero -->
    <Card class="mb-4 max-w-4xl">
      <CardHeader>
        <CardTitle>Companies</CardTitle>
        <CardDescription>
          Overview of your companies — manage settings, billing and members.
        </CardDescription>
      </CardHeader>
    </Card>

    <!-- Controls: search, filters, view toggle -->
    <section class="max-w-4xl">
      <div class="mb-6 mx-auto max-w-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-3 w-full sm:w-auto">
            <Input class="min-w-0 w-full" v-model="q" placeholder="Search companies" />

            <Select v-model="planFilter">
              <SelectTrigger class="min-w-[120px]">
                <SelectValue placeholder="All plans" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem value="all">All plans</SelectItem>
                <SelectItem value="free">Free</SelectItem>
                <SelectItem value="pro">Pro</SelectItem>
                <SelectItem value="business">Business</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- View Mode Toggle -->
          <div class="flex items-center gap-1 p-1 bg-muted rounded-lg">
            <Button
              type="button"
              role="tab"
              variant="ghost"
              size="sm"
              :class="viewMode === 'grid' ? 'bg-background shadow-sm' : ''"
              @click="viewMode = 'grid'">
              <Grid class="w-4 h-4" />
              <span class="sr-only">Grid</span>
            </Button>

            <Button
              type="button"
              role="tab"
              variant="ghost"
              size="sm"
              :class="viewMode === 'list' ? 'bg-background shadow-sm' : ''"
              @click="viewMode = 'list'">
              <List class="w-4 h-4" />
              <span class="sr-only">List</span>
            </Button>
          </div>

        </div>
      </div>
    </section>

    <section class="py-4 max-w-4xl">
      <div>
  <div v-if="!props.companies || (Array.isArray(props.companies) ? props.companies.length === 0 : props.companies.total === 0)" class="py-20 text-center">
          <div class="mx-auto max-w-md">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-muted text-muted-foreground mx-auto">
              <Building2 class="w-8 h-8" />
            </div>
            <h3 class="mt-6 text-lg font-medium">No companies yet</h3>
            <p class="mt-2 text-sm text-muted-foreground">Create your first company to get started managing billboards and contracts.</p>
            <div class="mt-6">
              <ModalLink :href="ziggyRoute('companies.create')" class="inline-flex items-center px-4 py-2 rounded-md border text-sm">Create your first company</ModalLink>
            </div>
          </div>
        </div>

        <div v-else>
          <!-- Bulk actions toolbar -->
          <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Checkbox :model-value="selectAll" @update:model-value="toggleSelectAll" aria-label="Select all" />
              <Button variant="outline" size="sm" @click="runBulkDelete" :disabled="selectedIds().length === 0">Delete selected</Button>
            </div>

            <div class="flex items-center gap-2">
              <Label>Sort:</Label>
              <Select v-model="sort">
                <SelectTrigger>
                  <SelectValue placeholder="Sort by..." />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="name">Name</SelectItem>
                  <SelectItem value="created_at">Created</SelectItem>
                  <SelectItem value="updated_at">Updated</SelectItem>
                  <SelectItem value="billboards_count">Billboards</SelectItem>
                </SelectContent>
              </Select>
              <Select v-model="direction">
                <SelectTrigger>
                  <SelectValue placeholder="Order..." />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="asc">Asc</SelectItem>
                  <SelectItem value="desc">Desc</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div v-if="viewMode === 'grid'" class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="c in filteredCompanies" :key="c.id">
              <CompanyCard :company="c" :currentCompany="props.currentCompany" view="grid" :selected="!!selected[c.id]" @toggle-select="toggleSelect" />
            </div>
          </div>

          <div v-else class="space-y-3">
            <div v-for="c in filteredCompanies" :key="c.id">
              <CompanyCard class="w-full" :company="c" :currentCompany="props.currentCompany" view="list" :selected="!!selected[c.id]" @toggle-select="toggleSelect" />
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-muted-foreground">
              Showing {{ props.companies.from }} to {{ props.companies.to }} of {{ props.companies.total }} companies
            </div>
            <div class="flex items-center gap-2">
              <button class="px-3 py-1 rounded-md border" :disabled="!props.companies.prev_page_url" @click.prevent="router.visit(props.companies.prev_page_url, { preserveState: true, preserveScroll: true })">Prev</button>
              <span class="text-sm">Page {{ props.companies.current_page }} / {{ props.companies.last_page }}</span>
              <button class="px-3 py-1 rounded-md border" :disabled="!props.companies.next_page_url" @click.prevent="router.visit(props.companies.next_page_url, { preserveState: true, preserveScroll: true })">Next</button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
