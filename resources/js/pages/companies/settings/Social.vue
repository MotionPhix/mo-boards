<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useForm } from '@inertiajs/vue3'

interface Props {
  company: Record<string, any>
  options: Record<string, any>
}
const props = defineProps<Props>()

const form = useForm({
  section: 'social',
  social_media_links: {
    facebook: props.company?.social_media_links?.facebook || '',
    twitter: props.company?.social_media_links?.twitter || '',
    linkedin: props.company?.social_media_links?.linkedin || '',
    instagram: props.company?.social_media_links?.instagram || '',
  },
})

const submit = () => {
  form.post(r('companies.settings.update'), { preserveScroll: true })
}

const r = (name: string, params?: Record<string, any>, absolute = false): string =>
  ((window as any).route?.(name, params, absolute) as string) ?? '#'
</script>

<template>
  <AppLayout
    title="Company Settings — Social"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Settings', href: r('companies.settings') },
      { label: 'Social', href: r('companies.settings.social') },
    ]"
  >
    <CompanySettingsLayout>
      <form @submit.prevent="submit">
        <input type="hidden" name="section" value="social" />
        <div class="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>Social Media Links</CardTitle>
              <CardDescription>Add your company's social media profiles</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <Label for="facebook">Facebook</Label>
                  <Input id="facebook" v-model="form.social_media_links.facebook" type="url" placeholder="https://facebook.com/yourcompany" class="mt-1" />
                </div>
                <div>
                  <Label for="twitter">Twitter/X</Label>
                  <Input id="twitter" v-model="form.social_media_links.twitter" type="url" placeholder="https://twitter.com/yourcompany" class="mt-1" />
                </div>
                <div>
                  <Label for="linkedin">LinkedIn</Label>
                  <Input id="linkedin" v-model="form.social_media_links.linkedin" type="url" placeholder="https://linkedin.com/company/yourcompany" class="mt-1" />
                </div>
                <div>
                  <Label for="instagram">Instagram</Label>
                  <Input id="instagram" v-model="form.social_media_links.instagram" type="url" placeholder="https://instagram.com/yourcompany" class="mt-1" />
                </div>
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
              Save Social Links
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>
