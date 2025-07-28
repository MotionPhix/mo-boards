<script setup lang="ts">
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3';
import { User, Building, LogOut } from 'lucide-vue-next'
import NavLink from '@/components/NavLink.vue'
import CompanySelector from '@/components/CompanySelector.vue'
import FlashMessages from '@/components/FlashMessages.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

interface Props {
  title: string
}

defineProps<Props>()

const userInitials = computed(() => {
  const user = page.props.user
  return user.name
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
})

const userAvatar = computed(() => {
  // Return user avatar URL if available
  return null
})

const logout = () => {
  router.post(route('logout'))
}

console.log('AppLayout mounted with user:', usePage().props);
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo & Navigation -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <Link :href="route('dashboard')" class="text-xl font-bold text-blue-600">
                BillboardPro
              </Link>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                Dashboard
              </NavLink>
              <NavLink :href="route('billboards.index')" :active="route().current('billboards.*')">
                Billboards
              </NavLink>
              <NavLink :href="route('team.index')" :active="route().current('team.*')">
                Team
              </NavLink>
              <NavLink :href="route('companies.index')" :active="route().current('companies.*')">
                Companies
              </NavLink>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <CompanySelector
              v-if="$page.props.companies?.length > 0"
              :companies="$page.props.companies"
              :current-company="$page.props.currentCompany"
            />

            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="relative h-8 w-8 rounded-full">
                  <Avatar class="h-8 w-8">
                    <AvatarImage :src="userAvatar" :alt="$page.props.user.name" />
                    <AvatarFallback>{{ userInitials }}</AvatarFallback>
                  </Avatar>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56" align="end">
                <DropdownMenuLabel class="font-normal">
                  <div class="flex flex-col space-y-1">
                    <p class="text-sm font-medium leading-none">{{ $page.props.user.name }}</p>
                    <p class="text-xs leading-none text-muted-foreground">
                      {{ $page.props.user.email }}
                    </p>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="$inertia.visit(route('profile.edit'))">
                  <User class="mr-2 h-4 w-4" />
                  Profile
                </DropdownMenuItem>
                <DropdownMenuItem @click="$inertia.visit(route('companies.index'))">
                  <Building class="mr-2 h-4 w-4" />
                  Companies
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="logout">
                  <LogOut class="mr-2 h-4 w-4" />
                  Log out
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <!-- Flash Messages -->
      <FlashMessages />

      <!-- Page Content -->
      <slot />
    </main>
  </div>
</template>
