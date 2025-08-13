<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Trash2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'

interface Props {
  company: Record<string, any>
  options: Record<string, any>
}
const props = defineProps<Props>()

// Initialize from existing links or sensible defaults
const initialLinksObj: Record<string, string> = props.company?.social_media_links ?? {
  facebook: '',
  twitter: '',
  linkedin: '',
  instagram: '',
}

// Editable list of links
const links = ref<{ key: string; url: string }[]>(
  Object.entries(initialLinksObj).map(([key, url]) => ({ key, url: (url as string) || '' }))
)

const form = useForm({
  section: 'social',
  social_media_links: initialLinksObj,
})

const submit = () => {
  // Build object from editable list, filtering out empty pairs
  const payload: Record<string, string> = {}
  for (const item of links.value) {
    const k = (item.key || '').trim()
    const v = (item.url || '').trim()
    if (k && v) payload[k] = v
  }

  form.social_media_links = payload

  form.post(r('companies.settings.update'), { 
    preserveScroll: true,

    onSuccess: () => {
      toast.success('Social links updated successfully!')
    },
  })
}

const addLink = () => {
  links.value.push({ key: '', url: '' })
}

const removeLink = (index: number) => {
  links.value.splice(index, 1)
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
    ]">
    <CompanySettingsLayout>
      <form @submit.prevent="submit">
        <input type="hidden" name="section" value="social" />
        <div class="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>
                Social Media Links
              </CardTitle>

              <CardDescription>
                Add your company's social media profiles
              </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">
              <div class="space-y-3">
                <div 
                  v-for="(item, idx) in links" :key="idx" 
                  class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
                  <div class="md:col-span-4">
                    <Label :for="`platform-${idx}`">Platform</Label>
                    <Input 
                      :id="`platform-${idx}`" 
                      v-model="item.key" 
                      type="text" 
                      placeholder="e.g., facebook, youtube" 
                      class="mt-1" 
                    />
                  </div>

                  <div class="md:col-span-7">
                    <Label :for="`url-${idx}`">URL</Label>
                    <Input 
                      :id="`url-${idx}`" 
                      v-model="item.url" 
                      type="url" 
                      placeholder="https://example.com/yourcompany" 
                      class="mt-1" 
                    />
                  </div>

                  <div class="md:col-span-1">
                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="icon" 
                      @click="removeLink(idx)">
                      <Trash2 />
                    </Button>
                  </div>
                </div>
              </div>

              <div>
                <Button 
                  type="button" 
                  variant="outline" 
                  @click="addLink">
                  Add another link
                </Button>
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end">
            <Button 
              type="submit" 
              :disabled="form.processing" 
              class="w-full sm:w-auto">
              Save Social Links
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>
