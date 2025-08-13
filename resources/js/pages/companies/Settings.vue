<script setup lang="ts">
import { router } from '@inertiajs/vue3'

const r = (name: string, params?: Record<string, any>, absolute = false) => (window as any).route?.(name, params, absolute)

// Forward old monolithic settings page to the new Profile subpage to keep deep links working
router.visit(r('companies.settings.profile'), { replace: true })
</script>

<template>
  <div />
</template>