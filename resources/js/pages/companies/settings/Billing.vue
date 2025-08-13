<script setup lang="ts">
import Base from '@/pages/companies/Billing.vue'
const props = defineProps<{ company: any; plans: any[]; currentPlan: string; transactions: any[] }>()
</script>

<template>
  <Base v-bind="props" />
</template>
