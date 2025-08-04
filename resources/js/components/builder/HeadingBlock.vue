<template>
  <div
    class="heading-block p-2 min-h-12 hover:bg-gray-50 cursor-text"
    @click="focusEditor"
    :style="computedStyles"
  >
    <component
      :is="headingTag"
      ref="editor"
      contenteditable
      @input="updateContent"
      @blur="onBlur"
      @focus="onFocus"
      class="outline-none font-bold"
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
    level?: number;
  };
}

interface Emits {
  (e: 'update', value: any): void;
  (e: 'focus'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const editor = ref<HTMLElement>();

const headingTag = computed(() => {
  const level = props.block.level || 2;
  return `h${Math.min(Math.max(level, 1), 6)}`;
});

const computedStyles = computed(() => {
  const level = props.block.level || 2;
  const baseFontSize = level === 1 ? '28px' : level === 2 ? '24px' : level === 3 ? '20px' : '18px';

  const baseStyles = {
    fontSize: baseFontSize,
    fontWeight: 'bold',
    color: '#000',
    padding: '8px',
    marginTop: level === 1 ? '24px' : '16px',
    marginBottom: '12px',
    lineHeight: '1.2'
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
    editor.value.innerHTML = 'Heading Text';
  }
});
</script>

<style scoped>
.heading-block {
  border: 1px solid transparent;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.heading-block:hover {
  border-color: #e5e7eb;
}

.heading-block:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}
</style>
