<template>
  <div class="space-y-4">
    <div v-if="activities.length === 0" class="text-center py-8 text-muted-foreground">
      No recent activity to display
    </div>

    <div
      v-for="activity in activities"
      :key="activity.id"
      class="flex items-center space-x-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
    >
      <div class="flex-shrink-0">
        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
          <MapPin class="h-4 w-4 text-blue-600" />
        </div>
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-gray-900">
            {{ activity.billboard_name }}
          </p>
          <Badge :variant="getStatusVariant(activity.status)">
            {{ activity.status }}
          </Badge>
        </div>
        <p class="text-sm text-muted-foreground truncate">
          {{ activity.location }}
        </p>
      </div>

      <div class="flex-shrink-0 text-xs text-muted-foreground">
        {{ formatDate(activity.updated_at) }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MapPin } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface Activity {
  id: number
  billboard_name: string
  location: string
  status: string
  updated_at: string
}

interface Props {
  activities: Activity[]
}

defineProps<Props>()

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

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffDays = Math.floor(diffHours / 24)

  if (diffHours < 1) {
    return 'Just now'
  } else if (diffHours < 24) {
    return `${diffHours} hours ago`
  } else if (diffDays === 1) {
    return 'Yesterday'
  } else {
    return `${diffDays} days ago`
  }
}
</script>
