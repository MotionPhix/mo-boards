<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
  Pencil as Edit,
  Plus,
  Copy,
  Eye,
  Download,
  FileText,
  Users,
  ZoomIn,
  ZoomOut,
  Circle,
  ExternalLink
} from 'lucide-vue-next';
import { useStorage } from '@vueuse/core';

interface Contract {
  id: number;
  uuid: string;
  title?: string;
  description?: string;
  client_name?: string;
  status: string;
  created_at: string;
}

interface ContractTemplate {
  id: number;
  uuid: string;
  name: string;
  description?: string;
  content?: string;
  default_terms?: Record<string, any>;
  custom_fields?: any[];
  is_active: boolean;
  contracts?: Contract[];
  created_at: string;
  updated_at: string;
}

interface Props {
  template: ContractTemplate;
}

const props = defineProps<Props>();

const zoom = ref(1.0);

const viewMode = useStorage('document_view', 'preview');

// Return the content directly for preview since CKEditor already uses inline styles
const processedContent = computed(() => {
  if (!props.template.content) return '';
  return props.template.content;
});

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatFieldName = (fieldName: string): string => {
  return fieldName
    .replace(/[_-]/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusVariant = (status: string) => {
  const variants: Record<string, "default" | "destructive" | "outline" | "secondary"> = {
    draft: "secondary",
    pending_approval: "outline",
    approved: "default",
    signed: "default",
    completed: "default",
    cancelled: "destructive",
  };
  return variants[status] || "secondary";
};

const zoomIn = () => {
  if (zoom.value < 2) {
    zoom.value += 0.1;
  }
};

const zoomOut = () => {
  if (zoom.value > 0.5) {
    zoom.value -= 0.1;
  }
};

const duplicateTemplate = () => {
  router.post(route('contract-templates.duplicate', props.template.uuid), {}, {
    onSuccess: () => {
      router.visit(route('contract-templates.index'));
    },
    onError: (errors) => {
      console.error('Error duplicating template:', errors);
    }
  });
};

const downloadTemplate = () => {
  // Direct download via backend PDF generation
  const downloadUrl = route('contract-templates.export-pdf', props.template.uuid);

  // Create a temporary link to trigger download
  const link = document.createElement('a');
  link.href = downloadUrl;
  link.target = '_blank';
  link.download = `template-${props.template.name.replace(/[^a-zA-Z0-9]/g, '-')}-${new Date().toISOString().split('T')[0]}.pdf`;

  // Trigger download
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<template>
  <AppLayout
    :title="template.name"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Templates', href: route('contract-templates.index') },
      { label: template.name }
    ]">

    <div class="py-8">
      <div class="max-w-7xl">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ template.name }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              {{ template.description || 'No description provided' }}
            </p>
          </div>
          <div class="flex gap-3">
            <Button @click="router.visit(route('contract-templates.edit', template.uuid))">
              <Edit class="w-4 h-4 mr-2" />
              Edit
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- Left Sidebar - Template Info & Actions -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Template Stats Card -->
            <Card>
              <CardHeader>
                <CardTitle>Template Overview</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 gap-4">
                  <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                      {{ template.contracts?.length || 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Contracts Created</div>
                  </div>

                  <div class="text-center p-4 rounded-lg" :class="template.is_active ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
                    <div class="flex items-center justify-center mb-2">
                      <Badge :variant="template.is_active ? 'default' : 'destructive'">
                        <Circle class="w-2 h-2 mr-1" :class="template.is_active ? 'fill-green-500' : 'fill-red-500'" />
                        {{ template.is_active ? 'Active' : 'Inactive' }}
                      </Badge>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                  </div>
                </div>

                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Created:</span>
                    <span class="font-medium">{{ formatDate(template.created_at) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Updated:</span>
                    <span class="font-medium">{{ formatDate(template.updated_at) }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Quick Actions Card -->
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <Button
                  class="w-full"
                  @click="router.visit(route('contracts.create') + '?template=' + template.uuid)"
                >
                  <Plus class="w-4 h-4 mr-2" />
                  Create Contract
                </Button>

                <Button variant="outline" class="w-full" @click="duplicateTemplate">
                  <Copy class="w-4 h-4 mr-2" />
                  Duplicate Template
                </Button>

                <Button variant="outline" class="w-full" @click="downloadTemplate">
                  <Download class="w-4 h-4 mr-2" />
                  Download PDF
                </Button>
              </CardContent>
            </Card>

            <!-- Custom Fields Info -->
            <Card v-if="template.custom_fields && template.custom_fields.length > 0">
              <CardHeader>
                <CardTitle>Custom Fields</CardTitle>
                <CardDescription>{{ template.custom_fields.length }} field(s) configured</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <div
                    v-for="(field, index) in template.custom_fields"
                    :key="index"
                    class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                  >
                    <div class="flex justify-between items-start">
                      <div>
                        <h4 class="font-medium text-sm">{{ field.label || field.name }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ field.description || 'No description' }}</p>
                      </div>
                      <Badge variant="secondary" class="text-xs">{{ field.type || 'text' }}</Badge>
                    </div>
                    <div v-if="field.required" class="mt-2">
                      <Badge variant="destructive" class="text-xs">Required</Badge>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Main Content Area -->
          <div class="lg:col-span-3">
            <Tabs v-model="viewMode" class="w-full">
              <TabsList class="grid w-full grid-cols-2 max-w-sm mx-auto">
                <TabsTrigger value="preview">
                  <FileText class="w-4 h-4 mr-2" />
                  Document Preview
                </TabsTrigger>

                <TabsTrigger value="contracts">
                  <Users class="w-4 h-4 mr-2" />
                  Contracts ({{ template.contracts?.length || 0 }})
                </TabsTrigger>
              </TabsList>

              <!-- Document Preview Tab -->
              <TabsContent value="preview" class="mt-6">
                <Card>
                  <CardHeader>
                    <div class="flex justify-between items-center">
                      <div>
                        <CardTitle>Document Preview</CardTitle>
                        <CardDescription>See how this template will appear as a contract document</CardDescription>
                      </div>
                      <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="zoomOut" :disabled="zoom <= 0.5">
                          <ZoomOut class="w-4 h-4" />
                        </Button>
                        <span class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-800 rounded">{{ Math.round(zoom * 100) }}%</span>
                        <Button variant="outline" size="sm" @click="zoomIn" :disabled="zoom >= 2">
                          <ZoomIn class="w-4 h-4" />
                        </Button>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <!-- Document Preview -->
                    <div class="flex justify-center bg-gray-100 dark:bg-gray-900 rounded-lg">
                      <div
                        id="template-preview"
                        class="bg-white w-full max-h-[800px] overflow-y-auto print:max-h-none"
                        :style="{
                          transform: `scale(${zoom})`,
                          transformOrigin: 'top center'
                        }"
                      >
                        <!-- Pure Template Content -->
                        <div class="p-8 print:p-12">
                          <div class="prose prose-sm max-w-none text-gray-800 leading-relaxed print:prose-base template-content">
                            <div v-if="template.content" v-html="processedContent"></div>
                            <div v-else class="text-gray-500 italic text-center py-20">
                              <FileText class="mx-auto h-16 w-16 mb-4" />
                              <p>No template content available</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <!-- Default Terms Display -->
                <Card v-if="template.default_terms && Object.keys(template.default_terms).length > 0" class="mt-6">
                  <CardHeader>
                    <CardTitle>Default Terms</CardTitle>
                    <CardDescription>Default values that will be applied to contracts</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div v-for="(value, key) in template.default_terms" :key="key" class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ formatFieldName(String(key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ value }}</dd>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <!-- Contracts Tab -->
              <TabsContent value="contracts" class="mt-6">
                <Card>
                  <CardHeader>
                    <div class="flex justify-between items-center">
                      <div>
                        <CardTitle>Contracts Using This Template</CardTitle>
                        <CardDescription>{{ template.contracts?.length || 0 }} contract(s) created from this template</CardDescription>
                      </div>
                      <Button @click="router.visit(route('contracts.index') + '?template=' + template.uuid)">
                        <ExternalLink class="w-4 h-4 mr-2" />
                        View All
                      </Button>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <div v-if="template.contracts && template.contracts.length > 0">
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Contract</TableHead>
                            <TableHead>Client</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          <TableRow v-for="contract in template.contracts.slice(0, 10)" :key="contract.id">
                            <TableCell>
                              <div>
                                <div class="font-medium">{{ contract.title || `Contract #${contract.id}` }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ contract.description || 'No description' }}</div>
                              </div>
                            </TableCell>
                            <TableCell>{{ contract.client_name || 'Unknown Client' }}</TableCell>
                            <TableCell>
                              <Badge :variant="getStatusVariant(contract.status)">
                                {{ contract.status }}
                              </Badge>
                            </TableCell>
                            <TableCell>{{ formatDate(contract.created_at) }}</TableCell>
                            <TableCell class="text-right">
                              <Button variant="ghost" size="sm" @click="router.visit(route('contracts.show', contract.uuid))">
                                <Eye class="w-4 h-4" />
                              </Button>
                            </TableCell>
                          </TableRow>
                        </TableBody>
                      </Table>
                    </div>
                    <div v-else class="text-center py-12">
                      <FileText class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No contracts yet</h3>
                      <p class="text-gray-600 dark:text-gray-400 mb-6">This template hasn't been used to create any contracts yet.</p>
                      <Button @click="router.visit(route('contracts.create') + '?template=' + template.id)">
                        <Plus class="w-4 h-4 mr-2" />
                        Create First Contract
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.prose h1, .prose h2, .prose h3, .prose h4 {
  color: #111827;
  font-weight: 600;
}

.prose p {
  color: #1f2937;
  line-height: 1.625;
}

.prose ul, .prose ol {
  margin-left: 1.5rem;
}

.prose blockquote {
  border-left: 4px solid #d1d5db;
  padding-left: 1rem;
  font-style: italic;
}

/* CKEditor Content Styles - Match editor output */
.template-content :deep(table) {
  border-collapse: collapse;
  margin: 1rem 0;
  overflow: hidden;
  table-layout: fixed;
  width: 100%;
  border: 2px solid #ced4da;
}

.template-content :deep(table td),
.template-content :deep(table th) {
  border: 2px solid #ced4da;
  box-sizing: border-box;
  min-width: 1em;
  padding: 8px 12px;
  position: relative;
  vertical-align: top;
}

.template-content :deep(table th) {
  background-color: #f8f9fa;
  font-weight: bold;
  text-align: left;
}

.template-content :deep(hr) {
  border: none;
  border-top: 2px solid #e5e7eb;
  margin: 2rem 0;
}

.template-content :deep(ul) {
  list-style-type: disc;
  margin-left: 1.5rem;
  margin: 1rem 0;
}

.template-content :deep(ol) {
  list-style-type: decimal;
  margin-left: 1.5rem;
  margin: 1rem 0;
}

.template-content :deep(li) {
  margin: 0.25rem 0;
  padding-left: 0.25rem;
}

.template-content :deep(blockquote) {
  border-left: 4px solid #d1d5db;
  padding-left: 1rem;
  margin: 1rem 0;
  font-style: italic;
  color: #6b7280;
}

.template-content :deep(h1) {
  font-size: 2rem;
  font-weight: bold;
  margin: 1.5rem 0 1rem 0;
  color: #111827;
}

.template-content :deep(h2) {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 1.25rem 0 0.75rem 0;
  color: #111827;
}

.template-content :deep(h3) {
  font-size: 1.25rem;
  font-weight: bold;
  margin: 1rem 0 0.5rem 0;
  color: #111827;
}

.template-content :deep(h4) {
  font-size: 1.125rem;
  font-weight: bold;
  margin: 1rem 0 0.5rem 0;
  color: #111827;
}

.template-content :deep(p) {
  margin: 0.75rem 0;
  line-height: 1.625;
}

.template-content :deep(strong) {
  font-weight: bold;
}

.template-content :deep(em) {
  font-style: italic;
}

.template-content :deep(u) {
  text-decoration: underline;
}

.template-content :deep(mark) {
  background-color: #fef08a;
  padding: 0.125rem;
}

/* Text alignment classes from CKEditor */
.template-content :deep([style*="text-align: left"]) {
  text-align: left;
}

.template-content :deep([style*="text-align: center"]) {
  text-align: center;
}

.template-content :deep([style*="text-align: right"]) {
  text-align: right;
}

.template-content :deep([style*="text-align: justify"]) {
  text-align: justify;
}

/* Print Styles */
@media print {
  body * {
    visibility: hidden;
  }

  #template-preview,
  #template-preview * {
    visibility: visible;
  }

  #template-preview {
    position: absolute;
    left: 0;
    top: 0;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: none !important;
    box-shadow: none !important;
  }

  /* Ensure proper A4 dimensions for print */
  @page {
    size: A4;
    margin: 0.5in;
  }

  /* Hide all UI elements during print */
  .print\:hidden {
    display: none !important;
  }

  /* Ensure tables print properly */
  .template-content table {
    page-break-inside: avoid;
  }

  .template-content th, .template-content td {
    page-break-inside: avoid;
  }
}
</style>
