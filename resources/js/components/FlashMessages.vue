<template>
  <!-- No UI here; we convert server flash messages to toasts -->
  <span style="display:none" />
 </template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
import { toast } from 'vue-sonner'

const page = usePage()

// Show toasts when flash props change
watch(
  () => (page.props as any).flash,
  (flash: any) => {
    if (!flash) return

    if (flash.success) {
      toast.success(String(flash.success))
    }

    if (flash.error) {
      toast.error(String(flash.error))
    }

    if (flash.warning) {
      toast.warning(String(flash.warning))
    }

    if (flash.info) {
      toast.info(String(flash.info))
    }
  },
  { deep: true, immediate: true }
)
</script>
