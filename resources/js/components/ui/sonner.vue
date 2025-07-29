<script setup lang="ts">
import { Toaster as SonnerToaster } from 'vue-sonner'

export interface ToasterProps {
  position?: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right' | 'top-center' | 'bottom-center'
  richColors?: boolean
  expand?: boolean
  duration?: number
  visibleToasts?: number
  closeButton?: boolean
  theme?: 'light' | 'dark' | 'system'
  style?: any
  offset?: string | number
  class?: string
}

const props = withDefaults(defineProps<ToasterProps>(), {
  position: 'top-right',
  richColors: false,
  expand: false,
  duration: 4000,
  visibleToasts: 3,
  closeButton: true,
  theme: 'system',
  style: undefined,
  offset: undefined,
  class: undefined,
})
</script>

<template>
  <SonnerToaster
    :theme="theme"
    :class="[
      'font-sans toaster group',
      props.class
    ]"
    :position="position"
    :rich-colors="richColors"
    :expand="expand"
    :duration="duration"
    :visible-toasts="visibleToasts"
    :close-button="closeButton"
    :style="style"
    :offset="offset"
  />
</template>

<style>
:root {
  --toast-bg: var(--background);
  --toast-text: var(--foreground);
  --toast-border: var(--border);
  --toast-success: hsl(var(--success));
  --toast-error: hsl(var(--destructive));
  --toast-warning: hsl(var(--warning));
  --toast-info: hsl(var(--primary));
}

.sonner-toast {
  background: var(--toast-bg) !important;
  color: var(--toast-text) !important;
  border: 1px solid var(--toast-border) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.sonner-toast[data-type="success"] {
  border-left: 3px solid var(--toast-success) !important;
}

.sonner-toast[data-type="error"] {
  border-left: 3px solid var(--toast-error) !important;
}

.sonner-toast[data-type="warning"] {
  border-left: 3px solid var(--toast-warning) !important;
}

.sonner-toast[data-type="info"] {
  border-left: 3px solid var(--toast-info) !important;
}

.sonner-toast-error {
  color: var(--toast-error) !important;
}

.sonner-toast-success {
  color: var(--toast-success) !important;
}

.sonner-toast-warning {
  color: var(--toast-warning) !important;
}

.sonner-toast-info {
  color: var(--toast-info) !important;
}

.sonner-loader {
  border-color: var(--toast-border) !important;
}

.dark .sonner-toast {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
</style>
