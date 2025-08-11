<script setup lang="ts">
import { ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

interface MediaItem {
  id: number
  name: string
  url: string
  preview_url?: string
}

defineProps<{ media: MediaItem[]; canDelete?: boolean }>()
const emit = defineEmits<{ (e: 'delete', id: number): void }>()

const lightboxOpen = ref(false)
const lightboxImage = ref<string | null>(null)

function openLightbox(src: string) {
  lightboxImage.value = src
  lightboxOpen.value = true
}
</script>

<template>
  <div>
    <div v-if="media?.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <div v-for="img in media" :key="img.id" class="relative group rounded-md overflow-hidden border border-border bg-muted/20">
        <img :src="img.preview_url || img.url" :alt="img.name" class="w-full h-36 object-cover cursor-zoom-in" @click="openLightbox(img.url)" />
        <button
          v-if="canDelete"
          type="button"
          class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition bg-destructive text-destructive-foreground rounded px-2 py-1 text-xs"
          @click.stop="emit('delete', img.id)"
        >Delete</button>
      </div>
    </div>
    <div v-else class="text-sm text-muted-foreground">No images uploaded.</div>

    <Dialog :open="lightboxOpen" @update:open="(v:boolean) => (lightboxOpen = v)">
      <DialogContent class="max-w-4xl">
        <DialogHeader>
          <DialogTitle>Preview</DialogTitle>
        </DialogHeader>
        <div class="w-full">
          <img v-if="lightboxImage" :src="lightboxImage" alt="Preview" class="w-full h-auto rounded" />
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>
