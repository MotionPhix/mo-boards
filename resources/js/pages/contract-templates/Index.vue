<template>
  <AppLayout
    title="Contract Templates"
    :breadcrumbs="[
      {label: 'Dashboard', href: route('dashboard')},
      { label: 'Templates' }
    ]">
    <div class="pb-12">
      <div class="max-w-7xl">
        <!-- Templates Header -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 dark:from-gray-900 dark:to-black rounded-2xl mb-8 overflow-hidden">
          <div class="relative px-6 py-10 sm:px-10 sm:py-14">
            <!-- Subtle Background Pattern -->
            <div class="absolute inset-0 opacity-5 dark:opacity-3">
              <svg class="absolute top-0 left-0" viewBox="0 0 400 400" fill="none">
                <circle cx="100" cy="100" r="80" fill="white" opacity="0.1"/>
                <circle cx="150" cy="150" r="40" fill="white" opacity="0.15"/>
                <circle cx="50" cy="150" r="25" fill="white" opacity="0.2"/>
              </svg>
            </div>

            <div class="relative text-white max-w-4xl flex justify-between items-center">
              <div>
                <div class="mb-4">
                  <h1 class="text-4xl sm:text-5xl font-bold mb-4 leading-tight">
                    Contract
                    <span class="text-gray-300 dark:text-gray-400">Templates</span>
                  </h1>
                  <div class="w-20 h-1 bg-gray-400 dark:bg-gray-500 rounded-full mb-4"></div>
                </div>

                <p class="text-gray-300 dark:text-gray-400 text-lg leading-relaxed mb-6">
                  Manage your contract templates, browse the marketplace for professional templates,
                  and streamline your contract creation process.
                </p>

                <!-- Stats section -->
                <div class="flex flex-wrap gap-6 text-sm text-gray-400 dark:text-gray-500">
                  <div>
                    <span class="font-semibold text-white dark:text-gray-200 text-lg">{{ companyTemplates.length }}</span>
                    <span class="ml-1">Your Templates</span>
                  </div>
                  <div>
                    <span class="font-semibold text-white dark:text-gray-200 text-lg">{{ purchasedTemplates.length }}</span>
                    <span class="ml-1">Purchased</span>
                  </div>
                  <div>
                    <span class="font-semibold text-white dark:text-gray-200 text-lg">{{ availableSystemTemplates.length }}</span>
                    <span class="ml-1">Available</span>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="hidden lg:flex flex-col gap-2 ml-8">
                <Link
                  :href="route('contract-templates.marketplace')"
                  class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-gray-800 bg-white hover:bg-gray-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                >
                  <svg class="shrink-0 w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  Marketplace
                </Link>
                <Link
                  :href="route('contract-templates.create')"
                  class="inline-flex items-center justify-center px-6 py-3 border-2 border-white text-base font-medium rounded-xl text-white bg-transparent hover:bg-white hover:text-gray-800 transition-all duration-200 transform hover:-translate-y-1"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Create New
                </Link>
              </div>
            </div>

            <!-- Mobile Action Buttons -->
            <div class="lg:hidden mt-8 flex flex-col sm:flex-row gap-4">
              <Link
                :href="route('contract-templates.marketplace')"
                class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-gray-800 bg-white hover:bg-gray-50 transition-all duration-200 shadow-lg"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Marketplace
              </Link>
              <Link
                :href="route('contract-templates.create')"
                class="flex-1 inline-flex items-center justify-center px-6 py-3 border-2 border-white text-base font-medium rounded-xl text-white bg-transparent hover:bg-white hover:text-gray-800 transition-all duration-200"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New
              </Link>
            </div>
          </div>
        </div>
        <!-- Templates Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Company Templates Card -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">My Templates</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ companyTemplates.length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <Link :href="route('contract-templates.create')" class="font-medium text-blue-700 hover:text-blue-900">
                  Create new template
                </Link>
              </div>
            </div>
          </div>

          <!-- Purchased Templates Card -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Purchased Templates</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ purchasedTemplates.length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <Link :href="route('contract-templates.marketplace')" class="font-medium text-blue-700 hover:text-blue-900">
                  Browse marketplace
                </Link>
              </div>
            </div>
          </div>

          <!-- Available Templates Card -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Available to Purchase</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ availableSystemTemplates.length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <Link :href="route('contract-templates.marketplace')" class="font-medium text-blue-700 hover:text-blue-900">
                  View available templates
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Link
                :href="route('contract-templates.create')"
                class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-700 ring-4 ring-white group-hover:bg-blue-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </span>
                </div>
                <div class="mt-4">
                  <h3 class="text-lg font-medium text-gray-900">Create Template</h3>
                  <p class="mt-2 text-sm text-gray-500">Create a new contract template from scratch</p>
                </div>
              </Link>

              <Link
                :href="route('contract-templates.marketplace')"
                class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-700 ring-4 ring-white group-hover:bg-green-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </span>
                </div>
                <div class="mt-4">
                  <h3 class="text-lg font-medium text-gray-900">Browse Marketplace</h3>
                  <p class="mt-2 text-sm text-gray-500">Find professional templates to purchase</p>
                </div>
              </Link>

              <button
                @click="showImportModal = true"
                class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors text-left"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-700 ring-4 ring-white group-hover:bg-purple-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                  </span>
                </div>
                <div class="mt-4">
                  <h3 class="text-lg font-medium text-gray-900">Import Template</h3>
                  <p class="mt-2 text-sm text-gray-500">Import templates from external sources</p>
                </div>
              </button>

              <Link
                v-if="companyTemplates.length > 0"
                :href="route('contract-templates.show', companyTemplates[0].uuid)"
                class="group relative bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
              >
                <div>
                  <span class="rounded-lg inline-flex p-3 bg-orange-50 text-orange-700 ring-4 ring-white group-hover:bg-orange-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </span>
                </div>
                <div class="mt-4">
                  <h3 class="text-lg font-medium text-gray-900">Preview Latest</h3>
                  <p class="mt-2 text-sm text-gray-500">Preview your most recent template</p>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- Your Templates Section -->
        <div class="bg-white shadow rounded-lg mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">My Templates</h3>
              <span class="text-sm text-gray-500">{{ companyTemplates.length }} templates</span>
            </div>
          </div>

          <div v-if="companyTemplates.length === 0" class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No templates yet</h3>
            <p class="text-gray-500 mb-6">Get started by creating your first template or browsing the marketplace.</p>
            <div class="flex justify-center gap-3">
              <Link
                :href="route('contract-templates.create')"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                Create Template
              </Link>
              <Link
                :href="route('contract-templates.marketplace')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Browse Marketplace
              </Link>
            </div>
          </div>

          <div v-else>
            <div class="overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>
                      Template
                    </TableHead>

                    <TableHead>
                      Status
                    </TableHead>

                    <TableHead>
                      Contracts Used
                    </TableHead>

                    <TableHead>
                    </TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow
                    v-for="template in companyTemplates"
                    :key="template.id">
                    <TableCell>
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ template.name }}</div>
                          <div class="text-sm text-gray-500">{{ template.description || 'No description' }}</div>
                        </div>
                      </div>
                    </TableCell>

                    <TableCell>
                      <span
                        :class="[
                          template.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full'
                        ]"
                      >
                        {{ template.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </TableCell>

                    <TableCell>
                      {{ template.contracts_count || 0 }} contracts
                    </TableCell>

                    <TableCell>
                      <div class="flex items-center justify-end gap-2">
                        <Button
                          size="icon"
                          variant="ghost"
                          @click="router.visit(route('contract-templates.show', template.uuid))">
                          <EyeIcon />
                        </Button>

                        <Button
                          size="icon"
                          variant="outline"
                          @click="() => router.visit(route('contract-templates.edit', template.uuid))">
                          <PencilIcon />
                        </Button>

                        <Button
                          size="icon"
                          variant="secondary"
                          @click="duplicateTemplate(template)">
                          <CopyIcon />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>

        <!-- Purchased Templates Section -->
        <div v-if="purchasedTemplates.length > 0" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">Purchased Templates</h3>
              <span class="text-sm text-gray-500">{{ purchasedTemplates.length }} templates</span>
            </div>
          </div>

          <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Template
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Category
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contracts Used
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Purchase Date
                  </th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="template in purchasedTemplates" :key="template.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                          <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ template.name }}</div>
                        <div class="text-sm text-gray-500">{{ template.description || 'Professional template' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ template.category }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ template.contracts_count || 0 }} contracts
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(template.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('contract-templates.show', template.uuid)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        View
                      </Link>
                      <button
                        @click="duplicateTemplate(template)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Use Template
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Import Modal (placeholder for future implementation) -->
    <div
      v-if="showImportModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showImportModal = false"></div>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900">Import Template</h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Template import functionality will be available in a future update.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-6">
            <button
              type="button"
              @click="showImportModal = false"
              class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { CopyIcon, EyeIcon, PencilIcon } from 'lucide-vue-next';

interface ContractTemplate {
  id: number;
  uuid: string;
  name: string;
  description?: string;
  is_active: boolean;
  contracts_count?: number;
  category?: string;
  created_at: string;
  updated_at: string;
}

interface Props {
  companyTemplates: ContractTemplate[];
  purchasedTemplates: ContractTemplate[];
  availableSystemTemplates: ContractTemplate[];
}

defineProps<Props>()

const showImportModal = ref(false);

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const duplicateTemplate = (template: ContractTemplate) => {
  router.post(route('contract-templates.duplicate', template.uuid), {}, {
    onSuccess: () => {
      // Optionally show a success message or refresh the page
      router.reload();
    },
    onError: (errors) => {
      console.error('Error duplicating template:', errors);
    }
  });
};
</script>
