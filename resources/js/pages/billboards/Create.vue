<template>
  <AppLayout title="Create Billboard">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Add New Billboard</h1>
          <p class="mt-2 text-gray-600">
            Create a new billboard for your inventory
          </p>
        </div>

        <!-- Form -->
        <Card>
          <CardHeader>
            <CardTitle>Billboard Information</CardTitle>
            <CardDescription>
              Fill in the details for your new billboard
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                  <Label htmlFor="name">Billboard Name *</Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1"
                    :class="{ 'border-red-500': form.errors.name }"
                    required
                  />
                  <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                    {{ form.errors.name }}
                  </div>
                </div>

                <!-- Status -->
                <div>
                  <Label htmlFor="status">Status *</Label>
                  <Select
                    :model-value="form.status"
                    @update:model-value="form.status = $event"
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
                  <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">
                    {{ form.errors.status }}
                  </div>
                </div>
              </div>

              <!-- Location -->
              <div>
                <Label htmlFor="location">Location *</Label>
                <Textarea
                  id="location"
                  v-model="form.location"
                  class="mt-1"
                  :class="{ 'border-red-500': form.errors.location }"
                  rows="3"
                  required
                />
                <div v-if="form.errors.location" class="text-red-600 text-sm mt-1">
                  {{ form.errors.location }}
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Latitude -->
                <div>
                  <Label htmlFor="latitude">Latitude</Label>
                  <Input
                    id="latitude"
                    v-model.number="form.latitude"
                    type="number"
                    step="any"
                    class="mt-1"
                    :class="{ 'border-red-500': form.errors.latitude }"
                  />
                  <div v-if="form.errors.latitude" class="text-red-600 text-sm mt-1">
                    {{ form.errors.latitude }}
                  </div>
                </div>

                <!-- Longitude -->
                <div>
                  <Label htmlFor="longitude">Longitude</Label>
                  <Input
                    id="longitude"
                    v-model.number="form.longitude"
                    type="number"
                    step="any"
                    class="mt-1"
                    :class="{ 'border-red-500': form.errors.longitude }"
                  />
                  <div v-if="form.errors.longitude" class="text-red-600 text-sm mt-1">
                    {{ form.errors.longitude }}
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Size -->
                <div>
                  <Label htmlFor="size">Size</Label>
                  <Select
                    :model-value="form.size"
                    @update:model-value="form.size = $event"
                  >
                    <SelectTrigger class="mt-1">
                      <SelectValue placeholder="Select size" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="14x48">14' x 48' (Bulletin)</SelectItem>
                      <SelectItem value="12x24">12' x 24' (Poster)</SelectItem>
                      <SelectItem value="6x12">6' x 12' (Junior)</SelectItem>
                      <SelectItem value="custom">Custom</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Width -->
                <div>
                  <Label htmlFor="width">Width (ft)</Label>
                  <Input
                    id="width"
                    v-model.number="form.width"
                    type="number"
                    step="0.01"
                    class="mt-1"
                    :class="{ 'border-red-500': form.errors.width }"
                  />
                  <div v-if="form.errors.width" class="text-red-600 text-sm mt-1">
                    {{ form.errors.width }}
                  </div>
                </div>

                <!-- Height -->
                <div>
                  <Label htmlFor="height">Height (ft)</Label>
                  <Input
                    id="height"
                    v-model.number="form.height"
                    type="number"
                    step="0.01"
                    class="mt-1"
                    :class="{ 'border-red-500': form.errors.height }"
                  />
                  <div v-if="form.errors.height" class="text-red-600 text-sm mt-1">
                    {{ form.errors.height }}
                  </div>
                </div>
              </div>

              <!-- Monthly Rate -->
              <div>
                <Label htmlFor="monthly_rate">Monthly Rate ($)</Label>
                <Input
                  id="monthly_rate"
                  v-model.number="form.monthly_rate"
                  type="number"
                  step="0.01"
                  class="mt-1"
                  :class="{ 'border-red-500': form.errors.monthly_rate }"
                />
                <div v-if="form.errors.monthly_rate" class="text-red-600 text-sm mt-1">
                  {{ form.errors.monthly_rate }}
                </div>
              </div>

              <!-- Description -->
              <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  class="mt-1"
                  :class="{ 'border-red-500': form.errors.description }"
                  rows="4"
                />
                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                  {{ form.errors.description }}
                </div>
              </div>

              <!-- Actions -->
              <div class="flex justify-end space-x-4">
                <Button
                  type="button"
                  variant="outline"
                  @click="$inertia.visit(route('billboards.index'))"
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  :disabled="form.processing"
                >
                  <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                  Create Billboard
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Loader2 } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
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

const form = useForm({
  name: '',
  location: '',
  latitude: null as number | null,
  longitude: null as number | null,
  size: '',
  width: null as number | null,
  height: null as number | null,
  monthly_rate: null as number | null,
  status: 'active',
  description: '',
})

const submit = () => {
  form.post(route('billboards.store'))
}
</script>
