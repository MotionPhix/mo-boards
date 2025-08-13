<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps<{ transactions: any }>();

const r = (name: string, params?: Record<string, any>, absolute = false) => (window as any).route?.(name, params, absolute);
const page = usePage();
</script>

<template>
  <AppLayout
    title="Transactions"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Billing', href: r('companies.settings.billing') },
      { label: 'Transactions', href: r('companies.settings.billing.transactions') },
    ]">
    <CompanySettingsLayout>
      <div class="overflow-x-auto rounded-md border">
        <table class="w-full text-sm">
          <thead class="bg-muted/50 text-left">
            <tr>
              <th class="p-3">Date</th>
              <th class="p-3">Type</th>
              <th class="p-3">Reference</th>
              <th class="p-3">Amount</th>
              <th class="p-3">Status</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in transactions.data" :key="t.id" class="border-t">
              <td class="p-3">{{ new Date(t.occurred_at || t.created_at).toLocaleString() }}</td>
              <td class="p-3 capitalize">{{ t.type }}</td>
              <td class="p-3">{{ t.reference }}</td>
              <td class="p-3">
                {{
                  new Intl.NumberFormat(undefined, {
                    style: 'currency',
                    currency: t.currency || 'MWK',
                  }).format((t.amount || 0) / 100)
                }}
              </td>
              <td class="p-3 capitalize">{{ t.status }}</td>
              <td class="p-3">
                <a
                  v-if="t.status === 'paid'"
                  class="text-primary hover:underline"
                  :href="r('companies.settings.billing.transactions.receipt', { transaction: t.id })">
                  Download receipt
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="text-muted-foreground mt-4 flex items-center justify-between text-sm">
        <div>
          Showing
          <strong>{{ transactions.from }}</strong>
          -
          <strong>{{ transactions.to }}</strong>
          of
          <strong>{{ transactions.total }}</strong>
        </div>

        <div class="flex gap-2">
          <Link
            :href="transactions.prev_page_url || '#'"
            :class="['rounded border px-3 py-1', { 'pointer-events-none opacity-50': !transactions.prev_page_url }]">
            Prev
          </Link>

          <Link
            :href="transactions.next_page_url || '#'"
            :class="['rounded border px-3 py-1', { 'pointer-events-none opacity-50': !transactions.next_page_url }]">
            Next
          </Link>
        </div>
      </div>
    </CompanySettingsLayout>
  </AppLayout>
</template>
