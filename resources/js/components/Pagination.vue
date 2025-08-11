<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface Props {
  // Accept either the direct links array or the full pagination object
  links?: PaginationLink[] | any
  from?: number
  to?: number
  total?: number
}

const props = defineProps<Props>()

// Extract the actual links array from various possible structures
const actualLinks = computed(() => {
  // If links is already an array, use it directly
  if (Array.isArray(props.links)) {
    return props.links
  }

  // If links is an object with meta.links (Laravel Resource structure)
  if (props.links && props.links.meta && Array.isArray(props.links.meta.links)) {
    return props.links.meta.links
  }

  // If links is an object with links property (some other structure)
  if (props.links && Array.isArray(props.links.links)) {
    return props.links.links
  }

  // Fallback to empty array
  return []
})

const hasValidLinks = computed(() => {
  return actualLinks.value.length > 0
})

const paginationLinks = computed(() => {
  if (!hasValidLinks.value) {
    return []
  }
  return actualLinks.value.filter((link: PaginationLink) =>
    !['Previous', 'Next', '&laquo; Previous', 'Next &raquo;'].includes(link.label)
  )
})

const navLinks = computed(() => {
  if (!hasValidLinks.value) {
    return { prev: null, next: null }
  }

  const allLinks = actualLinks.value as PaginationLink[]
  return {
    prev: allLinks.find(link =>
      link.label === 'Previous' || link.label === '&laquo; Previous'
    )?.url || null,
    next: allLinks.find(link =>
      link.label === 'Next' || link.label === 'Next &raquo;'
    )?.url || null,
  }
})

// Clean up labels like "&laquo; Previous" into plain text
const normalizeLabel = (label: string) => {
  return label
    .replace('&laquo; ', '')
    .replace(' &raquo;', '')
}
</script>

<template>
  <nav class="flex items-center justify-between" v-if="hasValidLinks">
    <div class="flex-1 flex justify-between sm:hidden">
      <Button
        v-if="navLinks.prev"
        variant="outline"
        @click="router.visit(navLinks.prev, { preserveScroll: true })"
      >
        Previous
      </Button>
      <Button
        v-if="navLinks.next"
        variant="outline"
        @click="router.visit(navLinks.next, { preserveScroll: true })"
      >
        Next
      </Button>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-muted-foreground">
          Showing
          <span class="font-medium">{{ from }}</span>
          to
          <span class="font-medium">{{ to }}</span>
          of
          <span class="font-medium">{{ total }}</span>
          results
        </p>
      </div>

      <div class="flex space-x-1">
        <Button
          v-if="navLinks.prev"
          variant="outline"
          size="sm"
          @click="router.visit(navLinks.prev, { preserveScroll: true })"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>

        <Button
          v-for="(link, index) in paginationLinks"
          :key="index"
          :variant="link.active ? 'default' : 'outline'"
          size="sm"
          :disabled="!link.url"
          @click="link.url && router.visit(link.url, { preserveScroll: true })"
        >
          {{ normalizeLabel(link.label) }}
        </Button>

        <Button
          v-if="navLinks.next"
          variant="outline"
          size="sm"
          @click="router.visit(navLinks.next, { preserveScroll: true })"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </nav>
</template>
