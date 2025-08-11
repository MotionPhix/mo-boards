<script setup lang="ts">
import type { NumberFieldRootEmits, NumberFieldRootProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { NumberFieldRoot, useForwardPropsEmits } from "reka-ui"
import { cn } from "@/lib/utils"
import { computed } from "vue"

type NumberLike = number | string | null | undefined

type Props = Omit<NumberFieldRootProps, 'modelValue' | 'defaultValue'> & {
  modelValue?: NumberLike
  defaultValue?: NumberLike
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
const emits = defineEmits<NumberFieldRootEmits>()

const delegatedProps = reactiveOmit(props, "class", "modelValue", "defaultValue")
const forwarded = useForwardPropsEmits(delegatedProps, emits)

function coerceNumber(val: NumberLike): number | null {
  if (val === null || typeof val === 'undefined' || val === '') return null
  if (typeof val === 'number') return isNaN(val) ? null : val
  if (typeof val === 'string') {
    const n = parseFloat(val.replace(/,/g, ''))
    return isNaN(n) ? null : n
  }
  return null
}

const coercedModel = computed<number | undefined>({
  get: () => {
    const n = coerceNumber(props.modelValue)
    return n === null ? undefined : n
  },
  set: (val) => emits('update:modelValue', val ?? undefined as unknown as number)
})

const coercedDefault = computed<number | undefined>(() => {
  const n = coerceNumber(props.defaultValue)
  return n === null ? undefined : n
})
</script>

<template>
  <NumberFieldRoot
    v-bind="forwarded"
    :model-value="coercedModel"
  :default-value="coercedDefault"
    @update:modelValue="(v) => (coercedModel as any).value = v"
    :class="cn('grid gap-1.5', props.class)"
  >
    <slot />
  </NumberFieldRoot>
</template>
