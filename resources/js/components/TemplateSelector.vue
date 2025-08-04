<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import {
  FileText,
  Eye,
  Edit,
  Copy,
  Trash2,
  Star,
  Building,
  Calendar
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'

interface ContractTemplate {
  id: number
  name: string
  description: string
  content: string
  is_active: boolean
  created_at: string
  updated_at: string
  contracts_count?: number
  is_system?: boolean
}

interface Props {
  templates: ContractTemplate[]
  selectedTemplateId?: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'select': [template: ContractTemplate]
  'preview': [template: ContractTemplate]
  'edit': [template: ContractTemplate]
  'duplicate': [template: ContractTemplate]
  'delete': [template: ContractTemplate]
}>()

const searchQuery = ref('')

// Filter templates based on search
const filteredTemplates = computed(() => {
  if (!searchQuery.value) return props.templates

  const query = searchQuery.value.toLowerCase()
  return props.templates.filter(template =>
    template.name.toLowerCase().includes(query) ||
    template.description?.toLowerCase().includes(query)
  )
})

// System templates (can be pre-built templates)
const systemTemplates = computed(() =>
  filteredTemplates.value.filter(t => t.is_system)
)

// User/Company templates
const userTemplates = computed(() =>
  filteredTemplates.value.filter(t => !t.is_system)
)

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const selectTemplate = (template: ContractTemplate) => {
  emit('select', template)
}

const previewTemplate = (template: ContractTemplate) => {
  emit('preview', template)
}

const duplicateTemplate = async (template: ContractTemplate) => {
  try {
    await router.post(route('contract-templates.duplicate', template.id), {}, {
      preserveState: true,
      onSuccess: () => {
        toast.success('Template duplicated successfully!', {
          description: `"${template.name} (Copy)" has been added to your templates.`
        })

        // Reload to refresh the templates list
        router.reload({ only: ['templates'] })
      },
      onError: (errors) => {
        toast.error('Failed to duplicate template', {
          description: Object.values(errors).flat().join(', ')
        })
      }
    })
  } catch (error) {
    console.error('Failed to duplicate template:', error)
    toast.error('Failed to duplicate template')
  }
}

const deleteTemplate = async (template: ContractTemplate) => {
  if (!confirm(`Are you sure you want to delete "${template.name}"? This action cannot be undone.`)) {
    return
  }

  try {
    await router.delete(route('contract-templates.destroy', template.id), {
      preserveState: true,
      onSuccess: () => {
        toast.success('Template deleted successfully!', {
          description: `"${template.name}" has been removed from your templates.`
        })

        // Reload to refresh the templates list
        router.reload({ only: ['templates'] })
      },
      onError: (errors) => {
        toast.error('Failed to delete template', {
          description: Object.values(errors).flat().join(', ')
        })
      }
    })
  } catch (error) {
    console.error('Failed to delete template:', error)
    toast.error('Failed to delete template')
  }
}

const getDefaultTemplateContent = (): string => {
  return `
    <div style="text-align: center; margin-bottom: 2rem;">
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
    <p>By signing below, both parties agree to the terms and conditions outlined in this contract.</p>
  `
}

// Pre-built system templates
const getSystemTemplates = (): ContractTemplate[] => {
  return [
    {
      id: -1,
      name: 'Standard Billboard Contract',
      description: 'A comprehensive template for standard billboard advertising agreements',
      content: getDefaultTemplateContent(),
      is_active: true,
      is_system: true,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
      contracts_count: 0
    },
    {
      id: -2,
      name: 'Short-term Campaign Agreement',
      description: 'Template for short-term advertising campaigns (less than 6 months)',
      content: `
        <div style="text-align: center; margin-bottom: 2rem;">
        {{company_logo_small}}
        </div>

        <h1>SHORT-TERM ADVERTISING CAMPAIGN AGREEMENT</h1>

        <p>Campaign Agreement between {{company_name}} and {{client_name}} for a short-term advertising campaign.</p>

        <h2>CAMPAIGN DETAILS</h2>
        <p><strong>Campaign Duration:</strong> {{start_date}} to {{end_date}}</p>
        <p><strong>Billboard Locations:</strong></p>
        <p>{{billboard_locations}}</p>

        <h2>INVESTMENT</h2>
        <p><strong>Monthly Rate:</strong> {{monthly_amount}}</p>
        <p><strong>Total Campaign Cost:</strong> {{total_amount}}</p>
        <p><strong>Payment:</strong> {{payment_terms}}</p>

        <h2>CAMPAIGN REQUIREMENTS</h2>
        <ul>
          <li>Client to provide high-resolution artwork</li>
          <li>Artwork approval required before installation</li>
          <li>Installation within 5 business days of approval</li>
        </ul>
      `,
      is_active: true,
      is_system: true,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
      contracts_count: 0
    },
    {
      id: -3,
      name: 'Long-term Partnership Agreement',
      description: 'Template for long-term partnerships with multiple billboard locations',
      content: `
        <div style="text-align: center; margin-bottom: 2rem;">
        {{company_logo}}
        </div>

        <h1>LONG-TERM BILLBOARD PARTNERSHIP AGREEMENT</h1>

        <p>This long-term partnership agreement establishes the terms for an extended advertising relationship between {{company_name}} and {{client_name}}.</p>

        <h2>PARTNERSHIP TERMS</h2>
        <p><strong>Partnership Period:</strong> {{start_date}} to {{end_date}}</p>
        <p><strong>Monthly Investment:</strong> {{monthly_amount}}</p>
        <p><strong>Total Partnership Value:</strong> {{total_amount}}</p>

        <h2>BILLBOARD PORTFOLIO</h2>
        <p>This agreement covers the following premium locations:</p>
        <p>{{billboard_locations}}</p>

        <h2>PARTNERSHIP BENEFITS</h2>
        <ul>
          <li>Priority booking for new locations</li>
          <li>Quarterly campaign performance reviews</li>
          <li>Flexible creative change-outs</li>
          <li>Dedicated account management</li>
        </ul>

        <h2>RENEWAL OPTIONS</h2>
        <p>This agreement may be renewed for additional terms upon mutual agreement of both parties.</p>
      `,
      is_active: true,
      is_system: true,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
      contracts_count: 0
    }
  ]
}

// Combine system templates with user templates (for future use)
// const allTemplates = computed(() => {
//   const system = getSystemTemplates()
//   const user = props.templates || []
//   return [...system, ...user]
// })
</script>

<template>
  <div class="template-selector">
    <!-- Search -->
    <div class="mb-6">
      <Input
        v-model="searchQuery"
        placeholder="Search templates..."
        class="w-full is-large"
      />
    </div>

    <!-- System Templates -->
    <div v-if="systemTemplates.length > 0" class="mb-8">
      <div class="flex items-center space-x-2 mb-4">
        <Star class="h-5 w-5 text-yellow-500" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recommended Templates</h3>
        <Badge variant="secondary">System</Badge>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card
          v-for="template in getSystemTemplates()"
          :key="`system-${template.id}`"
          class="cursor-pointer hover:shadow-md transition-all duration-200 flex flex-col h-full"
          :class="{
            'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-950/30': selectedTemplateId === template.id,
            'hover:border-blue-300 dark:hover:border-blue-600': selectedTemplateId !== template.id
          }"
          @click="selectTemplate(template)"
        >
          <CardHeader class="pb-3 flex-shrink-0">
            <div class="flex items-start justify-between">
              <div class="flex items-start space-x-3 flex-1">
                <!-- Radio indicator -->
                <div class="flex-shrink-0 mt-1">
                  <div
                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors"
                    :class="selectedTemplateId === template.id
                      ? 'border-blue-500 bg-blue-500'
                      : 'border-gray-300 dark:border-gray-600'"
                  >
                    <div
                      v-if="selectedTemplateId === template.id"
                      class="w-2 h-2 rounded-full bg-white"
                    ></div>
                  </div>
                </div>

                <div class="flex-1 min-w-0">
                  <CardTitle class="text-base flex items-center">
                    <FileText class="h-4 w-4 mr-2 text-blue-600 flex-shrink-0" />
                    <span class="truncate">{{ template.name }}</span>
                  </CardTitle>
                  <CardDescription class="text-sm mt-1 line-clamp-2">
                    {{ template.description }}
                  </CardDescription>
                </div>
              </div>

              <Badge v-if="template.is_system" variant="outline" class="ml-2 flex-shrink-0">
                System
              </Badge>
            </div>
          </CardHeader>

          <CardContent class="pt-0 flex-1 flex flex-col justify-between">
            <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
              <Building class="h-3 w-3" />
              <span>Standard Template</span>
            </div>

            <!-- Actions always at bottom -->
            <div class="flex items-center justify-end space-x-1 mt-auto">
              <Button
                variant="ghost"
                size="sm"
                @click.stop="previewTemplate(template)"
                title="Preview"
                class="hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <Eye class="h-4 w-4" />
              </Button>

              <Button
                variant="ghost"
                size="sm"
                @click.stop="duplicateTemplate(template)"
                title="Duplicate Template"
                class="hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <Copy class="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- User Templates -->
    <div v-if="userTemplates.length > 0">
      <div class="flex items-center space-x-2 mb-4">
        <Building class="h-5 w-5 text-blue-600" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Templates</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card
          v-for="template in userTemplates"
          :key="template.id"
          class="cursor-pointer hover:shadow-md transition-all duration-200 flex flex-col h-full"
          :class="{
            'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-950/30': selectedTemplateId === template.id,
            'hover:border-blue-300 dark:hover:border-blue-600': selectedTemplateId !== template.id
          }"
          @click="selectTemplate(template)"
        >
          <CardHeader class="pb-3 flex-shrink-0">
            <div class="flex items-start space-x-3">
              <div class="flex-1 min-w-0">
                <CardTitle class="text-base flex items-center">
                  <FileText class="h-4 w-4 mr-2 text-blue-600 flex-shrink-0" />
                  <span class="truncate">{{ template.name }}</span>
                </CardTitle>
                <CardDescription class="text-sm mt-1 line-clamp-2">
                  {{ template.description }}
                </CardDescription>
              </div>
            </div>
          </CardHeader>

          <CardContent class="pt-0 flex-1 flex flex-col justify-between">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
              <div class="flex items-center space-x-2">
                <Calendar class="h-3 w-3" />
                <span>{{ formatDate(template.updated_at) }}</span>
              </div>
              <div v-if="template.contracts_count" class="text-xs text-gray-500 dark:text-gray-400">
                {{ template.contracts_count }} contracts
              </div>
            </div>

            <div class="flex-1"></div>

            <Separator class="my-2" />

            <!-- Actions always at bottom -->
            <div class="flex items-center space-x-1 mt-auto">
              <Button
                variant="ghost"
                size="sm"
                @click.stop="previewTemplate(template)"
                title="Preview"
                class="hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <Eye class="h-4 w-4" />
              </Button>

              <ModalLink :href="route('contract-templates.edit', template.id)">
                <Button
                  variant="ghost"
                  size="sm"
                  title="Edit"
                  class="hover:bg-gray-100 dark:hover:bg-gray-800"
                  @click.stop
                >
                  <Edit class="h-4 w-4" />
                </Button>
              </ModalLink>

              <Button
                variant="ghost"
                size="sm"
                @click.stop="duplicateTemplate(template)"
                title="Duplicate"
                class="hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <Copy class="h-4 w-4" />
              </Button>

              <Button
                variant="ghost"
                size="sm"
                @click.stop="deleteTemplate(template)"
                title="Delete"
                class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredTemplates.length === 0" class="text-center py-12">
      <FileText class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No templates found</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ searchQuery ? 'Try adjusting your search terms.' : 'Get started by creating your first template using the "Create New Template" button above.' }}
      </p>
    </div>
  </div>
</template>


