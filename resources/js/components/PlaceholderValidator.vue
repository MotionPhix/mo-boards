<script setup lang="ts">
import { computed } from 'vue'
import { getAllPlaceholders } from '@/services/placeholderService'
import { Badge } from '@/components/ui/badge'
import { AlertTriangle, CheckCircle } from 'lucide-vue-next'

interface Props {
  content: string
}

const props = defineProps<Props>()

// Get all valid placeholders
const validPlaceholders = getAllPlaceholders().map(p => p.value)

// Extract all placeholders from content
const foundPlaceholders = computed(() => {
  const matches = props.content.match(/\{\{[^}]+\}\}/g) || []
  return [...new Set(matches)] // Remove duplicates
})

// Validate placeholders
const validation = computed(() => {
  const valid: string[] = []
  const invalid: string[] = []

  foundPlaceholders.value.forEach(placeholder => {
    if (validPlaceholders.includes(placeholder)) {
      valid.push(placeholder)
    } else {
      invalid.push(placeholder)
    }
  })

  return { valid, invalid }
})

// Get suggestions for invalid placeholders
function getSuggestions(invalidPlaceholder: string): string[] {
  const cleaned = invalidPlaceholder.toLowerCase().replace(/[{}]/g, '')
  return validPlaceholders.filter(valid => {
    const validCleaned = valid.toLowerCase().replace(/[{}]/g, '')
    return validCleaned.includes(cleaned) || cleaned.includes(validCleaned)
  }).slice(0, 3) // Limit to 3 suggestions
}
</script>

<template>
  <div v-if="foundPlaceholders.length > 0" class="space-y-2">
    <!-- Valid placeholders indicator -->
    <div v-if="validation.valid.length > 0" class="flex items-center gap-2">
      <CheckCircle class="h-4 w-4 text-green-600" />
      <span class="text-sm text-green-700 dark:text-green-400">
        {{ validation.valid.length }} valid placeholder{{ validation.valid.length !== 1 ? 's' : '' }}
      </span>
      <Badge variant="secondary" class="text-xs">
        {{ validation.valid.length }}
      </Badge>
    </div>

    <!-- Invalid placeholders warning -->
    <div v-if="validation.invalid.length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
      <div class="flex items-start gap-2">
        <AlertTriangle class="h-4 w-4 text-red-600 mt-0.5 flex-shrink-0" />
        <div class="flex-1 min-w-0">
          <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">
            Invalid Placeholder{{ validation.invalid.length !== 1 ? 's' : '' }} Found
          </h4>

          <div class="space-y-2">
            <div v-for="placeholder in validation.invalid" :key="placeholder" class="text-sm">
              <div class="flex items-center gap-2 mb-1">
                <code class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded text-xs">
                  {{ placeholder }}
                </code>
                <span class="text-red-700 dark:text-red-300">- Unknown placeholder</span>
              </div>

              <!-- Suggestions -->
              <div v-if="getSuggestions(placeholder).length > 0" class="ml-2 text-xs text-red-600 dark:text-red-400">
                Did you mean:
                <span v-for="(suggestion, index) in getSuggestions(placeholder)" :key="suggestion">
                  <code class="mx-1 px-1 py-0.5 bg-red-100 dark:bg-red-900 rounded">{{ suggestion }}</code>{{ index < getSuggestions(placeholder).length - 1 ? ',' : '' }}
                </span>
              </div>
            </div>
          </div>

          <p class="text-xs text-red-600 dark:text-red-400 mt-2">
            These placeholders won't be replaced when the document is processed.
            Use the Placeholder Guide to find the correct placeholders.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
