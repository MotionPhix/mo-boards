<template>
  <Head title="Create Account" />

  <div class="min-h-screen flex items-center justify-center bg-muted/40 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h2 class="mt-6 text-3xl font-extrabold text-foreground">
          Join {{ companyName }}
        </h2>
        <p class="mt-2 text-sm text-muted-foreground">
          Create your account to accept the invitation
        </p>
      </div>

      <Card class="mt-8 shadow-md">
        <CardContent class="pt-6">
          <form @submit.prevent="submit" class="space-y-6">
            <Alert v-if="status" :variant="alertVariant">
              <p>{{ status }}</p>
            </Alert>

            <div>
              <Label for="name">Name</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                class="mt-1"
                :class="{ 'border-destructive': form.errors.name }"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">
                {{ form.errors.name }}
              </p>
            </div>

            <div>
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                disabled
                readonly
                autocomplete="email"
                class="mt-1 bg-muted/50"
              />
              <p class="mt-1 text-xs text-muted-foreground">
                Email address is pre-filled from your invitation
              </p>
            </div>

            <div>
              <Label for="password">Password</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                required
                class="mt-1"
                :class="{ 'border-destructive': form.errors.password }"
              />
              <p v-if="form.errors.password" class="mt-1 text-sm text-destructive">
                {{ form.errors.password }}
              </p>
            </div>

            <div>
              <Label for="password_confirmation">Confirm Password</Label>
              <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                class="mt-1"
              />
            </div>

            <div>
              <Button type="submit" class="w-full" :disabled="form.processing">
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Create Account & Join
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <div class="text-center mt-4">
        <p class="text-sm text-muted-foreground">
          Already have an account?
          <Link :href="route('login')" class="font-medium text-primary hover:underline">
            Sign in
          </Link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import { Alert } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps({
  status: String,
  email: String,
  name: String,
  companyName: String
})

const alertVariant = computed(() => {
  return props.status?.includes('error') ? 'destructive' : 'default'
})

const form = useForm({
  name: props.name || '',
  email: props.email || '',
  password: '',
  password_confirmation: ''
})

const submit = () => {
  form.post(route('register.invited'), {
    preserveScroll: true
  })
}
</script>
