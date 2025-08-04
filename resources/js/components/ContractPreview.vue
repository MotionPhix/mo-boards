<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@/components/ui/dropdown-menu'
import {
  Printer,
  Download,
  FileText,
  Calendar,
  MapPin,
  User,
  MoreHorizontal,
  Edit
} from 'lucide-vue-next'

interface Props {
  contract: any // Contract data
  templateContent: string // HTML template content
  readonly?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  readonly: false
})

const emit = defineEmits<{
  'print': []
  'download': []
  'edit': []
}>()

// Process template content with contract data
const processedContent = computed(() => {
  if (!props.templateContent || !props.contract) {
    return props.templateContent || ''
  }

  let content = props.templateContent

  // Replace template variables with actual contract data
  const replacements = {
    '{{CLIENT_NAME}}': props.contract.client_name || '[Client Name]',
    '{{CLIENT_COMPANY}}': props.contract.client_company || '[Client Company]',
    '{{CLIENT_ADDRESS}}': props.contract.client_address || '[Client Address]',
    '{{CLIENT_EMAIL}}': props.contract.client_email || '[Client Email]',
    '{{CLIENT_PHONE}}': props.contract.client_phone || '[Client Phone]',
    '{{CONTRACT_NUMBER}}': props.contract.contract_number || '[Contract Number]',
    '{{CONTRACT_DATE}}': formatDate(props.contract.created_at) || '[Contract Date]',
    '{{START_DATE}}': formatDate(props.contract.start_date) || '[Start Date]',
    '{{END_DATE}}': formatDate(props.contract.end_date) || '[End Date]',
    '{{MONTHLY_AMOUNT}}': props.contract.monthly_amount || '[Monthly Amount]',
    '{{TOTAL_AMOUNT}}': props.contract.total_amount || '[Total Amount]',
    '{{CURRENCY}}': props.contract.currency || '[Currency]',
    '{{PAYMENT_TERMS}}': props.contract.payment_terms || '[Payment Terms]',
    '{{COMPANY_NAME}}': props.contract.company?.name || '[Company Name]',
    '{{COMPANY_ADDRESS}}': props.contract.company?.address || '[Company Address]',
    '{{COMPANY_EMAIL}}': props.contract.company?.email || '[Company Email]',
    '{{COMPANY_PHONE}}': props.contract.company?.phone || '[Company Phone]',
    '{{COMPANY_WEBSITE}}': props.contract.company?.website || '[Company Website]',
    '{{COMPANY_SIGNATORY}}': props.contract.company?.signatory || '[Company Signatory]',
    '{{COMPANY_LOGO}}': getCompanyLogo(),
  }

  // Replace all variables
  Object.entries(replacements).forEach(([variable, value]) => {
    content = content.replace(new RegExp(variable, 'g'), value)
  })

  return content
})

const getCompanyLogo = (): string => {
  // If company has a logo, return img tag with actual logo URL
  if (props.contract.company?.logo_url) {
    return `<img src="${props.contract.company.logo_url}" alt="${props.contract.company.name} Logo" style="max-width: 200px; height: auto;" />`;
  }

  // If no logo, return placeholder or empty string
  return `<div style="width: 200px; height: 100px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">[Company Logo]</div>`;
};

