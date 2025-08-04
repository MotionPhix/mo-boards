<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import {
  FileText,
  ArrowLeft,
  Eye,
  Pencil as Edit,
  Save,
  Settings,
  Loader2
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import DocumentEditor from '@/components/DocumentEditor.vue'
import ContractPreview from '@/components/ContractPreview.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader } from '@/components/ui/card'
import type { Contract } from '@/types'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { useStorage } from '@vueuse/core'

interface Props {
  contract: {
    data: Contract
  }
  templates?: any[]
  contractContent?: string
}

const props = defineProps<Props>()

const activeTab = useStorage('document_active_tab', 'preview')
const isEditing = ref(false)
const isSaving = ref(false)

// Contract document content
const documentContent = ref(props.contractContent || getDefaultContent())

// Computed contract data for template processing
const contractData = computed(() => props.contract.data)

function getDefaultContent(): string {
  return `
    <div style="text-align: center; margin-bottom: 40px;">
      <div style="margin-bottom: 30px;">
        <!-- Company Logo Area -->
        <div style="width: 150px; height: 100px; margin: 0 auto; border: 2px solid #4a5568; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #f7fafc;">
          <strong style="color: #4a5568;">{{company_name}}</strong>
        </div>
      </div>

      <h1 style="font-size: 28px; font-weight: bold; margin: 30px 0; text-transform: uppercase; letter-spacing: 2px;">
        ADVERTISING<br>
        RENTAL AGREEMENT<br>
        BETWEEN
      </h1>

      <div style="margin: 40px 0;">
        <p style="font-size: 18px; margin: 20px 0;">{{company_name}}</p>
        <p style="font-size: 16px; font-weight: bold; margin: 20px 0;">AND</p>
        <p style="font-size: 18px; margin: 20px 0;">{{client_company}}</p>
      </div>

      <p style="margin-top: 60px; color: #666;">
        Contract Serial No: {{contract_number}}
      </p>
    </div>

    <div style="page-break-before: always; padding: 40px 0;">
      <h2 style="font-weight: bold; font-size: 16px; margin-bottom: 20px;">1. THE AGREEMENT</h2>

      <p style="margin-bottom: 20px; line-height: 1.6;">
        This agreement is made on <strong>{{today_date}}</strong> between <strong>{{company_name}}</strong>
        (hereinafter referred to as <strong>"the Company"</strong>) and <strong>{{client_company}}</strong>
        (hereinafter referred to as <strong>"the Client"</strong>) for the rental of
        advertising space as detailed below:
      </p>

      <table style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 1px solid #ddd;">
        <thead>
          <tr style="background-color: #f5f5f5;">
            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: bold;">Billboard Location</th>
            <th style="border: 1px solid #ddd; padding: 12px; text-align: right; font-weight: bold;">Monthly Rate</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="border: 1px solid #ddd; padding: 12px; vertical-align: top;">
              <strong>{{billboard_locations}}</strong><br>
              <small style="color: #666;">Location details and specifications</small>
            </td>
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right; vertical-align: top;">
              <strong>{{monthly_amount}}</strong>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr style="background-color: #f9f9f9;">
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right; font-weight: bold;">
              Total Amount:
            </td>
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right; font-weight: bold;">
              {{total_amount}}
            </td>
          </tr>
        </tfoot>
      </table>

      <p style="margin: 20px 0; line-height: 1.6;">
        <strong>**</strong> The rental period shall commence on <strong>{{start_date}}</strong>, and end on <strong>{{end_date}}</strong>.
        Thus the contract will be run for the agreed duration.
      </p>

      <h2 style="font-weight: bold; font-size: 16px; margin: 40px 0 20px 0;">2. MAINTENANCE</h2>

      <p style="margin-bottom: 20px; line-height: 1.6;">
        The Company shall be responsible for the maintenance and repair of any
        damaged billboards during the rental period. Any damage caused by
        natural wear and tear or weather conditions shall be repaired by the
        Company at no additional cost. However, any damage caused by the
        Client's negligence or misuse shall be repaired at the Client's
        expense.
      </p>

      <h2 style="font-weight: bold; font-size: 16px; margin: 40px 0 20px 0;">3. DISPUTES</h2>

      <p style="margin-bottom: 20px; line-height: 1.6;">
        Any disputes arising from this agreement shall be resolved through
        amicable negotiation between both parties. If no resolution can be
        reached, the matter shall be referred to arbitration in accordance
        with the applicable laws.
      </p>
    </div>

    <div style="page-break-before: always; padding: 40px 0;">
      <h2 style="font-weight: bold; font-size: 16px; margin-bottom: 20px;">4. TERMINATION</h2>

      <p style="margin-bottom: 20px; line-height: 1.6;">
        Either party may terminate this agreement with a 30-day written
        notice. In case of early termination by the Client, a cancellation fee
        equivalent to one month's rental shall be payable. The Company
        reserves the right to terminate this agreement immediately in case of
        breach of contract terms by the Client.
      </p>

      <h2 style="font-weight: bold; font-size: 16px; margin: 40px 0 20px 0;">5. TERMS AND CONDITIONS</h2>

      <p style="margin-bottom: 20px; line-height: 1.6;">
        Payment terms: {{payment_terms}}. The Client agrees to make payments
        according to the agreed schedule. Late payments may incur additional
        charges as specified in the payment terms. The Company reserves the
        right to suspend advertising services for overdue accounts.
      </p>

      <div style="margin: 40px 0;">
        <h3 style="font-weight: bold; font-size: 14px; margin-bottom: 10px;">NOTES</h3>
        <p style="margin-bottom: 10px; line-height: 1.6; color: #666; font-size: 14px;">
          All advertising content must comply with local advertising standards and regulations.
          The Client is responsible for obtaining any necessary permits or approvals.
          Installation and maintenance schedules will be coordinated between both parties.
        </p>
      </div>
    </div>

    <div style="page-break-before: always; padding: 40px 0;">
      <div style="margin-bottom: 60px;">
        <h3 style="font-weight: bold; margin-bottom: 30px;">Signed on behalf of {{company_name}} ("The Company")</h3>

        <div style="margin-bottom: 20px;">
          <span style="display: inline-block; width: 80px;">Name</span>
          <span style="display: inline-block; width: 20px;">:</span>
          <span style="border-bottom: 1px solid #000; display: inline-block; width: 300px; height: 20px;"></span>
        </div>

        <div style="margin-bottom: 20px;">
          <span style="display: inline-block; width: 80px;">Title</span>
          <span style="display: inline-block; width: 20px;">:</span>
          <span style="border-bottom: 1px solid #000; display: inline-block; width: 300px; height: 20px;"></span>
        </div>

        <div style="margin-bottom: 30px;">
          <span style="display: inline-block; width: 80px;">Date</span>
          <span style="display: inline-block; width: 20px;">:</span>
          <span style="border-bottom: 1px solid #000; display: inline-block; width: 300px; height: 20px;"></span>
        </div>

        <div style="color: #666; font-size: 14px; line-height: 1.4;">
          {{company_address}}<br><br>
          <div style="margin-top: 10px;">
            <p>P: {{company_phone}}</p>
            <p>E: {{company_email}}</p>
          </div>
        </div>
      </div>

      <hr style="border: none; border-top: 1px solid #000; margin: 40px 0;">

      <div style="margin-top: 60px;">
        <h3 style="font-weight: bold; margin-bottom: 30px;">Signed on behalf of {{client_company}} ("The Client")</h3>

        <div style="margin-bottom: 20px;">
          <span style="display: inline-block; width: 80px;">Name</span>
          <span style="display: inline-block; width: 20px;">:</span>
          <span style="border-bottom: 1px solid #000; display: inline-block; width: 300px; height: 20px;"></span>
        </div>

        <div style="margin-bottom: 20px;">
          <span style="display: inline-block; width: 80px;">Title</span>
          <span style="display: inline-block; width: 20px;">:</span>
          <span style="border-bottom: 1px solid #000; display: inline-block; width: 300px; height: 20px;"></span>
        </div>

        <div style="margin-bottom: 30px;">
          <span style="display: inline-block; width: 80px;">Date</span>
          <span style="display: inline-block; width: 20px;">:</span>
          <span style="border-bottom: 1px solid #000; display: inline-block; width: 300px; height: 20px;"></span>
        </div>

        <div style="color: #666; font-size: 14px; line-height: 1.4;">
          {{client_address}}<br><br>
          <div style="margin-top: 10px;">
            <p>P: {{client_phone}}</p>
            <p>E: {{client_email}}</p>
          </div>
        </div>
      </div>

      <div style="text-align: center; margin-top: 60px; padding-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px;">
        <p>Completely administrate standardized deliverables via flexible networks.</p>
        <p>Generated on {{today_date}} by Admin User</p>
      </div>
    </div>
  `
}

// Handle content updates from editor
function onContentUpdate(newContent: string) {
  documentContent.value = newContent
}

// Handle template applied from modal
function onTemplateApplied(template: any) {
  // Update the document content with the new template
  documentContent.value = template.content || getDefaultContent()
  activeTab.value = 'editor'
  isEditing.value = true

  // Optionally reload the page to get fresh data
  router.reload({
    only: ['contractContent']
  })
}

// Save contract document
async function saveDocument(content?: string) {
  isSaving.value = true

  try {
    const contentToSave = content || documentContent.value

    await router.patch(route('contracts.update-document', props.contract.data.id), {
      content: contentToSave
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        documentContent.value = contentToSave
        isEditing.value = false
        activeTab.value = 'preview'

        toast.success('Document saved successfully!', {
          description: 'Your contract document has been updated.'
        })
      },
      onError: (errors) => {
        toast.error('Failed to save document', {
          description: Object.values(errors).flat().join(', ')
        })
      }
    })
  } catch (error) {
    console.error('Failed to save document:', error)
  } finally {
    isSaving.value = false
  }
}

