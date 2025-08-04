<template>
  <div
    class="text-block p-2 min-h-12 hover:bg-gray-50 cursor-text"
    @click="focusEditor"
    :style="computedStyles"
  >
    <div
      ref="editor"
      contenteditable
      @input="updateContent"
      @blur="onBlur"
      @focus="onFocus"
      class="outline-none"
      v-html="block.content"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Props {
  block: {
    id: string;
    type: string;
    content: string;
    styles?: Record<string, any>;
  };
}

interface Emits {
  (e: 'update', value: any): void;
  (e: 'focus'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const editor = ref<HTMLElement>();

const computedStyles = computed(() => {
  const baseStyles = {
    fontSize: '14px',
    lineHeight: '1.6',
    color: '#000',
    padding: '8px',
    minHeight: '48px'
  };

  return { ...baseStyles, ...(props.block.styles || {}) };
});

const focusEditor = () => {
  editor.value?.focus();
  emit('focus');
};

const updateContent = (event: Event) => {
  const target = event.target as HTMLElement;
  emit('update', { content: target.innerHTML });
};

const onFocus = () => {
  emit('focus');
};

const onBlur = () => {
  // Optional: save state or validation
};

onMounted(() => {
  if (editor.value && !props.block.content) {
    editor.value.innerHTML = 'Click to edit text...';
  }
});
</script>

<style scoped>
.text-block {
  border: 1px solid transparent;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.text-block:hover {
  border-color: #e5e7eb;
}

.text-block:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}
</style>
