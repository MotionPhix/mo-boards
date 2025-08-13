<script setup lang="ts">
import { ChevronsUpDown } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import ThemeToggle from '@/components/ThemeToggle.vue'
import UserMenuContent from '@/components/UserMenuContent.vue'

import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '@/components/ui/avatar'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'

const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
    avatar: string
    current_company?: {
      name: string
      subscription_plan: string
    }
  }
}>()

// Use useMediaQuery directly instead of useSidebar to avoid context issues
const isMobile = useMediaQuery('(max-width: 768px)')

const handleLogout = () => {
  router.post(route('logout'))
}

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <Avatar class="h-8 w-8 rounded-lg">
              <AvatarImage :src="user.avatar" :alt="user.name" />
              <AvatarFallback class="rounded-lg">
                {{ getInitials(user.name) }}
              </AvatarFallback>
            </Avatar>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">{{ user.name }}</span>
              <span class="truncate text-xs">{{ user.email }}</span>
            </div>
            <ChevronsUpDown class="ml-auto size-4" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>
          <DropdownMenuContent class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg" side="bottom" align="end" :side-offset="4">
            <UserMenuContent :user="(user as any)" />
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
              <DropdownMenuLabel class="px-2 py-1.5 text-xs font-medium text-muted-foreground">Theme</DropdownMenuLabel>
              <div class="px-2 py-1">
                <ThemeToggle />
              </div>
            </DropdownMenuGroup>
          </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
