<template>
  <div class="overflow-x-auto">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Code</TableHead>
          <TableHead>Name</TableHead>
          <TableHead>Location</TableHead>
          <TableHead>Size</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Monthly Rate</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="billboard in billboards" :key="billboard.id">
          <TableCell class="font-medium">{{ billboard.code }}</TableCell>
          <TableCell>{{ billboard.name }}</TableCell>
          <TableCell>{{ billboard.location }}</TableCell>
          <TableCell>{{ billboard.size || 'N/A' }}</TableCell>
          <TableCell>
            <Badge :variant="getStatusVariant(billboard.status)">
              {{ billboard.status }}
            </Badge>
          </TableCell>
          <TableCell>${{ billboard.monthly_rate.toLocaleString() }}/month</TableCell>
          <TableCell class="text-right">
            <div class="flex justify-end space-x-2">
              <Button
                variant="outline"
                size="sm"
                @click="$emit('view', billboard)"
              >
                View
              </Button>
              <Button
                variant="outline"
                size="sm"
                @click="$emit('edit', billboard)"
              >
                Edit
              </Button>
              <Button
                variant="destructive"
                size="sm"
                @click="$emit('delete', billboard)"
              >
                Delete
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
</script>
