<script setup lang="ts">
import { onUnmounted, ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { Loader2, AlertCircle, CheckCircle, Eye, EyeOff } from 'lucide-vue-next'
import GuestLayout from '@/layouts/AuthLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { useStorage } from '@vueuse/core';
import axios from 'axios';

const step = useStorage('registration_step', 1)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const validationErrors = ref<Record<string, string[]>>({})
const isValidatingStep = ref(false)
const stepValidationStatus = ref<Record<number, boolean>>({
  1: false,
  2: false,
  3: false
})

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  company_name: '',
  industry: '',
  company_size: '',
  address: '',
  subscription_plan: '',
})

// Watch for form changes to clear validation status when user modifies data
watch(() => [form.name, form.email, form.phone, form.password, form.password_confirmation], () => {
  if (stepValidationStatus.value[1]) {
    stepValidationStatus.value[1] = false
  }
}, { deep: true })

watch(() => [form.company_name, form.industry, form.company_size, form.address], () => {
  if (stepValidationStatus.value[2]) {
    stepValidationStatus.value[2] = false
  }
}, { deep: true })

watch(() => form.subscription_plan, () => {
  if (stepValidationStatus.value[3]) {
    stepValidationStatus.value[3] = false
  }
})

const plans = [
  {
    id: 'starter',
    name: 'Starter',
    price: 29,
    features: [
      'Up to 10 billboards',
      '2 team members',
      'Basic analytics',
      'Email support'
    ]
  },
  {
    id: 'professional',
    name: 'Professional',
    price: 79,
    features: [
      'Up to 50 billboards',
      '10 team members',
      'Advanced analytics',
      'Priority support',
      'API access'
    ]
  },
  {
    id: 'enterprise',
    name: 'Enterprise',
    price: 199,
    features: [
      'Unlimited billboards',
      'Unlimited team members',
      'Custom integrations',
      '24/7 support',
      'Dedicated account manager'
    ]
  }
]

// Password strength validation
const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return { score: 0, feedback: [] }

  let score = 0
  const feedback = []

  if (password.length >= 8) score++
  else feedback.push('At least 8 characters')

  if (/[a-z]/.test(password)) score++
  else feedback.push('One lowercase letter')

  if (/[A-Z]/.test(password)) score++
  else feedback.push('One uppercase letter')

  if (/\d/.test(password)) score++
  else feedback.push('One number')

  if (/[^a-zA-Z\d]/.test(password)) score++
  else feedback.push('One special character')

  return { score, feedback }
})

const passwordStrengthColor = computed(() => {
  const score = passwordStrength.value.score
  if (score <= 2) return 'bg-red-500'
  if (score <= 3) return 'bg-yellow-500'
  if (score <= 4) return 'bg-blue-500'
  return 'bg-green-500'
})

const passwordStrengthText = computed(() => {
  const score = passwordStrength.value.score
  if (score <= 2) return 'Weak'
  if (score <= 3) return 'Fair'
  if (score <= 4) return 'Good'
  return 'Strong'
})

// Check if current step is valid
const isCurrentStepValid = computed(() => {
  if (step.value === 1) {
    return form.name && form.email && form.password && form.password_confirmation &&
           form.password === form.password_confirmation && passwordStrength.value.score >= 3
  }
  if (step.value === 2) {
    return form.company_name
  }
  if (step.value === 3) {
    return form.subscription_plan
  }
  return false
})

// Step validation functions
const validateStep = async (stepNumber: number): Promise<boolean> => {
  isValidatingStep.value = true
  validationErrors.value = {}

  try {
    let data = {}

    if (stepNumber === 1) {
      data = {
        name: form.name,
        email: form.email,
        phone: form.phone,
        password: form.password,
        password_confirmation: form.password_confirmation,
      }
    } else if (stepNumber === 2) {
      data = {
        company_name: form.company_name,
        industry: form.industry,
        company_size: form.company_size,
        address: form.address,
      }
    } else if (stepNumber === 3) {
      data = {
        subscription_plan: form.subscription_plan,
      }
    }

    const response = await axios.post(`/register/validate-step-${stepNumber}`, data)

    if (response.data.valid) {
      stepValidationStatus.value[stepNumber] = true
      return true
    }
  } catch (error: any) {
    console.error('Validation error:', error)
    stepValidationStatus.value[stepNumber] = false

    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {}
    } else {
      validationErrors.value = {
        general: ['An error occurred during validation. Please try again.']
      }
    }
    return false
  } finally {
    isValidatingStep.value = false
  }

  return false
}

