<script setup lang="ts">
import { Check, X, Loader2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { InputError } from '@/components'

const props = defineProps<{
  form: any
  plans: Record<string, any>
  validationErrors: Record<string, string[]>
  isValidating: boolean
  isValid: boolean
}>()

const emit = defineEmits<{
  (e: 'back'): void
  (e: 'submit'): void
}>()
</script>

<template>
  <Card class="shadow-none border-none">
    <CardHeader class="p-0">
      <CardTitle>Choose Your Plan</CardTitle>
      <CardDescription>
        Select the plan that best fits your needs
      </CardDescription>
    </CardHeader>

    <CardContent class="p-0 border-none">
      <div class="space-y-4 mb-6">
        <div
          v-for="plan in plans"
          :key="plan.id"
          :class="[
            'border rounded-lg p-4 cursor-pointer transition-all',
            form.subscription_plan === plan.id
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-gray-300'
          ]"
          @click="form.subscription_plan = plan.id">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-semibold">{{ plan.name }}</h3>
              <p class="text-2xl font-bold text-blue-600">${{ plan.price }}<span class="text-sm font-normal text-gray-600">/month</span></p>
            </div>
            <div class="w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center">
              <div
                v-if="form.subscription_plan === plan.id"
                class="w-2 h-2 bg-blue-600 rounded-full" />
            </div>
          </div>
          <ul class="mt-3 space-y-1 text-sm text-gray-600">
            <li v-for="feature in plan.features" :key="feature">
              ✓ {{ feature }}
            </li>
          </ul>
        </div>
      </div>

      <div v-if="validationErrors.subscription_plan || form.errors.subscription_plan" class="text-red-600 text-sm mb-4">
        {{ Array.isArray(validationErrors.subscription_plan) ? validationErrors.subscription_plan[0] : validationErrors.subscription_plan || form.errors.subscription_plan }}
      </div>

      <div class="flex gap-4">
        <Button
          type="button"
          variant="outline"
          :disabled="isValidating"
          @click="$emit('back')">
          Back
        </Button>
        <Button
          class="flex-1"
          :disabled="isValidating || !form.subscription_plan"
          @click="$emit('submit')">
          <Loader2 v-if="isValidating" class="mr-2 h-4 w-4 animate-spin" />
          Complete Registration
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
