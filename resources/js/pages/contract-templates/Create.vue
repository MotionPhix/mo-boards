<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Plus, FileText, Save, Eye, AlertCircle, SaveIcon, XIcon } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import DocumentEditor from '@/components/DocumentEditor.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { Alert, AlertDescription } from '@/components/ui/alert'

interface Props {
  categories: string[]
  placeholders?: Record<string, Record<string, string>>
  errors?: Record<string, string>
}

const props = defineProps<Props>()
// Type definitions
interface Role {
  id: number
  name: string
}

interface User {
  id: number
  name: string
  email: string
  roles?: Role[]
}

interface AuthProps {
  user?: User
}

interface PageProps {
  auth?: AuthProps
  [key: string]: any
}

const page = usePage<PageProps>()

// Check if user is super admin
const isSuperAdmin = computed(() => {
  return page.props.auth?.user?.roles?.some((role: Role) => role.name === 'super_admin')
})

// Category management
const allCategories = ref([...props.categories])
const showNewCategoryInput = ref(false)
const newCategoryName = ref('')

// Textarea autosize
const descriptionRef = ref<HTMLTextAreaElement | null>(null)

const form = reactive({
  name: '',
  description: '',
  category: '',
  content: getStarterContent(),
  price: '',
  is_active: true,
  is_premium: false,
  tags: '',
  processing: false
})

const showPreview = ref(false)

// Auto-resize textarea
const autoResizeTextarea = () => {
  if (descriptionRef.value) {
    descriptionRef.value.style.height = 'auto'
    descriptionRef.value.style.height = descriptionRef.value.scrollHeight + 'px'
  }
}

// Add new category
const addNewCategory = () => {
  if (newCategoryName.value.trim()) {
    const categoryName = newCategoryName.value.trim()
    if (!allCategories.value.includes(categoryName)) {
      allCategories.value.push(categoryName)
      form.category = categoryName // Set as selected
    }
    newCategoryName.value = ''
    showNewCategoryInput.value = false
  }
}

const cancelNewCategory = () => {
  newCategoryName.value = ''
  showNewCategoryInput.value = false
}

function getStarterContent(): string {
  const isPremiumTemplate = isSuperAdmin.value && form.is_premium;

  if (isPremiumTemplate) {
    return `<h1 style="text-align: center; color: #6366f1;">{{template_name}} - Advanced Contract Template</h1>

<div style="border: 2px solid #6366f1; padding: 20px; margin: 20px 0; border-radius: 8px; background-color: #f8fafc;">
  <h3>Advanced Template Features</h3>
  <ul>
    <li>Advanced contract clauses and terms</li>
    <li>Professional legal language</li>
    <li>Comprehensive placeholder support</li>
    <li>Industry-specific customizations</li>
  </ul>
</div>

<h2>Contract Agreement</h2>
<p>This Premium Contract Agreement ("Agreement") is entered into on {{start_date}} between:</p>

<p><strong>Company:</strong> {{company_name}}<br>
<strong>Address:</strong> {{company_address}}<br>
<strong>Contact:</strong> {{company_contact}}</p>

<p><strong>Client:</strong> {{client_name}}<br>
<strong>Address:</strong> {{client_address}}<br>
<strong>Contact:</strong> {{client_contact}}</p>

<h3>Terms and Conditions</h3>
<p>This agreement shall commence on {{start_date}} and continue until {{end_date}}, for a total duration of {{contract_duration}}.</p>

<p><strong>Total Contract Value:</strong> {{total_amount}}<br>
<strong>Payment Terms:</strong> {{payment_terms}}<br>
<strong>Payment Schedule:</strong> {{payment_schedule}}</p>

<h3>Scope of Work</h3>
<p>{{scope_of_work}}</p>

<h3>Deliverables</h3>
<p>{{deliverables}}</p>

<h3>Premium Clauses</h3>
<p><strong>Intellectual Property:</strong> {{ip_terms}}</p>
<p><strong>Confidentiality:</strong> {{confidentiality_terms}}</p>
<p><strong>Liability Limitations:</strong> {{liability_terms}}</p>
<p><strong>Dispute Resolution:</strong> {{dispute_resolution}}</p>

<h3>Signatures</h3>
<p><strong>Company Representative:</strong><br>
Name: {{company_rep_name}}<br>
Title: {{company_rep_title}}<br>
Signature: _________________ Date: _________</p>

<p><strong>Client Representative:</strong><br>
Name: {{client_rep_name}}<br>
Title: {{client_rep_title}}<br>
Signature: _________________ Date: _________</p>`;
  }

  return `<h1>{{contract_title}}</h1>

<p><strong>Contract Date:</strong> {{contract_date}}</p>
<p><strong>Contract Number:</strong> {{contract_number}}</p>

<h2>Parties</h2>

<p><strong>Company:</strong><br>
{{company_name}}<br>
{{company_address}}<br>
{{company_city}}, {{company_state}} {{company_zip}}<br>
Phone: {{company_phone}}<br>
Email: {{company_email}}</p>

<p><strong>Client:</strong><br>
{{client_name}}<br>
{{client_address}}<br>
{{client_city}}, {{client_state}} {{client_zip}}<br>
Phone: {{client_phone}}<br>
Email: {{client_email}}</p>

<h2>Contract Details</h2>

<p><strong>Service Description:</strong><br>
{{service_description}}</p>

<p><strong>Contract Period:</strong><br>
Start Date: {{start_date}}<br>
End Date: {{end_date}}<br>
Duration: {{contract_duration}}</p>

<p><strong>Financial Terms:</strong><br>
Total Amount: {{total_amount}}<br>
Payment Terms: {{payment_terms}}<br>
Due Date: {{due_date}}</p>

<h2>Terms and Conditions</h2>

<ol>
  <li><strong>Payment Terms:</strong> {{payment_terms_details}}</li>
  <li><strong>Cancellation Policy:</strong> {{cancellation_policy}}</li>
  <li><strong>Liability:</strong> {{liability_terms}}</li>
  <li><strong>Force Majeure:</strong> {{force_majeure}}</li>
</ol>

<h2>Signatures</h2>

<table style="width: 100%; margin-top: 2em;">
  <tr>
    <td style="width: 50%; padding: 1em; vertical-align: top;">
      <p><strong>Company Representative:</strong></p>
      <br><br>
      <p>_________________________<br>
      {{company_representative}}<br>
      {{company_representative_title}}<br>
      Date: {{signature_date}}</p>
    </td>
    <td style="width: 50%; padding: 1em; vertical-align: top;">
      <p><strong>Client:</strong></p>
      <br><br>
      <p>_________________________<br>
      {{client_name}}<br>
      {{client_title}}<br>
      Date: {{signature_date}}</p>
    </td>
  </tr>
</table>`;
}

