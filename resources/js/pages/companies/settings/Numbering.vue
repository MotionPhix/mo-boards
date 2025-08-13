<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
  company: Record<string, any>
  options: Record<string, any>
}
const props = defineProps<Props>()
// Local Ziggy wrapper returning a string for templates
const r = (name: string, params?: Record<string, any>, absolute = false): string =>
  ((window as any).route?.(name, params, absolute) as string) ?? '#'

const form = useForm({
  section: 'numbering',
  contract_number_prefix: props.company?.contract_number_prefix || '',
  contract_number_suffix: props.company?.contract_number_suffix || '',
  contract_number_format: props.company?.contract_number_format || 'sequential',
  contract_number_length: props.company?.contract_number_length || 6,
  contract_number_start: props.company?.contract_number_start || 1,
  billboard_code_prefix: props.company?.billboard_code_prefix || '',
  billboard_code_suffix: props.company?.billboard_code_suffix || '',
  billboard_code_format: props.company?.billboard_code_format || 'sequential',
  billboard_code_length: props.company?.billboard_code_length || 4,
  billboard_code_start: props.company?.billboard_code_start || 1,
})

// Helper function to pad numbers with zeros (matching backend logic)
const padNumber = (num: number, length: number): string => {
  return num.toString().padStart(length, '0')
}

// Helper function to generate date-based numbers (matching backend logic exactly)
const formatDateBasedNumber = (num: number, length: number): string => {
  const now = new Date()
  const yearMonth = now.getFullYear().toString() + (now.getMonth() + 1).toString().padStart(2, '0')
  const remainingLength = Math.max(1, length - 6) // Reserve 6 digits for YYYYMM
  const paddedNumber = padNumber(num, remainingLength)
  return yearMonth + paddedNumber
}

// Computed property for contract number preview (matching backend logic)
const contractNumberPreview = computed(() => {
  const prefix = form.contract_number_prefix || ''
  const suffix = form.contract_number_suffix || ''
  const length = form.contract_number_length || 6
  const start = form.contract_number_start || 1
  const format = form.contract_number_format || 'sequential'

  let formattedNumber = ''

  switch (format) {
    case 'sequential':
      formattedNumber = padNumber(start, length)
      break
    case 'date_based':
      formattedNumber = formatDateBasedNumber(start, length)
      break
    case 'custom':
      formattedNumber = padNumber(start, length) // Falls back to sequential
      break
    default:
      formattedNumber = padNumber(start, length)
  }

  return `${prefix}${formattedNumber}${suffix}`
})

// Computed property for billboard code preview (matching backend logic)
const billboardCodePreview = computed(() => {
  const prefix = form.billboard_code_prefix || ''
  const suffix = form.billboard_code_suffix || ''
  const length = form.billboard_code_length || 4
  const start = form.billboard_code_start || 1
  const format = form.billboard_code_format || 'sequential'

  let formattedCode = ''

  switch (format) {
    case 'sequential':
      formattedCode = padNumber(start, length)
      break
    case 'location_based':
      // Note: Backend doesn't implement location logic, falls back to sequential
      formattedCode = padNumber(start, length)
      break
    case 'custom':
      formattedCode = padNumber(start, length) // Falls back to sequential
      break
    default:
      formattedCode = padNumber(start, length)
  }

  return `${prefix}${formattedCode}${suffix}`
})

// Generate example sequences for preview
const contractNumberExamples = computed(() => {
  const examples = []
  const baseStart = form.contract_number_start || 1

  for (let i = 0; i < 3; i++) {
    const prefix = form.contract_number_prefix || ''
    const suffix = form.contract_number_suffix || ''
    const length = form.contract_number_length || 6
    const format = form.contract_number_format || 'sequential'
    const currentNum = baseStart + i

    let formattedNumber = ''

    switch (format) {
      case 'sequential':
        formattedNumber = padNumber(currentNum, length)
        break
      case 'date_based':
        formattedNumber = formatDateBasedNumber(currentNum, length)
        break
      case 'custom':
        formattedNumber = padNumber(currentNum, length)
        break
      default:
        formattedNumber = padNumber(currentNum, length)
    }

    examples.push(`${prefix}${formattedNumber}${suffix}`)
  }

  return examples
})

