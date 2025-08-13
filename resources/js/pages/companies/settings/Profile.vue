<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ImageIcon, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
  company: Record<string, any>;
  options: Record<string, any>;
}

const props = defineProps<Props>();

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);

const form = useForm({
  section: 'profile',
  address: props.company?.address || '',
  city: props.company?.city || '',
  state: props.company?.state || '',
  zip_code: props.company?.zip_code || '',
  country: props.company?.country || '',
  phone: props.company?.phone || '',
  email: props.company?.email || '',
  website: props.company?.website || '',
  company_description: props.company?.company_description || '',
  logo: null as File | null,
});

const handleLogoChange = (event: Event) => {
  const input = event.target as HTMLInputElement | null;
  const file = input?.files?.[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e: ProgressEvent<FileReader>) => {
      const result = e.target?.result;
      logoPreview.value = typeof result === 'string' ? result : null;
    };
    reader.readAsDataURL(file);
  }
};
const triggerLogoPicker = () => logoInput.value?.click();

const submit = () => {
  form.post(route('companies.settings.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Profile updated successfully!');
    },
  });
};

// Local helper to get relative URLs from Ziggy
const r = (name: string, params?: Record<string, any>, absolute = false): string =>
  ((window as any).route?.(name, params, absolute) as string) ?? '#';
</script>

<template>
  <AppLayout
    title="Company Settings — Profile"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Settings', href: r('companies.settings') },
      { label: 'Profile', href: r('companies.settings.profile') },
    ]">
    <CompanySettingsLayout>
      <form @submit.prevent="submit" enctype="multipart/form-data">
        <input type="hidden" name="section" value="profile" />
        <div class="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>Company Information & Branding</CardTitle>
              <CardDescription>Update your company details and logo</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div>
                <Label for="logo">Company Logo</Label>
                <div class="mt-2 flex items-center gap-4">
                  <div class="border-muted-foreground/30 bg-muted flex h-20 w-20 items-center justify-center rounded-lg border-2 border-dashed">
                    <img
                      v-if="logoPreview || company?.logo_url"
                      :src="logoPreview || company?.logo_url"
                      alt="Company Logo"
                      class="h-full w-full rounded-lg object-cover"
                    />
                    <ImageIcon v-else class="text-muted-foreground h-8 w-8" />
                  </div>
                  <div>
                    <input ref="logoInput" id="logo" type="file" accept="image/*" @change="handleLogoChange" class="hidden" />
                    <Button type="button" variant="outline" @click="triggerLogoPicker">
                      {{ company?.has_logo ? 'Change Logo' : 'Choose Logo' }}
                    </Button>
                    <p class="text-muted-foreground mt-1 text-xs">PNG, JPG, GIF, SVG up to 2MB</p>
                  </div>
                </div>
                <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <Label for="email">Company Email</Label>
                  <Input id="email" v-model="form.email" type="email" class="mt-1" :class="{ 'border-red-500': form.errors.email }" />
                  <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                  <Label for="phone">Phone Number</Label>
                  <Input id="phone" v-model="form.phone" type="tel" class="mt-1" :class="{ 'border-red-500': form.errors.phone }" />
                  <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
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
                  <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</p>
                </div>
              </div>

              <div>
                <Label for="address">Address</Label>
                <Textarea id="address" v-model="form.address" rows="3" class="mt-1" :class="{ 'border-red-500': form.errors.address }" />
                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
              </div>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                  <Label for="city">City</Label>
                  <Input id="city" v-model="form.city" class="mt-1" :class="{ 'border-red-500': form.errors.city }" />
                  <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
                </div>
                <div>
                  <Label for="state">State/Province</Label>
                  <Input id="state" v-model="form.state" class="mt-1" :class="{ 'border-red-500': form.errors.state }" />
                  <p v-if="form.errors.state" class="mt-1 text-sm text-red-600">{{ form.errors.state }}</p>
                </div>
                <div>
                  <Label for="zip_code">ZIP/Postal Code</Label>
                  <Input id="zip_code" v-model="form.zip_code" class="mt-1" :class="{ 'border-red-500': form.errors.zip_code }" />
                  <p v-if="form.errors.zip_code" class="mt-1 text-sm text-red-600">{{ form.errors.zip_code }}</p>
                </div>
                <div>
                  <Label for="country">Country</Label>
                  <Input id="country" v-model="form.country" class="mt-1" :class="{ 'border-red-500': form.errors.country }" />
                  <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
              <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
              Save Settings
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>
