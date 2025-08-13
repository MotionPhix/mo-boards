<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Plus, Pencil as Edit, Save, AlertCircle, Trash2 } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import DocumentEditor from '@/components/DocumentEditor.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { Alert, AlertDescription } from '@/components/ui/alert'
import type { ContractTemplate } from '@/types'

interface Props {
  template: ContractTemplate
  categories: string[]
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
  name: props.template.name || '',
  description: props.template.description || '',
  category: props.template.category || '',
  content: props.template.content || '',
  price: props.template.price ? props.template.price.toString() : '',
  is_active: props.template.is_active ?? true,
  is_premium: props.template.is_premium ?? false,
  tags: props.template.tags ? props.template.tags.join(', ') : '',
  processing: false
})

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

const submitForm = () => {
  if (form.processing) return

  form.processing = true

  const formData = {
    name: form.name,
    description: form.description,
    content: form.content,
    category: form.category,
    is_active: form.is_active,
    is_premium: isSuperAdmin.value ? form.is_premium : false, // Only super admins can create premium templates
    price: form.price,
    _method: 'PUT'
  }

  router.post(route('contract-templates.update', props.template.uuid), formData, {
    onFinish: () => {
      form.processing = false
    },
    onSuccess: () => {
  router.visit(route('contract-templates.index'))
    },
  })
}

const deleteTemplate = () => {
  if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
    router.delete(route('contract-templates.destroy', props.template.uuid))
  }
}
</script>

<template>
  <AppLayout
    :title="`Edit Template: ${props.template.name}`"
    :breadcrumbs="[
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Contract Templates', href: route('contract-templates.index') },
      { label: 'Edit Template' }
    ]">
    <div class="min-h-screen">
      <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="rounded bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shadow-sm">
          <div class="px-6 py-6">
            <div class="flex items-center justify-between">
              <!-- Title -->
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <Edit class="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Edit Template</h1>
                  <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">
                    Modify your contract template with rich text formatting and placeholders
                  </p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 mt-6">
              <Button
                @click="deleteTemplate"
                variant="outline"
                class="border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20"
              >
                <Trash2 class="h-4 w-4 mr-2" />
                Delete
              </Button>

              <Button
                @click="submitForm"
                :disabled="form.processing"
                class="bg-emerald-600 hover:bg-emerald-700 text-white">
                <Save class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Updating...' : 'Update' }}
              </Button>
            </div>
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="props.errors && Object.keys(props.errors).length > 0" class="mt-6">
          <Alert class="border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800">
            <AlertCircle class="h-4 w-4 text-red-600 dark:text-red-400" />
            <AlertDescription class="text-red-800 dark:text-red-200">
              <ul class="list-disc list-inside">
                <li v-for="(error, field) in props.errors" :key="field">{{ error }}</li>
              </ul>
            </AlertDescription>
          </Alert>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 py-8">
          <!-- Main Editor Area - Takes 3 columns -->
          <div class="lg:col-span-3 space-y-6">
            <!-- Template Details Card -->
            <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
              <CardHeader>
                <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100">Template Information</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <Label for="name" class="text-sm font-medium text-slate-700 dark:text-slate-300">Template Name</Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    placeholder="Enter template name..."
                    class="mt-1.5 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
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

                <div class="grid grid-cols-1 gap-4">
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

                      <div v-if="showNewCategoryInput" class="flex gap-2 mt-1.5">
                        <Input
                          v-model="newCategoryName"
                          placeholder="Enter new category name"
                          class="flex-1 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
                          @keyup.enter="addNewCategory"
                          @keyup.escape="cancelNewCategory"
                        />
                        <Button
                          @click="addNewCategory"
                          size="sm"
                          class="bg-emerald-600 hover:bg-emerald-700 text-white"
                        >
                          Add
                        </Button>
                        <Button
                          @click="cancelNewCategory"
                          variant="outline"
                          size="sm"
                          class="border-slate-300 dark:border-slate-600"
                        >
                          Cancel
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
                    placeholder="legal, commercial, service (comma-separated)"
                    class="mt-1.5 border-slate-300 dark:border-slate-600 focus:border-emerald-500 dark:focus:border-emerald-400 bg-white dark:bg-slate-900"
                  />
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Separate tags with commas</p>
                </div>
              </CardContent>
            </Card>

            <!-- Content Editor Card -->
            <Card class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm">
              <CardHeader>
                <CardTitle class="text-lg font-semibold text-slate-900 dark:text-slate-100">Template Content</CardTitle>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3 mt-3">
                  <p class="text-sm text-blue-800 dark:text-blue-300 font-medium mb-1">💡 Pro Tip</p>
                  <p class="text-sm text-blue-700 dark:text-blue-300">
                    Use placeholders like <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">&#123;&#123;client_name&#125;&#125;</code>,
                    <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">&#123;&#123;start_date&#125;&#125;</code>,
                    <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded text-xs">&#123;&#123;amount&#125;&#125;</code> to create dynamic content
                  </p>
                </div>
              </CardHeader>
              <CardContent>
                <!-- Use the existing DocumentEditor component -->
                <DocumentEditor
                  v-model="form.content"
                  class="min-h-[600px]"
                  placeholder="Enter your contract template content here..."
                />
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
    </div>
  </AppLayout>
</template>