// Navigation functions
function goBack() {
  router.visit(route('contracts.show', props.contract.data.id))
}

// Save current document as template
async function saveAsTemplate() {
  const templateName = prompt('Enter a name for this template:')
  if (!templateName) return

  try {
    await router.post(route('contract-templates.store'), {
      name: templateName,
      description: `Template created from contract ${props.contract.data.contract_number}`,
      content: documentContent.value,
      is_active: true
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Template saved successfully!', {
          description: `"${templateName}" has been added to your template library.`
        })

        // Reload the current page to refresh templates list
        router.reload({
          only: ['templates']
        })
      },
      onError: (errors) => {
        toast.error('Failed to save template', {
          description: Object.values(errors).flat().join(', ')
        })
      }
    })
  } catch (error) {
    console.error('Failed to save template:', error)
    toast.error('Failed to save template', {
      description: 'An unexpected error occurred. Please try again.'
    })
  }
}
</script>

<template>
  <AppLayout
    :title="`Contract Document - ${contract.data.contract_number}`"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Contracts', href: route('contracts.index') },
      { label: contract.data.contract_number, href: route('contracts.show', contract.data.id) },
      { label: 'Document' }
    ]"
  >
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-start sm:space-y-0">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" @click="goBack" class="p-2">
            <ArrowLeft class="h-4 w-4" />
          </Button>

          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <FileText class="h-5 w-5 text-blue-600" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                Contract Document
              </h1>

              <p class="text-sm text-gray-600">
                {{ contract.data.contract_number }}
              </p>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <!-- Save Button (primary action) -->
          <Button
            v-if="isEditing"
            variant="default"
            @click="saveDocument"
            :disabled="isSaving"
          >
            <Loader2 v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
            <Save v-else class="h-4 w-4 mr-2" />
            {{ isSaving ? 'Saving...' : 'Save Document' }}
          </Button>

          <!-- Actions Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="sm">
                <Settings class="h-4 w-4 mr-2" />
                Actions
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem as-child>
                <ModalLink
                  :href="route('contracts.template-selector', contract.data.id)"
                  class="flex items-center w-full"
                  @template-applied="onTemplateApplied"
                >
                  <Settings class="mr-2 h-4 w-4" />
                  Change Template
                </ModalLink>
              </DropdownMenuItem>
              <DropdownMenuItem @click="saveAsTemplate">
                <FileText class="mr-2 h-4 w-4" />
                Save as Template
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem as-child>
                <ModalLink :href="route('contracts.edit', contract.data.id)" class="flex items-center w-full">
                  <Edit class="mr-2 h-4 w-4" />
                  Edit Contract Details
                </ModalLink>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Main Content -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-center">
            <div class="flex items-center space-x-2">
              <!-- Mode Toggle -->
              <div class="inline-flex rounded-lg border border-gray-200 p-1">
                <Button
                  :variant="activeTab === 'preview' ? 'default' : 'ghost'"
                  size="sm"
                  @click="activeTab = 'preview'"
                  class="rounded-md"
                >
                  <Eye class="h-4 w-4 mr-2" />
                  Preview
                </Button>
                <Button
                  :variant="activeTab === 'editor' ? 'default' : 'ghost'"
                  size="sm"
                  @click="activeTab = 'editor'; isEditing = true"
                  class="rounded-md"
                >
                  <Edit class="h-4 w-4 mr-2" />
                  Edit
                </Button>
              </div>
            </div>
          </div>
        </CardHeader>

        <CardContent>
          <!-- Content -->
          <div v-if="activeTab === 'preview'">
            <ContractPreview
              :contract="contractData"
              :template-content="documentContent"
              @edit="activeTab = 'editor'; isEditing = true"
              @print="() => console.log('Print document')"
              @download="() => console.log('Download PDF')"
            />
          </div>

          <div v-if="activeTab === 'editor'">
            <DocumentEditor
              v-model="documentContent"
              :contract-data="contractData"
              :readonly="false"
              placeholder="Start writing your contract document..."
              @update:model-value="onContentUpdate"
              @save="saveDocument"
            />
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
