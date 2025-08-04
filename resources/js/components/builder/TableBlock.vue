<template>
  <div class="table-block">
    <div class="table-controls flex items-center gap-2 mb-2">
      <button @click="addRow" class="text-sm bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200">
        + Row
      </button>
      <button @click="addColumn" class="text-sm bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200">
        + Column
      </button>
      <button @click="removeRow" class="text-sm bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
        - Row
      </button>
      <button @click="removeColumn" class="text-sm bg-orange-100 text-orange-700 px-2 py-1 rounded hover:bg-orange-200">
        - Column
      </button>
    </div>

    <table
      class="table-content w-full border-collapse"
      :style="tableStyles"
    >
      <tbody>
        <tr
          v-for="(row, rowIndex) in tableData"
          :key="rowIndex"
          class="table-row"
        >
          <td
            v-for="(cell, colIndex) in row"
            :key="colIndex"
            class="table-cell border border-gray-300 p-2 cursor-text hover:bg-gray-50"
            :style="cellStyles"
            @click="focusCell(rowIndex, colIndex)"
          >
            <span
              :ref="(el) => setCellRef(rowIndex, colIndex, el as HTMLElement)"
              contenteditable
              @input="updateCell(rowIndex, colIndex, $event)"
              @focus="onFocus"
              class="outline-none block"
              v-html="cell"
            />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';

interface Props {
  block: {
    id: string;
    type: string;
    content: string; // JSON stringified 2D array
    styles?: Record<string, any>;
  };
}

interface Emits {
  (e: 'update', value: any): void;
  (e: 'focus'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const cellRefs = ref<Record<string, HTMLElement>>({});

const tableData = computed(() => {
  try {
    const parsed = JSON.parse(props.block.content || '[]');
    if (parsed.length === 0) {
      return [['Cell 1', 'Cell 2'], ['Cell 3', 'Cell 4']];
    }
    return parsed;
  } catch {
    return [['Cell 1', 'Cell 2'], ['Cell 3', 'Cell 4']];
  }
});

const tableStyles = computed(() => ({
  fontSize: '14px',
  lineHeight: '1.6',
  ...props.block.styles
}));

const cellStyles = computed(() => ({
  minWidth: '100px',
  verticalAlign: 'top'
}));

const setCellRef = (rowIndex: number, colIndex: number, el: HTMLElement) => {
  if (el) {
    cellRefs.value[`${rowIndex}-${colIndex}`] = el;
  }
};

const updateCell = (rowIndex: number, colIndex: number, event: Event) => {
  const target = event.target as HTMLElement;
  const newData = [...tableData.value];
  newData[rowIndex][colIndex] = target.innerHTML;

  emit('update', { content: JSON.stringify(newData) });
};

const addRow = () => {
  const newData = [...tableData.value];
  const columnCount = newData[0]?.length || 2;
  const newRow = Array(columnCount).fill('New cell');
  newData.push(newRow);

  emit('update', { content: JSON.stringify(newData) });
};

const addColumn = () => {
  const newData = tableData.value.map((row: string[]) => [...row, 'New cell']);
  emit('update', { content: JSON.stringify(newData) });
};

const removeRow = () => {
  if (tableData.value.length > 1) {
    const newData = [...tableData.value];
    newData.pop();
    emit('update', { content: JSON.stringify(newData) });
  }
};

const removeColumn = () => {
  if (tableData.value[0]?.length > 1) {
    const newData = tableData.value.map((row: string[]) => row.slice(0, -1));
    emit('update', { content: JSON.stringify(newData) });
  }
};

const focusCell = (rowIndex: number, colIndex: number) => {
  nextTick(() => {
    cellRefs.value[`${rowIndex}-${colIndex}`]?.focus();
  });
  emit('focus');
};

const onFocus = () => {
  emit('focus');
};
</script>

<style scoped>
.table-block {
  padding: 8px;
  border: 1px solid transparent;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.table-block:hover {
  border-color: #e5e7eb;
}

.table-block:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}

.table-cell {
  transition: background-color 0.2s ease;
}

.table-cell:hover {
  background-color: #f9fafb;
}

.table-cell:focus-within {
  background-color: #eff6ff;
}
</style>
