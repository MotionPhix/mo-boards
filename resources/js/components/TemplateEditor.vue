<script setup lang="ts">
import DocumentEditor from '@/components/DocumentEditor.vue'

defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  placeholder: {
    type: String,
    default: 'Start typing your template content here...',
  },
})

const emit = defineEmits(['update:modelValue'])

// Forward modelValue changes from DocumentEditor to parent
function updateContent(content: string) {
  emit('update:modelValue', content)
}
</script>

<template>
  <div class="template-editor-wrapper">
    <DocumentEditor
      :model-value="modelValue"
      @update:model-value="updateContent"
    />
  </div>
</template>

<style>
.template-editor-wrapper {
  width: 100%;
}
</style>
