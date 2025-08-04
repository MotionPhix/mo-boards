<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  ArrowLeft,
  Edit,
  Download,
  Eye,
  FileText,
  Printer
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Contract {
  id: number;
  uuid: string;
  contract_number: string;
  client_name: string;
  design?: string;
  content?: string;
  status: string;
  template?: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

interface Props {
  contract: Contract;
  processedContent: string;
}

const props = defineProps<Props>();

const zoom = ref(1.0);

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

const editDocument = () => {
  router.visit(route('contracts.document.edit', props.contract.uuid));
};

const downloadPdf = () => {
  window.open(route('contracts.document.pdf', props.contract.uuid), '_blank');
};

const printDocument = () => {
  window.print();
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
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
</script>

<template>
  <AppLayout
    :title="`Contract Document: ${contract.contract_number}`"
    :breadcrumbs="[
      { label: 'Contracts', href: route('contracts.index') },
      { label: contract.contract_number, href: route('contracts.show', contract.id) },
      { label: 'Document' }
    ]">

    <div class="py-8">
      <div class="max-w-7xl">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Contract Document</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              {{ contract.contract_number }} - {{ contract.client_name }}
            </p>
          </div>
          <div class="flex gap-3">
            <Button variant="outline" @click="editDocument">
              <Edit class="w-4 h-4 mr-2" />
              Edit Document
            </Button>
            <Button variant="outline" @click="downloadPdf">
              <Download class="w-4 h-4 mr-2" />
              Download PDF
            </Button>
            <Button variant="outline" @click="printDocument">
              <Printer class="w-4 h-4 mr-2" />
              Print
            </Button>
            <Button variant="outline" @click="router.visit(route('contracts.show', contract.id))">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Contract
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
          <!-- Sidebar -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Contract Info -->
            <Card>
              <CardHeader>
                <CardTitle>Contract Details</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                    <Badge :variant="getStatusVariant(contract.status)">
                      {{ contract.status }}
                    </Badge>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Client:</span>
                    <span class="text-sm font-medium">{{ contract.client_name }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Created:</span>
                    <span class="text-sm font-medium">{{ formatDate(contract.created_at) }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Updated:</span>
                    <span class="text-sm font-medium">{{ formatDate(contract.updated_at) }}</span>
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
                  <h4 class="font-medium text-sm">{{ contract.template.name }}</h4>
                  <Badge variant="secondary" class="text-xs">Template-based</Badge>
                </div>
              </CardContent>
            </Card>

            <!-- Document Controls -->
            <Card>
              <CardHeader>
                <CardTitle>Document Controls</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm">Zoom:</span>
                  <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="zoomOut" :disabled="zoom <= 0.5">
                      <Eye class="w-3 h-3" />
                    </Button>
                    <span class="text-sm font-mono">{{ Math.round(zoom * 100) }}%</span>
                    <Button variant="outline" size="sm" @click="zoomIn" :disabled="zoom >= 2">
                      <Eye class="w-3 h-3" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Main Document Area -->
          <div class="lg:col-span-4">
            <Card>
              <CardHeader>
                <div class="flex justify-between items-center">
                  <div>
                    <CardTitle>Final Contract Document</CardTitle>
                    <CardDescription>This is the final contract with all placeholders replaced</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <!-- Document Preview -->
                <div class="flex justify-center bg-gray-100 dark:bg-gray-900 rounded-lg">
                  <div
                    id="contract-document"
                    class="bg-white w-full max-h-[800px] overflow-y-auto print:max-h-none"
                    :style="{
                      transform: `scale(${zoom})`,
                      transformOrigin: 'top center'
                    }"
                  >
                    <!-- Contract Content -->
                    <div class="p-8 print:p-12">
                      <div class="prose prose-sm max-w-none text-gray-800 leading-relaxed print:prose-base contract-content">
                        <div v-if="processedContent" v-html="processedContent"></div>
                        <div v-else class="text-gray-500 italic text-center py-20">
                          <FileText class="mx-auto h-16 w-16 mb-4" />
                          <p>No contract content available</p>
                          <Button class="mt-4" @click="editDocument">
                            <Edit class="w-4 h-4 mr-2" />
                            Add Content
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Contract content styling */
.contract-content :deep(table) {
  border-collapse: collapse;
  margin: 1rem 0;
  overflow: hidden;
  table-layout: fixed;
  width: 100%;
  border: 2px solid #ced4da;
}

.contract-content :deep(table td),
.contract-content :deep(table th) {
  border: 2px solid #ced4da;
  box-sizing: border-box;
  min-width: 1em;
  padding: 8px 12px;
  position: relative;
  vertical-align: top;
}

.contract-content :deep(table th) {
  background-color: #f8f9fa;
  font-weight: bold;
  text-align: left;
}

.contract-content :deep(hr) {
  border: none;
  border-top: 2px solid #e5e7eb;
  margin: 2rem 0;
}

.contract-content :deep(ul) {
  list-style-type: disc;
  margin-left: 1.5rem;
  margin: 1rem 0;
}

.contract-content :deep(ol) {
  list-style-type: decimal;
  margin-left: 1.5rem;
  margin: 1rem 0;
}

.contract-content :deep(li) {
  margin: 0.25rem 0;
  padding-left: 0.25rem;
}

.contract-content :deep(blockquote) {
  border-left: 4px solid #d1d5db;
  padding-left: 1rem;
  margin: 1rem 0;
  font-style: italic;
  color: #6b7280;
}

.contract-content :deep(h1) {
  font-size: 2rem;
  font-weight: bold;
  margin: 1.5rem 0 1rem 0;
  color: #111827;
}

.contract-content :deep(h2) {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 1.25rem 0 0.75rem 0;
  color: #111827;
}

.contract-content :deep(h3) {
  font-size: 1.25rem;
  font-weight: bold;
  margin: 1rem 0 0.5rem 0;
  color: #111827;
}

.contract-content :deep(h4) {
  font-size: 1.125rem;
  font-weight: bold;
  margin: 1rem 0 0.5rem 0;
  color: #111827;
}

.contract-content :deep(p) {
  margin: 0.75rem 0;
  line-height: 1.625;
}

.contract-content :deep(strong) {
  font-weight: bold;
}

.contract-content :deep(em) {
  font-style: italic;
}

.contract-content :deep(u) {
  text-decoration: underline;
}

.contract-content :deep(mark) {
  background-color: #fef08a;
  padding: 0.125rem;
}

/* Print Styles */
@media print {
  body * {
    visibility: hidden;
  }

  #contract-document,
  #contract-document * {
    visibility: visible;
  }

  #contract-document {
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

  @page {
    size: A4;
    margin: 0.5in;
  }

  .contract-content table {
    page-break-inside: avoid;
  }

  .contract-content th, .contract-content td {
    page-break-inside: avoid;
  }
}
</style>
