<template>
  <Head title="Company Settings" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl leading-tight font-semibold text-gray-800">Company Settings</h2>
          <p class="mt-1 text-sm text-gray-600">Manage your company information, branding, and system preferences</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" enctype="multipart/form-data">
          <div class="space-y-8">
            <!-- Company Information & Branding -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Building2 class="h-5 w-5" />
                  Company Information & Branding
                </CardTitle>
                <CardDescription> Update your company details and upload your logo </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Logo Upload -->
                <div>
                  <Label for="logo">Company Logo</Label>
                  <div class="mt-2 flex items-center gap-4">
                    <div class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                      <img
                        v-if="logoPreview || company.logo_url"
                        :src="logoPreview || company.logo_url"
                        alt="Company Logo"
                        class="w-full h-full object-cover rounded-lg"
                      />
                      <ImageIcon v-else class="w-8 h-8 text-gray-400" />
                    </div>
                    <div>
                      <input
                        ref="logoInput"
                        id="logo"
                        type="file"
                        accept="image/*"
                        @change="handleLogoChange"
                        class="hidden"
                      />
                      <Button
                        type="button"
                        variant="outline"
                        @click="$refs.logoInput.click()"
                      >
                        {{ company.has_logo ? 'Change Logo' : 'Choose Logo' }}
                      </Button>
                      <p class="text-xs text-gray-500 mt-1">
                        PNG, JPG, GIF, SVG up to 2MB
                      </p>
                    </div>
                  </div>
                  <p v-if="form.errors.logo" class="text-sm text-red-600 mt-1">
                    {{ form.errors.logo }}
                  </p>
                </div>

                <!-- Contact Information Grid -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                  <div>
                    <Label for="email">Company Email</Label>
                    <Input id="email" v-model="form.email" type="email" class="mt-1" :class="{ 'border-red-500': form.errors.email }" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                      {{ form.errors.email }}
                    </p>
                  </div>

                  <div>
                    <Label for="phone">Phone Number</Label>
                    <Input id="phone" v-model="form.phone" type="tel" class="mt-1" :class="{ 'border-red-500': form.errors.phone }" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                      {{ form.errors.phone }}
                    </p>
                  </div>

                  <div>
                    <Label for="website">Website</Label>
                    <Input
                      id="website"
                      v-model="form.website"
                      type="url"
                      placeholder="https://example.com"
                      class="mt-1"
                      :class="{ 'border-red-500': form.errors.website }"
                    />
                    <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">
                      {{ form.errors.website }}
                    </p>
                  </div>
                </div>

                <!-- Address -->
                <div>
                  <Label for="address">Address</Label>
                  <Textarea id="address" v-model="form.address" rows="3" class="mt-1" :class="{ 'border-red-500': form.errors.address }" />
                  <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                    {{ form.errors.address }}
                  </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                  <div>
                    <Label for="city">City</Label>
                    <Input id="city" v-model="form.city" class="mt-1" :class="{ 'border-red-500': form.errors.city }" />
                    <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">
                      {{ form.errors.city }}
                    </p>
                  </div>

                  <div>
                    <Label for="state">State/Province</Label>
                    <Input id="state" v-model="form.state" class="mt-1" :class="{ 'border-red-500': form.errors.state }" />
                    <p v-if="form.errors.state" class="mt-1 text-sm text-red-600">
                      {{ form.errors.state }}
                    </p>
                  </div>

                  <div>
                    <Label for="zip_code">ZIP/Postal Code</Label>
                    <Input id="zip_code" v-model="form.zip_code" class="mt-1" :class="{ 'border-red-500': form.errors.zip_code }" />
                    <p v-if="form.errors.zip_code" class="mt-1 text-sm text-red-600">
                      {{ form.errors.zip_code }}
                    </p>
                  </div>

                  <div>
                    <Label for="country">Country</Label>
                    <Input id="country" v-model="form.country" class="mt-1" :class="{ 'border-red-500': form.errors.country }" />
                    <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">
                      {{ form.errors.country }}
                    </p>
                  </div>
                </div>

                <!-- Company Description -->
                <div>
                  <Label for="company_description">Company Description</Label>
                  <Textarea
                    id="company_description"
                    v-model="form.company_description"
                    rows="4"
                    placeholder="Brief description of your company..."
                    class="mt-1"
                    :class="{ 'border-red-500': form.errors.company_description }"
                  />
                  <p v-if="form.errors.company_description" class="mt-1 text-sm text-red-600">
                    {{ form.errors.company_description }}
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- Contract Number Formatting -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="h-5 w-5" />
                  Contract Number Formatting
                </CardTitle>
                <CardDescription> Configure how contract numbers are generated </CardDescription>
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
                        <SelectItem v-for="format in options.contractNumberFormats" :key="format.value" :value="format.value">
                          {{ format.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="contract_number_length">Number Length</Label>
                    <Input id="contract_number_length" v-model.number="form.contract_number_length" type="number" min="3" max="10" class="mt-1" />
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
                    <Input id="contract_number_start" v-model.number="form.contract_number_start" type="number" min="1" class="mt-1" />
                  </div>
                </div>

                <!-- Preview -->
                <div class="bg-gray-50 p-4 rounded-lg border">
                  <h4 class="font-medium text-sm text-gray-700 mb-2">Current Preview:</h4>
                  <p class="font-mono text-lg">{{ contractNumberPreview }}</p>
                  <p class="text-xs text-gray-500 mt-1">Next actual number: {{ company.next_contract_number_preview }}</p>
                </div>
              </CardContent>
            </Card>

            <!-- Billboard Code Formatting -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <MapPin class="h-5 w-5" />
                  Billboard Code Formatting
                </CardTitle>
                <CardDescription> Configure how billboard codes are generated </CardDescription>
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
                        <SelectItem v-for="format in options.billboardCodeFormats" :key="format.value" :value="format.value">
                          {{ format.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="billboard_code_length">Number Length</Label>
                    <Input id="billboard_code_length" v-model.number="form.billboard_code_length" type="number" min="2" max="8" class="mt-1" />
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
                    <Input id="billboard_code_start" v-model.number="form.billboard_code_start" type="number" min="1" class="mt-1" />
                  </div>
                </div>

                <!-- Preview -->
                <div class="rounded-lg border bg-gray-50 p-4">
                  <h4 class="mb-2 text-sm font-medium text-gray-700">Preview:</h4>
                  <p class="font-mono text-lg">{{ billboardCodePreview }}</p>
                </div>
              </CardContent>
            </Card>

            <!-- Business Settings -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Settings class="h-5 w-5" />
                  Business Settings
                </CardTitle>
                <CardDescription> Configure timezone, currency, and date/time formats </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                  <div>
                    <Label for="timezone">Timezone</Label>
                    <Select v-model="form.timezone" class="mt-1">
                      <SelectTrigger>
                        <SelectValue placeholder="Select timezone" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="timezone in options.timezones" :key="timezone.value" :value="timezone.value">
                          {{ timezone.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="currency">Currency</Label>
                    <Select v-model="form.currency" class="mt-1">
                      <SelectTrigger>
                        <SelectValue placeholder="Select currency" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="currency in options.currencies" :key="currency.value" :value="currency.value">
                          {{ currency.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="date_format">Date Format</Label>
                    <Select v-model="form.date_format" class="mt-1">
                      <SelectTrigger>
                        <SelectValue placeholder="Select date format" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="format in options.dateFormats" :key="format.value" :value="format.value">
                          {{ format.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="time_format">Time Format</Label>
                    <Select v-model="form.time_format" class="mt-1">
                      <SelectTrigger>
                        <SelectValue placeholder="Select time format" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="format in options.timeFormats" :key="format.value" :value="format.value">
                          {{ format.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Billing & Payment Settings -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CreditCard class="h-5 w-5" />
                  Billing & Payment Settings
                </CardTitle>
                <CardDescription> Configure payment terms, tax settings, and invoice preferences </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                  <div>
                    <Label for="payment_terms_days">Payment Terms (Days)</Label>
                    <Input id="payment_terms_days" v-model.number="form.payment_terms_days" type="number" min="1" max="365" class="mt-1" />
                  </div>

                  <div>
                    <Label for="late_fee_percentage">Late Fee (%)</Label>
                    <Input
                      id="late_fee_percentage"
                      v-model.number="form.late_fee_percentage"
                      type="number"
                      min="0"
                      max="100"
                      step="0.01"
                      class="mt-1"
                    />
                  </div>

                  <div>
                    <Label for="tax_id">Tax ID</Label>
                    <Input id="tax_id" v-model="form.tax_id" class="mt-1" />
                  </div>

                  <div>
                    <Label for="default_tax_rate">Default Tax Rate (%)</Label>
                    <Input id="default_tax_rate" v-model.number="form.default_tax_rate" type="number" min="0" max="100" step="0.01" class="mt-1" />
                  </div>
                </div>

                <div class="flex items-center space-x-2">
                  <Checkbox id="auto_generate_invoices" v-model="form.auto_generate_invoices" />
                  <Label for="auto_generate_invoices"> Automatically generate invoices when contracts are signed </Label>
                </div>
              </CardContent>
            </Card>

            <!-- Notification Settings -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Bell class="h-5 w-5" />
                  Notification Settings
                </CardTitle>
                <CardDescription> Configure when and how you receive notifications </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <div class="space-y-4">
                  <div class="flex items-center space-x-2">
                    <Checkbox id="email_contracts" v-model="form.notification_settings.email_contracts" />
                    <Label for="email_contracts"> Email notifications for contract changes </Label>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Checkbox id="email_payments" v-model="form.notification_settings.email_payments" />
                    <Label for="email_payments"> Email notifications for payment updates </Label>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Checkbox id="email_billboards" v-model="form.notification_settings.email_billboards" />
                    <Label for="email_billboards"> Email notifications for billboard changes </Label>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Checkbox id="email_team" v-model="form.notification_settings.email_team" />
                    <Label for="email_team"> Email notifications for team changes </Label>
                  </div>
                </div>

                <div>
                  <Label for="slack_webhook">Slack Webhook URL (Optional)</Label>
                  <Input
                    id="slack_webhook"
                    v-model="form.notification_settings.slack_webhook"
                    type="url"
                    placeholder="https://hooks.slack.com/services/..."
                    class="mt-1"
                  />
                  <p class="mt-1 text-xs text-gray-500">Receive important notifications in your Slack channel</p>
                </div>
              </CardContent>
            </Card>

            <!-- Social Media Links -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Share2 class="h-5 w-5" />
                  Social Media Links
                </CardTitle>
                <CardDescription> Add your company's social media profiles </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <Label for="facebook">Facebook</Label>
                    <Input
                      id="facebook"
                      v-model="form.social_media_links.facebook"
                      type="url"
                      placeholder="https://facebook.com/yourcompany"
                      class="mt-1"
                    />
                  </div>

                  <div>
                    <Label for="twitter">Twitter/X</Label>
                    <Input
                      id="twitter"
                      v-model="form.social_media_links.twitter"
                      type="url"
                      placeholder="https://twitter.com/yourcompany"
                      class="mt-1"
                    />
                  </div>

                  <div>
                    <Label for="linkedin">LinkedIn</Label>
                    <Input
                      id="linkedin"
                      v-model="form.social_media_links.linkedin"
                      type="url"
                      placeholder="https://linkedin.com/company/yourcompany"
                      class="mt-1"
                    />
                  </div>

                  <div>
                    <Label for="instagram">Instagram</Label>
                    <Input
                      id="instagram"
                      v-model="form.social_media_links.instagram"
                      type="url"
                      placeholder="https://instagram.com/yourcompany"
                      class="mt-1"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Save Button -->
            <div class="flex justify-end">
              <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Save Settings
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Building2, FileText, MapPin, Settings, CreditCard, Bell, Share2, ImageIcon, Loader2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps({
  company: Object,
  options: Object,
});

const logoInput = ref(null);
const logoPreview = ref(null);
const currentLogo = ref(props.company?.logo_path || null);

const form = useForm({
  // Company information
  address: props.company?.address || '',
  city: props.company?.city || '',
  state: props.company?.state || '',
  zip_code: props.company?.zip_code || '',
  country: props.company?.country || '',
  phone: props.company?.phone || '',
  email: props.company?.email || '',
  website: props.company?.website || '',
  company_description: props.company?.company_description || '',

  // Contract number formatting
  contract_number_prefix: props.company?.contract_number_prefix || '',
  contract_number_suffix: props.company?.contract_number_suffix || '',
  contract_number_format: props.company?.contract_number_format || 'sequential',
  contract_number_length: props.company?.contract_number_length || 6,
  contract_number_start: props.company?.contract_number_start || 1,

  // Billboard code formatting
  billboard_code_prefix: props.company?.billboard_code_prefix || '',
  billboard_code_suffix: props.company?.billboard_code_suffix || '',
  billboard_code_format: props.company?.billboard_code_format || 'sequential',
  billboard_code_length: props.company?.billboard_code_length || 4,
  billboard_code_start: props.company?.billboard_code_start || 1,

  // Business settings
  timezone: props.company?.timezone || 'UTC',
  currency: props.company?.currency || 'USD',
  date_format: props.company?.date_format || 'Y-m-d',
  time_format: props.company?.time_format || 'H:i',

  // Billing settings
  payment_terms_days: props.company?.payment_terms_days || 30,
  late_fee_percentage: props.company?.late_fee_percentage || 0,
  auto_generate_invoices: props.company?.auto_generate_invoices || false,
  tax_id: props.company?.tax_id || '',
  default_tax_rate: props.company?.default_tax_rate || 0,

  // Notification settings
  notification_settings: {
    email_contracts: props.company?.notification_settings?.email_contracts ?? true,
    email_payments: props.company?.notification_settings?.email_payments ?? true,
    email_billboards: props.company?.notification_settings?.email_billboards ?? true,
    email_team: props.company?.notification_settings?.email_team ?? true,
    slack_webhook: props.company?.notification_settings?.slack_webhook || '',
  },

  // Social media
  social_media_links: {
    facebook: props.company?.social_media_links?.facebook || '',
    twitter: props.company?.social_media_links?.twitter || '',
    linkedin: props.company?.social_media_links?.linkedin || '',
    instagram: props.company?.social_media_links?.instagram || '',
  },

  // Logo file
  logo: null,
});

const contractNumberPreview = computed(() => {
  const prefix = form.contract_number_prefix || '';
  const suffix = form.contract_number_suffix || '';
  const length = form.contract_number_length || 6;
  const start = form.contract_number_start || 1;

  let number = '';

  if (form.contract_number_format === 'sequential') {
    number = start.toString().padStart(length, '0');
  } else if (form.contract_number_format === 'date_based') {
    const now = new Date();
    const year = now.getFullYear();
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    number = `${year}${month}${start.toString().padStart(length - 6, '0')}`;
  } else {
    number = start.toString().padStart(length, '0');
  }

  return `${prefix}${number}${suffix}`;
});

const billboardCodePreview = computed(() => {
  const prefix = form.billboard_code_prefix || '';
  const suffix = form.billboard_code_suffix || '';
  const length = form.billboard_code_length || 4;
  const start = form.billboard_code_start || 1;

  let code = '';

  if (form.billboard_code_format === 'sequential') {
    code = start.toString().padStart(length, '0');
  } else if (form.billboard_code_format === 'location_based') {
    code = `${start.toString().padStart(length, '0')}`;
  } else {
    code = start.toString().padStart(length, '0');
  }

  return `${prefix}${code}${suffix}`;
});

const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.logo = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submit = () => {
  form.post(route('companies.settings.update'), {
    preserveScroll: true,
  });
};
</script>
