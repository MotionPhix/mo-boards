<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field'
import { Save, Loader2, Download, Plus, Trash2 } from 'lucide-vue-next'
import DocumentEditor from '@/components/DocumentEditor.vue'
import PlaceholderPreview from '@/components/PlaceholderPreview.vue'
import { ref, computed, watch } from 'vue'
import { Separator } from '@/components/ui/separator'

type Contract = {
  id: number
  uuid: string
  contract_number: string
  design?: string
  notes?: string
  created_at: string
  updated_at: string
  client: {
    name: string
    email?: string | null
    phone?: string | null
    company?: string | null
    address?: string | null
  }
  dates: {
    start_date: string
    end_date: string
    duration_months?: number | null
  }
  terms: {
    payment_terms: 'monthly' | 'quarterly' | 'semi_annual' | 'annual' | 'one_time'
  }
  financial: {
    currency: string
    formatted_monthly?: string | null
    formatted_total?: string | null
  }
  billboards: Array<{
    id: number
    code?: string
    name: string
    location?: string
    rate?: number
    notes?: string | null
    formatted_rate?: string
  }>
  template?: {
    id: number
    name: string
  } | null
}

type BillboardOption = { id: number; name: string; code?: string; location?: string; monthly_rate?: number }
type Option = { value: string; label: string }
type Template = { id: number; name: string; description?: string; content: string }
type PlaceholderCatalog = Record<string, Record<string, string>>

interface Props {
  // Accept either { contract: Contract } or { contract: { data: Contract } }
  contract: Contract | { data: Contract }
  billboards: BillboardOption[]
  currencyOptions: Option[]
  templatesOwned?: Template[]
  templatesPurchased?: Template[]
  placeholdersCatalog?: PlaceholderCatalog
}

const props = defineProps<Props>()
const contractData = computed<Contract>(() => {
  const c: any = props.contract as any
  return (c && typeof c === 'object' && 'data' in c) ? (c.data as Contract) : (c as Contract)
})

const selectedPlaceholder = ref<string | null>(null)
// Build options from backend-provided catalog; fallback to a minimal set if absent
const placeholderOptions = computed(() => {
  const catalog = (props.placeholdersCatalog || {}) as PlaceholderCatalog
  const items: Array<{ value: string; label: string }> = []
  const groups = Object.keys(catalog)
  if (groups.length > 0) {
    groups.forEach(group => {
      const groupMap = catalog[group]
      Object.entries(groupMap).forEach(([placeholder, label]) => {
        items.push({ value: placeholder, label })
      })
    })
  } else {
    // Fallback minimal placeholders
    items.push(
      { value: '{{client_name}}', label: 'Client Name' },
      { value: '{{company_name}}', label: 'Company Name' },
      { value: '{{start_date}}', label: 'Start Date' },
      { value: '{{end_date}}', label: 'End Date' },
      { value: '{{total_amount}}', label: 'Total Amount' },
      { value: '{{monthly_amount}}', label: 'Monthly Amount' },
      { value: '{{payment_terms}}', label: 'Payment Terms' },
      { value: '{{billboards_table}}', label: 'Billboards Table' },
      { value: '{{billboards_list}}', label: 'Billboards List' },
    )
  }
  return items
})

const insertPlaceholder = () => {
  if (!selectedPlaceholder.value) return
  const event = new CustomEvent('insert-placeholder', { detail: { text: selectedPlaceholder.value } })
  document.dispatchEvent(event)
  selectedPlaceholder.value = null
}

// Template picker state
const selectedTemplateId = ref<number | null>(null)
const applyTemplateById = (id: number) => {
  const tpl = (props.templatesOwned || []).concat(props.templatesPurchased || []).find(t => t.id === id)
  if (!tpl) return
  // Replace the current design with the template content
  form.design = tpl.content || ''
  selectedTemplateId.value = id
}

// Local helper to compute months between dates (inclusive months like backend)
const monthsBetween = (start: string, end: string): number => {
  try {
    const s = new Date(start)
    const e = new Date(end)
    const months = (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth()) + 1
    return isNaN(months) ? 0 : Math.max(0, months)
  } catch {
    return 0
  }
}

