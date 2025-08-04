<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import TemplateEditor from '@/components/TemplateEditor.vue'
import { ref } from 'vue'

const form = useForm({
  name: '',
  description: '',
  content: `<div style="text-align: center; margin-bottom: 2rem;">
{{company_logo}}
</div>

<h1>{{contract_type}} AGREEMENT</h1>

<p>This agreement is entered into on <strong>{{today_date}}</strong> between:</p>

<h2>PARTIES</h2>
<p><strong>Client:</strong> {{client_name}}<br>
{{client_company}}<br>
{{client_address}}<br>
Email: {{client_email}}<br>
Phone: {{client_phone}}</p>

<p><strong>Company:</strong> {{company_name}}<br>
{{company_address}}</p>

<h2>BILLBOARD ADVERTISING SERVICES</h2>
<p>The Company agrees to provide billboard advertising services at the following locations:</p>
<p>{{billboard_locations}}</p>

<h2>TERMS AND CONDITIONS</h2>
<p><strong>Contract Period:</strong> From {{start_date}} to {{end_date}}</p>
<p><strong>Monthly Fee:</strong> {{monthly_amount}}</p>
<p><strong>Total Contract Value:</strong> {{total_amount}}</p>
<p><strong>Payment Terms:</strong> {{payment_terms}}</p>

<h2>OBLIGATIONS</h2>
<p>The Client agrees to:</p>
<ul>
  <li>Provide advertising materials in the specified format</li>
  <li>Make payments according to the agreed schedule</li>
  <li>Comply with local advertising regulations</li>
</ul>

<p>The Company agrees to:</p>
<ul>
  <li>Display the advertising materials as agreed</li>
  <li>Maintain the billboard in good condition</li>
  <li>Provide regular updates on campaign performance</li>
</ul>

<h2>CANCELLATION</h2>
<p>Either party may terminate this agreement with 30 days written notice.</p>

<h2>SIGNATURES</h2>
<p>By signing below, both parties agree to the terms and conditions outlined in this contract.</p>`,
  is_active: true
})

const templateModal = ref(null)

const submit = () => {
  form.post(route('contract-templates.store'), {
    onSuccess: () => {
      toast.success('Template created successfully!', {
        description: `"${form.name}" has been added to your templates.`
      })

      // Close the modal after successful creation
      templateModal.value?.close()
    },
    onError: (errors) => {
      toast.error('Failed to create template', {
        description: Object.values(errors).flat().join(', ')
      })
    }
  })
}
</script>

<template>
  <ModalUi
    ref="templateModal"
    max-width="6xl"
    padding-classes="p-0"
  >
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Create New Template</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        Create a reusable contract template for your organization
      </p>
    </div>

    <form @submit.prevent="submit" class="p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Template Name -->
        <div class="space-y-2">
          <Label for="name">Template Name</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="e.g., Standard Billboard Contract"
            :class="{ 'border-red-500': form.errors.name }"
            required
          />
          <p v-if="form.errors.name" class="text-sm text-red-600">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Template Status -->
        <div class="space-y-2">
          <Label for="is_active">Template Status</Label>
          <div class="flex items-center space-x-2">
            <Switch
              id="is_active"
              v-model:checked="form.is_active"
            />
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ form.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="space-y-2">
        <Label for="description">Description</Label>
        <Textarea
          id="description"
          v-model="form.description"
          placeholder="Describe when and how this template should be used..."
          rows="3"
          :class="{ 'border-red-500': form.errors.description }"
        />
        <p v-if="form.errors.description" class="text-sm text-red-600">
          {{ form.errors.description }}
        </p>
      </div>

      <!-- Template Content -->
      <div class="space-y-2">
        <Label for="content">Template Content</Label>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Use the editor below to create your template. You can insert placeholders using the "Placeholders" dropdown in the toolbar.
        </p>
        <TemplateEditor
          v-model="form.content"
          placeholder="Start writing your template content..."
          :class="{ 'border-red-500': form.errors.content }"
        />
        <p v-if="form.errors.content" class="text-sm text-red-600">
          {{ form.errors.content }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <ModalLink href="/contracts">
          <Button variant="outline" type="button">
            Cancel
          </Button>
        </ModalLink>

        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Creating...' : 'Create Template' }}
        </Button>
      </div>
    </form>
  </ModalUi>
</template>
