<template>
  <AppLayout
    title="Create Billboard"
    :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Add New Billboard</h1>
          <p class="text-muted-foreground">
            Create a new billboard for your inventory
          </p>
        </div>

        <!-- Form -->
        <Card className="bg-background border shadow-sm">
          <CardHeader>
            <CardTitle className="text-xl">Billboard Information</CardTitle>
            <CardDescription className="text-muted-foreground">
              Fill in the details for your new billboard
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
                  >
                    <SelectTrigger class="mt-1 w-full">
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="opt in props.statuses"
                        :key="opt.value"
                        :value="opt.value"
                      >
                        {{ opt.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
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
                <p class="text-xs text-muted-foreground mb-2">Drop the pin or search to set precise coordinates. We’ll default to your current location if allowed.</p>
                <LocationPicker
                  v-model:latitude="form.latitude"
                  v-model:longitude="form.longitude"
                  v-model:address="form.location"
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
                :max-files="MAX_FILES"
                :max-size="MAX_SIZE"
                v-model:files="newImageFiles"
              />

              <!-- Upload progress -->
              <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-3 w-full h-2 bg-muted rounded">
                <div class="h-2 bg-primary rounded transition-all" :style="{ width: `${uploadProgress}%` }"></div>
              </div>

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
                  Create Billboard
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import { route } from 'ziggy-js'
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
  statuses: Array<{ value: 'active' | 'available' | 'maintenance' | 'removed'; label: string }>
}
const props = defineProps<Props>()

const breadcrumbs = [
  { label: 'Billboards', href: route('billboards.index') },
  { label: 'Create', href: route('billboards.create') }
]


const form = useForm({
  name: '',
  location: '',
  latitude: null as number | null,
  longitude: null as number | null,
  width: null as number | null,
  height: null as number | null,
  monthly_rate: null as number | null,
  status: 'active' as 'active' | 'available' | 'maintenance' | 'removed',
  description: '',
})

const submit = () => {
  // Build FormData to include images[]
  const fd = new FormData()
  const data = form.data()
  Object.entries(data).forEach(([k, v]) => {
    if (v === null || v === undefined) return
    fd.append(k, typeof v === 'number' ? String(v) : (v as any))
  })
  newImageFiles.value.forEach((file) => fd.append('images[]', file))

  router.post(route('billboards.store'), fd, {
    onStart: () => {
      toast('Creating billboard...', { description: 'Please wait while we process your request.' })
    },
    onProgress: (event: any) => {
      if (event?.percentage != null) uploadProgress.value = event.percentage
    },
    onSuccess: () => {
      toast.success('Success', { description: `Billboard "${form.name}" created successfully!` })
      uploadProgress.value = 0
    },
    onError: () => {
      toast.error('Error', { description: 'Failed to create billboard. Please check the form and try again.' })
    },
    preserveScroll: true,
  })
}

// Image selection state
const newImageFiles = ref<File[]>([])
const uploadProgress = ref(0)

const MAX_FILES = 10
const MAX_SIZE = 5 * 1024 * 1024 // 5MB
</script>
