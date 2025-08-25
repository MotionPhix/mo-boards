&lt;script setup lang="ts">
import { Loader2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { InputError } from '@/components'

const props = defineProps<{
  form: any
  validationErrors: Record<string, string[]>
  isValidating: boolean
  isValid: boolean
}>()

const emit = defineEmits<{
  (e: 'next'): void
  (e: 'back'): void
}>()
&lt;/script>

&lt;template>
  &lt;Card class="shadow-none border-none">
    &lt;CardHeader class="p-0">
      &lt;CardTitle>Company Information&lt;/CardTitle>
      &lt;CardDescription>
        Tell us about your company
      &lt;/CardDescription>
    &lt;/CardHeader>

    &lt;CardContent class="p-0 border-none">
      &lt;form @submit.prevent="$emit('next')" class="space-y-4">
        &lt;div>
          &lt;Label htmlFor="company_name">Company Name *&lt;/Label>
          &lt;Input
            id="company_name"
            v-model="form.company_name"
            type="text"
            class="mt-1"
            :class="{ 'border-red-500': validationErrors.company_name || form.errors.company_name }"
            required
            autocomplete="organization" />
          &lt;div v-if="validationErrors.company_name || form.errors.company_name" class="text-red-600 text-sm mt-1">
            {{ Array.isArray(validationErrors.company_name) ? validationErrors.company_name[0] : validationErrors.company_name || form.errors.company_name }}
          &lt;/div>
        &lt;/div>

        &lt;div>
          <script setup lang="ts">
          import { Loader2 } from 'lucide-vue-next'
          import { Button } from '@/components/ui/button'
          import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
          import { Input } from '@/components/ui/input'
          import { Label } from '@/components/ui/label'
          import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
          import { Textarea } from '@/components/ui/textarea'
          import { InputError } from '@/components'

          const props = defineProps<{
            form: any
            validationErrors: Record<string, string[]>
            isValidating: boolean
            isValid: boolean
          }>()

          const emit = defineEmits<{
            (e: 'next'): void
            (e: 'back'): void
          }>()
          </script>

          <template>
            <Card class="shadow-none border-none">
              <CardHeader class="p-0">
                <CardTitle>Company Information</CardTitle>
                <CardDescription>
                  Tell us about your company
                </CardDescription>
              </CardHeader>

              <CardContent class="p-0 border-none">
                <form @submit.prevent="$emit('next')" class="space-y-4">
                  <div>
                    <Label htmlFor="company_name">Company Name *</Label>
                    <Input
                      id="company_name"
                      v-model="form.company_name"
                      type="text"
                      class="mt-1"
                      :class="{ 'border-red-500': validationErrors.company_name || form.errors.company_name }"
                      required
                      autocomplete="organization" />
                    <div v-if="validationErrors.company_name || form.errors.company_name" class="text-red-600 text-sm mt-1">
                      {{ Array.isArray(validationErrors.company_name) ? validationErrors.company_name[0] : validationErrors.company_name || form.errors.company_name }}
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="industry">Industry</Label>
                    <Select
                      :model-value="form.industry"
                      @update:model-value="form.industry = $event">
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
                      @update:model-value="form.company_size = $event">
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
                      placeholder="Enter your company address" />
                    <div v-if="validationErrors.address || form.errors.address" class="text-red-600 text-sm mt-1">
                      {{ Array.isArray(validationErrors.address) ? validationErrors.address[0] : validationErrors.address || form.errors.address }}
                    </div>
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
                      type="submit"
                      class="flex-1"
                      :disabled="isValidating || !isValid">
                      <Loader2 v-if="isValidating" class="mr-2 h-4 w-4 animate-spin" />
                      Continue
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </template>
