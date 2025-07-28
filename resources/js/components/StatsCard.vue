<template>
  <Card>
    <CardContent class="p-6">
      <div class="flex items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">
          {{ title }}
        </CardTitle>
        <component
          :is="iconComponent"
          :class="`h-4 w-4 text-${color}-600`"
        />
      </div>
      <div class="text-2xl font-bold">{{ value }}</div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  MapPin,
  DollarSign,
  TrendingUp,
  Users,
  type LucideIcon
} from 'lucide-vue-next'
import { Card, CardContent, CardTitle } from '@/components/ui/card'

interface Props {
  title: string
  value: string | number
  icon: string
  color: 'blue' | 'green' | 'purple' | 'orange'
}

const props = defineProps<Props>()

const iconMap: Record<string, LucideIcon> = {
  MapPin,
  DollarSign,
  TrendingUp,
  Users,
}

const iconComponent = computed(() => iconMap[props.icon])
</script>
