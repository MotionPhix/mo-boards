<script setup lang="ts">
import { onUnmounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Loader2 } from 'lucide-vue-next'
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
import { useStorage } from '@vueuse/core';

const step = useStorage('registration_steps', 1)

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

const nextStep = () => {
  if (step.value < 3) {
    step.value++
  }
}

const submit = () => {
  form.post(route('register'))
}

onUnmounted(() => {
  step.value = null
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
            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
            step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
          ]">
            1
          </div>
          <div class="w-8 h-0.5 bg-gray-200"></div>
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
            step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
          ]">
            2
          </div>
          <div class="w-8 h-0.5 bg-gray-200"></div>
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
            step >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
          ]">
            3
          </div>
        </div>
      </div>

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
                :class="{ 'border-red-500': form.errors.name }"
                required
              />
              <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                {{ form.errors.name }}
              </div>
            </div>

            <div>
              <Label htmlFor="email">Email Address *</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.email }"
                required
              />
              <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                {{ form.errors.email }}
              </div>
            </div>

            <div>
              <Label htmlFor="phone">Phone Number</Label>
              <Input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.phone }"
              />
              <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">
                {{ form.errors.phone }}
              </div>
            </div>

            <div>
              <Label htmlFor="password">Password *</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.password }"
                required
              />
              <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                {{ form.errors.password }}
              </div>
            </div>

            <div>
              <Label htmlFor="password_confirmation">Confirm Password *</Label>
              <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1"
                required
              />
            </div>

            <Button type="submit" class="w-full">
              Continue
            </Button>
          </form>
        </CardContent>
      </Card>

      <!-- Step 2: Company Information -->
      <Card v-if="step === 2">
        <CardHeader>
          <CardTitle>Company Information</CardTitle>
          <CardDescription>
            Tell us about your company
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="nextStep" class="space-y-4">
            <div>
              <Label htmlFor="company_name">Company Name *</Label>
              <Input
                id="company_name"
                v-model="form.company_name"
                type="text"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.company_name }"
                required
              />
              <div v-if="form.errors.company_name" class="text-red-600 text-sm mt-1">
                {{ form.errors.company_name }}
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
            </div>

            <div>
              <Label htmlFor="address">Address</Label>
              <Textarea
                id="address"
                v-model="form.address"
                class="mt-1"
                rows="3"
              />
            </div>

            <div class="flex space-x-2">
              <Button type="button" variant="outline" @click="step = 1" class="flex-1">
                Back
              </Button>
              <Button type="submit" class="flex-1">
                Continue
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Step 3: Subscription Plan -->
      <Card v-if="step === 3">
        <CardHeader>
          <CardTitle>Choose Your Plan</CardTitle>
          <CardDescription>
            Select the plan that best fits your needs
          </CardDescription>
        </CardHeader>
        <CardContent>
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

          <div class="flex space-x-2">
            <Button type="button" variant="outline" @click="step = 2" class="flex-1">
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
