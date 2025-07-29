<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import {
  LayoutDashboard,
  Building2,
  Flag as Billboard,
  FileText,
  ScrollText,
  Users,
  Settings,
  Building,
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

const page = usePage()

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
const companies = computed(() => user.value?.companies || [])
const currentCompany = computed(() => user.value?.current_company)

// Navigation data based on your actual routes
const navMain = computed(() => [
  {
    title: 'Dashboard',
    url: route('dashboard'),
    icon: LayoutDashboard,
    isActive: isActiveRoute('/dashboard', false),
  },
  {
    title: 'Billboards',
    url: route('billboards.index'),
    icon: Billboard,
    isActive: isActiveRoute('/billboards'),
    items: [
      {
        title: 'All Billboards',
        url: route('billboards.index'),
      },
      {
        title: 'Add Billboard',
        url: route('billboards.create'),
      },
    ],
  },
  {
    title: 'Contracts',
    url: route('contracts.index'),
    icon: FileText,
    isActive: isActiveRoute('/contracts'),
    items: [
      {
        title: 'All Contracts',
        url: route('contracts.index'),
      },
      {
        title: 'Create Contract',
        url: route('contracts.create'),
      },
    ],
  },
  {
    title: 'Contract Templates',
    url: route('contract-templates.index'),
    icon: ScrollText,
    isActive: isActiveRoute('/contract-templates'),
    items: [
      {
        title: 'All Templates',
        url: route('contract-templates.index'),
      },
      {
        title: 'Create Template',
        url: route('contract-templates.create'),
      },
    ],
  },
  {
    title: 'Team',
    url: route('team.index'),
    icon: Users,
    isActive: isActiveRoute('/team'),
    items: [
      {
        title: 'Team Members',
        url: route('team.index'),
      },
      {
        title: 'Invite Member',
        url: route('team.create'),
      },
    ],
  },
  {
    title: 'Companies',
    url: route('companies.index'),
    icon: Building,
    isActive: isActiveRoute('/companies'),
    items: [
      {
        title: 'All Companies',
        url: route('companies.index'),
      },
      {
        title: 'Create Company',
        url: route('companies.create'),
      },
    ],
  },
])

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
  <Sidebar v-bind="props" variant="inset">
    <SidebarHeader>
      <TeamSwitcher :teams="teams" />
    </SidebarHeader>
    <SidebarContent>
      <NavMain :items="navMain" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="user" />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
