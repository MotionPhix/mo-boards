<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import TemplateSelector from '@/components/TemplateSelector.vue'

interface Props {
  contract: {
    data: any
  }
  templates?: any[]
}

const props = defineProps<Props>()

const selectedTemplate = ref<any>(null)

// Handle template selection
function onTemplateSelected(template: any) {
  selectedTemplate.value = template

  // Apply template and redirect back to document page
  router.patch(route('contracts.apply-template', props.contract.data.id), {
    template_id: template.id,
    content: template.content
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Template applied!', {
        description: `Using template "${template.name}" for this contract.`
      })

      // Close modal and redirect back
      router.visit(route('contracts.document', props.contract.data.id))
    },
    onError: (errors) => {
      toast.error('Failed to apply template', {
        description: Object.values(errors).flat().join(', ')
      })
    }
  })
}
</script>

<template>
  <Modal>
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-6xl w-full max-h-[80vh] overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select Contract Template</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Choose a template to base your contract document on
        </p>
      </div>

      <div class="p-6 overflow-y-auto max-h-[60vh]">
        <TemplateSelector
          :templates="templates || []"
          :selected-template-id="selectedTemplate?.id"
          @select="onTemplateSelected"
        />
      </div>
    </div>
  </Modal>
</template>
