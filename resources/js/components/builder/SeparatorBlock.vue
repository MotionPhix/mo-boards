<template>
  <div class="separator-block">
    <div class="separator-controls flex items-center gap-2 mb-2">
      <select
        v-model="separatorType"
        @change="updateSeparatorType"
        class="text-sm border rounded px-2 py-1"
      >
        <option value="hr">Horizontal Line</option>
        <option value="space">Space</option>
        <option value="pagebreak">Page Break</option>
      </select>

      <div v-if="separatorType === 'space'" class="flex items-center gap-1">
        <label class="text-sm">Height:</label>
        <input
          v-model="spaceHeight"
          @input="updateSpaceHeight"
          type="number"
          min="10"
          max="200"
          class="w-16 text-sm border rounded px-2 py-1"
        />
        <span class="text-sm text-gray-500">px</span>
      </div>
    </div>

    <!-- Horizontal Line -->
    <div
      v-if="separatorType === 'hr'"
      class="separator-preview"
      :style="hrStyles"
    >
      <hr style="border: none; border-top: 1px solid #ddd; margin: 0;" />
    </div>

    <!-- Space -->
    <div
      v-else-if="separatorType === 'space'"
      class="separator-preview bg-gray-50 border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-500 text-sm"
      :style="spaceStyles"
    >
      Space ({{ spaceHeight }}px)
    </div>

    <!-- Page Break -->
    <div
      v-else-if="separatorType === 'pagebreak'"
      class="separator-preview bg-blue-50 border-2 border-dashed border-blue-300 flex items-center justify-center text-blue-600 text-sm py-4"
      :style="pageBreakStyles"
    >
      📄 Page Break
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface Props {
  block: {
    id: string;
    type: string;
    content: string;
    styles?: Record<string, any>;
    separatorType?: 'hr' | 'space' | 'pagebreak';
    spaceHeight?: number;
  };
}

interface Emits {
  (e: 'update', value: any): void;
  (e: 'focus'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const separatorType = ref<'hr' | 'space' | 'pagebreak'>(props.block.separatorType || 'hr');
const spaceHeight = ref(props.block.spaceHeight || 20);

const hrStyles = computed(() => ({
  margin: '16px 0',
  ...props.block.styles
}));

const spaceStyles = computed(() => ({
  height: `${spaceHeight.value}px`,
  minHeight: '20px',
  ...props.block.styles
}));

const pageBreakStyles = computed(() => ({
  pageBreakAfter: 'always',
  breakAfter: 'page',
  margin: '16px 0',
  ...props.block.styles
}));

const updateSeparatorType = () => {
  emit('update', {
    separatorType: separatorType.value,
    spaceHeight: spaceHeight.value
  });
  emit('focus');
};

const updateSpaceHeight = () => {
  emit('update', {
    separatorType: separatorType.value,
    spaceHeight: spaceHeight.value
  });
};

// Generate inline styles for export
const getInlineStyles = () => {
  if (separatorType.value === 'hr') {
    return 'border: none; border-top: 1px solid #ddd; margin: 16px 0;';
  } else if (separatorType.value === 'space') {
    return `height: ${spaceHeight.value}px; display: block;`;
  } else if (separatorType.value === 'pagebreak') {
    return 'page-break-after: always; break-after: page; height: 0; margin: 0;';
  }
  return '';
};

defineExpose({
  getInlineStyles
});
</script>

<style scoped>
.separator-block {
  padding: 8px;
  border: 1px solid transparent;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.separator-block:hover {
  border-color: #e5e7eb;
}

.separator-block:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}

.separator-preview {
  min-height: 20px;
}
</style>