const formatDate = (dateString: string | undefined): string => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const print = () => {
  const printWindow = window.open('', '_blank')
  if (printWindow) {
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Contract - ${props.contract.contract_number}</title>
        <style>
          @page {
            size: A4;
            margin: 2.5cm;
          }
          body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            max-width: none;
            margin: 0;
            padding: 0;
          }
          h1, h2, h3, h4, h5, h6 {
            margin-top: 24px;
            margin-bottom: 12px;
            color: #000;
            page-break-after: avoid;
          }
          h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
          }
          h2 { font-size: 16px; }
          h3 { font-size: 14px; }
          p {
            margin-bottom: 12px;
            text-align: justify;
          }
          ul, ol {
            margin: 12px 0;
            padding-left: 24px;
          }
          li {
            margin: 6px 0;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            page-break-inside: avoid;
          }
          table, th, td {
            border: 1px solid #000;
          }
          th, td {
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
          }
          th {
            background-color: #f5f5f5;
            font-weight: bold;
          }
          blockquote {
            margin: 20px 0;
            padding: 12px 20px;
            border-left: 4px solid #ccc;
            background-color: #f9f9f9;
            font-style: italic;
            page-break-inside: avoid;
          }
          .signature-section {
            margin-top: 60px;
            page-break-inside: avoid;
          }
          .signature-line {
            border-bottom: 1px solid #000;
            width: 300px;
            margin: 40px 0 8px 0;
            display: inline-block;
          }
          .page-break {
            page-break-before: always;
          }
          @media print {
            .no-print {
              display: none !important;
            }
          }
        </style>
      </head>
      <body>
        ${processedContent.value}

        <div class="signature-section">
          <h3>Signatures</h3>
          <table style="width: 100%; border: none;">
            <tr>
              <td style="width: 50%; border: none; vertical-align: top;">
                <p><strong>Client:</strong></p>
                <div class="signature-line"></div>
                <p style="margin-top: 8px;">
                  ${props.contract.client?.name || '[Client Name]'}<br>
                  Date: _______________
                </p>
              </td>
              <td style="width: 50%; border: none; vertical-align: top;">
                <p><strong>Company:</strong></p>
                <div class="signature-line"></div>
                <p style="margin-top: 8px;">
                  ${props.contract.company?.name || '[Company Name]'}<br>
                  Date: _______________
                </p>
              </td>
            </tr>
          </table>
        </div>
      </body>
      </html>
    `)
    printWindow.document.close()
    printWindow.print()
  }
  emit('print')
}

const downloadPdf = () => {
  // This would integrate with a PDF generation service
  // For now, we'll trigger the print dialog
  print()
  emit('download')
}
</script>

<template>
  <div class="contract-preview">
    <!-- Header Actions -->
    <div class="flex items-center justify-between mb-6 no-print">
      <div class="flex items-center space-x-3">
        <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center">
          <FileText class="h-4 w-4 text-blue-600" />
        </div>
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Contract Preview</h2>
          <p class="text-sm text-gray-600">{{ contract.contract_number }}</p>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <Badge variant="secondary" class="text-xs">
          Preview Mode
        </Badge>

        <!-- Primary action for editing -->
        <Button
          v-if="!readonly"
          variant="default"
          size="sm"
          @click="$emit('edit')"
        >
          <Edit class="h-4 w-4 mr-2" />
          Edit Document
        </Button>

        <!-- Actions dropdown for other operations -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm">
              <MoreHorizontal class="h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem @click="print">
              <Printer class="mr-2 h-4 w-4" />
              Print Document
            </DropdownMenuItem>
            <DropdownMenuItem @click="downloadPdf">
              <Download class="mr-2 h-4 w-4" />
              Download PDF
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>

    <!-- Contract Summary Card -->
    <Card class="mb-6 no-print">
      <CardHeader>
        <CardTitle class="text-sm">Contract Summary</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div class="flex items-center space-x-2">
            <User class="h-4 w-4 text-gray-400" />
            <div>
              <div class="font-medium">{{ contract.client?.name }}</div>
              <div class="text-gray-500">{{ contract.client?.company }}</div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <Calendar class="h-4 w-4 text-gray-400" />
            <div>
              <div class="font-medium">{{ formatDate(contract.dates?.start_date) }} - {{ formatDate(contract.dates?.end_date) }}</div>
              <div class="text-gray-500">{{ Math.round(contract.dates?.duration_months || 0) }} months</div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <MapPin class="h-4 w-4 text-gray-400" />
            <div>
              <div class="font-medium">{{ contract.financial?.formatted_total }}</div>
              <div class="text-gray-500">{{ contract.billboards?.length || 0 }} locations</div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Separator class="mb-6 no-print" />

    <!-- Document Content -->
    <div class="contract-document bg-white border border-gray-200 rounded-lg">
      <div class="p-8 md:p-12">
        <div
          class="prose prose-sm max-w-none contract-content"
          v-html="processedContent"
        />
      </div>
    </div>
  </div>
</template>

<style>
/* Contract Document Styles */
.contract-content {
  font-family: 'Times New Roman', serif;
  font-size: 14px;
  line-height: 1.6;
  color: #000;
}

.contract-content h1 {
  font-size: 20px;
  text-align: center;
  margin-bottom: 2em;
  border-bottom: 2px solid #000;
  padding-bottom: 0.5em;
  font-weight: bold;
}

.contract-content h2 {
  font-size: 18px;
  margin-top: 2em;
  margin-bottom: 1em;
  font-weight: bold;
}

.contract-content h3 {
  font-size: 16px;
  margin-top: 1.5em;
  margin-bottom: 0.75em;
  font-weight: bold;
}

.contract-content p {
  margin-bottom: 1em;
  text-align: justify;
}

.contract-content ul, .contract-content ol {
  margin: 1em 0;
  padding-left: 2em;
}

.contract-content li {
  margin: 0.5em 0;
}

.contract-content table {
  width: 100%;
  border-collapse: collapse;
  margin: 2em 0;
}

.contract-content table th,
.contract-content table td {
  border: 1px solid #000;
  padding: 0.75em;
  text-align: left;
  vertical-align: top;
}

.contract-content table th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.contract-content blockquote {
  margin: 1.5em 0;
  padding: 1em 1.5em;
  border-left: 4px solid #ccc;
  background-color: #f9f9f9;
  font-style: italic;
}

/* Print-specific styles */
@media print {
  .no-print {
    display: none !important;
  }

  .contract-document {
    border: none !important;
    box-shadow: none !important;
    margin: 0 !important;
  }

  .contract-content {
    font-size: 12px !important;
  }
}

/* Page break utilities */
.page-break {
  page-break-before: always;
}

.avoid-break {
  page-break-inside: avoid;
}
</style>