const nextStep = async () => {
  const isValid = await validateStep(step.value)
  if (isValid && step.value < 3) {
    step.value++
    // Clear any previous validation errors when moving to next step
    validationErrors.value = {}
  }
}

const previousStep = () => {
  if (step.value > 1) {
    step.value--
    validationErrors.value = {}
  }
}

const submit = async () => {
  // Clear any previous errors
  validationErrors.value = {}

  // Validate all steps before submitting
  let allStepsValid = true

  for (let i = 1; i <= 3; i++) {
    const isValid = await validateStep(i)
    if (!isValid) {
      allStepsValid = false
      step.value = i // Go to the first invalid step
      break
    }
  }

  if (allStepsValid) {
    form.post(route('register'), {
      onError: (errors) => {
        console.error('Registration errors:', errors)
        validationErrors.value = {}

        // Convert Inertia errors to our format
        Object.keys(errors).forEach(key => {
          validationErrors.value[key] = [errors[key]]
        })

        // If there are validation errors, go back to the appropriate step
        if (errors.name || errors.email || errors.password || errors.phone) {
          step.value = 1
        } else if (errors.company_name || errors.industry || errors.company_size || errors.address) {
          step.value = 2
        } else if (errors.subscription_plan) {
          step.value = 3
        }
      },
      onSuccess: () => {
        // Clear storage on successful registration
        step.value = 1
        stepValidationStatus.value = { 1: false, 2: false, 3: false }
      }
    })
  }
}

// Real-time validation for email
const checkEmailAvailability = async () => {
  if (form.email && form.email.includes('@') && form.email.length > 5) {
    try {
      // Clear previous email errors
      if (validationErrors.value.email) {
        delete validationErrors.value.email
      }

      await axios.post('/register/validate-step-1', {
        name: form.name || 'temp',
        email: form.email,
        phone: form.phone,
        password: form.password || 'TempPass123!',
        password_confirmation: form.password || 'TempPass123!',
      })
    } catch (error: any) {
      if (error.response?.data?.errors?.email) {
        validationErrors.value = {
          ...validationErrors.value,
          email: error.response.data.errors.email
        }
      }
    }
  }
}

onUnmounted(() => {
  // Don't clear step on unmount - let user resume where they left off
})
</script>

