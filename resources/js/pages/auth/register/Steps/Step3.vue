<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import InputError from '@/components/InputError.vue'
import { Check, ChevronDown, ChevronUp } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { Separator } from '@/components/ui/separator'

defineProps<{
  plans: {
    name: string
    displayName: string
    price: number
    features: string[]
    recommended?: boolean
  }[]
}>()

const form = useForm({
  subscription_plan: '',
})

const activeTab = ref<string>('pro')

type BillingCycle = 'monthly' | 'yearly'
const billingCycle = ref<BillingCycle>('monthly')
const yearlyDiscountMultiplier = 10
const selectPlan = (name: string) => {
  form.subscription_plan = name
  activeTab.value = name
}

const defaultVisibleFeatures = 6

type FeaturePart = { text: string; highlight: boolean }
const formatFeatureParts = (feature: string): FeaturePart[] => {
  const pattern = /(unlimited|\b\d[\d,\.]*\b)/gi
  const parts: FeaturePart[] = []
  let lastIndex = 0
  let match: RegExpExecArray | null
  while ((match = pattern.exec(feature)) !== null) {
    if (match.index > lastIndex) {
      parts.push({ text: feature.slice(lastIndex, match.index), highlight: false })
    }
    parts.push({ text: match[0], highlight: true })
    lastIndex = match.index + match[0].length
  }
  if (lastIndex < feature.length) {
    parts.push({ text: feature.slice(lastIndex), highlight: false })
  }
  return parts
}

const expanded = ref<Record<string, boolean>>({})
const toggleExpand = (name: string) => {
  expanded.value[name] = !expanded.value[name]
}

// Normalize backend feature payloads to a clean string[]
const toArray = (v: unknown): string[] => {
  if (Array.isArray(v)) return v as string[]
  if (typeof v === 'string') {
    return v
      .split(/[\n,]/)
      .map(s => s.trim())
      .filter(Boolean)
  }
  if (v && typeof v === 'object') {
    return Object.values(v as Record<string, unknown>)
      .map(x => String(x))
      .map(s => s.trim())
      .filter(Boolean)
  }
  return []
}

const planFeatures = (plan: { features: unknown }): string[] => {
  const arr = toArray((plan as any).features)
  const seen = new Set<string>()
  const out: string[] = []
  for (const f of arr) {
    if (!seen.has(f)) {
      seen.add(f)
      out.push(f)
    }
  }
  return out
}

const submit = () => {
  form.post(route('register.store'), {
    onFinish: () => {
      toast.success('', {
        description: ''
      })
    }
  })
}

const goBack = () => {
  router.visit(route('register.step2'))
}
</script>

