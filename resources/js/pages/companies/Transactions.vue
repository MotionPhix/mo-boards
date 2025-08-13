<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableCaption } from '@/components/ui/table';
import TableEmpty from '@/components/ui/table/TableEmpty.vue';

const props = defineProps<{ transactions: any }>();

const r = (name: string, params?: Record<string, any>, absolute = false) => (window as any).route?.(name, params, absolute);
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
      <div class="rounded-md border overflow-x-auto">
        <Table>
          <TableCaption class="text-muted-foreground mb-4">
            Company billing transactions
          </TableCaption>

          <TableHeader>
            <TableRow>
              <TableHead>Date</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Reference</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Status</TableHead>
              <TableHead></TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="transactions?.data?.length">
              <TableRow v-for="t in transactions.data" :key="t.id">
                <TableCell>{{ new Date(t.occurred_at || t.created_at).toLocaleString() }}</TableCell>
                <TableCell class="capitalize">{{ t.type }}</TableCell>
                <TableCell>{{ t.reference }}</TableCell>
                <TableCell>
                  {{
                    new Intl.NumberFormat(undefined, {
                      style: 'currency',
                      currency: t.currency || 'MWK',
                    }).format((t.amount || 0) / 100)
                  }}
                </TableCell>
                <TableCell class="capitalize">{{ t.status }}</TableCell>
                <TableCell>
                  <a
                    v-if="t.status === 'paid'"
                    class="text-primary hover:underline"
                    :href="r('companies.settings.billing.transactions.receipt', { transaction: t.id })"
                  >
                    Download receipt
                  </a>
                </TableCell>
              </TableRow>
            </template>

            <template v-else>
              <TableEmpty :colspan="6">
                <div class="text-center">
                  <div class="text-base font-medium text-foreground">No transactions yet</div>
                  <div class="mt-1 text-sm text-muted-foreground">When you subscribe or are billed, your transactions will appear here.</div>
                  <div class="mt-4">
                    <Link :href="r('companies.settings.billing')" class="text-sm text-primary underline underline-offset-4">Go to Billing</Link>
                  </div>
                </div>
              </TableEmpty>
            </template>
          </TableBody>
        </Table>
      </div>

      <div 
        v-if="transactions?.data?.length"
        class="text-muted-foreground mt-4 flex items-center justify-between text-sm">
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
