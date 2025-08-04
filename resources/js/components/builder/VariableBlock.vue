<template>
  <div class="variable-block inline-block">
    <span
      class="variable-tag px-2 py-1 bg-yellow-100 border border-yellow-300 rounded text-yellow-800 text-sm font-medium cursor-pointer hover:bg-yellow-200 transition-colors"
      :style="computedStyles"
      @click="editVariable"
    >
      {{ displayText }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  block: {
    id: string;
    type: string;
    content: string; // {{variable_key}}
    label?: string;
    styles?: Record<string, any>;
  };
}

interface Emits {
  (e: 'update', value: any): void;
  (e: 'focus'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const displayText = computed(() => {
  if (props.block.label) {
    return props.block.label;
  }

  // Extract variable name from {{variable_key}} format
  const match = props.block.content.match(/\{\{([^}]+)\}\}/);
  if (match) {
    return match[1].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }

  return props.block.content;
});

const computedStyles = computed(() => {
  const baseStyles = {
    background: '#fef3c7',
    border: '1px solid #f59e0b',
    color: '#92400e',
    padding: '4px 8px',
    borderRadius: '4px',
    fontSize: '14px',
    fontWeight: '500'
  };

  return { ...baseStyles, ...(props.block.styles || {}) };
});

const editVariable = () => {
  emit('focus');
  // Could open a variable selector dialog
};
</script>

<style scoped>
.variable-block {
  display: inline-block;
  vertical-align: baseline;
}

.variable-tag {
  user-select: none;
}
</style>
