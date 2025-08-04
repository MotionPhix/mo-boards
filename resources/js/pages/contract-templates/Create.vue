<template>
  <AppLayout title="Create Contract Template">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Create Contract Template
        </h2>
        <Link
          :href="route('contract-templates.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Templates
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-8">
          <!-- Template Basic Information -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Template Information</h3>
              <p class="mt-1 text-sm text-gray-500">
                Basic details about your contract template.
              </p>
            </div>
            <div class="px-6 py-4 space-y-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                <div class="mt-1">
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="e.g., Billboard Advertising Agreement"
                  />
                </div>
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.name }}
                </div>
              </div>

              <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <div class="mt-1">
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Describe when and how this template should be used..."
                  ></textarea>
                </div>
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                  {{ form.errors.description }}
                </div>
              </div>

              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  Make this template active and available for use
                </label>
              </div>
            </div>
          </div>

          <!-- Template Content Editor -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Template Content</h3>
                  <p class="mt-1 text-sm text-gray-500">
                    Create your contract template using our rich text editor with placeholder support.
                  </p>
                </div>
                <div class="flex gap-2">
                  <button
                    type="button"
                    @click="showPlaceholderDocs = true"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Placeholder Help
                  </button>
                </div>
              </div>
            </div>
            <div class="p-6">
                <DocumentEditor v-model="form.content" />
                <div v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                  {{ form.errors.content }}
                </div>
            </div>
          </div>

          <!-- Default Terms -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Default Terms</h3>
              <p class="mt-1 text-sm text-gray-500">
                Set default values for common contract terms (JSON format).
              </p>
            </div>
            <div class="px-6 py-4">
              <textarea
                v-model="defaultTermsText"
                rows="5"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
                placeholder='{"payment_terms": "30 days", "cancellation_policy": "7 days notice"}'
              ></textarea>
              <div v-if="form.errors.default_terms" class="mt-1 text-sm text-red-600">
                {{ form.errors.default_terms }}
              </div>
            </div>
          </div>

          <!-- Custom Fields -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Custom Fields</h3>
              <p class="mt-1 text-sm text-gray-500">
                Define custom fields as JSON array.
              </p>
            </div>
            <div class="px-6 py-4">
              <textarea
                v-model="customFieldsText"
                rows="5"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
                placeholder='[{"name": "client_name", "type": "text", "label": "Client Name", "required": true}]'
              ></textarea>
              <div v-if="form.errors.custom_fields" class="mt-1 text-sm text-red-600">
                {{ form.errors.custom_fields }}
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="route('contract-templates.index')"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Creating...' : 'Create Template' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import DocumentEditor from '@/components/DocumentEditor.vue';
import PlaceholderDocumentation from '@/components/PlaceholderDocumentation.vue';

const showPlaceholderDocs = ref(false);

const form = useForm({
  name: '',
  description: '',
  content: '',
  default_terms: {},
  custom_fields: [],
  is_active: true,
});

// Handle JSON fields as text for editing
const defaultTermsText = ref('{}');
const customFieldsText = ref('[]');

// Watch for changes and update form
watch(defaultTermsText, (newValue) => {
  try {
    form.default_terms = JSON.parse(newValue);
  } catch {
    // Invalid JSON, keep the original value
  }
});

watch(customFieldsText, (newValue) => {
  try {
    form.custom_fields = JSON.parse(newValue);
  } catch {
    // Invalid JSON, keep the original value
  }
});

const submit = () => {
  form.post(route('contract-templates.store'), {
    onSuccess: () => {
      // Redirect will be handled by the controller
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};
</script>
