<template>
  <div class="overflow-x-auto">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Code</TableHead>
          <TableHead>Name & Location</TableHead>
          <TableHead>Dimensions</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Pricing</TableHead>
          <TableHead>Occupancy</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="billboard in billboards"
          :key="billboard.id"
          class="hover:bg-muted/50 transition-colors"
        >
          <TableCell class="font-medium">
            <div class="flex flex-col">
              <span class="font-semibold">{{ billboard.code }}</span>
            </div>
          </TableCell>

          <TableCell>
            <div class="flex flex-col space-y-1">
              <span class="font-medium">{{ billboard.name }}</span>
              <span class="text-sm text-muted-foreground flex items-center">
                <MapPin class="w-3 h-3 mr-1" />
                {{ billboard.location }}
              </span>
            </div>
          </TableCell>

          <TableCell>
            <div class="flex flex-col space-y-1">
              <span class="font-medium">{{ billboard.dimensions.size }}</span>
              <span class="text-xs text-muted-foreground">{{ billboard.dimensions.area }} sq ft</span>
            </div>
          </TableCell>

          <TableCell>
            <Badge :variant="getStatusVariant(billboard.status.current)">
              <div class="flex items-center space-x-1">
                <div
                  class="w-2 h-2 rounded-full"
                  :class="getStatusDotColor(billboard.status.current)"
                ></div>
                <span>{{ billboard.status.label }}</span>
              </div>
            </Badge>
          </TableCell>

          <TableCell>
            <div class="flex flex-col space-y-1">
              <span class="font-semibold">{{ billboard.pricing.formatted_rate }}</span>
              <span class="text-xs text-muted-foreground">per month</span>
            </div>
          </TableCell>

          <TableCell>
            <div class="flex items-center space-x-2">
              <Badge
                :variant="billboard.contracts.is_occupied ? 'default' : 'outline'"
                class="text-xs"
              >
                {{ billboard.contracts.is_occupied ? 'Occupied' : 'Available' }}
              </Badge>
              <span class="text-xs text-muted-foreground">
                {{ billboard.contracts.active_count }} active
              </span>
            </div>
          </TableCell>

          <TableCell class="text-right">
            <div class="flex justify-end space-x-1">
              <Button
                v-if="billboard.actions.can_view"
                variant="ghost"
                size="sm"
                @click="$emit('view', billboard)"
                class="h-8 w-8 p-0"
              >
                <Eye class="h-4 w-4" />
              </Button>
              <Button
                v-if="billboard.actions.can_edit"
                variant="ghost"
                size="sm"
                @click="$emit('edit', billboard)"
                class="h-8 w-8 p-0"
              >
                <Edit class="h-4 w-4" />
              </Button>
              <Button
                v-if="billboard.actions.can_delete"
                variant="ghost"
                size="sm"
                @click="$emit('delete', billboard)"
                class="h-8 w-8 p-0 text-destructive hover:bg-destructive/10"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
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
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import { MapPin, Eye, Edit, Trash2 } from 'lucide-vue-next'
import type { Billboard } from '@/types'

interface Props {
  billboards: Billboard[]
}

defineProps<Props>()

defineEmits<{
  edit: [billboard: Billboard]
  view: [billboard: Billboard]
  delete: [billboard: Billboard]
}>()

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'default'
    case 'inactive':
      return 'secondary'
    case 'maintenance':
      return 'destructive'
    default:
      return 'outline'
  }
}

const getStatusDotColor = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-500 dark:bg-emerald-400'
    case 'inactive':
      return 'bg-muted-foreground'
    case 'maintenance':
      return 'bg-destructive'
    default:
      return 'bg-muted'
  }
}
</script>
