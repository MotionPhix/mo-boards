<template>
  <div>
    <Label class="text-foreground mb-1 block">{{ label }}</Label>
    <Dropzone
      :accept="accept"
      :max-files="maxFiles"
      :max-size="maxSize"
      :multiple="multiple"
      @files-selected="onFilesSelected"
      @file-rejected="onFileRejected"
    >
      <template #default>
        <p class="text-sm text-muted-foreground">
          <slot>Drag & drop images here, or click to select</slot>
        </p>
      </template>
      <template #helper>
        <p class="text-xs text-muted-foreground mt-1">
          <slot name="helper">JPEG/PNG up to {{ Math.floor(maxSize / (1024*1024)) }}MB each. Max {{ maxFiles }} files.</slot>
        </p>
      </template>
    </Dropzone>

    <!-- Existing images -->
    <div v-if="existing.length" class="mt-4">
      <h4 class="text-sm font-medium text-foreground mb-2">Existing</h4>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <div v-for="img in existing" :key="`existing-${img.id}`" class="relative group rounded-md overflow-hidden border border-border bg-muted/20">
          <img :src="img.preview_url || img.url" :alt="img.name" class="w-full h-32 object-cover" />
          <button
            v-if="canDelete"
            type="button"
            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition bg-destructive text-destructive-foreground rounded px-2 py-1 text-xs"
            @click="$emit('delete-existing', img.id)"
          >Delete</button>
        </div>
      </div>
    </div>

    <!-- New selection previews -->
    <ImagePreviewGrid v-if="previews.length" class="mt-4" :images="previews.map((src) => ({ src }))" @remove="removeNew" />
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue'
import Dropzone from '@/components/upload/Dropzone.vue'
import ImagePreviewGrid from '@/components/upload/ImagePreviewGrid.vue'
import { Label } from '@/components/ui/label'
import { toast } from 'vue-sonner'

interface ExistingMedia { id: number; name: string; url: string; preview_url?: string }

const props = withDefaults(defineProps<{
  label?: string
  accept?: string
  maxFiles?: number
  maxSize?: number
  multiple?: boolean
  existing?: ExistingMedia[]
  files?: File[]
  canDelete?: boolean
}>(), {
  label: 'Images',
  accept: 'image/jpeg,image/png',
  maxFiles: 10,
  maxSize: 5 * 1024 * 1024,
  multiple: true,
  existing: () => [],
  files: () => [],
  canDelete: false,
})

const emit = defineEmits<{
  (e: 'update:files', files: File[]): void
  (e: 'file-rejected', payload: { file: File; reason: string }): void
  (e: 'delete-existing', id: number): void
}>()

const selected = ref<File[]>([...props.files])
const previews = ref<string[]>([])

watch(() => props.files, (val) => {
  selected.value = [...val]
  rebuildPreviews()
})

function rebuildPreviews() {
  // clear existing previews
  previews.value.forEach((url) => URL.revokeObjectURL(url))
  previews.value = selected.value.map((f) => URL.createObjectURL(f))
}

function onFilesSelected(files: File[]) {
  for (const file of files) {
    if (selected.value.length >= props.maxFiles) {
      toast.error('Too many files', { description: `Maximum ${props.maxFiles} images allowed` })
      break
    }
    if (!props.accept.split(',').includes(file.type)) {
      emit('file-rejected', { file, reason: 'unsupported' })
      toast.error('Unsupported file', { description: `${file.name} is not an accepted type` })
      continue
    }
    if (file.size > props.maxSize) {
      emit('file-rejected', { file, reason: 'too-large' })
      toast.error('File too large', { description: `${file.name} exceeds ${Math.floor(props.maxSize/(1024*1024))}MB` })
      continue
    }
    selected.value.push(file)
  }
  emit('update:files', [...selected.value])
  rebuildPreviews()
}

function onFileRejected(payload: { file: File; reason: string }) {
  emit('file-rejected', payload)
}

function removeNew(index: number) {
  selected.value.splice(index, 1)
  const [url] = previews.value.splice(index, 1)
  if (url) URL.revokeObjectURL(url)
  emit('update:files', [...selected.value])
}

onBeforeUnmount(() => {
  previews.value.forEach((url) => URL.revokeObjectURL(url))
})

const existing = props.existing
const canDelete = props.canDelete
</script>

<style scoped>
</style>
