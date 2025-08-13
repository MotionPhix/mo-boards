<script setup lang="ts">
import Base from '@/pages/companies/Transactions.vue'
const props = defineProps<{ transactions: any }>()
</script>

<template>
  <Base v-bind="props" />
</template>
