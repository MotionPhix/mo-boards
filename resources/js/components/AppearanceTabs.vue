<script setup lang="ts">
import { useTheme } from '@/composables/useTheme';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

interface Props {
    class?: string;
}

const { class: containerClass = '' } = defineProps<Props>();

const { theme, setTheme } = useTheme();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div :class="['inline-flex gap-1 rounded-lg bg-muted p-1', containerClass]">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="setTheme(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                theme === value
                    ? 'bg-background shadow-sm text-foreground'
                    : 'text-muted-foreground hover:bg-muted-foreground/10 hover:text-foreground',
            ]">
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
