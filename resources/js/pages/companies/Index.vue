<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{ companies: any[]; currentCompany: any | null }>()
const ziggyRoute = (window as any).route as (...args: any[]) => string
</script>

<template>
  <Head title="Companies" />
  <AppLayout title="Companies" :breadcrumbs="[{ label: 'Companies', href: ziggyRoute('companies.index') }]">
    <div class="mb-4 flex items-center justify-end">
      <ModalLink :href="ziggyRoute('companies.create')" class="inline-flex items-center px-3 py-2 rounded-md bg-primary text-primary-foreground text-sm">New Company</ModalLink>
    </div>

    <div class="py-8">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="bg-card text-card-foreground rounded-lg border">
          <div class="p-4 divide-y">
            <div v-for="c in companies" :key="c.id" class="py-4 flex items-center justify-between">
              <div>
                <div class="font-medium">{{ c.name }}</div>
                <div class="text-sm text-muted-foreground">{{ c.industry || '—' }}</div>
              </div>
              <div class="flex items-center gap-2">
                <Link :href="ziggyRoute('companies.switch', c.id)" method="post" as="button" class="px-2 py-1 text-xs rounded border">Switch</Link>
                <span class="text-xs text-muted-foreground">Billboards: {{ c.billboards_count }}</span>
              </div>
            </div>
            <div v-if="!companies?.length" class="py-10 text-center text-muted-foreground">
              <div>No companies yet.</div>
              <div class="mt-3">
                <ModalLink :href="ziggyRoute('companies.create')" class="inline-flex items-center px-3 py-2 rounded-md border text-sm">
                  Create your first company
                </ModalLink>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>