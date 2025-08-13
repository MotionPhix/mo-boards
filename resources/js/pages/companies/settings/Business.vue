<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

interface Props {
  company: Record<string, any>;
  options: Record<string, any>;
}

const props = defineProps<Props>();

const form = useForm({
  section: 'business',
  timezone: props.company?.timezone || 'UTC',
  currency: props.company?.currency || 'USD',
  date_format: props.company?.date_format || 'Y-m-d',
  time_format: props.company?.time_format || 'H:i',
  payment_terms_days: props.company?.payment_terms_days ?? 30,
  late_fee_percentage: props.company?.late_fee_percentage ?? 0,
  auto_generate_invoices: props.company?.auto_generate_invoices ?? false,
  tax_id: props.company?.tax_id || '',
  default_tax_rate: props.company?.default_tax_rate ?? 0,
});

const submit = () => {
  form.post(r('companies.settings.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Business settings updated successfully!');
    },
  });
};

const r = (name: string, params?: Record<string, any>, absolute = false): string =>
  ((window as any).route?.(name, params, absolute) as string) ?? '#';
</script>

<template>
  <AppLayout
    title="Company Settings — Business"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Settings', href: r('companies.settings') },
      { label: 'Business', href: r('companies.settings.business') },
    ]">
    <CompanySettingsLayout>
      <form @submit.prevent="submit">
        <input type="hidden" name="section" value="business" />
        <div class="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>Localization</CardTitle>
              <CardDescription>
                Timezone, currency and formatting
              </CardDescription>
            </CardHeader>
            <CardContent class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <Label for="timezone">Timezone</Label>
                <Select v-model="form.timezone" class="mt-1">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select timezone" />
                  </SelectTrigger>

                  <SelectContent>
                    <SelectItem
                      v-for="tz in options.timezones"
                      :key="tz.value" :value="tz.value">
                      {{ tz.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label for="currency">Currency</Label>
                <Select v-model="form.currency" class="mt-1">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select currency" />
                  </SelectTrigger>

                  <SelectContent>
                    <SelectItem
                      v-for="c in options.currencies"
                      :key="c.value" :value="c.value">
                      {{ c.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label for="date_format">Date format</Label>
                <Select v-model="form.date_format" class="mt-1">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select date format" />
                  </SelectTrigger>

                  <SelectContent>
                    <SelectItem v-for="df in options.dateFormats" :key="df.value" :value="df.value">
                      {{ df.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label for="time_format">Time format</Label>
                <Select v-model="form.time_format" class="mt-1">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select time format" />
                  </SelectTrigger>

                  <SelectContent>
                    <SelectItem v-for="tf in options.timeFormats" :key="tf.value" :value="tf.value">
                      {{ tf.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Billing & Tax</CardTitle>

              <CardDescription>
                Default payment terms and tax configuration
              </CardDescription>
            </CardHeader>

            <CardContent class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <NumberField
                  id="payment_terms_days"
                  :min="1" :max="365"
                  v-model.number="form.payment_terms_days">
                  <Label for="payment_terms_days">
                    Payment terms (days)
                  </Label>
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
              </div>

              <div>
                <NumberField
                  id="late_fee_percentage"
                  :step="0.01"
                  :min="0"
                  :max="100"
                  v-model.number="form.late_fee_percentage"
                  :format-options="{
                    style: 'percent',
                  }">
                  <Label for="late_fee_percentage">
                    Late fee (%)
                  </Label>

                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
              </div>

              <div>
                <Label for="tax_id">Tax ID</Label>
                <Input id="tax_id" v-model="form.tax_id" />
              </div>

              <div>
                <NumberField
                  id="default_tax_rate"
                  :step="0.01"
                  :min="0"
                  :max="100"
                  v-model.number="form.default_tax_rate"
                  :format-options="{
                    style: 'percent',
                  }">
                  <Label for="default_tax_rate">Default tax rate (%)</Label>

                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
              </div>

              <div class="flex items-center gap-2 md:col-span-2">
                <Switch
                  id="auto_generate_invoices"
                  v-model:checked="form.auto_generate_invoices"
                />

                <Label for="auto_generate_invoices">
                  Automatically generate invoices when contracts are signed
                </Label>
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end">
            <Button
              type="submit"
              :disabled="form.processing">
              Save Business Settings
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>
