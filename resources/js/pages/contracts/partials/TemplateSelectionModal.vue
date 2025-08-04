<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Plus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import TemplateSelector from '@/components/TemplateSelector.vue'

interface Props {
  templates?: any[]
  selectedTemplateId?: number
  contractId: number
}

const props = defineProps<Props>()

function onTemplateSelected(template: any, closeModal: () => void) {
  // Apply the template to the contract
  router.patch(route('contracts.apply-template', props.contractId), {
    template_id: template.id,
    content: template.content
  }, {
    preserveState: true, // Keep the modal state
    onSuccess: () => {
      toast.success('Template applied!', {
        description: `Using template "${template.name}" for this contract.`
      })

      // Manually close the modal after successful template application
      closeModal()
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
  <ModalUi
    :close-button="false"
    max-width="6xl"
    padding-classes="p-0"
    panel-classes="bg-white dark:bg-gray-900 rounded-lg shadow-xl overflow-hidden"
    v-slot="{ close, reload }"
  >
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select Contract Template</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Choose a template to base your contract document on
          </p>
        </div>

        <section class="flex items-center space-x-2">
          <!-- Create New Template Button - Opens nested modal -->
          <!-- The @close="reload({ only: ['templates'] })" will refresh the templates list when the child modal closes -->
          <ModalLink
            :as="Button"
            size="icon"
            :href="route('contract-templates.create')"
            @close="reload({ only: ['templates'] })">
            <Plus />
          </ModalLink>

          <Button @click="close" variant="ghost">
            Cancel
          </Button>
        </section>
      </div>
    </div>

    <div class="p-6 max-h-[70vh] overflow-y-auto">
      <TemplateSelector
        :templates="templates || []"
        :selected-template-id="selectedTemplateId"
        @select="(template) => onTemplateSelected(template, close)"
      />
    </div>
  </ModalUi>
</template>
