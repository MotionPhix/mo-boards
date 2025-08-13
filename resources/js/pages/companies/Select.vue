<template>
  <Head title="Select Company" />
  <AppLayout title="Select Company" :breadcrumbs="[{ label: 'Companies', href: ziggyRoute('companies.index') }, { label: 'Select', href: ziggyRoute('companies.index') }]">

    <div class="py-8">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="bg-card text-card-foreground rounded-lg border p-6">
          <div class="space-y-3">
            <div v-for="c in companies" :key="c.id" class="flex items-center justify-between">
              <div class="min-w-0">
                <div class="font-medium truncate">{{ c.name }}</div>
                <div class="text-sm text-muted-foreground truncate">{{ c.industry || '—' }}</div>
              </div>
              <Link :href="ziggyRoute('companies.switch', c.id)" method="post" as="button" class="px-3 py-1 rounded border text-sm">Switch</Link>
            </div>
            <div v-if="!companies?.length" class="text-center text-muted-foreground">
              <div>No companies available.</div>
              <div class="mt-3">
                <Link :href="ziggyRoute('companies.create')" class="inline-flex items-center px-3 py-2 rounded-md border text-sm">Create a company</Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{ companies: any[] }>()
const ziggyRoute = (window as any).route as (...args: any[]) => string
</script>
