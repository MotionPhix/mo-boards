<script setup lang="ts">
import { computed } from 'vue'
import { cva } from 'class-variance-authority'
import { XCircle } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  id: string
  title?: string
  description?: string
  variant?: 'default' | 'destructive'
  action?: {
    label: string
    onClick: () => void
  }
}>()

const emit = defineEmits<{
  dismiss: [id: string]
}>()

const toastVariants = cva(
  'group pointer-events-auto relative flex w-full items-center justify-between space-x-2 overflow-hidden rounded-md border p-4 pr-6 shadow-lg transition-all',
  {
    variants: {
      variant: {
        default: 'bg-background border',
        destructive:
          'destructive group border-destructive bg-destructive text-destructive-foreground',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
)

const titleClass = computed(() => {
  return props.variant === 'destructive'
    ? 'text-destructive-foreground font-semibold text-sm'
    : 'text-foreground font-semibold text-sm'
})

const descriptionClass = computed(() => {
  return props.variant === 'destructive'
    ? 'text-destructive-foreground opacity-90 text-sm'
    : 'text-muted-foreground text-sm'
})

const dismissToast = () => {
  emit('dismiss', props.id)
}
</script>

<template>
  <div :class="toastVariants({ variant })" class="data-[swipe=move]:transition-none data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=end]:animate-out data-[swipe=end]:fade-out-80 slide-in-from-right">
    <div class="flex flex-col gap-1">
      <div v-if="title" :class="titleClass">{{ title }}</div>
      <div v-if="description" :class="descriptionClass">{{ description }}</div>
    </div>

    <div class="flex items-center gap-2">
      <Button v-if="action" size="sm" @click="action.onClick">
        {{ action.label }}
      </Button>
      <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-full" @click="dismissToast">
        <XCircle class="h-4 w-4" />
        <span class="sr-only">Close</span>
      </Button>
    </div>
  </div>
</template>
