<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  name: '',
  industry: '',
  size: null as string | null,
  address: '',
  subscription_plan: 'starter',
})

function submit() {
  form.post(route('companies.store'))
}
</script>

<template>
  <Head title="Create Company" />
  <AppLayout>
    <template #header>
      <h2 class="text-xl font-semibold">Create Company</h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="bg-card text-card-foreground rounded-lg border p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium">Name</label>
              <input v-model="form.name" class="mt-1 w-full rounded border px-3 py-2" required />
              <p v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium">Industry</label>
              <input v-model="form.industry" class="mt-1 w-full rounded border px-3 py-2" />
            </div>

            <div>
              <label class="block text-sm font-medium">Size</label>
              <select v-model="form.size" class="mt-1 w-full rounded border px-3 py-2">
                <option :value="null">Select size</option>
                <option value="1-10">1-10</option>
                <option value="11-50">11-50</option>
                <option value="51-200">51-200</option>
                <option value="200+">200+</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium">Address</label>
              <textarea v-model="form.address" class="mt-1 w-full rounded border px-3 py-2" rows="3"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium">Subscription Plan</label>
              <select v-model="form.subscription_plan" class="mt-1 w-full rounded border px-3 py-2" required>
                <option value="starter">Starter</option>
                <option value="professional">Professional</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded bg-primary text-primary-foreground text-sm" :disabled="form.processing">
              Create
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
