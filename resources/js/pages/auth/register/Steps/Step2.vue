<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from '@/components/InputError.vue'
import { toast } from 'vue-sonner';
import { ArrowLeft } from 'lucide-vue-next'

const form = useForm({
  company_name: '',
  industry: '',
  company_size: '',
  address: '',
  logo: null as File | null,
})

const submit = () => {
  form.post(route('register.validate.step2'), {
    onSuccess: () => {
      toast.success('Company information saved!', {
        description: 'Proceeding to the next step.',
      });
    },
  })
}

const goBack = () => {
  router.visit(route('register'))
}

const industries = [
  { value: 'outdoor-advertising', label: 'Outdoor Advertising' },
  { value: 'marketing-agency', label: 'Marketing Agency' },
  { value: 'real-estate', label: 'Real Estate' },
  { value: 'retail', label: 'Retail' },
  { value: 'other', label: 'Other' },
]

const companySizes = [
  { value: '1-10', label: '1-10 employees' },
  { value: '11-50', label: '11-50 employees' },
  { value: '51-200', label: '51-200 employees' },
  { value: '200+', label: '200+ employees' },
]
</script>

<template>
  <form @submit.prevent="submit">
    <Card>
      <CardHeader>
        <CardTitle>Company Information</CardTitle>
        <CardDescription>
          Tell us about your company. You can also upload your company logo.
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label for="company_name">Company Name</Label>
            <Input
              id="company_name"
              v-model="form.company_name"
              type="text"
              required
              autofocus
            />
            <InputError :message="form.errors.company_name" />
          </div>

          <div class="grid gap-2">
            <Label for="industry">Industry</Label>
            <Select v-model="form.industry">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select Industry" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="industry in industries"
                  :key="industry.value"
                  :value="industry.value">
                  {{ industry.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.industry" />
          </div>

          <div class="grid gap-2">
            <Label for="company_size">Company Size</Label>
            <Select v-model="form.company_size">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select Company Size" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="size in companySizes"
                  :key="size.value"
                  :value="size.value">
                  {{ size.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.company_size" />
          </div>

          <div class="grid gap-2">
            <Label for="address">Company Address</Label>
            <Input
              id="address"
              v-model="form.address"
              type="text"
              required
            />
            <InputError :message="form.errors.address" />
          </div>

          <div class="grid gap-2">
            <Label for="logo">Company Logo (optional)</Label>
            <Input
              id="logo"
              type="file"
              accept="image/*"
              @change="form.logo = $event.target.files?.[0] || null"
            />
            <p class="text-sm text-muted-foreground">
              Upload your company logo (max 2MB)
            </p>
            <InputError :message="form.errors.logo" />
          </div>

          <div class="flex justify-between gap-4">
            <Button
              size="sm"
              type="button"
              variant="outline"
              @click="goBack">
               <ArrowLeft /> Back
            </Button>

            <Button
              size="lg"
              type="submit"
              :disabled="form.processing"
              :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
              Continue
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </form>
</template>
