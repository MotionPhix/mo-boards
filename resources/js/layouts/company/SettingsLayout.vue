<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'

interface Props {
  items?: NavItem[]
}

const props = withDefaults(defineProps<Props>(), { items: () => [] })

// Default company settings sidebar items; can be overridden by passing items
const page = usePage()
const abilities = (page.props as any).auth?.user?.abilities || {}

const provided = props.items.length ? [...props.items] : []
// Small helper to generate URLs from named routes (Ziggy)
const r = (name: string, params?: Record<string, any>, absolute = false) => (window as any).route?.(name, params, absolute)
// Build default sidebar items using named routes (relative URLs)
const base: (NavItem | undefined)[] = provided.length
  ? provided
  : [
      { title: 'Profile', href: r('companies.settings.profile') },
      { title: 'Numbering', href: r('companies.settings.numbering') },
      { title: 'Business', href: r('companies.settings.business') },
      abilities.can_view_company_billing ? { title: 'Billing', href: r('companies.settings.billing') } : undefined,
      abilities.can_view_company_billing ? { title: 'Transactions', href: r('companies.settings.billing.transactions') } : undefined,
      { title: 'Notifications', href: r('companies.settings.notifications') },
      { title: 'Social', href: r('companies.settings.social') },
    ]

// Ensure Billing and Transactions appear when allowed, even if items are provided
if (abilities.can_view_company_billing && provided.length) {
  const billingHref = r('companies.settings.billing')
  const txHref = r('companies.settings.billing.transactions')
  const hasBilling = provided.some(i => i.href === billingHref)
  if (!hasBilling) base.push({ title: 'Billing', href: billingHref })
  const hasTx = provided.some(i => i.href === txHref)
  if (!hasTx) base.push({ title: 'Transactions', href: txHref })
}

// Remove any accidental duplicates by href
const seen = new Set<string>()
const sidebarNavItems: NavItem[] = base.filter(Boolean).filter((i) => {
  if (!i) return false
  if (seen.has(i.href)) return false
  seen.add(i.href)
  return true
}) as NavItem[]

const ziggy = (page.props as any).ziggy
const currentPath = ziggy?.location ? new URL(ziggy.location).pathname : ''
</script>

<template>
  <div class="max-w-5xl">
    <Heading title="Company settings" description="Manage company profile, billing, and preferences" />

    <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
      <aside class="w-full max-w-xl lg:w-56">
        <nav class="flex flex-col space-x-0 space-y-1">
          <Button
            v-for="item in sidebarNavItems"
            :key="item.href"
            variant="ghost"
            :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
            as-child
          >
            <Link :href="item.href">
              {{ item.title }}
            </Link>
          </Button>
        </nav>
      </aside>

      <Separator class="my-6 md:hidden" />

      <div class="flex-1 md:max-w-4xl">
        <slot />
      </div>
    </div>
  </div>
</template>
