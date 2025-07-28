<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  LayoutDashboard,
  Building2,
  Users,
  Building,
  FileText,
  Menu,
  User,
  LogOut,
  Moon,
  Sun,
  Monitor
} from 'lucide-vue-next'
import { ref } from 'vue'
import AppShell from '@/components/AppShell.vue'
import CompanySelector from '@/components/CompanySelector.vue'
import FlashMessages from '@/components/FlashMessages.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu'
import { Separator } from '@/components/ui/separator'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarTrigger
} from '@/components/ui/sidebar'
import { useAppearance } from '@/composables/useAppearance'
import type { NavItem, PageProps } from '@/types'

interface Props {
  title: string
}

defineProps<Props>()

const page = usePage<PageProps>()
const { appearance, updateAppearance } = useAppearance()
const mobileMenuOpen = ref(false)

// Navigation items for the billboard management system
const navigationItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
    isActive: route().current('dashboard')
  },
  {
    title: 'Billboards',
    href: '/billboards',
    icon: Building2,
    isActive: route().current('billboards.*')
  },
  {
    title: 'Contracts',
    href: '/contracts',
    icon: FileText,
    isActive: route().current('contracts.*')
  },
  {
    title: 'Contract Templates',
    href: '/contract-templates',
    icon: FileText,
    isActive: route().current('contract-templates.*')
  },
  {
    title: 'Team',
    href: '/team',
    icon: Users,
    isActive: route().current('team.*')
  },
  {
    title: 'Companies',
    href: '/companies',
    icon: Building,
    isActive: route().current('companies.*')
  }
]

