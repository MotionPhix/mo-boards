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
                    @update:model-value="(val) => form.status = val as 'active' | 'inactive' | 'maintenance'"
                  >
                    <SelectTrigger class="mt-1">
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="inactive">Inactive</SelectItem>
                      <SelectItem value="maintenance">Maintenance</SelectItem>
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
                <LocationPicker
                  v-model:latitude="form.latitude"
                  v-model:longitude="form.longitude"
                />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                  <!-- Latitude -->
                  <div>
                    <Label htmlFor="latitude" className="text-foreground text-xs">Latitude</Label>
                    <Input
                      id="latitude"
                      v-model.number="form.latitude"
                      type="number"
                      step="any"
                      class="mt-1"
                      :class="{ 'border-destructive': form.errors.latitude }"
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
                      v-model.number="form.longitude"
                      type="number"
                      step="any"
                      class="mt-1"
                      :class="{ 'border-destructive': form.errors.longitude }"
                    />
                    <div v-if="form.errors.longitude" class="text-destructive text-sm mt-1">
                      {{ form.errors.longitude }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Width -->
                <div>
                  <Label htmlFor="width" className="text-foreground">Width (ft)</Label>
                  <Input
                    id="width"
                    v-model.number="form.width"
                    type="number"
                    step="0.01"
                    class="mt-1"
                    :class="{ 'border-destructive': form.errors.width }"
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
                    v-model.number="form.height"
                    type="number"
                    step="0.01"
                    class="mt-1"
                    :class="{ 'border-destructive': form.errors.height }"
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
                  v-model.number="form.monthly_rate"
                  type="number"
                  step="0.01"
                  class="mt-1"
                  :class="{ 'border-destructive': form.errors.monthly_rate }"
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

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
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
import { useToast } from '@/composables/useToast'

interface Props {
  billboard: {
    id: number
    name: string
    location: string
    latitude: number | null
    longitude: number | null
    width: number | null
    height: number | null
    monthly_rate: number | null
    status: 'active' | 'inactive' | 'maintenance'
    description: string | null
  }
}

const props = defineProps<Props>()

const breadcrumbs = [
  { label: 'Billboards', href: route('billboards.index') },
  { label: props.billboard.name, href: route('billboards.edit', props.billboard.id) }
]

const toast = useToast()

const form = useForm({
  name: props.billboard.name,
  location: props.billboard.location,
  latitude: props.billboard.latitude as number | null,
  longitude: props.billboard.longitude as number | null,
  width: props.billboard.width as number | null,
  height: props.billboard.height as number | null,
  monthly_rate: props.billboard.monthly_rate as number | null,
  status: props.billboard.status as 'active' | 'inactive' | 'maintenance',
  description: props.billboard.description || '',
})

const submit = () => {
  form.put(route('billboards.update', props.billboard.id), {
    onStart: () => {
      toast.toast({
        title: 'Updating billboard...',
        description: 'Please wait while we process your request.',
      })
    },
    onSuccess: () => {
      toast.toast({
        title: 'Success',
        description: `Billboard "${form.name}" updated successfully!`,
        variant: 'default',
      })
    },
    onError: () => {
      toast.toast({
        title: 'Error',
        description: 'Failed to update billboard. Please check the form and try again.',
        variant: 'destructive',
      })
    }
  })
}
</script>
