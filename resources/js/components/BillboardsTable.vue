<template>
  <div class="overflow-x-auto">
    <Table class="min-w-full">
      <TableHeader>
        <TableRow>
          <TableHead>
            <button class="inline-flex items-center gap-1" @click="$emit('sort', 'code')">
              <span>Code</span>
              <component
                :is="sortIcon('code')"
                class="h-3.5 w-3.5 text-muted-foreground"
              />
            </button>
          </TableHead>
          <TableHead>
            <button class="inline-flex items-center gap-1" @click="$emit('sort', 'name')">
              <span>Name & Location</span>
              <component
                :is="sortIcon('name')"
                class="h-3.5 w-3.5 text-muted-foreground"
              />
            </button>
          </TableHead>
          <TableHead>Dimensions</TableHead>
          <TableHead>
            <button class="inline-flex items-center gap-1" @click="$emit('sort', 'status')">
              <span>Status</span>
              <component
                :is="sortIcon('status')"
                class="h-3.5 w-3.5 text-muted-foreground"
              />
            </button>
          </TableHead>
          <TableHead>
            <button class="inline-flex items-center gap-1" @click="$emit('sort', 'monthly_rate')">
              <span>Pricing</span>
              <component
                :is="sortIcon('monthly_rate')"
                class="h-3.5 w-3.5 text-muted-foreground"
              />
            </button>
          </TableHead>
          <TableHead>
            <button class="inline-flex items-center gap-1" @click="$emit('sort', 'active_contracts_count')">
              <span>Occupancy</span>
              <component
                :is="sortIcon('active_contracts_count')"
                class="h-3.5 w-3.5 text-muted-foreground"
              />
            </button>
          </TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="billboard in billboards"
          :key="billboard.id"
          class="hover:bg-muted/50 transition-colors"
        >
          <TableCell class="align-top">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-accent text-accent-foreground text-xs font-semibold">
                {{ billboard.code?.slice(0,2) || '?' }}
              </span>
              <div class="flex flex-col leading-tight">
                <span class="font-semibold tracking-tight">{{ billboard.code }}</span>
                <span class="text-[11px] text-muted-foreground">ID: {{ billboard.id }}</span>
              </div>
            </div>
          </TableCell>

          <TableCell class="align-top">
            <div class="flex flex-col gap-0.5">
              <span class="font-medium">{{ billboard.name }}</span>
              <span class="text-xs text-muted-foreground flex items-center">
                <MapPin class="w-3 h-3 mr-1" />
                {{ billboard.location }}
              </span>
            </div>
          </TableCell>

          <TableCell class="align-top">
            <div class="flex flex-col gap-0.5">
              <span class="font-medium">{{ billboard.dimensions.size }}</span>
              <span class="text-[11px] text-muted-foreground">{{ billboard.dimensions.area }} sq ft</span>
            </div>
          </TableCell>

          <TableCell class="align-top">
            <div class="inline-flex items-center gap-2">
              <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs border"
                    :class="[
                      getStatusVariant(billboard.status.current) === 'default' && 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-800',
                      getStatusVariant(billboard.status.current) === 'secondary' && 'bg-muted text-muted-foreground border-muted',
                      getStatusVariant(billboard.status.current) === 'destructive' && 'bg-destructive/10 text-destructive border-destructive/20',
                      getStatusVariant(billboard.status.current) === 'outline' && 'bg-background text-foreground border-border'
                    ]">
                <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotColor(billboard.status.current)" />
                <span>{{ billboard.status.label }}</span>
              </span>
            </div>
          </TableCell>

          <TableCell class="align-top">
            <div class="flex flex-col gap-0.5">
              <span class="font-semibold">{{ billboard.pricing.formatted_rate }}</span>
              <span class="text-[11px] text-muted-foreground">per month</span>
            </div>
          </TableCell>

          <TableCell class="align-top">
            <div class="flex items-center gap-2">
              <Badge :variant="billboard.contracts.is_occupied ? 'default' : 'outline'" class="text-xs">
                {{ billboard.contracts.is_occupied ? 'Occupied' : 'Available' }}
              </Badge>
              <span class="text-[11px] text-muted-foreground">
                {{ billboard.contracts.active_count }} active
              </span>
            </div>
          </TableCell>

          <TableCell class="text-right align-top">
            <div class="flex justify-end">
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                    <MoreHorizontal class="h-4 w-4" />
                    <span class="sr-only">Open row actions</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-44">
                  <DropdownMenuLabel>Actions</DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem v-if="billboard.actions.can_view" @click="$emit('view', billboard)">
                    <Eye class="h-4 w-4" /> View
                  </DropdownMenuItem>
                  <DropdownMenuItem v-if="billboard.actions.can_edit" @click="$emit('edit', billboard)">
                    <Edit class="h-4 w-4" /> Edit
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem v-if="billboard.actions.can_delete" variant="destructive" @click="$emit('delete', billboard)">
                    <Trash2 class="h-4 w-4" /> Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import { MapPin, Eye, Edit, Trash2, ArrowUpDown, ChevronUp, ChevronDown, MoreHorizontal } from 'lucide-vue-next'
import type { Billboard } from '@/types'

interface Props {
  billboards: Billboard[]
  sortBy?: string
  sortDirection?: 'asc' | 'desc'
}

const props = defineProps<Props>()

defineEmits<{
  edit: [billboard: Billboard]
  view: [billboard: Billboard]
  delete: [billboard: Billboard]
  sort: [field: string]
}>()

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'default'
    case 'available':
      return 'secondary'
    case 'maintenance':
      return 'destructive'
    case 'removed':
      return 'outline'
    default:
      return 'outline'
  }
}

const getStatusDotColor = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-500 dark:bg-emerald-400'
    case 'available':
      return 'bg-muted-foreground'
    case 'maintenance':
      return 'bg-destructive'
    case 'removed':
      return 'bg-foreground'
    default:
      return 'bg-muted'
  }
}

const sortIcon = (field: string) => {
  if (props.sortBy !== field) return ArrowUpDown
  return props.sortDirection === 'asc' ? ChevronUp : ChevronDown
}
</script>