const billboardCodeExamples = computed(() => {
  const examples = []
  const baseStart = form.billboard_code_start || 1

  for (let i = 0; i < 3; i++) {
    const prefix = form.billboard_code_prefix || ''
    const suffix = form.billboard_code_suffix || ''
    const length = form.billboard_code_length || 4
    const format = form.billboard_code_format || 'sequential'
    const currentNum = baseStart + i

    let formattedCode = ''

    switch (format) {
      case 'sequential':
        formattedCode = padNumber(currentNum, length)
        break
      case 'location_based':
        formattedCode = padNumber(currentNum, length) // Backend fallback
        break
      case 'custom':
        formattedCode = padNumber(currentNum, length)
        break
      default:
        formattedCode = padNumber(currentNum, length)
    }

    examples.push(`${prefix}${formattedCode}${suffix}`)
  }

  return examples
})

// Format descriptions for better UX
const getFormatDescription = (format: string, type: 'contract' | 'billboard'): string => {
  switch (format) {
    case 'sequential':
      return 'Sequential numbering (1, 2, 3...)'
    case 'date_based':
      return 'Year-month prefix + sequential (202501001, 202501002...)'
    case 'location_based':
      return type === 'billboard'
        ? 'Location-based numbering (currently same as sequential)'
        : 'Location-based numbering'
    case 'custom':
      return 'Custom format (currently same as sequential)'
    default:
      return 'Sequential numbering'
  }
}

// Show warning for formats that aren't fully implemented
const showFormatWarning = computed(() => {
  return form.billboard_code_format === 'location_based' ||
         form.contract_number_format === 'custom' ||
         form.billboard_code_format === 'custom'
})

const submit = () => {
  form.post(r('companies.settings.update'), { preserveScroll: true })
}
</script>

