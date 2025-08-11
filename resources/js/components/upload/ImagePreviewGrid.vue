<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue'

interface ImageItem {
  src: string
  name?: string
}

const props = defineProps<{ images: ImageItem[] }>()
const emit = defineEmits<{ (e: 'remove', index: number): void }>()

// Revoke object URLs when removed
watch(() => props.images.slice(), (next, prev) => {
  const prevSet = new Set(prev.map(i => i.src))
  next.forEach(i => prevSet.delete(i.src))
  prevSet.forEach(url => URL.revokeObjectURL(url))
})

onBeforeUnmount(() => {
  props.images.forEach(i => URL.revokeObjectURL(i.src))
})
</script>

<template>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
    <div v-for="(img, idx) in images" :key="img.src + idx" class="relative rounded-md overflow-hidden border border-border bg-muted/20">
      <img :src="img.src" :alt="img.name || 'image'" class="w-full h-32 object-cover" />
      <button
        type="button"
        class="absolute top-2 right-2 bg-secondary text-secondary-foreground rounded px-2 py-1 text-xs"
        @click="emit('remove', idx)"
      >Remove</button>
    </div>
  </div>
</template>
