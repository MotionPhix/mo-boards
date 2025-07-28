<template>
  <nav class="flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
      <Button
        v-if="links.prev"
        variant="outline"
        @click="$inertia.visit(links.prev)"
      >
        Previous
      </Button>
      <Button
        v-if="links.next"
        variant="outline"
        @click="$inertia.visit(links.next)"
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
          v-if="links.prev"
          variant="outline"
          size="sm"
          @click="$inertia.visit(links.prev)"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>

        <Button
          v-for="(link, index) in paginationLinks"
          :key="index"
          :variant="link.active ? 'default' : 'outline'"
          size="sm"
          :disabled="!link.url"
          @click="link.url && $inertia.visit(link.url)"
          v-html="link.label"
        />

        <Button
          v-if="links.next"
          variant="outline"
          size="sm"
          @click="$inertia.visit(links.next)"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface Props {
  links: PaginationLink[]
  from?: number
  to?: number
  total?: number
}

const props = defineProps<Props>()

const paginationLinks = computed(() => {
  return props.links.filter(link =>
    !['Previous', 'Next'].includes(link.label)
  )
})

const links = computed(() => {
  const allLinks = props.links
  return {
    prev: allLinks.find(link => link.label === 'Previous')?.url,
    next: allLinks.find(link => link.label === 'Next')?.url,
  }
})
</script>
