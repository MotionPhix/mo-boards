<template>
  <Link
    :href="href"
    :class="[
      'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-150 ease-in-out',
      active
        ? 'border-blue-500 text-gray-900'
        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
    ]">
    <slot />
  </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface Props {
  href: string
  active?: boolean
}

defineProps<Props>()
</script>
