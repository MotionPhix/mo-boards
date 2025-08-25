<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Step1 from './register/Steps/Step1.vue'
import Step2 from './register/Steps/Step2.vue'
import Step3 from './register/Steps/Step3.vue'
import { Toaster } from 'vue-sonner'
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
  step: number
  plans?: {
    name: string
    price: number
    features: string[]
    recommended?: boolean
  }[]
}>()
</script>

<template>

  <AuthSimpleLayout>
    <Head title="Register" />

    <Toaster rich-colors />

    <Step1 v-if="step === 1" />
    <Step2 v-else-if="step === 2" />
    <Step3 v-else-if="step === 3" :plans="plans ?? []" />

  </AuthSimpleLayout>

</template>
