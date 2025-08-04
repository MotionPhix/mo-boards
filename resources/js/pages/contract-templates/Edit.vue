<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AlertDialog from '@/components/ui/alert-dialog/AlertDialog.vue';
import AlertDialogAction from '@/components/ui/alert-dialog/AlertDialogAction.vue';
import AlertDialogCancel from '@/components/ui/alert-dialog/AlertDialogCancel.vue';
import AlertDialogContent from '@/components/ui/alert-dialog/AlertDialogContent.vue';
import AlertDialogDescription from '@/components/ui/alert-dialog/AlertDialogDescription.vue';
import AlertDialogFooter from '@/components/ui/alert-dialog/AlertDialogFooter.vue';
import AlertDialogHeader from '@/components/ui/alert-dialog/AlertDialogHeader.vue';
import AlertDialogTitle from '@/components/ui/alert-dialog/AlertDialogTitle.vue';
import {
  ArrowLeft,
  Eye,
  HelpCircle,
  Plus,
  Copy,
  Trash2,
  Circle,
  Loader2
} from 'lucide-vue-next';
import DocumentEditor from '@/components/DocumentEditor.vue';
import PlaceholderDocumentation from '@/components/PlaceholderDocumentation.vue';
import DefaultTermsEditor from '@/components/DefaultTermsEditor.vue';
import CustomFieldsEditor from '@/components/CustomFieldsEditor.vue';

interface ContractTemplate {
  id: number;
  name: string;
  description?: string;
  content?: string;
  default_terms?: any;
  custom_fields?: any[];
  is_active: boolean;
  contracts?: any[];
  created_at: string;
  updated_at: string;
}

interface Props {
  template: ContractTemplate;
}

const props = defineProps<Props>();

const showPlaceholderDocs = ref(false);
const showDeleteModal = ref(false);
const selectedPlaceholder = ref('');

// Define available placeholders for the template
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
  // Add custom fields placeholders based on your application's needs
];

// Function to insert placeholder at cursor position
const insertPlaceholder = () => {
  if (!selectedPlaceholder.value) return;

  const placeholder = placeholders.find(p => p.id === selectedPlaceholder.value);
  if (!placeholder) return;

  // In a real CKEditor implementation, we'd use CKEditor's API
  // Since we don't have direct access to the editor instance here,
  // we'll need to set up a way to pass this to the DocumentEditor component

  // For demonstration, we'll create a custom event that the DocumentEditor can listen for
  // This event will pass the placeholder text to insert
  const event = new CustomEvent('insert-placeholder', {
    detail: { text: placeholder.value }
  });
  document.dispatchEvent(event);

  // Reset the selection
  selectedPlaceholder.value = '';
};

const form = useForm({
  name: props.template.name,
  description: props.template.description || '',
  content: props.template.content || '',
  default_terms: props.template.default_terms || {},
  custom_fields: props.template.custom_fields || [],
  is_active: props.template.is_active,
});

