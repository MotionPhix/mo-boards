<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Eye, Loader2 } from 'lucide-vue-next'

interface Props {
  contractUuid: string
  content: string
}

const props = defineProps<Props>()

const isLoading = ref(false)
const previewContent = ref('')
const placeholderValues = ref<Record<string, string>>({})
const showPreview = ref(false)

// Generate preview
async function generatePreview() {
  if (!props.content.trim()) {
    return
  }

  isLoading.value = true

  try {
    const response = await fetch(route('contracts.preview-placeholders', props.contractUuid), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        content: props.content
      })
    })

    if (response.ok) {
      const data = await response.json()
      previewContent.value = data.preview_content
      placeholderValues.value = data.placeholder_values
      showPreview.value = true
    } else {
      console.error('Failed to generate preview')
    }
  } catch (error) {
    console.error('Error generating preview:', error)
  } finally {
    isLoading.value = false
  }
}

// Check if content has placeholders
const hasPlaceholders = computed(() => {
  return props.content.includes('{{') && props.content.includes('}}')
})

// Get placeholders found in content
const foundPlaceholders = computed(() => {
  const matches = props.content.match(/\{\{[^}]+\}\}/g) || []
  return [...new Set(matches)] // Remove duplicates
})
</script>

<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button
        variant="outline"
        size="sm"
        @click="generatePreview"
        :disabled="!hasPlaceholders || isLoading"
        class="gap-2"
      >
        <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
        <Eye v-else class="h-4 w-4" />
        {{ isLoading ? 'Generating...' : 'Preview' }}
      </Button>
    </DialogTrigger>

    <DialogContent class="max-w-4xl max-h-[80vh] overflow-hidden">
      <DialogHeader>
        <DialogTitle>Placeholder Preview</DialogTitle>
        <DialogDescription>
          See how your document will look with actual contract data
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <!-- Placeholder Values Summary -->
        <div v-if="Object.keys(placeholderValues).length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Placeholder Values:</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
            <div
              v-for="(value, placeholder) in placeholderValues"
              :key="placeholder"
              class="flex justify-between items-start gap-2 p-2 bg-white dark:bg-gray-700 rounded border"
            >
              <span class="font-mono text-blue-600 dark:text-blue-400 break-all">{{ placeholder }}</span>
              <span class="text-gray-600 dark:text-gray-300 text-right">
                {{ value || '(empty)' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Found Placeholders in Content -->
        <div v-if="foundPlaceholders.length > 0" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
            Found {{ foundPlaceholders.length }} placeholder(s) in content:
          </h4>
          <div class="flex flex-wrap gap-1">
            <span
              v-for="placeholder in foundPlaceholders"
              :key="placeholder"
              class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 rounded text-xs font-mono"
            >
              {{ placeholder }}
            </span>
          </div>
        </div>

        <!-- Preview Content -->
        <div class="border rounded-lg overflow-hidden">
          <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Document Preview</h4>
          </div>
          <div class="p-4 max-h-96 overflow-y-auto bg-white dark:bg-gray-900">
            <div
              v-if="previewContent"
              v-html="previewContent"
              class="prose prose-sm dark:prose-invert max-w-none"
            ></div>
            <div v-else class="text-gray-500 dark:text-gray-400 text-center py-8">
              Click "Preview" to see how your document will look with actual data
            </div>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* Custom styles for the preview content */
:deep(.prose) {
  font-size: 0.875rem;
  line-height: 1.25rem;
}

:deep(.prose h1) {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

:deep(.prose h2) {
  font-size: 1.125rem;
  margin-bottom: 0.5rem;
}

:deep(.prose h3) {
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

:deep(.prose p) {
  margin-bottom: 0.5rem;
}

:deep(.prose ul) {
  margin-bottom: 0.5rem;
}
</style>
