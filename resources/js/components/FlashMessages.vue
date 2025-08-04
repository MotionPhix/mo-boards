<template>
  <div class="fixed top-4 left-4 space-y-2 z-50">
    <Alert v-if="flash.success" variant="default" class="w-96">
      <CheckCircle class="h-4 w-4" />
      <AlertTitle>Success</AlertTitle>
      <AlertDescription>{{ flash.success }}</AlertDescription>
    </Alert>

    <Alert v-if="flash.error" variant="destructive" class="w-96">
      <AlertCircle class="h-4 w-4" />
      <AlertTitle>Error</AlertTitle>
      <AlertDescription>{{ flash.error }}</AlertDescription>
    </Alert>

    <Alert v-if="flash.info" variant="default" class="w-96">
      <Info class="h-4 w-4" />
      <AlertTitle>Info</AlertTitle>
      <AlertDescription>{{ flash.info }}</AlertDescription>
    </Alert>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { CheckCircle, AlertCircle, Info } from 'lucide-vue-next'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import type { PageProps } from '@/types'

const page = usePage<PageProps>()
const flash = page.props.flash
</script>
