<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  company: any
  plans: Array<{ id: string; name: string; price: number; currency: string; interval?: string; interval_count?: number; features: string[] }>
  currentPlan: string
  transactions: Array<any>
}>()

const form = useForm({ plan_id: '' })
const page = usePage()
const abilities = (page.props as any).auth?.user?.abilities || {}

// Small helper to access Ziggy's route() with a typed string return
const r = (name: string, params?: Record<string, any>, absolute = false): string =>
  ((window as any).route?.(name, params, absolute) as string) ?? '#'

const subscribe = (planId: string) => {
  form.plan_id = planId
  form.post(r('companies.settings.billing.subscribe'))
}

const intervalLabel = (plan: { interval?: string; interval_count?: number }) => {
  const unit = plan.interval || 'month'
  const count = plan.interval_count || 1
  if (count === 1) return `/${unit.slice(0, 2) === 'mo' ? 'mo' : unit}`
  return `/${count} ${unit}${count > 1 ? 's' : ''}`
}

const popularPlanId = computed(() => {
  if (!props.plans?.length) return null
  const pro = props.plans.find(p => p.id?.toLowerCase?.() === 'pro')
  if (pro) return pro.id
  // fallback: median by price
  const sorted = [...props.plans].sort((a, b) => a.price - b.price)
  return sorted[Math.floor(sorted.length / 2)]?.id ?? sorted[0]?.id ?? null
})

const isCurrent = (id: string) => id === props.currentPlan

const planTagClass = (id: string) => {
  const key = (id || '').toLowerCase()
  switch (key) {
    case 'pro':
      return 'text-primary'
    case 'team':
      return 'text-green-600 dark:text-green-500'
    case 'business':
      return 'text-blue-600 dark:text-blue-500'
    case 'enterprise':
      return 'text-indigo-600 dark:text-indigo-400'
    default:
      return 'text-muted-foreground'
  }
}

const currentPlanName = computed(() => {
  const m = props.plans?.find(p => p.id === props.currentPlan)
  return m?.name || props.currentPlan
})
</script>

<template>
  <AppLayout
    title="Company Billing"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Billing', href: r('companies.settings.billing') }
    ]">
    <CompanySettingsLayout :items="[
      { title: 'Company profile', href: r('companies.settings.profile') },
      { title: 'Numbering', href: r('companies.settings.numbering') },
      { title: 'Billing', href: r('companies.settings.billing') },
      { title: 'Notifications', href: r('companies.settings.notifications') },
      { title: 'Social', href: r('companies.settings.social') },
    ]">
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 text-center">
      <h2 class="text-2xl font-semibold tracking-tight">Simple pricing</h2>
      <p class="mt-1 text-sm text-muted-foreground">Current plan: <span class="font-medium">{{ currentPlanName }}</span>. Switch plans anytime.</p>
        </div>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <Card
            v-for="plan in plans"
            :key="plan.id"
            :class="[
      'relative flex h-full flex-col rounded-xl border bg-card shadow-sm transition-all',
              'hover:shadow-md hover:-translate-y-0.5',
              isCurrent(plan.id) ? 'ring-2 ring-primary' : 'ring-1 ring-border/50',
            ]"
          >
            <div v-if="popularPlanId && plan.id === popularPlanId" class="absolute -top-3 left-1/2 z-10 -translate-x-1/2">
              <span class="rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground shadow">Most popular</span>
            </div>

      <CardHeader class="pt-6">
              <CardTitle class="flex items-center justify-between">
        <span class="text-base font-semibold" :class="planTagClass(plan.id)">{{ plan.name }}</span>
                <svg v-if="plan.price === 0" class="h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
                <svg v-else class="h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
              </CardTitle>
              <div class="mt-2">
                <span v-if="plan.price === 0" class="text-3xl font-extrabold tracking-tight">Free</span>
                <template v-else>
                  <div class="text-xs uppercase tracking-wide text-muted-foreground">{{ plan.currency }}</div>
                  <div class="text-3xl font-extrabold leading-tight">
                    {{ new Intl.NumberFormat(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Math.round(plan.price/100)) }}
                    <span class="text-sm font-normal text-muted-foreground">{{ intervalLabel(plan) }}</span>
                  </div>
                </template>
              </div>
              <CardDescription v-if="isCurrent(plan.id)" class="mt-1">Current plan</CardDescription>
            </CardHeader>

      <CardContent class="pb-0">
              <ul class="space-y-2 text-sm">
                <li v-for="f in plan.features" :key="f" class="flex items-start gap-2 text-muted-foreground">
                  <svg class="mt-0.5 h-4 w-4 text-green-600 dark:text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5" />
                  </svg>
                  <span>{{ f }}</span>
                </li>
              </ul>
            </CardContent>

      <div class="flex-1" />
      <CardFooter class="mt-6 flex justify-center pb-6">
              <Button
                :variant="isCurrent(plan.id) ? 'secondary' : (plan.id === popularPlanId ? 'default' : 'outline')"
                class="w-full rounded-lg"
                :disabled="isCurrent(plan.id) || !abilities.can_manage_company_billing"
                @click="subscribe(plan.id)"
              >
        {{ isCurrent(plan.id) ? 'Active' : 'Choose plan' }}
              </Button>
            </CardFooter>
          </Card>
        </div>
    <p class="mt-6 text-center text-xs text-muted-foreground">Standard local taxes and VAT may apply, following the laws of your country.</p>
      </div>

      <div class="mt-10">
        <h2 class="mb-4 text-lg font-semibold">Recent transactions</h2>
        <div v-if="!transactions?.length" class="text-sm text-muted-foreground">No transactions yet.</div>
        <div v-else class="overflow-x-auto rounded-md border">
          <table class="w-full text-sm">
            <thead class="bg-muted/50 text-left">
              <tr>
                <th class="p-3">Date</th>
                <th class="p-3">Type</th>
                <th class="p-3">Reference</th>
                <th class="p-3">Amount</th>
                <th class="p-3">Status</th>
                <th class="p-3"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transactions" :key="t.id" class="border-t">
                <td class="p-3">{{ new Date(t.occurred_at || t.created_at).toLocaleString() }}</td>
                <td class="p-3 capitalize">{{ t.type }}</td>
                <td class="p-3">{{ t.reference }}</td>
                <td class="p-3">
                  {{ new Intl.NumberFormat(undefined, { style: 'currency', currency: t.currency || 'MWK' }).format((t.amount || 0)/100) }}
                </td>
                <td class="p-3 capitalize">{{ t.status }}</td>
                <td class="p-3">
                  <a
                    v-if="t.status === 'paid'"
                    class="text-primary hover:underline"
                    :href="r('companies.settings.billing.transactions.receipt', { transaction: t.id })"
                  >Download receipt</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-3 text-right">
          <a class="text-sm text-primary hover:underline" :href="r('companies.settings.billing.transactions')">View all transactions →</a>
        </div>
      </div>
    </CompanySettingsLayout>
  </AppLayout>
</template>
