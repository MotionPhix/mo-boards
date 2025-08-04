<template>
  <button
    v-if="permissions"
    @click="$emit('click')"
    class="w-full flex items-center gap-3 px-3 py-3 text-left rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 group"
    :class="{
      'opacity-50 cursor-not-allowed': !permissions,
      'hover:bg-blue-50 dark:hover:bg-blue-900/20': variant === 'primary',
      'hover:bg-gray-50 dark:hover:bg-gray-700': variant === 'secondary'
    }"
    :disabled="!permissions"
  >
    <div class="flex-shrink-0">
      <div
        class="w-8 h-8 rounded-md flex items-center justify-center"
        :class="{
          'bg-blue-100 text-blue-600 group-hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400': variant === 'primary',
          'bg-gray-100 text-gray-600 group-hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400': variant === 'secondary'
        }"
      >
        <component :is="iconComponent" class="w-4 h-4" />
      </div>
    </div>

    <div class="flex-1 min-w-0">
      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
        {{ title }}
      </div>
      <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
        {{ description }}
      </div>
    </div>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  Flag,
  FileText,
  ScrollText,
  UserPlus,
  Building,
  Settings,
  Plus
} from 'lucide-vue-next'

interface Props {
  icon: string
  title: string
  description: string
  permissions: boolean
  variant?: 'primary' | 'secondary'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary'
})

defineEmits<{
  click: []
}>()

// Map icon names to components
const iconMap = {
  Flag,
  FileText,
  ScrollText,
  UserPlus,
  Building,
  Settings,
  Plus
}

const iconComponent = computed(() => {
  return iconMap[props.icon as keyof typeof iconMap] || Plus
})
</script>