const form = useForm({
  // Client
  client_name: contractData.value.client.name || '',
  client_email: contractData.value.client.email || '',
  client_phone: contractData.value.client.phone || '',
  client_company: contractData.value.client.company || '',
  client_address: contractData.value.client.address || '',
  // Dates
  start_date: contractData.value.dates.start_date || '',
  end_date: contractData.value.dates.end_date || '',
  // Terms
  payment_terms: contractData.value.terms.payment_terms || 'monthly',
  currency: contractData.value.financial.currency || 'USD',
  // Status
  status: (contractData.value as any).status?.current || 'draft',
  // Content
  design: contractData.value.design || '',
  content: contractData.value.design || '', // trigger server-side processing
  // Billboards
  billboards: (contractData.value.billboards || []).map(b => ({ id: b.id, rate: b.rate ?? 0, notes: b.notes ?? '' })),
  // Notes
  notes: contractData.value.notes || '',
})

const availableBillboards = computed(() => props.billboards || [])

const addBillboardId = ref<string>('')
const addSelectedBillboard = () => {
  if (!addBillboardId.value) return
  const id = parseInt(addBillboardId.value, 10)
  if (!id) return
  if (form.billboards.find(b => b.id === id)) {
    addBillboardId.value = ''
    return
  }
  const option = availableBillboards.value.find(b => b.id === id)
  if (!option) return
  form.billboards.push({ id, rate: option.monthly_rate ?? 0, notes: '' })
  addBillboardId.value = ''
}

const removeBillboard = (id: number) => {
  form.billboards = form.billboards.filter(b => b.id !== id)
}

const monthlyTotal = computed(() => form.billboards.reduce((sum, b) => sum + (Number(b.rate) || 0), 0))
const durationMonths = computed(() => monthsBetween(form.start_date, form.end_date))
const totalAmount = computed(() => monthlyTotal.value * durationMonths.value)
const noBillboards = computed(() => form.billboards.length === 0)

// If there are no billboards, force status to draft (mirrors backend rule)
watch(noBillboards, (val: boolean) => {
  if (val) form.status = 'draft'
})

const submit = () => {
  // Keep content in sync so server processes placeholders from latest design
  // (controller processes `content` into document_content)
  ;(form as any).content = (form as any).design
  form.put(route('contracts.update', contractData.value.uuid), {
    preserveScroll: true,
    onSuccess: () => {},
    onError: (errors) => {
      console.error('Validation errors:', errors)
    },
  })
}

// Preview handled via PlaceholderPreview modal component

