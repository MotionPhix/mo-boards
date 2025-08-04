<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  ArrowLeft,
  Eye,
  Save,
  Loader2,
  Download
} from 'lucide-vue-next';
import DocumentEditor from '@/components/DocumentEditor.vue';
import { ref } from 'vue';

interface Contract {
  id: number;
  uuid: string;
  contract_number: string;
  client_name: string;
  design?: string;
  content?: string;
  custom_field_values?: any[];
  template?: {
    id: number;
    name: string;
    custom_fields?: any[];
  };
  created_at: string;
  updated_at: string;
}

interface Props {
  contract: Contract;
}

const props = defineProps<Props>();

const selectedPlaceholder = ref('');

// Define available placeholders for the contract
const placeholders = [
  { id: 'client_name', label: 'Client Name', value: '{{client_name}}' },
  { id: 'contract_id', label: 'Contract ID', value: '{{contract_id}}' },
  { id: 'contract_date', label: 'Contract Date', value: '{{contract_date}}' },
  { id: 'contract_amount', label: 'Contract Amount', value: '{{contract_amount}}' },
  { id: 'start_date', label: 'Start Date', value: '{{start_date}}' },
  { id: 'end_date', label: 'End Date', value: '{{end_date}}' },
  { id: 'company_name', label: 'Company Name', value: '{{company_name}}' },
  { id: 'company_address', label: 'Company Address', value: '{{company_address}}' },
  { id: 'company_email', label: 'Company Email', value: '{{company_email}}' },
  { id: 'company_phone', label: 'Company Phone', value: '{{company_phone}}' },
  { id: 'client_email', label: 'Client Email', value: '{{client_email}}' },
  { id: 'client_phone', label: 'Client Phone', value: '{{client_phone}}' },
  { id: 'client_address', label: 'Client Address', value: '{{client_address}}' },
];

// Function to insert placeholder at cursor position
const insertPlaceholder = () => {
  if (!selectedPlaceholder.value) return;

  const placeholder = placeholders.find(p => p.id === selectedPlaceholder.value);
  if (!placeholder) return;

  // Dispatch custom event that the DocumentEditor can listen for
  const event = new CustomEvent('insert-placeholder', {
    detail: { text: placeholder.value }
  });
  document.dispatchEvent(event);

  // Reset the selection
  selectedPlaceholder.value = '';
};

const form = useForm({
  design: props.contract.design || '',
  custom_field_values: props.contract.custom_field_values || [],
});

const submit = () => {
  form.put(route('contracts.document.update', props.contract.uuid), {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by controller redirect
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};

const previewDocument = () => {
  router.visit(route('contracts.document.show', props.contract.uuid));
};

const downloadPdf = () => {
  window.open(route('contracts.document.pdf', props.contract.uuid), '_blank');
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <AppLayout
    :title="`Edit Contract Document: ${contract.contract_number}`"
    :breadcrumbs="[
      { label: 'Contracts', href: route('contracts.index') },
      { label: contract.contract_number, href: route('contracts.show', contract.id) },
      { label: 'Edit Document' }
    ]">

    <div class="py-8">
      <div class="max-w-7xl">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Contract Document</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Customize the contract content while preserving the template structure
            </p>
          </div>
          <div class="flex gap-3">
            <Button variant="outline" @click="previewDocument">
              <Eye class="w-4 h-4 mr-2" />
              Preview Document
            </Button>
            <Button variant="outline" @click="downloadPdf">
              <Download class="w-4 h-4 mr-2" />
              Download PDF
            </Button>
            <Button variant="outline" @click="router.visit(route('contracts.show', contract.id))">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Contract
            </Button>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
              <!-- Document Editor -->
              <Card>
                <CardHeader>
                  <div class="flex justify-between items-center">
                    <div>
                      <CardTitle>Contract Document</CardTitle>
                      <CardDescription>
                        Edit your contract content using placeholders for dynamic values
                      </CardDescription>
                    </div>

                    <div class="flex gap-2">
                      <Select v-model="selectedPlaceholder" @update:modelValue="insertPlaceholder">
                        <SelectTrigger class="w-[200px]">
                          <SelectValue placeholder="Insert placeholder..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="">Select a placeholder</SelectItem>
                          <SelectItem v-for="placeholder in placeholders" :key="placeholder.id" :value="placeholder.id">
                            {{ placeholder.label }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                </CardHeader>

                <CardContent>
                  <div>
                    <DocumentEditor
                      v-model="form.design"
                    />
                    <div v-if="form.errors.design" class="mt-1 text-sm text-red-600">
                      {{ form.errors.design }}
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Custom Fields -->
              <Card v-if="contract.template?.custom_fields && contract.template.custom_fields.length > 0">
                <CardHeader>
                  <CardTitle>Custom Field Values</CardTitle>
                  <CardDescription>
                    Fill in the custom field values for this contract
                  </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                  <div
                    v-for="(field, index) in contract.template.custom_fields"
                    :key="field.id"
                    class="space-y-2"
                  >
                    <Label :for="`field_${field.id}`">{{ field.label || field.name }}</Label>
                    <Input
                      :id="`field_${field.id}`"
                      v-model="form.custom_field_values[index]"
                      :type="field.type || 'text'"
                      :placeholder="field.placeholder || ''"
                      :required="field.required || false"
                    />
                    <p v-if="field.description" class="text-sm text-gray-600 dark:text-gray-400">
                      {{ field.description }}
                    </p>
                  </div>
                </CardContent>
              </Card>

              <!-- Action Buttons -->
              <Card>
                <CardContent class="pt-6">
                  <div class="flex justify-end space-x-3">
                    <Button
                      type="button"
                      variant="outline"
                      @click="router.visit(route('contracts.show', contract.id))"
                    >
                      Cancel
                    </Button>
                    <Button
                      type="submit"
                      :disabled="form.processing"
                    >
                      <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                      <Save v-else class="w-4 h-4 mr-2" />
                      {{ form.processing ? 'Saving...' : 'Save Document' }}
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
              <!-- Contract Info -->
              <Card>
                <CardHeader>
                  <CardTitle>Contract Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Contract #:</span>
                      <span class="font-medium">{{ contract.contract_number }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Client:</span>
                      <span class="font-medium">{{ contract.client_name }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Created:</span>
                      <span class="font-medium">{{ formatDate(contract.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600 dark:text-gray-400">Updated:</span>
                      <span class="font-medium">{{ formatDate(contract.updated_at) }}</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Template Info -->
              <Card v-if="contract.template">
                <CardHeader>
                  <CardTitle>Source Template</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="space-y-2">
                    <h4 class="font-medium">{{ contract.template.name }}</h4>
                    <Badge variant="secondary" class="text-xs">
                      Template-based
                    </Badge>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      This contract was created from a template. You can modify the content here without affecting the original template.
                    </p>
                  </div>
                </CardContent>
              </Card>

              <!-- Placeholder Help -->
              <Card>
                <CardHeader>
                  <CardTitle>Available Placeholders</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="space-y-2">
                    <div v-for="placeholder in placeholders.slice(0, 6)" :key="placeholder.id" class="text-xs">
                      <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">{{ placeholder.value }}</code>
                      <span class="ml-2 text-gray-600 dark:text-gray-400">{{ placeholder.label }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                      Use the dropdown above to insert placeholders at your cursor position.
                    </p>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
