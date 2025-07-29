<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useToast, type Toast } from '@/composables/useToast'
import { onClickOutside } from '@vueuse/core'
import UiToast from '@/components/ui/toast.vue'

const { toasts, dismiss } = useToast()
const toast = ref<HTMLDivElement | null>(null)

onMounted(() => {
  // Add keyboard event listener for Escape key
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})

function handleKeyDown(event: KeyboardEvent) {
  if (event.key === 'Escape' && toasts.value.length > 0) {
    // Remove the most recent toast
    const mostRecentToast = toasts.value[toasts.value.length - 1]
    if (mostRecentToast) {
      dismiss(mostRecentToast.id)
    }
  }
}

// Auto-dismiss on click outside
onClickOutside(toast, () => {
  if (toasts.value.length > 0) {
    const mostRecentToast = toasts.value[toasts.value.length - 1]
    if (mostRecentToast) {
      dismiss(mostRecentToast.id)
    }
  }
})
</script>

<template>
  <div
    ref="toast"
    class="fixed bottom-0 right-0 z-[100] flex max-h-screen w-full flex-col-reverse gap-2 p-4 sm:max-w-[420px] sm:bottom-0 sm:right-0 sm:flex-col-reverse sm:top-auto"
  >
    <TransitionGroup name="toast">
      <UiToast
        v-for="toast in toasts"
        :key="toast.id"
        :id="toast.id"
        :title="toast.title"
        :description="toast.description"
        :action="toast.action"
        :variant="toast.variant"
        @dismiss="dismiss"
      />
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