const userInitials = computed(() => {
  const user = page.props.auth?.user
  if (!user?.name) return 'U'
  return user.name
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const userAvatar = computed(() => {
  return page.props.auth?.user?.avatar || null
})

const currentCompany = computed(() => {
  return page.props.auth?.user?.current_company || null
})

const companies = computed(() => {
  return page.props.auth?.user?.companies || []
})

const logout = () => {
  router.post(route('logout'))
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

onMounted(() => {
  // Initialize theme on mount
  const savedAppearance = localStorage.getItem('appearance') as 'light' | 'dark' | 'system' | null
  if (savedAppearance) {
    updateAppearance(savedAppearance)
  }
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <AppShell variant="sidebar">
      <!-- Sidebar for desktop -->
      <Sidebar collapsible="icon" variant="inset">
        <!-- Sidebar Header -->
        <SidebarHeader>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton size="lg" as-child>
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                  <Building2 class="h-6 w-6" />
                  <span class="font-bold">BillboardPro</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarHeader>

        <!-- Sidebar Content -->
        <SidebarContent>
          <!-- Company Selector -->
          <div class="px-3 py-2" v-if="companies.length > 0">
            <CompanySelector
              :companies="companies"
              :current-company="currentCompany"
            />
          </div>

          <!-- Navigation Menu -->
          <SidebarMenu>
            <SidebarMenuItem v-for="item in navigationItems" :key="item.title">
              <SidebarMenuButton
                as-child
                :is-active="item.isActive"
                :tooltip="item.title"
              >
                <Link :href="item.href">
                  <component :is="item.icon" />
                  <span>{{ item.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarContent>

        <!-- Sidebar Footer -->
        <SidebarFooter>
          <SidebarMenu>
            <SidebarMenuItem>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <SidebarMenuButton
                    size="lg"
                    class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                  >
                    <Avatar class="h-8 w-8 rounded-lg">
                      <AvatarImage :src="userAvatar" :alt="page.props.auth?.user?.name" />
                      <AvatarFallback class="rounded-lg">{{ userInitials }}</AvatarFallback>
                    </Avatar>
                    <div class="grid flex-1 text-left text-sm leading-tight">
                      <span class="truncate font-semibold">{{ page.props.auth?.user?.name }}</span>
                      <span class="truncate text-xs">{{ page.props.auth?.user?.email }}</span>
                    </div>
                  </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg" align="start" side="bottom" :side-offset="4">
                  <DropdownMenuLabel class="p-0 font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                      <Avatar class="h-8 w-8 rounded-lg">
                        <AvatarImage :src="userAvatar" :alt="page.props.auth?.user?.name" />
                        <AvatarFallback class="rounded-lg">{{ userInitials }}</AvatarFallback>
                      </Avatar>
                      <div class="grid flex-1 text-left text-sm leading-tight">
                        <span class="truncate font-semibold">{{ page.props.auth?.user?.name }}</span>
                        <span class="truncate text-xs">{{ page.props.auth?.user?.email }}</span>
                      </div>
                    </div>
                  </DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="router.visit(route('profile.edit'))">
                    <User class="mr-2 h-4 w-4" />
                    Profile
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="router.visit(route('companies.index'))">
                    <Building class="mr-2 h-4 w-4" />
                    Companies
                  </DropdownMenuItem>
                  <DropdownMenuSub>
                    <DropdownMenuSubTrigger>
                      <Monitor class="mr-2 h-4 w-4" />
                      Theme
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
                      <DropdownMenuItem @click="updateAppearance('light')">
                        <Sun class="mr-2 h-4 w-4" />
                        Light
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateAppearance('dark')">
                        <Moon class="mr-2 h-4 w-4" />
                        Dark
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateAppearance('system')">
                        <Monitor class="mr-2 h-4 w-4" />
                        System
                      </DropdownMenuItem>
                    </DropdownMenuSubContent>
                  </DropdownMenuSub>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="logout">
                    <LogOut class="mr-2 h-4 w-4" />
                    Log out
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarFooter>
      </Sidebar>

      <!-- Main Content Area -->
      <SidebarInset>
        <!-- Mobile Header -->
        <header class="flex h-16 items-center justify-between border-b bg-background px-4 lg:hidden">
          <Sheet v-model:open="mobileMenuOpen">
            <SheetTrigger as-child>
              <Button variant="ghost" size="icon">
                <Menu class="h-5 w-5" />
                <span class="sr-only">Toggle navigation menu</span>
              </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-80 p-0">
              <!-- Mobile Menu Header -->
              <div class="flex h-16 items-center justify-between border-b px-6">
                <Link :href="route('dashboard')" class="flex items-center space-x-2" @click="closeMobileMenu">
                  <Building2 class="h-6 w-6 text-primary" />
                  <span class="text-xl font-bold">BillboardPro</span>
                </Link>
              </div>

              <!-- Mobile Company Selector -->
              <div class="border-b p-4" v-if="companies.length > 0">
                <CompanySelector
                  :companies="companies"
                  :current-company="currentCompany"
                />
              </div>

              <!-- Mobile Navigation -->
              <nav class="flex-1 space-y-1 p-4">
                <Link
                  v-for="item in navigationItems"
                  :key="item.title"
                  :href="item.href"
                  @click="closeMobileMenu"
                  :class="[
                    'flex items-center space-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    item.isActive
                      ? 'bg-primary text-primary-foreground'
                      : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                  ]"
                >
                  <component :is="item.icon" class="h-4 w-4" />
                  <span>{{ item.title }}</span>
                </Link>
              </nav>

              <!-- Mobile User Menu -->
              <div class="border-t p-4">
                <div class="flex items-center space-x-3 mb-4">
                  <Avatar class="h-8 w-8">
                    <AvatarImage :src="userAvatar" :alt="page.props.auth?.user?.name" />
                    <AvatarFallback>{{ userInitials }}</AvatarFallback>
                  </Avatar>
                  <div class="flex flex-col">
                    <span class="text-sm font-medium">{{ page.props.auth?.user?.name }}</span>
                    <span class="text-xs text-muted-foreground">{{ page.props.auth?.user?.email }}</span>
                  </div>
                </div>
                <Separator class="mb-4" />
                <div class="space-y-1">
                  <Button variant="ghost" class="w-full justify-start" @click="router.visit(route('profile.edit')); closeMobileMenu()">
                    <User class="mr-2 h-4 w-4" />
                    Profile
                  </Button>
                  <Button variant="ghost" class="w-full justify-start" @click="router.visit(route('companies.index')); closeMobileMenu()">
                    <Building class="mr-2 h-4 w-4" />
                    Companies
                  </Button>
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" class="w-full justify-start">
                        <Monitor class="mr-2 h-4 w-4" />
                        Theme
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                      <DropdownMenuItem @click="updateAppearance('light')">
                        <Sun class="mr-2 h-4 w-4" />
                        Light
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateAppearance('dark')">
                        <Moon class="mr-2 h-4 w-4" />
                        Dark
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateAppearance('system')">
                        <Monitor class="mr-2 h-4 w-4" />
                        System
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                  <Separator class="my-2" />
                  <Button variant="ghost" class="w-full justify-start text-destructive hover:text-destructive" @click="logout">
                    <LogOut class="mr-2 h-4 w-4" />
                    Log out
                  </Button>
                </div>
              </div>
            </SheetContent>
          </Sheet>

          <!-- Mobile Logo -->
          <Link :href="route('dashboard')" class="flex items-center space-x-2">
            <Building2 class="h-6 w-6 text-primary" />
            <span class="text-lg font-bold">BillboardPro</span>
          </Link>

          <!-- Mobile User Avatar -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="rounded-full">
                <Avatar class="h-8 w-8">
                  <AvatarImage :src="userAvatar" :alt="page.props.auth?.user?.name" />
                  <AvatarFallback>{{ userInitials }}</AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56" align="end">
              <DropdownMenuLabel class="font-normal">
                <div class="flex flex-col space-y-1">
                  <p class="text-sm font-medium leading-none">{{ page.props.auth?.user?.name }}</p>
                  <p class="text-xs leading-none text-muted-foreground">
                    {{ page.props.auth?.user?.email }}
                  </p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="router.visit(route('profile.edit'))">
                <User class="mr-2 h-4 w-4" />
                Profile
              </DropdownMenuItem>
              <DropdownMenuItem @click="logout">
                <LogOut class="mr-2 h-4 w-4" />
                Log out
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </header>

        <!-- Desktop Header with Breadcrumbs -->
        <header class="hidden lg:flex h-16 items-center border-b bg-background px-6">
          <SidebarTrigger class="mr-4" />
          <div class="flex items-center space-x-2 text-sm text-muted-foreground">
            <span>{{ title }}</span>
            <span v-if="currentCompany" class="text-xs bg-muted px-2 py-1 rounded">
              {{ currentCompany.name }}
            </span>
          </div>
        </header>

        <!-- Flash Messages -->
        <FlashMessages />

        <!-- Page Content -->
        <main class="flex-1">
          <slot />
        </main>
      </SidebarInset>
    </AppShell>
  </div>
</template>