<template>
  <AppLayout
    title="Company Settings — Numbering"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Settings', href: r('companies.settings') },
      { label: 'Numbering', href: r('companies.settings.numbering') },
    ]">
    <CompanySettingsLayout>
      <form @submit.prevent="submit">
        <input type="hidden" name="section" value="numbering" />
        <div class="space-y-8">
          <!-- Format Warning -->
          <Alert v-if="showFormatWarning" class="border-amber-200 bg-amber-50">
            <AlertDescription class="text-amber-800">
              <strong>Note:</strong> Some format types (Location-based, Custom) currently fall back to sequential numbering.
              Full implementation is planned for future updates.
            </AlertDescription>
          </Alert>

          <Card>
            <CardHeader>
              <CardTitle>Contract Number Formatting</CardTitle>
              <CardDescription>Configure how contract numbers are generated</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Preview Section -->
              <div class="rounded-lg border bg-muted/50 p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-medium text-muted-foreground">Live Preview</h4>
                  <Badge variant="secondary" class="font-mono text-lg px-3 py-1">
                    {{ contractNumberPreview }}
                  </Badge>
                </div>
                <div class="text-sm text-muted-foreground mb-2">
                  <span class="font-medium">{{ getFormatDescription(form.contract_number_format, 'contract') }}</span>
                </div>
                <div class="text-sm text-muted-foreground">
                  <span class="font-medium">Next numbers will be:</span>
                  <div class="flex flex-wrap gap-2 mt-2">
                    <Badge
                      v-for="(example, index) in contractNumberExamples"
                      :key="index"
                      variant="outline"
                      class="font-mono"
                    >
                      {{ example }}
                    </Badge>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <Label for="contract_number_format">Format Type</Label>
                  <Select v-model="form.contract_number_format" class="mt-1">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Select format" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="format in options?.contractNumberFormats" :key="format.value" :value="format.value">
                        {{ format.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label for="contract_number_length">Number Length</Label>
                  <NumberField v-model="form.contract_number_length" :min="3" :max="10" :step="1" class="mt-1">
                    <NumberFieldContent>
                      <NumberFieldDecrement />
                      <NumberFieldInput />
                      <NumberFieldIncrement />
                    </NumberFieldContent>
                  </NumberField>
                  <p v-if="form.contract_number_format === 'date_based'" class="text-xs text-muted-foreground mt-1">
                    6 digits reserved for YYYYMM, remaining for sequential number
                  </p>
                </div>
                <div>
                  <Label for="contract_number_prefix">Prefix (Optional)</Label>
                  <Input id="contract_number_prefix" v-model="form.contract_number_prefix" placeholder="e.g., CNT" maxlength="10" class="mt-1" />
                </div>
                <div>
                  <Label for="contract_number_suffix">Suffix (Optional)</Label>
                  <Input id="contract_number_suffix" v-model="form.contract_number_suffix" placeholder="e.g., -2025" maxlength="10" class="mt-1" />
                </div>
                <div>
                  <Label for="contract_number_start">Starting Number</Label>
                  <NumberField v-model="form.contract_number_start" :min="1" :step="1" class="mt-1">
                    <NumberFieldContent>
                      <NumberFieldDecrement />
                      <NumberFieldInput />
                      <NumberFieldIncrement />
                    </NumberFieldContent>
                  </NumberField>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Billboard Code Formatting</CardTitle>
              <CardDescription>Configure how billboard codes are generated</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Preview Section -->
              <div class="rounded-lg border bg-muted/50 p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-medium text-muted-foreground">Live Preview</h4>
                  <Badge variant="secondary" class="font-mono text-lg px-3 py-1">
                    {{ billboardCodePreview }}
                  </Badge>
                </div>
                <div class="text-sm text-muted-foreground mb-2">
                  <span class="font-medium">{{ getFormatDescription(form.billboard_code_format, 'billboard') }}</span>
                </div>
                <div class="text-sm text-muted-foreground">
                  <span class="font-medium">Next codes will be:</span>
                  <div class="flex flex-wrap gap-2 mt-2">
                    <Badge
                      v-for="(example, index) in billboardCodeExamples"
                      :key="index"
                      variant="outline"
                      class="font-mono"
                    >
                      {{ example }}
                    </Badge>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <Label for="billboard_code_format">Format Type</Label>
                  <Select v-model="form.billboard_code_format" class="mt-1">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Select format" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="format in options?.billboardCodeFormats" :key="format.value" :value="format.value">
                        {{ format.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label for="billboard_code_length">Number Length</Label>
                  <NumberField v-model="form.billboard_code_length" :min="2" :max="8" :step="1" class="mt-1">
                    <NumberFieldContent>
                      <NumberFieldDecrement />
                      <NumberFieldInput />
                      <NumberFieldIncrement />
                    </NumberFieldContent>
                  </NumberField>
                </div>
                <div>
                  <Label for="billboard_code_prefix">Prefix (Optional)</Label>
                  <Input id="billboard_code_prefix" v-model="form.billboard_code_prefix" placeholder="e.g., BB" maxlength="10" class="mt-1" />
                </div>
                <div>
                  <Label for="billboard_code_suffix">Suffix (Optional)</Label>
                  <Input id="billboard_code_suffix" v-model="form.billboard_code_suffix" placeholder="e.g., -NYC" maxlength="10" class="mt-1" />
                </div>
                <div>
                  <Label for="billboard_code_start">Starting Number</Label>
                  <NumberField v-model="form.billboard_code_start" :min="1" :step="1" class="mt-1">
                    <NumberFieldContent>
                      <NumberFieldDecrement />
                      <NumberFieldInput />
                      <NumberFieldIncrement />
                    </NumberFieldContent>
                  </NumberField>
                </div>
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
              Save Numbering Settings
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>
