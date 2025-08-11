<script setup lang="ts">
interface DocItem {
  id: number
  name: string
  url: string
  type?: string
  size?: string
}

defineProps<{ docs: DocItem[]; canDelete?: boolean }>()
const emit = defineEmits<{ (e: 'delete', id: number): void }>()
</script>

<template>
  <div class="space-y-2">
    <div v-if="!docs?.length" class="text-sm text-muted-foreground">No documents uploaded.</div>
    <div v-for="doc in docs" :key="doc.id" class="flex items-center justify-between rounded border border-border bg-muted/20 px-3 py-2">
      <div class="min-w-0">
        <a :href="doc.url" target="_blank" class="text-foreground hover:underline truncate block">{{ doc.name }}</a>
        <div class="text-xs text-muted-foreground">{{ doc.type || 'PDF' }} • {{ doc.size || '' }}</div>
      </div>
      <button v-if="canDelete" type="button" class="text-destructive hover:underline text-sm" @click="emit('delete', doc.id)">Delete</button>
    </div>
  </div>
</template>
