<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import {
  LayoutDashboard,
  Building2,
  Flag as Billboard,
  FileText,
  Users,
  Settings,
  Library as LibraryBig,
} from 'lucide-vue-next'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import TeamSwitcher from '@/components/TeamSwitcher.vue'

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from '@/components/ui/sidebar'

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: 'icon',
})

type AuthPageProps = {
  auth?: {
    user?: {
      id: number
      name: string
      email: string
      avatar: string
      abilities?: Record<string, boolean>
      companies?: any[]
      current_company?: {
        id: number
        name: string
        subscription_plan?: string
      }
    }
  }
}

const page = usePage<AuthPageProps>()

// Get current route name for active state detection
const currentRoute = computed(() => page.url)

// Check if current route matches or starts with the given path
const isActiveRoute = (path: string, includeSubpaths = true) => {
  if (includeSubpaths) {
    return currentRoute.value.startsWith(path)
  }
  return currentRoute.value === path
}

// Get user and companies data from Inertia shared props
const user = computed(() => page.props.auth?.user)
const abilities = computed(() => page.props.auth?.user?.abilities || {})
const companies = computed(() => user.value?.companies || [])
const currentCompany = computed(() => user.value?.current_company)

// Narrowed user for NavUser component to satisfy its prop contract
const navUser = computed(() => {
  const u = user.value as any
  if (!u) return undefined
  const cc = u.current_company || undefined
  return {
    id: u.id,
    name: u.name,
    email: u.email,
    avatar: u.avatar || '',
    current_company: cc
      ? {
          name: cc.name,
          subscription_plan: cc.subscription_plan || 'Free',
        }
      : undefined,
  }
})

// Navigation data - permission-aware
const navMain = computed(() => {
  const items = [
    {
      title: 'Dashboard',
      url: route('dashboard'),
      icon: LayoutDashboard,
      isActive: isActiveRoute('/dashboard', false),
      show: true, // All authenticated users see dashboard
    },
    {
      title: 'Billboards',
      url: route('billboards.index'),
      icon: Billboard,
      isActive: isActiveRoute('/billboards'),
      show: !!abilities.value.can_view_billboards,
    },
    {
      title: 'Contracts',
      url: route('contracts.index'),
      icon: FileText,
      isActive: isActiveRoute('/contracts'),
      show: !!abilities.value.can_view_contracts,
    },
    {
      title: 'Templates',
      url: route('contract-templates.index'),
      icon: LibraryBig,
      isActive: isActiveRoute('/contract-templates') || isActiveRoute('/template-marketplace'),
      show: !!abilities.value.can_view_contract_templates,
    },
    {
      title: 'Team',
      url: route('team.index'),
      icon: Users,
      isActive: isActiveRoute('/team'),
      show: !!abilities.value.can_view_team,
    },
    {
      title: 'Settings',
      url: route('companies.settings'),
      icon: Settings,
      isActive: isActiveRoute('/companies/settings'),
      show: !!abilities.value.can_manage_company_settings || !!abilities.value.can_view_company_billing,
    },
  ]

  return items.filter((i) => i.show)
})

// Teams data for the TeamSwitcher
const teams = computed(() =>
  companies.value.map((company: any) => ({
    id: company.id,
    name: company.name,
    logo: Building2,
    plan: company.subscription_plan || 'Free',
    isOwner: company.pivot?.is_owner || false,
    isActive: company.id === currentCompany.value?.id,
  }))
)
</script>

<template>
  <Sidebar
    v-bind="props"
    variant="floating"
    collapsible="offcanvas">
    <SidebarHeader>
      <TeamSwitcher :teams="teams" />
    </SidebarHeader>
    <SidebarContent>
      <NavMain :items="navMain" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser v-if="navUser" :user="navUser" />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
