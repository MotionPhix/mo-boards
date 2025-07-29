<script setup lang="ts">
import { ChevronsUpDown, Plus, Check, Building2, Crown } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  DropdownMenu,
  DropdownMenuContent,
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

interface Team {
  id: number
  name: string
  plan: string
  logo: any
  isOwner: boolean
  isActive: boolean
}

interface Props {
  teams: Team[]
}

const props = defineProps<Props>()

const activeTeam = computed(() =>
  props.teams.find(team => team.isActive) || props.teams[0]
)

const ownedTeams = computed(() =>
  props.teams.filter(team => team.isOwner)
)

const memberTeams = computed(() =>
  props.teams.filter(team => !team.isOwner)
)

const switchCompany = (companyId: number) => {
  router.post(route('companies.switch', { company: companyId }))
}

const createNewCompany = () => {
  router.visit(route('companies.create'))
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
            <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
              <component :is="activeTeam?.logo || Building2" class="size-4" />
            </div>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-semibold">
                {{ activeTeam?.name || 'Select Company' }}
              </span>
              <span class="truncate text-xs flex items-center gap-1">
                <Crown v-if="activeTeam?.isOwner" class="size-3" />
                {{ activeTeam?.plan || 'Free' }}
              </span>
            </div>
            <ChevronsUpDown class="ml-auto size-4" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
          class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
          side="bottom"
          align="start"
        >
          <DropdownMenuLabel class="text-xs text-muted-foreground">
            Your Companies
          </DropdownMenuLabel>

          <!-- Owned Companies -->
          <template v-if="ownedTeams.length > 0">
            <DropdownMenuLabel class="text-xs text-muted-foreground px-2">
              Owned
            </DropdownMenuLabel>
            <DropdownMenuItem
              v-for="team in ownedTeams"
              :key="`owned-${team.id}`"
              @click="switchCompany(team.id)"
              class="gap-2 p-2 cursor-pointer"
            >
              <div class="flex size-6 items-center justify-center rounded-sm border">
                <component :is="team.logo" class="size-4 shrink-0" />
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-1">
                  {{ team.name }}
                  <Crown class="size-3 text-yellow-500" />
                </div>
                <div class="text-xs text-muted-foreground">{{ team.plan }}</div>
              </div>
              <Check v-if="team.isActive" class="size-4" />
            </DropdownMenuItem>
          </template>

          <!-- Member Companies -->
          <template v-if="memberTeams.length > 0">
            <DropdownMenuSeparator v-if="ownedTeams.length > 0" />
            <DropdownMenuLabel class="text-xs text-muted-foreground px-2">
              Member
            </DropdownMenuLabel>
            <DropdownMenuItem
              v-for="team in memberTeams"
              :key="`member-${team.id}`"
              @click="switchCompany(team.id)"
              class="gap-2 p-2 cursor-pointer"
            >
              <div class="flex size-6 items-center justify-center rounded-sm border">
                <component :is="team.logo" class="size-4 shrink-0" />
              </div>
              <div class="flex-1">
                <div>{{ team.name }}</div>
                <div class="text-xs text-muted-foreground">{{ team.plan }}</div>
              </div>
              <Check v-if="team.isActive" class="size-4" />
            </DropdownMenuItem>
          </template>

          <DropdownMenuSeparator />
          <DropdownMenuItem @click="createNewCompany" class="gap-2 p-2 cursor-pointer">
            <div class="flex size-6 items-center justify-center rounded-md border bg-background">
              <Plus class="size-4" />
            </div>
            <div class="font-medium text-muted-foreground">Add Company</div>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
