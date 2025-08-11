<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import LocationPicker from '@/components/map/LocationPicker.vue'
import { route } from 'ziggy-js'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { toast } from 'vue-sonner'
import MediaGallery from '@/components/media/MediaGallery.vue'

interface Props {
  billboard: any
  revenue_data: any
  utilization_data: any
  permissions: {
    can_update: boolean
    can_delete: boolean
    can_duplicate: boolean
    can_view_analytics: boolean
    can_manage_media: boolean
  }
}

const props = defineProps<Props>()

const breadcrumbs = [
  { label: 'Billboards', href: route('billboards.index') },
  { label: props.billboard.name, href: route('billboards.show', props.billboard.uuid) }
]

const billboard = props.billboard
const permissions = props.permissions
const media = computed(() => billboard.media ?? [])

const currency = (value: number) => new Intl.NumberFormat(undefined, { style: 'currency', currency: billboard.pricing.currency || 'USD' }).format(value)

const noop = () => {}

// lightbox handled within MediaGallery

async function deleteMedia(id: number) {
  if (!permissions.can_manage_media) return
  const key = billboard.uuid ?? String(billboard.id)
  await router.delete(route('billboards.media.delete', key), {
    data: { media_id: id },
    preserveScroll: true,
    onSuccess: () => toast.success('Deleted', { description: 'Image removed.' }),
    onError: () => toast.error('Error', { description: 'Failed to delete image.' })
  })
}
</script>

<template>
  <AppLayout :title="`Billboard: ${billboard.name}`" :breadcrumbs="breadcrumbs">
    <div class="max-w-6xl">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-3xl font-bold tracking-tight text-foreground truncate">{{ billboard.name }}</h1>
          <p class="text-muted-foreground truncate">{{ billboard.location || 'No address provided' }}</p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
          <Button v-if="permissions.can_update" :href="route('billboards.edit', billboard.uuid)" as-child>
            <a class="bg-primary text-primary-foreground hover:bg-primary/90 px-3 py-2 rounded-md">Edit</a>
          </Button>
        </div>
      </div>

      <!-- Top grid: Map and Key Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-md border border-border bg-card p-3">
          <LocationPicker
            :latitude="billboard.coordinates.latitude ?? null"
            :longitude="billboard.coordinates.longitude ?? null"
            :address="billboard.location || null"
            :nearby-markers="[]"
            map-height="320px"
            @update:latitude="noop"
            @update:longitude="noop"
            @update:address="noop"
          />
        </div>
        <div class="rounded-md border border-border bg-card p-4">
          <h2 class="font-semibold text-foreground mb-3">Key stats</h2>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded border border-border p-3 bg-muted/30">
              <div class="text-xs text-muted-foreground">Utilization</div>
              <div class="text-foreground font-semibold text-lg">{{ billboard.performance?.utilization_rate ?? 0 }}%</div>
            </div>
            <div class="rounded border border-border p-3 bg-muted/30">
              <div class="text-xs text-muted-foreground">Revenue</div>
              <div class="text-foreground font-semibold text-lg">{{ currency(billboard.performance?.total_revenue ?? 0) }}</div>
            </div>
            <div class="rounded border border-border p-3 bg-muted/30">
              <div class="text-xs text-muted-foreground">Avg. Duration</div>
              <div class="text-foreground font-semibold text-lg">{{ billboard.performance?.average_contract_duration ?? 0 }} days</div>
            </div>
            <div class="rounded border border-border p-3 bg-muted/30">
              <div class="text-xs text-muted-foreground">Status</div>
              <div class="text-foreground font-semibold text-lg">{{ billboard.status.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Details + Contracts + Gallery -->
      <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="rounded-md border border-border bg-card p-4">
            <h2 class="font-semibold text-foreground mb-3">Details</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
              <div class="rounded border border-border p-3 bg-muted/30">
                <div class="text-xs text-muted-foreground">Code</div>
                <div class="text-foreground font-medium">{{ billboard.code || '—' }}</div>
              </div>
              <div class="rounded border border-border p-3 bg-muted/30">
                <div class="text-xs text-muted-foreground">Size</div>
                <div class="text-foreground font-medium">{{ billboard.dimensions.width || '—' }} × {{ billboard.dimensions.height || '—' }}</div>
              </div>
              <div class="rounded border border-border p-3 bg-muted/30">
                <div class="text-xs text-muted-foreground">Monthly Rate</div>
                <div class="text-foreground font-medium">{{ billboard.pricing.formatted_rate || '—' }}</div>
              </div>
            </div>
            <p v-if="billboard.description" class="text-sm text-muted-foreground mt-3">{{ billboard.description }}</p>
          </div>

          <div class="rounded-md border border-border bg-card p-4">
            <h2 class="font-semibold text-foreground mb-3">Images</h2>
            <MediaGallery :media="media" :can-delete="permissions.can_manage_media" @delete="deleteMedia" />
          </div>
        </div>

        <div class="space-y-6">
          <div class="rounded-md border border-border bg-card p-4">
            <h2 class="font-semibold text-foreground mb-3">Recent Contract</h2>
            <div v-if="billboard.contracts.current_contract" class="text-sm text-muted-foreground">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-foreground font-medium">{{ billboard.contracts.current_contract.client_name }}</div>
                  <div>{{ billboard.contracts.current_contract.start_date }} — {{ billboard.contracts.current_contract.end_date }}</div>
                </div>
                <div class="text-foreground">{{ billboard.contracts.current_contract.monthly_amount }}</div>
              </div>
            </div>
            <div v-else class="text-sm text-muted-foreground">No recent contracts.</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
