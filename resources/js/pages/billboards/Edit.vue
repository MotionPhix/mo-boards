<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'
import { Loader2 } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import LocationPicker from '@/components/map/LocationPicker.vue'
import { toast } from 'vue-sonner'
import ImageUploader from '@/components/upload/ImageUploader.vue'

interface Props {
  billboard: {
    id: number
    uuid?: string
    name: string
    location: string
    latitude: number | null
    longitude: number | null
    width: number | null
    height: number | null
    monthly_rate: number | null
    status: {
      current: 'active' | 'available' | 'maintenance' | 'removed'
      label: string
      color: string
      can_edit: boolean
    }
    description: string | null
    media?: Array<{ id: number; name: string; url: string; preview_url?: string }>
  }
  nearby_markers?: { id: number; name: string; code?: string; latitude: number; longitude: number }[]
  permissions?: { can_manage_media?: boolean }
  statuses?: Array<{ value: 'active' | 'available' | 'maintenance' | 'removed'; label: string }>
}

const props = defineProps<Props>()
const page = usePage()
const isCompanyOwner = () => Boolean((page?.props as any)?.auth?.user?.abilities?.is_company_owner)

const breadcrumbs = [
  { label: 'Billboards', href: route('billboards.index') },
  { label: props.billboard.name, href: route('billboards.edit', props.billboard.uuid!) }
]

const permissions = computed(() => props.permissions ?? { can_manage_media: false })

const form = useForm({
  name: props.billboard.name,
  location: props.billboard.location,
  latitude: (props.billboard.latitude ?? null) as number | null,
  longitude: (props.billboard.longitude ?? null) as number | null,
  width: props.billboard.width as number | null,
  height: props.billboard.height as number | null,
  monthly_rate: props.billboard.monthly_rate as number | null,
  status: props.billboard.status.current as 'active' | 'available' | 'maintenance' | 'removed',
  description: props.billboard.description || '',
})

// Image upload state and handlers
const existingImages = computed(() => props.billboard.media ?? [])
const newImageFiles = ref<File[]>([])

async function removeExistingImage(id: number) {
  if (!props.billboard.uuid) {
    toast.error('Missing UUID', { description: 'Unable to delete media because the billboard UUID is missing.' })
    return
  }
  await router.delete(route('billboards.media.delete', props.billboard.uuid), {
    data: { media_id: id },
    preserveScroll: true,
  })
}