const submitForm = () => {
  if (form.processing) return

  form.processing = true

  const formData = {
    name: form.name,
    description: form.description,
    content: form.content,
    category: form.category,
    is_active: form.is_active,
    is_premium: isSuperAdmin.value ? form.is_premium : false,
    price: form.price,
  }

  router.post(route('contract-templates.store'), formData, {
    onFinish: () => {
      form.processing = false
    },
    onSuccess: () => {
  router.visit(route('contract-templates.index'))
    },
  })
}

// Watch for system template toggle to update content template
watch(() => form.is_premium, () => {
  if (isSuperAdmin.value && !form.content.includes('<div style="border: 2px solid #6366f1;')) {
    // Only update if content hasn't been manually edited much
    const wordCount = form.content.replace(/<[^>]*>/g, '').split(/\s+/).length
    if (wordCount < 50) { // Arbitrary threshold for "minimal editing"
      form.content = getStarterContent()
    }
  }
})

const togglePreview = () => {
  showPreview.value = !showPreview.value
}

const getTagsArray = () => {
  return form.tags ? form.tags.split(',').map(tag => tag.trim()).filter(tag => tag) : []
}

// Insert placeholder helper: dispatch a custom event that DocumentEditor listens for
function insertPlaceholder(text: string) {
  const event = new CustomEvent('insert-placeholder', { detail: { text } })
  document.dispatchEvent(event)
}
</script>