const submit = () => {
  form.put(route('contract-templates.update', props.template.id), {
    preserveScroll: true,

    onSuccess: () => {
      // Redirect will be handled by the controller
    },

    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};

const confirmDelete = () => {
  showDeleteModal.value = true;
};

const deleteTemplate = () => {
  router.delete(route('contract-templates.destroy', props.template.id), {
    onSuccess: () => {
      // Redirect will be handled by the controller
    },
    onError: (errors) => {
      console.error('Delete error:', errors);
    },
  });
};

const duplicateTemplate = () => {
  router.post(route('contract-templates.duplicate', props.template.id), {}, {
    onSuccess: () => {
      router.visit(route('contract-templates.index'));
    },
    onError: (errors) => {
      console.error('Error duplicating template:', errors);
    }
  });
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Function to print preview the template
const printPreview = () => {
  // Create a new window for printing
  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    console.error('Could not open print window');
    return;
  }

  // Get the template content HTML
  const templateHTML = form.content;

  // Create the print document
  const printDocument = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>${form.name}</title>
      <meta charset="utf-8">
      <style>
        body {
          margin: 0;
          padding: 20px;
          font-family: 'Arial', sans-serif;
          line-height: 1.6;
          color: #333;
        }

        /* Table styles */
        table {
          border-collapse: collapse;
          margin: 1rem 0;
          width: 100%;
          border: 2px solid #ced4da;
        }

        table td, table th {
          border: 2px solid #ced4da;
          padding: 8px 12px;
          vertical-align: top;
        }

        table th {
          background-color: #f8f9fa;
          font-weight: bold;
          text-align: left;
        }

        /* List styles */
        ul {
          list-style-type: disc;
          margin-left: 1.5rem;
          margin: 1rem 0;
        }

        ol {
          list-style-type: decimal;
          margin-left: 1.5rem;
          margin: 1rem 0;
        }

        li {
          margin: 0.25rem 0;
          padding-left: 0.25rem;
        }

        /* Text formatting */
        h1 {
          font-size: 2rem;
          font-weight: bold;
          margin: 1.5rem 0 1rem 0;
        }

        h2 {
          font-size: 1.5rem;
          font-weight: bold;
          margin: 1.25rem 0 0.75rem 0;
        }

        h3 {
          font-size: 1.25rem;
          font-weight: bold;
          margin: 1rem 0 0.5rem 0;
        }

        h4 {
          font-size: 1.125rem;
          font-weight: bold;
          margin: 1rem 0 0.5rem 0;
        }

        p {
          margin: 0.75rem 0;
          line-height: 1.625;
        }

        blockquote {
          border-left: 4px solid #d1d5db;
          padding-left: 1rem;
          margin: 1rem 0;
          font-style: italic;
          color: #6b7280;
        }

        hr {
          border: none;
          border-top: 2px solid #e5e7eb;
          margin: 2rem 0;
        }

        strong {
          font-weight: bold;
        }

        em {
          font-style: italic;
        }

        u {
          text-decoration: underline;
        }

        mark {
          background-color: #fef08a;
          padding: 0.125rem;
        }

        /* Print specific styles */
        @media print {
          body {
            margin: 0;
            padding: 20px;
          }

          table {
            page-break-inside: avoid;
          }

          th, td {
            page-break-inside: avoid;
          }

          @page {
            size: A4;
            margin: 0.5in;
          }
        }
      </style>
    </head>
    <body>
      ${templateHTML}
    </body>
    </html>
  `;

  // Write the document and print
  printWindow.document.write(printDocument);
  printWindow.document.close();

  // Wait for content to load then print
  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
  };
};
</script>

<template>
  <AppLayout
    :title="`Edit Template: ${template.name}`"
    :breadcrumbs="[
      { label: 'Templates', href: route('contract-templates.index') },
      { label: template.name, href: route('contract-templates.show', template.id) },
      { label: 'Edit' }
    ]">

    <div class="py-8">
      <div class="max-w-7xl">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Template</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Modify your contract template content and settings
            </p>
          </div>

          <div class="flex gap-3">
            <Button variant="outline" @click="printPreview">
              <Eye class="w-4 h-4 mr-2" />
              Print Preview
            </Button>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
              <!-- Template Basic Information -->
              <Card>
                <CardHeader>
                  <CardTitle>Template Information</CardTitle>
                  <CardDescription>
                    Update the basic details of your contract template
                  </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                  <div class="space-y-2">
                    <Label for="name">Template Name</Label>
                    <Input
                      id="name"
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="e.g., Billboard Advertising Agreement"
                    />
                    <div v-if="form.errors.name" class="text-sm text-red-600">
                      {{ form.errors.name }}
                    </div>
                  </div>

                  <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      placeholder="Describe when and how this template should be used..."
                    />
                    <div v-if="form.errors.description" class="text-sm text-red-600">
                      {{ form.errors.description }}
                    </div>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Checkbox
                      id="is_active"
                      v-model:checked="form.is_active"
                    />
                    <Label for="is_active">
                      Make this template active and available for use
                    </Label>
                  </div>
                </CardContent>
              </Card>

              <!-- Template Content Editor -->
              <Card>
                <CardHeader>
                  <div class="flex justify-between items-center">
                    <div>
                      <CardTitle>Template Content</CardTitle>
                      <CardDescription>
                        Update your contract template using our rich text editor with placeholder support
                      </CardDescription>
                    </div>

                    <div class="flex gap-2">
                      <Select v-model="selectedPlaceholder" @update:modelValue="insertPlaceholder">
                        <SelectTrigger class="w-[200px] is-large">
                          <SelectValue placeholder="Insert placeholder..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="placeholder in placeholders" :key="placeholder.id" :value="placeholder.id">
                            {{ placeholder.label }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <Button
                        type="button"
                        variant="outline"
                        class="is-large"
                        @click="showPlaceholderDocs = true">
                        <HelpCircle class="w-4 h-4 mr-2" />
                        Placeholder Help
                      </Button>
                    </div>
                  </div>
                </CardHeader>

                <CardContent>
                  <!-- Template Editor -->
                  <div
                      class="no-tailwind">
                    <DocumentEditor
                      v-model="form.content"
                    />
                    <div v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                      {{ form.errors.content }}
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Default Terms -->
              <Card>
                <CardHeader>
                  <CardTitle>Default Terms</CardTitle>
                  <CardDescription>
                    Update default values for common contract terms
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <DefaultTermsEditor v-model="form.default_terms" />
                  <div v-if="form.errors.default_terms" class="mt-1 text-sm text-red-600">
                    {{ form.errors.default_terms }}
                  </div>
                </CardContent>
              </Card>

              <!-- Custom Fields -->
              <Card>
                <CardHeader>
                  <CardTitle>Custom Fields</CardTitle>
                  <CardDescription>
                    Update custom fields that can be filled when creating contracts from this template
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <CustomFieldsEditor v-model="form.custom_fields" />
                  <div v-if="form.errors.custom_fields" class="mt-1 text-sm text-red-600">
                    {{ form.errors.custom_fields }}
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
                        @click="router.visit(route('contract-templates.index'))"
                      >
                        Cancel
                      </Button>
                      <Button
                        type="submit"
                        :disabled="form.processing"
                      >
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        {{ form.processing ? 'Updating...' : 'Update Template' }}
                      </Button>
                    </div>
                  </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
              <!-- Template Usage Statistics -->
              <Card>
                <CardHeader>
                  <CardTitle>Template Usage</CardTitle>
                  <CardDescription>
                    Statistics about how this template is being used
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="grid grid-cols-1 gap-6">
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                      <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ template.contracts?.length || 0 }}
                      </div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">Contracts Created</div>
                    </div>

                    <div class="space-y-3">
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Last Modified:</span>
                        <span class="text-sm font-medium">{{ formatDate(template.updated_at) }}</span>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Created On:</span>
                        <span class="text-sm font-medium">{{ formatDate(template.created_at) }}</span>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                        <Badge :variant="template.is_active ? 'default' : 'secondary'">
                          <Circle class="w-2 h-2 mr-1" :class="template.is_active ? 'fill-green-500' : 'fill-gray-500'" />
                          {{ template.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Quick Actions -->
              <Card>
                <CardHeader>
                  <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                  <Button
                    variant="outline"
                    class="w-full"
                    @click="router.visit(route('contracts.create') + '?template=' + template.id)"
                  >
                    <Plus class="w-4 h-4 mr-2" />
                    Create Contract
                  </Button>

                  <Button
                    variant="outline"
                    class="w-full"
                    @click="duplicateTemplate"
                  >
                    <Copy class="w-4 h-4 mr-2" />
                    Duplicate Template
                  </Button>

                  <Button
                    variant="destructive"
                    class="w-full"
                    @click="confirmDelete"
                  >
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete Template
                  </Button>
                </CardContent>
              </Card>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Placeholder Documentation Modal -->
    <PlaceholderDocumentation
      v-if="showPlaceholderDocs"
      @close="showPlaceholderDocs = false"
    />

    <!-- Delete Confirmation Dialog -->
    <AlertDialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Template</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete "{{ template.name }}"? This action cannot be undone.
            All contracts using this template will remain unchanged, but you won't be able to create new contracts from this template.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction variant="destructive" @click="deleteTemplate">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
