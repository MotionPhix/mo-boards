<script setup lang="ts">
import { computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Building2, Check, Users, Clock, MoreHorizontal, Trash2, UsersIcon, PencilLine } from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'

const props = defineProps<{
  company: any
  currentCompany?: any | null
  view?: 'grid' | 'list'
  selected?: boolean
}>()

const emit = defineEmits<{
  'toggle-select': [id: number]
}>()

const view = computed(() => props.view ?? 'grid')

function capitalize(value: string | null | undefined): string {
  if (!value) return ''
  return String(value).charAt(0).toUpperCase() + String(value).slice(1)
}

const plan = computed(() => capitalize(props.company?.subscription_plan || 'free'))
const updatedAt = computed(() => (props.company?.updated_at ? new Date(props.company.updated_at).toLocaleDateString() : '—'))
const progressWidth = computed(() => {
  const count = Number(props.company?.billboards_count ?? 0)
  return Math.min(count * 10, 100) + '%'
})

function toggleSelect(event: Event) {
  event.stopPropagation()
  emit('toggle-select', props.company.id)
}

function deleteCompany() {
  if (confirm('Are you sure you want to delete this company?')) {
    router.delete(ziggyRoute('companies.destroy', props.company.id))
  }
}

const ziggyRoute = (window as any).route as (...args: any[]) => string
</script>

<template>
  <Card v-if="view === 'grid'" class="group relative overflow-hidden hover:shadow-lg transition-shadow">
    <CardContent class="p-6">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-4 min-w-0">
          <div class="relative group/select">
            <!-- Regular Avatar -->
            <Avatar class="w-12 h-12 ring-2 ring-border transition-opacity"
                   :class="{ 'opacity-0': selected, 'group-hover/select:opacity-0': !selected }">
              <AvatarFallback class="bg-muted">
                <Building2 class="w-6 h-6 text-foreground/60" />
              </AvatarFallback>
            </Avatar>

            <!-- Selection Button (shown on hover or when selected) -->
            <button
              type="button"
              @click.stop.prevent="toggleSelect"
              class="absolute inset-0 w-12 h-12 rounded-full bg-background border-2 shadow-sm flex items-center justify-center transition-all opacity-0"
              :class="{
                'opacity-100 border-primary': selected,
                'group-hover/select:opacity-100 hover:bg-accent border-border': !selected
              }"
              :aria-pressed="selected ? 'true' : 'false'">
              <Check :class="[
                'w-6 h-6 transition-colors',
                selected ? 'text-primary' : 'text-foreground/60'
              ]" />
            </button>
          </div>

          <div class="min-w-0">
            <CardTitle class="text-base truncate">{{ company.name }}</CardTitle>
            <CardDescription class="mt-1 truncate">{{ company.industry || '—' }}</CardDescription>
          </div>
        </div>

        <div class="flex flex-col items-end gap-2">
          <Badge variant="outline">{{ plan }}</Badge>
          <Badge variant="secondary">{{ company.pivot?.is_owner ? 'Owner' : 'Member' }}</Badge>
        </div>
      </div>

      <p class="mt-4 text-sm text-muted-foreground line-clamp-2">
        {{ company.description || 'No description available.' }}
      </p>

      <div class="mt-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Badge variant="secondary" class="text-xs">
            Size: {{ company.size || '—' }}
          </Badge>

          <Badge variant="secondary" class="text-xs">
            Billboards: {{ company.billboards_count ?? 0 }}
          </Badge>
        </div>
      </div>

      <div class="mt-6 pt-4 border-t flex items-center justify-between">
        <div class="flex items-center gap-2">
          <template v-if="company.id === currentCompany?.id">
            <Badge variant="outline">Active</Badge>
          </template>

          <template v-else>
            <Button variant="outline" size="sm" as-child>
              <Link :href="ziggyRoute('companies.switch', company.id)" method="post">Switch</Link>
            </Button>
          </template>
        </div>

          <!-- Owner-only actions (hover reveal) -->
          <div>
            <DropdownMenu v-if="company.pivot?.is_owner">
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon">
                  <MoreHorizontal />
                </Button>
              </DropdownMenuTrigger>

              <DropdownMenuContent align="end" :side-offset="-36">
                <DropdownMenuItem as-child>
                  <Link :href="ziggyRoute('companies.settings') + '?tab=profile'">Edit company</Link>
                </DropdownMenuItem>
                <DropdownMenuItem as-child>
                  <Link :href="ziggyRoute('team.index')">View team</Link>
                </DropdownMenuItem>
                <DropdownMenuItem class="text-destructive" @click="deleteCompany">
                  Delete company
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
      </div>
    </CardContent>
  </Card>

  <Card v-else class="group relative w-full hover:shadow-sm transition-shadow">
    <CardContent class="p-4">
      <div class="flex items-center justify-between gap-4 w-full">
        <div class="flex items-center gap-4 min-w-0">
          <div class="relative group/select">
            <!-- Regular Avatar -->
            <Avatar class="w-12 h-12 ring-2 ring-border transition-opacity"
                   :class="{ 'opacity-0': selected, 'group-hover/select:opacity-0': !selected }">
              <AvatarFallback class="bg-muted">
                <Building2 class="w-6 h-6 text-foreground/60" />
              </AvatarFallback>
            </Avatar>

            <!-- Selection Button (shown on hover or when selected) -->
            <button
              type="button"
              @click.stop.prevent="toggleSelect"
              class="absolute inset-0 w-12 h-12 rounded-full bg-background border-2 shadow-sm flex items-center justify-center transition-all opacity-0"
              :class="{
                'opacity-100 border-primary': selected,
                'group-hover/select:opacity-100 hover:bg-accent border-border': !selected
              }"
              :aria-pressed="selected ? 'true' : 'false'">
              <Check :class="[
                'w-6 h-6 transition-colors',
                selected ? 'text-primary' : 'text-foreground/60'
              ]" />
            </button>
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-3">
              <h3 class="text-base font-medium truncate">{{ company.name }}</h3>
              <Badge variant="outline">{{ plan }}</Badge>
              <Badge v-if="company.id === currentCompany?.id" variant="secondary">Active</Badge>
            </div>
            <div class="flex items-center gap-3 mt-1 text-sm text-muted-foreground">
              <span class="truncate">{{ company.industry || '—' }}</span>
              <span class="flex items-center gap-1">
                <Users class="w-4 h-4" />
                {{ company.size || '—' }}
              </span>
              <span>
                Billboards: {{ company.billboards_count ?? 0 }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <template v-if="company.id !== currentCompany?.id">
            <Button variant="outline" size="sm" as-child>
              <Link :href="ziggyRoute('companies.switch', company.id)" method="post">Switch</Link>
            </Button>
          </template>

          <!-- Owner actions -->
          <div class="opacity-0 group-hover:opacity-100 transition-opacity">
            <section
              class="flex items-center gap-1 p-1 bg-muted rounded-lg"
              v-if="company.pivot?.is_owner">
              <Button
                size="icon"
                variant="ghost"
                :as="Link" :href="ziggyRoute('companies.settings') + '?tab=profile'">
                <PencilLine />
              </Button>

              <Button
                size="icon"
                variant="ghost"
                class="hover:bg-green-500 hover:text-green-100"
                :as="Link" :href="ziggyRoute('team.index')">
                <UsersIcon />
              </Button>

              <Button
                size="icon"
                variant="ghost"
                class="hover:bg-destructive hover:text-red-100"
                @click="deleteCompany">
                <Trash2 class="w-4 h-4" />
              </Button>
            </section>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
