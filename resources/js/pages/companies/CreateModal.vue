<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

const form = useForm({
  name: '',
  industry: '',
  size: null as string | null,
  address: '',
  subscription_plan: 'starter',
})

function submit() {
  form.post(route('companies.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Close modal by navigating back
      history.back()
    },
  })
}
</script>

<template>
  <Head title="Create Company" />
  <ModalUi max-width="lg">
    <Card>
      <CardHeader>
        <CardTitle>Create Company</CardTitle>
        <CardDescription>Set up your company details. You can change these later in Settings.</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-5">
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required />
            <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
          </div>

          <div class="space-y-2">
            <Label for="industry">Industry</Label>
            <Input id="industry" v-model="form.industry" />
          </div>

          <div class="space-y-2">
            <Label for="size">Size</Label>
            <Select v-model="form.size">
              <SelectTrigger>
                <SelectValue placeholder="Select size" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null">Select size</SelectItem>
                <SelectItem value="1-10">1-10</SelectItem>
                <SelectItem value="11-50">11-50</SelectItem>
                <SelectItem value="51-200">51-200</SelectItem>
                <SelectItem value="200+">200+</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="address">Address</Label>
            <Textarea id="address" v-model="form.address" rows="3" />
          </div>

          <div class="space-y-2">
            <Label for="plan">Subscription Plan</Label>
            <Select v-model="form.subscription_plan">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="starter">Starter</SelectItem>
                <SelectItem value="professional">Professional</SelectItem>
                <SelectItem value="enterprise">Enterprise</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <Button type="submit" :disabled="form.processing">Create</Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </ModalUi>
  
</template>