<template>
  <AppLayout
    title="Create Contract Template"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Contract Templates', href: route('contract-templates.index') },
      { label: 'Create Template' }
    ]"
  >
    <!-- Full-width header/hero section -->
    <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-slate-900 dark:to-slate-800 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-5xl">
        <!-- Header -->
        <div class="flex flex-col gap-6 lg:flex-row lg:justify-between lg:items-center">
          <!-- Title Section -->
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                <Plus class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Create Contract Template</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">
                  Build a reusable contract template with rich text formatting and placeholders
                </p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-3">
            <Button
              v-if="form.content"
              variant="outline"
              @click="togglePreview"
              class="border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20"
            >
              <Eye class="h-4 w-4 mr-2" />
              {{ showPreview ? 'Edit' : 'Preview' }}
            </Button>

            <Button
              @click="submitForm"
              :disabled="form.processing || !form.name || !form.content"
              class="bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white shadow-lg hover:shadow-xl transition-all duration-200"
              size="lg"
            >
              <Save class="h-5 w-5 mr-2" />
              {{ form.processing ? 'Creating...' : 'Create Template' }}
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content with max-w-5xl constraint -->
  <div class="max-w-5xl space-y-6">
      <!-- Form Errors -->
      <div v-if="props.errors && Object.keys(props.errors).length > 0" class="space-y-2">
        <Alert variant="destructive" v-for="(error, field) in props.errors" :key="field">
          <AlertCircle class="h-4 w-4" />
          <AlertDescription>{{ error }}</AlertDescription>
        </Alert>
      </div>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Form - Takes 3 columns -->
        <div class="lg:col-span-3 space-y-6">
          <!-- Basic Information -->
          <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <FileText class="h-5 w-5 text-slate-600 dark:text-slate-400" />
                Basic Information
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label for="name" class="text-sm font-medium text-slate-700 dark:text-slate-300">Template Name *</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="e.g., Billboard Advertising Contract"
                  class="mt-1.5 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
                  required
                />
              </div>

              <div>
                <Label for="description" class="text-sm font-medium text-slate-700 dark:text-slate-300">Description</Label>
                <Textarea
                  ref="descriptionRef"
                  id="description"
                  v-model="form.description"
                  placeholder="Brief description of what this template is for..."
                  class="mt-1.5 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900 resize-none"
                  rows="3"
                  @input="autoResizeTextarea"
                  style="min-height: 80px;"
                />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <Label for="category" class="text-sm font-medium text-slate-700 dark:text-slate-300">Category</Label>
                  <div class="space-y-2">
                    <Select v-model="form.category" v-if="!showNewCategoryInput">
                      <SelectTrigger class="mt-1.5 w-full border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900">
                        <SelectValue placeholder="Select a category" />
                      </SelectTrigger>
                      <SelectContent class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700">
                        <SelectItem v-for="category in allCategories" :key="category" :value="category">
                          {{ category }}
                        </SelectItem>
                        <div class="border-t border-slate-200 dark:border-slate-600 pt-2 mt-2">
                          <button
                            @click="showNewCategoryInput = true"
                            class="w-full text-left px-2 py-1.5 text-sm text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-md flex items-center gap-2"
                          >
                            <Plus class="h-3 w-3" />
                            Add new category
                          </button>
                        </div>
                      </SelectContent>
                    </Select>

                    <div v-if="showNewCategoryInput" class="flex gap-2 items-center mt-1.5">
                      <Input
                        v-model="newCategoryName"
                        placeholder="Enter new category name"
                        class="flex-1 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
                        @keyup.enter="addNewCategory"
                        @keyup.escape="cancelNewCategory"
                      />
                      <Button
                        :disabled="!newCategoryName.trim()"
                        @click="addNewCategory"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white"
                      >
                        <SaveIcon class="h-4 w-4" />
                      </Button>
                      <Button
                        @click="cancelNewCategory"
                        variant="outline"
                        class="border-slate-300 dark:border-slate-600"
                      >
                        <XIcon class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>

                <!-- Price field only for super admin users -->
                <div v-if="isSuperAdmin">
                  <Label for="price" class="text-sm font-medium text-slate-700 dark:text-slate-300">Price (Optional)</Label>
                  <Input
                    id="price"
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    class="mt-1.5 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
                  />
                </div>
              </div>

              <div>
                <Label for="tags" class="text-sm font-medium text-slate-700 dark:text-slate-300">Tags</Label>
                <Input
                  id="tags"
                  v-model="form.tags"
                  type="text"
                  placeholder="advertising, billboard, outdoor (comma-separated)"
                  class="mt-1.5 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
                />
                <div v-if="getTagsArray().length > 0" class="flex flex-wrap gap-1 mt-2">
                  <Badge v-for="tag in getTagsArray()" :key="tag" variant="secondary"
                         class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                    {{ tag }}
                  </Badge>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Content Editor -->
          <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <FileText class="h-5 w-5 text-slate-600 dark:text-slate-400" />
                Contract Content *
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="!showPreview" class="space-y-3">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                  <p class="text-sm text-blue-800 dark:text-blue-300 font-medium mb-1">💡 Pro Tip</p>
                  <p class="text-sm text-blue-700 dark:text-blue-300">
                    Use placeholders like <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">&#123;&#123;client_name&#125;&#125;</code>,
                    <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">&#123;&#123;start_date&#125;&#125;</code>,
                    <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">&#123;&#123;amount&#125;&#125;</code> to create dynamic content
                  </p>
                </div>
                <!-- Use the existing DocumentEditor component -->
                <DocumentEditor
                  v-model="form.content"
                  class="min-h-[600px]"
                />
              </div>

              <div v-else class="border border-slate-300 dark:border-slate-600 rounded-lg p-6 bg-slate-50 dark:bg-slate-900 min-h-[500px]">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Template Preview</h3>
                <div class="prose prose-slate dark:prose-invert max-w-none" v-html="form.content"></div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar - Takes 1 column -->
  <div class="space-y-6">
          <!-- Template Settings -->
          <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100">Settings</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <Label class="text-sm font-medium text-slate-700 dark:text-slate-300">Active Template</Label>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Make this template available for use</p>
                </div>
                <Switch v-model:checked="form.is_active" />
              </div>

              <!-- Premium template option only for super admin users -->
              <div v-if="isSuperAdmin" class="flex items-center justify-between">
                <div>
                  <Label class="text-sm font-medium text-slate-700 dark:text-slate-300">System Template</Label>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Make this available as a system template</p>
                </div>
                <Switch v-model:checked="form.is_premium" />
              </div>
            </CardContent>
          </Card>

          <!-- Placeholders palette -->
          <Card v-if="props.placeholders" class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100">Placeholders</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div v-for="(group, groupName) in props.placeholders" :key="groupName" class="space-y-2">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ groupName }}</div>
                <div class="flex flex-wrap gap-2">
                  <Button
                    v-for="(label, placeholder) in group"
                    :key="placeholder"
                    size="sm"
                    variant="outline"
                    class="text-xs"
                    @click="insertPlaceholder(placeholder)"
                    :title="label"
                  >{{ placeholder }}</Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Template Scope Info -->
          <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100">Template Scope</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="text-sm text-slate-600 dark:text-slate-400 space-y-2">
                <div v-if="isSuperAdmin && form.is_premium" class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-lg p-3">
                  <p class="text-sm text-violet-800 dark:text-violet-300 font-medium">🌟 System Template</p>
                  <p class="text-xs text-violet-700 dark:text-violet-300 mt-1">Available to all companies via marketplace</p>
                </div>
                <div v-else-if="isSuperAdmin" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                  <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">🌍 System Template</p>
                  <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Available to all companies</p>
                </div>
                <div v-else class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-3">
                  <p class="text-sm text-emerald-800 dark:text-emerald-300 font-medium">🏢 Company Template</p>
                  <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1">Available to your team</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Help & Tips -->
          <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100">Template Tips</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="text-sm text-slate-600 dark:text-slate-400 space-y-2">
                <p><strong>Placeholders:</strong> Use curly braces like <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded text-xs">&#123;&#123;variable&#125;&#125;</code></p>
                <p><strong>Common placeholders:</strong></p>
                <ul class="text-xs space-y-1 ml-4 list-disc">
                  <li><code>&#123;&#123;client_name&#125;&#125;</code></li>
                  <li><code>&#123;&#123;company_name&#125;&#125;</code></li>
                  <li><code>&#123;&#123;start_date&#125;&#125;</code></li>
                  <li><code>&#123;&#123;end_date&#125;&#125;</code></li>
                  <li><code>&#123;&#123;total_amount&#125;&#125;</code></li>
                  <li><code>&#123;&#123;contract_duration&#125;&#125;</code></li>
                </ul>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