const downloadPdf = () => {
  window.open(route('contracts.export-pdf', contractData.value.uuid), '_blank')
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <AppLayout
    :title="`Edit Contract: ${contractData.contract_number}`"
    :breadcrumbs="[
      { label: 'Contracts', href: route('contracts.index') },
      { label: contractData.contract_number, href: route('contracts.show', contractData.uuid) },
      { label: 'Edit' },
    ]"
  >
    <div class="py-8">
      <div class="max-w-5xl">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Contract</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Update the contract document content</p>
          </div>
          <div class="flex gap-3">
            <PlaceholderPreview :contract-uuid="contractData.uuid" :content="form.design" />
            <Button variant="outline" @click="downloadPdf">
              <Download class="w-4 h-4" />
              Download PDF
            </Button>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
              <!-- Client & Contract Info -->
              <Card>
                <CardHeader>
                  <CardTitle>Client & Contract</CardTitle>
                  <CardDescription>Update basic contract details</CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="text-sm font-medium">Client Name</label>
                      <Input v-model="form.client_name" placeholder="Client Name" />
                      <div v-if="form.errors.client_name" class="text-xs text-red-600 mt-1">{{ form.errors.client_name }}</div>
                    </div>
                    <div>
                      <label class="text-sm font-medium">Client Company</label>
                      <Input v-model="form.client_company" placeholder="Client Company" />
                    </div>
                    <div>
                      <label class="text-sm font-medium">Client Email</label>
                      <Input v-model="form.client_email" type="email" placeholder="email@example.com" />
                      <div v-if="form.errors.client_email" class="text-xs text-red-600 mt-1">{{ form.errors.client_email }}</div>
                    </div>
                    <div>
                      <label class="text-sm font-medium">Client Phone</label>
                      <Input v-model="form.client_phone" placeholder="+265..." />
                    </div>
                    <div class="md:col-span-2">
                      <label class="text-sm font-medium">Client Address</label>
                      <Textarea v-model="form.client_address" rows="2" placeholder="Street, City, Country" />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div>
                      <label class="text-sm font-medium">Start Date</label>
                      <Input v-model="form.start_date" type="date" />
                      <div v-if="form.errors.start_date" class="text-xs text-red-600 mt-1">{{ form.errors.start_date }}</div>
                    </div>

                    <div>
                      <label class="text-sm font-medium">End Date</label>
                      <Input v-model="form.end_date" type="date" />
                      <div v-if="form.errors.end_date" class="text-xs text-red-600 mt-1">{{ form.errors.end_date }}</div>
                    </div>

                    <div>
                      <label class="text-sm font-medium">Payment Terms</label>
                      <Select v-model="form.payment_terms">
                        <SelectTrigger class="w-full">
                          <SelectValue placeholder="Select terms" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="monthly">Monthly</SelectItem>
                          <SelectItem value="quarterly">Quarterly</SelectItem>
                          <SelectItem value="semi_annual">Semi-Annual</SelectItem>
                          <SelectItem value="annual">Annual</SelectItem>
                          <SelectItem value="one_time">One-Time</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div>
                      <label class="text-sm font-medium">Currency</label>
            <Select v-model="form.currency">
                        <SelectTrigger class="w-full">
                          <SelectValue placeholder="Select currency" />
                        </SelectTrigger>
                        <SelectContent>
              <SelectItem v-for="opt in props.currencyOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Billboards -->
              <Card>
                <CardHeader>
                  <div class="flex items-center justify-between">
                    <div>
                      <CardTitle>Billboards</CardTitle>
                      <CardDescription>Set monthly rate and optional notes per billboard</CardDescription>
                    </div>
                    <div class="flex gap-2 items-center">
                      <Select v-model="addBillboardId">
                        <SelectTrigger class="w-[220px]">
                          <SelectValue placeholder="Add billboard..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="b in availableBillboards" :key="b.id" :value="String(b.id)">
                            {{ b.code ? `${b.code} — ` : '' }}{{ b.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <Button type="button" size="sm" @click="addSelectedBillboard"><Plus class="h-4 w-4 mr-1" /> Add</Button>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <div v-if="form.billboards.length === 0" class="text-sm text-muted-foreground">No billboards added.</div>
                  <div v-else class="space-y-3">
                    <div
                      v-for="b in form.billboards"
                      :key="b.id"
                      class="border rounded-md p-3 flex items-start justify-between gap-3"
                    >
                      <div class="flex-1">
                        <div class="text-sm font-medium flex items-center gap-2">
                          <span>
                            {{ availableBillboards.find(x => x.id === b.id)?.name || 'Billboard' }}
                          </span>
                          <Badge variant="outline">{{ availableBillboards.find(x => x.id === b.id)?.code }}</Badge>
                        </div>
                        <div class="text-xs text-muted-foreground">
                          {{ availableBillboards.find(x => x.id === b.id)?.location }}
                        </div>
                        <div class="mt-2">
                          <label class="text-xs font-medium">Notes</label>
                          <Textarea v-model="b.notes" rows="2" />
                        </div>
                      </div>
                      <div class="w-56">
                        <label class="text-xs font-medium">Monthly Rate</label>
                        <NumberField
                          v-model="b.rate"
                          :format-options="{
                            style: 'currency',
                            currency: form.currency || 'USD',
                            currencyDisplay: 'code',
                            currencySign: 'accounting'
                          }"
                        >
                          <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                          </NumberFieldContent>
                        </NumberField>
                        <div class="flex justify-end mt-2">
                          <Button type="button" variant="destructive" size="icon" @click="removeBillboard(b.id)">
                            <Trash2 class="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Document Editor -->
              <Card>
                <CardHeader>
                  <div class="flex justify-between items-center">
                    <div>
                      <CardTitle>Contract Document</CardTitle>
                      <CardDescription>
                        Use placeholders to dynamically inject data into the document design
                      </CardDescription>
                    </div>
                    <div class="flex gap-2">
                      <Select v-model="selectedPlaceholder" @update:modelValue="insertPlaceholder">
                        <SelectTrigger class="w-[220px]">
                          <SelectValue placeholder="Insert placeholder..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="ph in placeholderOptions" :key="ph.value" :value="ph.value">
                            {{ ph.label }} — {{ ph.value }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <DocumentEditor v-model="form.design" />
                  <div class="mt-1 text-xs text-muted-foreground">
                    Tip: Use {{ '{' }}{{ '{' }}billboards_table{{ '}' }}{{ '}' }} for a table layout, or {{ '{' }}{{ '{' }}billboards_list{{ '}' }}{{ '}' }} for a compact list.
                  </div>
                  <div v-if="form.errors.design" class="mt-1 text-sm text-red-600">{{ form.errors.design }}</div>
                </CardContent>
              </Card>

              <!-- Action Buttons -->
              <Card>
                <CardContent class="pt-6">
                  <div class="flex justify-end space-x-3">
                    <Button type="button" variant="outline" @click="router.visit(route('contracts.show', contractData.uuid))">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">
                      <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                      <Save v-else class="w-4 h-4 mr-2" />
                      {{ form.processing ? 'Saving...' : 'Save' }}
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
              <!-- Contract Info -->
              <Card>
                <CardHeader>
                  <CardTitle>Contract Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4 text-sm">
                  <div class="flex justify-between"><span class="text-muted-foreground">Contract #:</span> <span class="font-medium">{{ contractData.contract_number }}</span></div>
                  <div class="flex justify-between"><span class="text-muted-foreground">Created:</span> <span class="font-medium">{{ formatDate(contractData.created_at) }}</span></div>
                  <div class="flex justify-between"><span class="text-muted-foreground">Updated:</span> <span class="font-medium">{{ formatDate(contractData.updated_at) }}</span></div>

                  <Separator />

                    <div>
                      <label class="text-sm font-medium text-muted-foreground">
                        Monthly Total</label>
                      <div class="mt-2 text-sm font-medium">{{ monthlyTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                    </div>
                    <div>
                      <label class="text-muted-foreground text-sm font-medium">Total Amount (months: {{ durationMonths }})</label>
                      <div class="mt-2 text-sm font-semibold">{{ totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                    </div>
                </CardContent>
              </Card>

              <!-- Status Selector -->
              <Card>
                <CardHeader>
                  <CardTitle>Status</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                  <Select v-model="form.status" :disabled="noBillboards">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="draft">Draft</SelectItem>
                      <SelectItem value="pending">Pending</SelectItem>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="completed">Completed</SelectItem>
                      <SelectItem value="cancelled">Cancelled</SelectItem>
                    </SelectContent>
                  </Select>

                  <p v-if="noBillboards" class="text-xs text-muted-foreground">
                    Status locked to Draft when no billboards are attached.
                  </p>
                </CardContent>
              </Card>

              <!-- Template Info -->
              <Card v-if="contractData.template">
                <CardHeader>
                  <CardTitle>Source Template</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="space-y-2">
                    <h4 class="font-medium">{{ contractData.template?.name }}</h4>
                    <Badge variant="secondary" class="text-xs">Template-based</Badge>
                    <p class="text-sm text-muted-foreground">
                      Editing here won't change the original template.
                    </p>
                  </div>
                </CardContent>
              </Card>

              <!-- Template Picker -->
              <Card v-if="(props.templatesOwned && props.templatesOwned.length) || (props.templatesPurchased && props.templatesPurchased.length)">
                <CardHeader>
                  <CardTitle>Templates</CardTitle>
                  <CardDescription>Apply a template to replace the current design</CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                  <div>
                    <label class="text-xs font-medium mb-1 block">My Company Templates</label>
                    <Select v-model="selectedTemplateId" @update:modelValue="(v:any) => applyTemplateById(Number(v))">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a company template" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="t in (props.templatesOwned || [])" :key="t.id" :value="String(t.id)">{{ t.name }}</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div v-if="props.templatesPurchased && props.templatesPurchased.length">
                    <label class="text-xs font-medium mb-1 block">Purchased Templates</label>
                    <Select @update:modelValue="(v:any) => applyTemplateById(Number(v))">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select a purchased template" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="t in props.templatesPurchased" :key="t.id" :value="String(t.id)">{{ t.name }}</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <p class="text-xs text-muted-foreground">Choosing a template will replace the current document design. You can still edit afterwards.</p>
                </CardContent>
              </Card>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