<template>
  <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <form @submit.prevent="submit">
      <Card class="mb-6">
        <CardHeader>
          <CardTitle id="plan-heading" class="text-2xl font-bold">Choose your plan</CardTitle>
          <CardDescription>
            Select the plan that best fits your needs..
          </CardDescription>
        </CardHeader>
      </Card>

      <div class="flex flex-col gap-y-1 items-center justify-between mb-6">
        <div class="inline-flex rounded-lg border p-1 bg-muted">
          <Button
            type="button"
            size="sm"
            :variant="billingCycle === 'monthly' ? 'default' : 'ghost'"
            class="rounded-md"
            @click="billingCycle = 'monthly'"
          >
            Monthly
          </Button>
          <Button
            type="button"
            size="sm"
            :variant="billingCycle === 'yearly' ? 'default' : 'ghost'"
            class="rounded-md"
            @click="billingCycle = 'yearly'"
          >
            Yearly
            <Badge variant="secondary" class="ml-2 hidden sm:inline-flex">Save 2 months</Badge>
          </Button>
        </div>

        <p class="text-xs text-muted-foreground">
          30-day free trial. Change or cancel anytime.
        </p>
      </div>

      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-3 max-w-xl mx-auto mb-6">
          <TabsTrigger v-for="plan in plans" :key="plan.name" :value="plan.name">
            {{ plan.displayName }}
          </TabsTrigger>
        </TabsList>

        <TabsContent v-for="plan in plans" :key="plan.name" :value="plan.name" class="w-full">
          <Card
            :class="[
              'relative flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700',
              form.subscription_plan === plan.name ? 'border-primary ring-2 ring-primary' : ''
            ]"
          >
            <div v-if="plan.recommended" class="absolute right-3 top-3">
              <Badge variant="secondary" class="text-xs uppercase tracking-wide">Most popular</Badge>
            </div>
            <CardHeader>
              <div class="flex justify-between items-start space-y-0">
                <div>
                  <CardTitle class="text-2xl font-bold">{{ plan.displayName }}</CardTitle>
                  <div class="mt-2 flex items-baseline">
                    <span class="text-4xl sm:text-5xl font-extrabold tracking-tight">
                      <template v-if="billingCycle === 'monthly'">
                        ${{ plan.price }}
                      </template>
                      <template v-else>
                        ${{ plan.price * yearlyDiscountMultiplier }}
                      </template>
                    </span>
                    <span class="ml-2 text-sm text-muted-foreground">
                      <template v-if="billingCycle === 'monthly'">/month</template>
                      <template v-else>/year</template>
                    </span>
                  </div>
                  <div v-if="billingCycle === 'yearly'" class="mt-1">
                    <Badge variant="outline" class="text-xs">Save 2 months</Badge>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <div
                    v-if="form.subscription_plan === plan.name"
                    class="rounded-full bg-primary text-primary-foreground p-1"
                    aria-hidden="true"
                  >
                    <Check class="h-4 w-4" />
                  </div>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div class="mb-3 flex items-center justify-between">
                <Badge variant="outline" class="text-xs">{{ planFeatures(plan).length }} features included</Badge>
              </div>
              <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <li
                  v-for="feature in (expanded[plan.name] ? planFeatures(plan) : planFeatures(plan).slice(0, defaultVisibleFeatures))"
                  :key="feature"
                  class="flex items-start"
                  :title="feature"
                >
                  <div class="mr-2 mt-0.5 rounded-full bg-primary/10 text-primary p-1">
                    <Check class="h-3.5 w-3.5" />
                  </div>
                  <span>
                    <template v-for="(part, idx) in formatFeatureParts(feature)" :key="idx">
                      <span :class="part.highlight ? 'font-semibold text-foreground' : ''">{{ part.text }}</span>
                    </template>
                  </span>
                </li>
              </ul>

              <div v-if="planFeatures(plan).length > defaultVisibleFeatures" class="mt-4 mb-2">
                <Button type="button" variant="ghost" size="sm" class="px-0" @click="toggleExpand(plan.name)">
                  <span v-if="!expanded[plan.name]" class="inline-flex items-center px-2">
                    Show all {{ planFeatures(plan).length }} features
                    <ChevronDown class="ml-1 h-4 w-4" />
                  </span>

                  <span v-else class="inline-flex items-center px-2">
                    Show less
                    <ChevronUp class="ml-1 h-4 w-4" />
                  </span>
                </Button>
              </div>

              <Separator />

              <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- <Button
                  type="button"
                  variant="outline"
                  @click="router.visit(route('register.step2'))">
                  Back
                </Button> -->
                <Button
                  type="button"
                  class="w-full"
                  :variant="form.subscription_plan === plan.name ? 'default' : 'secondary'"
                  @click="selectPlan(plan.name)">
                  {{ form.subscription_plan === plan.name ? 'Selected' : 'Choose plan' }}
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      <InputError class="mt-4" :message="form.errors.subscription_plan" />

      <div class="flex justify-between gap-4 mt-8">
        <Button
          type="button"
          variant="outline"
          @click="goBack">
          Back
        </Button>

        <Button
          type="submit"
          :disabled="form.processing || !form.subscription_plan"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.subscription_plan }">
          Complete Registration
        </Button>
      </div>
    </form>
  </div>
</template>