/* CKEditor Document Editor custom styles */
.ck-editor__editable {
  min-height: 600px !important;
  padding: 2rem !important;
  font-family: 'Times New Roman', serif !important;
  font-size: 14px !important;
  line-height: 1.6 !important;
}

.ck.ck-editor {
  border: 1px solid rgb(203 213 225);
  border-radius: 0.5rem;
}

.dark .ck.ck-editor {
  border-color: rgb(71 85 105);
}

.ck.ck-editor__main > .ck-editor__editable {
  background: white;
  color: rgb(15 23 42);
}

.dark .ck.ck-editor__main > .ck-editor__editable {
  background: rgb(15 23 42);
  color: rgb(241 245 249);
}

.ck.ck-toolbar {
  background: rgb(248 250 252);
  border-bottom: 1px solid rgb(203 213 225);
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

.dark .ck.ck-toolbar {
  background: rgb(30 41 59);
  border-bottom-color: rgb(71 85 105);
}

/* Document-specific styles */
.ck-content {
  max-width: 21cm !important;
  margin: 0 auto !important;
  padding: 2.54cm !important;
  box-shadow: 0 0 5px rgba(0,0,0,.1) !important;
}

.ck-content h1 {
  font-size: 2em !important;
  font-weight: bold !important;
  margin: 1em 0 0.5em 0 !important;
}

.ck-content h2 {
  font-size: 1.5em !important;
  font-weight: bold !important;
  margin: 0.8em 0 0.4em 0 !important;
}

.ck-content h3 {
  font-size: 1.25em !important;
  font-weight: bold !important;
  margin: 0.6em 0 0.3em 0 !important;
}

.ck-content p {
  margin: 0.5em 0 !important;
}

.ck-content table {
  border-collapse: collapse !important;
  margin: 1em 0 !important;
  width: 100% !important;
}

.ck-content table td,
.ck-content table th {
  border: 1px solid #ccc !important;
  padding: 0.5em !important;
}
</style>
