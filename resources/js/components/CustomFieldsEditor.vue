<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
  }
})

const emit = defineEmits(['update:modelValue'])

const fieldsText = ref(JSON.stringify(props.modelValue, null, 2))

watch(fieldsText, (newValue) => {
  try {
    const parsedValue = JSON.parse(newValue)
    emit('update:modelValue', parsedValue)
  } catch (error) {
    // Invalid JSON, keep the original value
    console.error('Invalid JSON in CustomFieldsEditor:', error)
  }
})

watch(() => props.modelValue, (newValue) => {
  fieldsText.value = JSON.stringify(newValue, null, 2)
}, { deep: true })
</script>

<template>
  <div class="custom-fields-editor">
    <textarea
      v-model="fieldsText"
      rows="5"
      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
      placeholder='[{"name": "client_name", "type": "text", "label": "Client Name", "required": true}]'
    ></textarea>
  </div>
</template>
