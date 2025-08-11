<script setup lang="ts">
import { useTheme } from '@/composables/useTheme'
import { Moon, Sun, Monitor } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const { theme, setTheme, isDark, isLight, isSystem } = useTheme()
</script>

<template>
  <div class="flex items-center gap-2">
    <Button
      @click="setTheme('light')"
      :variant="isLight ? 'default' : 'ghost'"
      size="sm"
    >
      <Sun class="h-4 w-4" />
    </Button>

    <Button
      @click="setTheme('dark')"
      :variant="isDark ? 'default' : 'ghost'"
      size="sm"
    >
      <Moon class="h-4 w-4" />
    </Button>

    <Button
      @click="setTheme('system')"
      :variant="isSystem ? 'default' : 'ghost'"
      size="sm"
    >
      <Monitor class="h-4 w-4" />
    </Button>
  </div>
</template>