const submit = () => {
  const data: Record<string, any> = { ...form.data() }
  // Build FormData for file uploads
  const fd = new FormData()
  Object.entries(data).forEach(([k, v]) => {
    if (v === null || v === undefined) return
    fd.append(k, typeof v === 'number' ? String(v) : (v as any))
  })
  newImageFiles.value.forEach((file) => fd.append('images[]', file))
  // method override for PUT
  fd.append('_method', 'put')

  router.post(route('billboards.update', props.billboard.uuid!), fd, {
    onStart: () => {
      toast('Updating billboard...', { description: 'Please wait while we process your request.' })
    },
    onSuccess: () => {
      toast.success('Success', { description: `Billboard "${form.name}" updated successfully!` })
    },
    onError: () => {
      toast.error('Error', { description: 'Failed to update billboard. Please check the form and try again.' })
    },
  onFinish: () => {},
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout
    title="Edit Billboard"
    :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Edit Billboard</h1>
          <p class="text-muted-foreground">
            Update billboard information
          </p>
        </div>

        <!-- Form -->
        <Card className="bg-background border shadow-sm">
          <CardHeader>
            <CardTitle className="text-xl">Billboard Information</CardTitle>
            <CardDescription className="text-muted-foreground">
              Edit the details for this billboard
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                  <Label htmlFor="name" className="text-foreground">Billboard Name *</Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1"
                    :class="{ 'border-destructive': form.errors.name }"
                    required
                  />
                  <div v-if="form.errors.name" class="text-destructive text-sm mt-1">
                    {{ form.errors.name }}
                  </div>
                </div>

                <!-- Status -->
                <div>
                  <Label htmlFor="status" className="text-foreground">Status *</Label>
                  <Select
                    :model-value="form.status"
                    @update:model-value="(val) => form.status = val as 'active' | 'available' | 'maintenance' | 'removed'"
                    :disabled="props.billboard.status && props.billboard.status.current === 'maintenance' && !isCompanyOwner()"
                  >
                    <SelectTrigger class="mt-1 w-full">
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="opt in (props.statuses || [])"
                        :key="opt.value"
                        :value="opt.value"
                      >
                        {{ opt.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="props.billboard.status && props.billboard.status.can_edit === false" class="text-xs text-muted-foreground mt-1">
                    Only a company owner can change status while in maintenance.
                  </p>
                  <div v-if="form.errors.status" class="text-destructive text-sm mt-1">
                    {{ form.errors.status }}
                  </div>
                </div>
              </div>

              <!-- Location -->
              <div>
                <Label htmlFor="location" className="text-foreground">Location *</Label>
                <Textarea
                  id="location"
                  v-model="form.location"
                  class="mt-1 min-h-[80px] resize-y"
                  :class="{ 'border-destructive': form.errors.location }"
                  rows="3"
                  required
                />
                <div v-if="form.errors.location" class="text-destructive text-sm mt-1">
                  {{ form.errors.location }}
                </div>
              </div>

              <!-- Location Map -->
              <div>
                <Label className="text-foreground mb-1 block">Map Location</Label>
                <LocationPicker
                  v-model:latitude="form.latitude"
                  v-model:longitude="form.longitude"
                  v-model:address="form.location"
                  :nearby-markers="props.nearby_markers || []"
                  map-height="420px"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                  <!-- Latitude -->
                  <div>
                    <Label htmlFor="latitude" className="text-foreground text-xs">Latitude</Label>
                    <Input
                      id="latitude"
                      :model-value="form.latitude ?? ''"
                      type="number"
                      step="any"
                      class="mt-1"
                      :class="{ 'border-destructive': form.errors.latitude }"
                      @update:model-value="(v: string | number) => { const n = Number(v); form.latitude = (v === '' || Number.isNaN(n)) ? null : n }"
                    />
                    <div v-if="form.errors.latitude" class="text-destructive text-sm mt-1">
                      {{ form.errors.latitude }}
                    </div>
                  </div>

                  <!-- Longitude -->
                  <div>
                    <Label htmlFor="longitude" className="text-foreground text-xs">Longitude</Label>
                    <Input
                      id="longitude"
                      :model-value="form.longitude ?? ''"
                      type="number"
                      step="any"
                      class="mt-1"
                      :class="{ 'border-destructive': form.errors.longitude }"
                      @update:model-value="(v: string | number) => { const n = Number(v); form.longitude = (v === '' || Number.isNaN(n)) ? null : n }"
                    />
                    <div v-if="form.errors.longitude" class="text-destructive text-sm mt-1">
                      {{ form.errors.longitude }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Images Upload -->
              <ImageUploader
                label="Billboard Images"
                accept="image/jpeg,image/png"
                :max-files="10"
                :max-size="5 * 1024 * 1024"
                :existing="existingImages"
                v-model:files="newImageFiles"
                :can-delete="permissions.can_manage_media"
                @delete-existing="removeExistingImage"
              />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Width -->
                <div>
                  <Label htmlFor="width" className="text-foreground">Width (ft)</Label>
                  <Input
                    id="width"
                    :model-value="form.width ?? ''"
                    type="number"
                    step="0.01"
                    class="mt-1"
                    :class="{ 'border-destructive': form.errors.width }"
                    @update:model-value="(v: string | number) => { const n = Number(v); form.width = (v === '' || Number.isNaN(n)) ? null : n }"
                  />
                  <div v-if="form.errors.width" class="text-destructive text-sm mt-1">
                    {{ form.errors.width }}
                  </div>
                </div>

                <!-- Height -->
                <div>
                  <Label htmlFor="height" className="text-foreground">Height (ft)</Label>
                  <Input
                    id="height"
                    :model-value="form.height ?? ''"
                    type="number"
                    step="0.01"
                    class="mt-1"
                    :class="{ 'border-destructive': form.errors.height }"
                    @update:model-value="(v: string | number) => { const n = Number(v); form.height = (v === '' || Number.isNaN(n)) ? null : n }"
                  />
                  <div v-if="form.errors.height" class="text-destructive text-sm mt-1">
                    {{ form.errors.height }}
                  </div>
                </div>
              </div>

              <!-- Monthly Rate -->
              <div>
                <Label htmlFor="monthly_rate" className="text-foreground">Monthly Rate ($)</Label>
                <Input
                  id="monthly_rate"
                  :model-value="form.monthly_rate ?? ''"
                  type="number"
                  step="0.01"
                  class="mt-1"
                  :class="{ 'border-destructive': form.errors.monthly_rate }"
                  @update:model-value="(v: string | number) => { const n = Number(v); form.monthly_rate = (v === '' || Number.isNaN(n)) ? null : n }"
                />
                <div v-if="form.errors.monthly_rate" class="text-destructive text-sm mt-1">
                  {{ form.errors.monthly_rate }}
                </div>
              </div>

              <!-- Description -->
              <div>
                <Label htmlFor="description" className="text-foreground">Description</Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  class="mt-1 min-h-[120px] resize-y"
                  :class="{ 'border-destructive': form.errors.description }"
                  rows="4"
                />
                <div v-if="form.errors.description" class="text-destructive text-sm mt-1">
                  {{ form.errors.description }}
                </div>
              </div>

              <!-- Actions -->
              <div class="flex justify-end space-x-4 pt-4">
                <Button
                  type="button"
                  variant="outline"
                  @click="router.visit(route('billboards.index'))"
                  class="border-border hover:bg-accent"
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  variant="default"
                  :disabled="form.processing"
                  class="bg-primary text-primary-foreground hover:bg-primary/90"
                >
                  <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                  Update Billboard
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
    </div>
  </AppLayout>
</template>
