<script setup lang="ts">
import { ref, computed } from 'vue'
import { placeholderGroups } from '@/services/placeholderService'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { HelpCircle, Search, Copy, Check } from 'lucide-vue-next'

const searchQuery = ref('')
const copiedPlaceholder = ref('')

// Filter placeholders based on search
const filteredGroups = computed(() => {
  if (!searchQuery.value) {
    return placeholderGroups
  }

  const query = searchQuery.value.toLowerCase()
  return placeholderGroups.map(group => ({
    ...group,
    placeholders: group.placeholders.filter(placeholder =>
      placeholder.label.toLowerCase().includes(query) ||
      placeholder.value.toLowerCase().includes(query) ||
      placeholder.description?.toLowerCase().includes(query)
    )
  })).filter(group => group.placeholders.length > 0)
})

// Copy placeholder to clipboard
async function copyPlaceholder(value: string) {
  try {
    await navigator.clipboard.writeText(value)
    copiedPlaceholder.value = value
    setTimeout(() => {
      copiedPlaceholder.value = ''
    }, 2000)
  } catch (error) {
    console.error('Failed to copy:', error)
  }
}

// Total count of placeholders
const totalPlaceholders = computed(() => {
  return placeholderGroups.reduce((total, group) => total + group.placeholders.length, 0)
})
</script>

<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button variant="outline" size="sm" class="gap-2">
        <HelpCircle class="h-4 w-4" />
        Placeholder Guide
      </Button>
    </DialogTrigger>

    <DialogContent class="max-w-4xl max-h-[80vh] overflow-hidden">
      <DialogHeader>
        <DialogTitle>Placeholder Documentation</DialogTitle>
        <DialogDescription>
          Available placeholders for templates and contracts ({{ totalPlaceholders }} total)
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <!-- Search -->
        <div class="relative">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search placeholders..."
            class="pl-10"
          />
        </div>

        <!-- Usage Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">How to use:</h4>
          <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
            <li>• Copy any placeholder by clicking on it</li>
            <li>• Paste placeholders into your templates or contracts</li>
            <li>• Placeholders will be automatically replaced with actual data when the document is saved</li>
            <li>• Use the Preview button to see how placeholders will look with real data</li>
          </ul>
        </div>

        <!-- Placeholder Groups -->
        <div class="space-y-4 max-h-96 overflow-y-auto">
          <template v-for="group in filteredGroups" :key="group.label">
            <div class="border rounded-lg overflow-hidden">
              <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ group.label }}
                  <Badge variant="secondary" class="ml-2">
                    {{ group.placeholders.length }}
                  </Badge>
                </h3>
              </div>

              <div class="p-4 space-y-3">
                <div
                  v-for="placeholder in group.placeholders"
                  :key="placeholder.value"
                  class="group relative border rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                  @click="copyPlaceholder(placeholder.value)"
                >
                  <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 mb-1">
                        <code class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-mono">
                          {{ placeholder.value }}
                        </code>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ placeholder.label }}
                        </span>
                      </div>
                      <p v-if="placeholder.description" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ placeholder.description }}
                      </p>
                    </div>

                    <div class="flex-shrink-0">
                      <Button
                        variant="ghost"
                        size="sm"
                        class="opacity-0 group-hover:opacity-100 transition-opacity h-8 w-8 p-0"
                        @click.stop="copyPlaceholder(placeholder.value)"
                      >
                        <Check
                          v-if="copiedPlaceholder === placeholder.value"
                          class="h-4 w-4 text-green-600"
                        />
                        <Copy v-else class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- No results -->
        <div v-if="searchQuery && filteredGroups.length === 0" class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400">
            No placeholders found matching "{{ searchQuery }}"
          </p>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* Custom scrollbar for the placeholder list */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-96::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.3);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.5);
}
</style>