<template>
  <GuestLayout>
    <Head title="Register" />

    <div class="w-full max-w-md mx-auto">
      <!-- Progress Steps -->
      <div class="flex justify-center mb-8">
        <div class="flex items-center space-x-4">
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium relative',
            step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
          ]">
            <CheckCircle v-if="stepValidationStatus[1]" class="w-4 h-4" />
            <span v-else>1</span>
          </div>
          <div :class="[
            'w-8 h-0.5',
            step > 1 ? 'bg-blue-600' : 'bg-gray-200'
          ]"></div>
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
            step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
          ]">
            <CheckCircle v-if="stepValidationStatus[2]" class="w-4 h-4" />
            <span v-else>2</span>
          </div>
          <div :class="[
            'w-8 h-0.5',
            step > 2 ? 'bg-blue-600' : 'bg-gray-200'
          ]"></div>
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
            step >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
          ]">
            <CheckCircle v-if="stepValidationStatus[3]" class="w-4 h-4" />
            <span v-else>3</span>
          </div>
        </div>
      </div>

      <!-- Validation Errors Alert -->
      <Alert v-if="Object.keys(validationErrors).length > 0" class="mb-6" variant="destructive">
        <AlertCircle class="h-4 w-4" />
        <AlertDescription>
          Please fix the following errors:
          <ul class="mt-2 list-disc list-inside">
            <li v-for="(errors, field) in validationErrors" :key="field">
              <span class="capitalize">{{ field.replace('_', ' ') }}:</span> {{ Array.isArray(errors) ? errors[0] : errors }}
            </li>
          </ul>
        </AlertDescription>
      </Alert>

      <!-- Step 1: Personal Information -->
      <Card v-if="step === 1" class="shadow-none border-none space-y-6">
        <CardHeader class="p-0">
          <CardTitle>Personal Information</CardTitle>
          <CardDescription>
            Let's start with your basic information
          </CardDescription>
        </CardHeader>

        <CardContent class="p-0 border-none">
          <form @submit.prevent="nextStep" class="space-y-4">
            <div>
              <Label htmlFor="name">Full Name *</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1"
                :class="{ 'border-red-500': validationErrors.name || form.errors.name }"
                required
                autocomplete="name"
              />
              <div v-if="validationErrors.name || form.errors.name" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.name) ? validationErrors.name[0] : validationErrors.name || form.errors.name }}
              </div>
            </div>

            <div>
              <Label htmlFor="email">Email Address *</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1"
                :class="{ 'border-red-500': validationErrors.email || form.errors.email }"
                @blur="checkEmailAvailability"
                required
                autocomplete="email"
              />
              <div v-if="validationErrors.email || form.errors.email" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.email) ? validationErrors.email[0] : validationErrors.email || form.errors.email }}
              </div>
            </div>

            <div>
              <Label htmlFor="phone">Phone Number</Label>
              <Input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="mt-1"
                :class="{ 'border-red-500': validationErrors.phone || form.errors.phone }"
                autocomplete="tel"
              />
              <div v-if="validationErrors.phone || form.errors.phone" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.phone) ? validationErrors.phone[0] : validationErrors.phone || form.errors.phone }}
              </div>
            </div>

            <div>
              <Label htmlFor="password">Password *</Label>
              <div class="relative">
                <Input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="mt-1 pr-10"
                  :class="{ 'border-red-500': validationErrors.password || form.errors.password }"
                  required
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                >
                  <Eye v-if="!showPassword" class="w-4 h-4" />
                  <EyeOff v-else class="w-4 h-4" />
                </button>
              </div>

              <!-- Password Strength Indicator -->
              <div v-if="form.password" class="mt-2">
                <div class="flex items-center space-x-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div
                      :class="passwordStrengthColor"
                      class="h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${(passwordStrength.score / 5) * 100}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-medium">{{ passwordStrengthText }}</span>
                </div>
                <div v-if="passwordStrength.feedback.length > 0" class="text-xs text-gray-600 mt-1">
                  Missing: {{ passwordStrength.feedback.join(', ') }}
                </div>
              </div>

              <div v-if="validationErrors.password || form.errors.password" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.password) ? validationErrors.password[0] : validationErrors.password || form.errors.password }}
              </div>
            </div>

            <div>
              <Label htmlFor="password_confirmation">Confirm Password *</Label>
              <div class="relative">
                <Input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  class="mt-1 pr-10"
                  :class="{ 'border-red-500': form.password && form.password_confirmation && form.password !== form.password_confirmation }"
                  required
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  @click="showPasswordConfirmation = !showPasswordConfirmation"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                >
                  <Eye v-if="!showPasswordConfirmation" class="w-4 h-4" />
                  <EyeOff v-else class="w-4 h-4" />
                </button>
              </div>
              <div v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="text-red-600 text-sm mt-1">
                Passwords do not match
              </div>
            </div>

            <Button
              type="submit"
              class="w-full"
              :disabled="isValidatingStep || !isCurrentStepValid"
            >
              <Loader2 v-if="isValidatingStep" class="mr-2 h-4 w-4 animate-spin" />
              Continue
            </Button>
          </form>
        </CardContent>
      </Card>

      <!-- Step 2: Company Information -->
      <Card v-if="step === 2" class="shadow-none border-none">
        <CardHeader class="p-0">
          <CardTitle>Company Information</CardTitle>
          <CardDescription>
            Tell us about your company
          </CardDescription>
        </CardHeader>
        <CardContent class="p-0 border-none">
          <form @submit.prevent="nextStep" class="space-y-4">
            <div>
              <Label htmlFor="company_name">Company Name *</Label>
              <Input
                id="company_name"
                v-model="form.company_name"
                type="text"
                class="mt-1"
                :class="{ 'border-red-500': validationErrors.company_name || form.errors.company_name }"
                required
                autocomplete="organization"
              />
              <div v-if="validationErrors.company_name || form.errors.company_name" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.company_name) ? validationErrors.company_name[0] : validationErrors.company_name || form.errors.company_name }}
              </div>
            </div>

            <div>
              <Label htmlFor="industry">Industry</Label>
              <Select
                :model-value="form.industry"
                @update:model-value="form.industry = $event"
              >
                <SelectTrigger class="mt-1">
                  <SelectValue placeholder="Select industry" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="outdoor-advertising">Outdoor Advertising</SelectItem>
                  <SelectItem value="marketing-agency">Marketing Agency</SelectItem>
                  <SelectItem value="real-estate">Real Estate</SelectItem>
                  <SelectItem value="retail">Retail</SelectItem>
                  <SelectItem value="other">Other</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="validationErrors.industry || form.errors.industry" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.industry) ? validationErrors.industry[0] : validationErrors.industry || form.errors.industry }}
              </div>
            </div>

            <div>
              <Label htmlFor="company_size">Company Size</Label>
              <Select
                :model-value="form.company_size"
                @update:model-value="form.company_size = $event"
              >
                <SelectTrigger class="mt-1">
                  <SelectValue placeholder="Select size" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="1-10">1-10 employees</SelectItem>
                  <SelectItem value="11-50">11-50 employees</SelectItem>
                  <SelectItem value="51-200">51-200 employees</SelectItem>
                  <SelectItem value="200+">200+ employees</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="validationErrors.company_size || form.errors.company_size" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.company_size) ? validationErrors.company_size[0] : validationErrors.company_size || form.errors.company_size }}
              </div>
            </div>

            <div>
              <Label htmlFor="address">Address</Label>
              <Textarea
                id="address"
                v-model="form.address"
                class="mt-1"
                rows="3"
                :class="{ 'border-red-500': validationErrors.address || form.errors.address }"
                placeholder="Enter your company address"
              />
              <div v-if="validationErrors.address || form.errors.address" class="text-red-600 text-sm mt-1">
                {{ Array.isArray(validationErrors.address) ? validationErrors.address[0] : validationErrors.address || form.errors.address }}
              </div>
            </div>

            <div class="flex space-x-2">
              <Button type="button" variant="outline" @click="previousStep" class="flex-1">
                Back
              </Button>
              <Button
                type="submit"
                class="flex-1"
                :disabled="isValidatingStep || !isCurrentStepValid"
              >
                <Loader2 v-if="isValidatingStep" class="mr-2 h-4 w-4 animate-spin" />
                Continue
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Step 3: Subscription Plan -->
      <Card v-if="step === 3" class="shadow-none border-none">
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
              @click="form.subscription_plan = plan.id"
            >
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-semibold">{{ plan.name }}</h3>
                  <p class="text-2xl font-bold text-blue-600">${{ plan.price }}<span class="text-sm font-normal text-gray-600">/month</span></p>
                </div>
                <div class="w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center">
                  <div
                    v-if="form.subscription_plan === plan.id"
                    class="w-2 h-2 bg-blue-600 rounded-full"
                  ></div>
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

          <div class="flex space-x-2">
            <Button type="button" variant="outline" @click="previousStep" class="flex-1">
              Back
            </Button>
            <Button
              @click="submit"
              :disabled="form.processing || !form.subscription_plan"
              class="flex-1"
            >
              <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
              Complete Registration
            </Button>
          </div>
        </CardContent>
      </Card>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
          Already have an account?
          <Link :href="route('login')" class="font-medium text-blue-600 hover:text-blue-500">
            Sign in
          </Link>
        </p>
      </div>
    </div>
  </GuestLayout>
</template>
