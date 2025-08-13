<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field'
import { useForm } from '@inertiajs/vue3'

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
    ]"
  >
    <CompanySettingsLayout>
      <form @submit.prevent="submit">
        <input type="hidden" name="section" value="numbering" />
        <div class="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>Contract Number Formatting</CardTitle>
              <CardDescription>Configure how contract numbers are generated</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <Label for="contract_number_format">Format Type</Label>
                  <Select v-model="form.contract_number_format" class="mt-1">
                    <SelectTrigger>
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
              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <Label for="billboard_code_format">Format Type</Label>
                  <Select v-model="form.billboard_code_format" class="mt-1">
                    <SelectTrigger>
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
              Save Numbering
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>

  { label: 'Companies', href: r('companies.index') },
  { label: 'Settings', href: r('companies.settings') },
  { label: 'Numbering', href: r('companies.settings.numbering') },
