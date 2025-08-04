<template>
  <div class="list-block">
    <div class="list-header flex items-center gap-2 mb-2">
      <select
        v-model="listType"
        @change="updateListType"
        class="text-sm border rounded px-2 py-1"
      >
        <option value="ul">Bullet List</option>
        <option value="ol">Numbered List</option>
      </select>
    </div>

    <component
      :is="listType"
      class="list-content pl-4"
      :style="computedStyles"
    >
      <li
        v-for="(item, index) in listItems"
        :key="index"
        class="list-item py-1 cursor-text hover:bg-gray-50"
        @click="focusItem(index)"
      >
        <span
          :ref="(el) => itemRefs[index] = el as HTMLElement"
          contenteditable
          @input="updateItem(index, $event)"
          @keydown="handleKeyDown(index, $event)"
          @focus="onFocus"
          class="outline-none block"
          v-html="item"
        />
      </li>
    </component>

    <button
      @click="addItem"
      class="mt-2 text-sm text-blue-600 hover:text-blue-800"
    >
      + Add item
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';

interface Props {
  block: {
    id: string;
    type: string;
    content: string; // JSON stringified array
    styles?: Record<string, any>;
    listType?: 'ul' | 'ol';
  };
}

interface Emits {
  (e: 'update', value: any): void;
  (e: 'focus'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const listType = ref<'ul' | 'ol'>(props.block.listType || 'ul');
const itemRefs = ref<(HTMLElement | null)[]>([]);

const listItems = computed(() => {
  try {
    return JSON.parse(props.block.content || '["Item 1", "Item 2"]');
  } catch {
    return ['Item 1', 'Item 2'];
  }
});

const computedStyles = computed(() => {
  const baseStyles = {
    fontSize: '14px',
    lineHeight: '1.6',
    color: '#000'
  };

  return { ...baseStyles, ...(props.block.styles || {}) };
});

const updateItem = (index: number, event: Event) => {
  const target = event.target as HTMLElement;
  const newItems = [...listItems.value];
  newItems[index] = target.innerHTML;

  emit('update', {
    content: JSON.stringify(newItems),
    listType: listType.value
  });
};

const addItem = () => {
  const newItems = [...listItems.value, 'New item'];
  emit('update', {
    content: JSON.stringify(newItems),
    listType: listType.value
  });

  nextTick(() => {
    const lastIndex = newItems.length - 1;
    focusItem(lastIndex);
  });
};

const focusItem = (index: number) => {
  nextTick(() => {
    itemRefs.value[index]?.focus();
  });
  emit('focus');
};

const handleKeyDown = (index: number, event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    event.preventDefault();
    addItem();
  } else if (event.key === 'Backspace' && !itemRefs.value[index]?.textContent?.trim()) {
    if (listItems.value.length > 1) {
      const newItems = listItems.value.filter((_: string, i: number) => i !== index);
      emit('update', {
        content: JSON.stringify(newItems),
        listType: listType.value
      });
    }
  }
};

const updateListType = () => {
  emit('update', {
    content: props.block.content,
    listType: listType.value
  });
};

const onFocus = () => {
  emit('focus');
};
</script>

<style scoped>
.list-block {
  padding: 8px;
  border: 1px solid transparent;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.list-block:hover {
  border-color: #e5e7eb;
}

.list-block:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}

.list-item {
  margin: 4px 0;
}
</style>
