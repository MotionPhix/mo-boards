<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  }
})

const emit = defineEmits(['update:modelValue'])

const termsText = ref(JSON.stringify(props.modelValue, null, 2))

watch(termsText, (newValue) => {
  try {
    const parsedValue = JSON.parse(newValue)
    emit('update:modelValue', parsedValue)
  } catch (error) {
    // Invalid JSON, keep the original value
    console.error('Invalid JSON in DefaultTermsEditor:', error)
  }
})

watch(() => props.modelValue, (newValue) => {
  termsText.value = JSON.stringify(newValue, null, 2)
}, { deep: true })
</script>

<template>
  <div class="default-terms-editor">
    <textarea
      v-model="termsText"
      rows="5"
      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
      placeholder='{"payment_terms": "30 days", "cancellation_policy": "7 days notice"}'
    ></textarea>
  </div>
</template>
