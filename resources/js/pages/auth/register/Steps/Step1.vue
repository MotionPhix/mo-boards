<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import InputError from '@/components/InputError.vue'
import { toast } from 'vue-sonner';

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('register.validate.step1'), {
    onSuccess: () => {
      toast.success('Personal data saved!', {
        description: 'Proceeding to the next step.',
      });
      router.visit(route('register.step2'));
    },
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <Card>
      <CardHeader>
        <CardTitle>Personal Information</CardTitle>
        <CardDescription>
          Please provide your personal details to get started.
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label for="name">Full Name</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="John Doe"
              required
              autofocus
            />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="john@example.com"
              required
            />
            <InputError :message="form.errors.email" />
          </div>

          <div class="grid gap-2">
            <Label for="phone">Phone Number</Label>
            <Input
              id="phone"
              v-model="form.phone"
              type="tel"
              placeholder="+1234567890"
              required
            />
            <p
              v-if="!form.errors.phone"
              class="text-sm text-muted-foreground">
              Include country code (e.g., +254 for Kenya)
            </p>
            <InputError :message="form.errors.phone" />
          </div>

          <div class="grid gap-2">
            <Label for="password">Password</Label>
            <Input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="new-password"
            />
            <InputError :message="form.errors.password" />
          </div>

          <div class="grid gap-2">
            <Label for="password_confirmation">Confirm Password</Label>
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              autocomplete="new-password"
            />
            <InputError :message="form.errors.password_confirmation" />
          </div>

          <section class="flex justify-end">
            <Button
              type="submit"
              size="lg"
              :disabled="form.processing"
              :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
              Continue
            </Button>
          </section>
        </div>
      </CardContent>
    </Card>
  </form>
</template>
