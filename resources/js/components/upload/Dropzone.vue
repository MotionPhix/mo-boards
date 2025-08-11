<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  accept?: string
  maxFiles?: number
  maxSize?: number // in bytes
  multiple?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  accept: '',
  maxFiles: 10,
  maxSize: 5 * 1024 * 1024, // 5MB default
  multiple: true,
})

const emit = defineEmits<{
  (e: 'files-selected', files: File[]): void
  (e: 'file-rejected', payload: { file: File; reason: string }): void
}>()

const isDragging = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)

function openPicker() {
  inputRef.value?.click()
}

function onDragOver(e: DragEvent) {
  e.preventDefault(); isDragging.value = true
}

function onDragLeave(e: DragEvent) {
  e.preventDefault(); isDragging.value = false
}

function handleFiles(list: FileList | File[]) {
  const files = Array.from(list)
  const accepted = props.accept ? props.accept.split(',').map(s => s.trim()) : []
  const out: File[] = []
  for (const file of files) {
    if (accepted.length && !accepted.includes(file.type)) {
      emit('file-rejected', { file, reason: 'Invalid type' }); continue
    }
    if (file.size > props.maxSize) {
      emit('file-rejected', { file, reason: 'File too large' }); continue
    }
    if (props.multiple && out.length >= props.maxFiles) {
      emit('file-rejected', { file, reason: 'Too many files' }); break
    }
    out.push(file)
    if (!props.multiple) break
  }
  if (out.length) emit('files-selected', out)
}

function onDrop(e: DragEvent) {
  e.preventDefault(); isDragging.value = false
  const fl = e.dataTransfer?.files
  if (!fl || !fl.length) return
  handleFiles(fl)
}

function onChange(e: Event) {
  const el = e.target as HTMLInputElement
  if (!el.files) return
  handleFiles(el.files)
  el.value = '' // reset to allow re-selecting same files
}
</script>

<template>
  <div
    class="rounded-md border border-dashed border-border bg-background p-4 text-center cursor-pointer hover:bg-accent/30 transition"
    :class="{ 'ring-2 ring-primary': isDragging }"
    @dragover="onDragOver"
    @dragleave="onDragLeave"
    @drop="onDrop"
    @click="openPicker"
  >
    <slot>
      <p class="text-sm text-muted-foreground">Drag & drop files here, or click to select</p>
    </slot>
    <input
      ref="inputRef"
      type="file"
      class="sr-only"
      :accept="props.accept"
      :multiple="props.multiple"
      @change="onChange"
    />
  </div>
  <slot name="helper" />
</template>
