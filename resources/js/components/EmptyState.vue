<template>
  <div class="text-center py-12">
    <div class="flex justify-center mb-4">
      <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
        <component
          :is="icon"
          :class="iconClass"
        />
      </div>
    </div>

    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
      {{ title }}
    </h3>

    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
      {{ description }}
    </p>

    <div v-if="$slots.actions" class="space-y-3">
      <slot name="actions" />
    </div>

    <Button
      v-else-if="actionLabel && actionHandler"
      @click="actionHandler"
      class="inline-flex items-center"
    >
      <component
        v-if="actionIcon"
        :is="actionIcon"
        class="w-4 h-4 mr-2"
      />
      {{ actionLabel }}
    </Button>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import type { Component } from 'vue'

interface Props {
  title: string
  description: string
  icon: Component
  iconClass?: string
  actionLabel?: string
  actionIcon?: Component
  actionHandler?: () => void
}

withDefaults(defineProps<Props>(), {
  iconClass: 'w-12 h-12 text-gray-400 dark:text-gray-500',
})
</script>
