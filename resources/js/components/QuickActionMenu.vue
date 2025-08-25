<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import QuickActionItem from '@/components/QuickActionItem.vue'

const isOpen = ref(false)
const page = usePage()

// Get user abilities from the middleware-provided auth data
const permissions = computed(() => {
  const user = page.props.auth?.user
  const abilities = user?.abilities || {}

  return {
    can_create_billboards: abilities.can_create_billboards || false,
    can_create_contracts: abilities.can_create_contracts || false,
    can_create_templates: abilities.can_create_contracts || false, // Templates use same permission as contracts
    can_invite_team: abilities.can_invite_team_members || false,
    can_create_companies: abilities.can_create_companies || false,
    can_manage_settings: abilities.can_manage_company_settings || false,
  }
})

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const closeMenu = () => {
  isOpen.value = false
}

// Quick Action Functions
const createBillboard = () => {
  closeMenu()
  router.visit(route('billboards.create'))
}

const createContract = () => {
  closeMenu()
  router.visit(route('contracts.create'))
}

const createTemplate = () => {
  closeMenu()
  router.visit(route('contract-templates.create'))
}

const inviteTeamMember = () => {
  closeMenu()
  router.visit(route('team.invite-modal'))
}

const createCompany = () => {
  closeMenu()
  router.visit(route('companies.create'))
}

const openSettings = () => {
  closeMenu()
  router.visit(route('companies.settings'))
}

// Close menu on escape key
const handleEscape = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <div class="fixed bottom-6 right-6 z-50">
    <!-- Quick Action Button -->
    <div class="relative">
      <!-- Main FAB Button -->
      <Button
        @click="toggleMenu"
        class="h-14 w-14 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 bg-primary hover:bg-primary/90"
        :class="{ 'rotate-45': isOpen }"
      >
        <Plus class="h-6 w-6" />
      </Button>

      <!-- Quick Action Menu -->
      <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 scale-95 translate-y-2"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-200"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-2"
      >
        <div
          v-if="isOpen"
          class="absolute bottom-16 right-0 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <div class="p-2">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 px-3 py-2 border-b border-gray-100 dark:border-gray-700">
              Quick Actions
            </div>

            <div class="py-2 space-y-1">
              <!-- Billboard Actions -->
              <QuickActionItem
                @click="createBillboard"
                icon="Flag"
                title="New Billboard"
                description="Add a new billboard location"
                :permissions="permissions.can_create_billboards"
              />

              <!-- Contract Actions -->
              <QuickActionItem
                @click="createContract"
                icon="FileText"
                title="New Contract"
                description="Create a new client contract"
                :permissions="permissions.can_create_contracts"
              />

              <!-- Template Actions -->
              <QuickActionItem
                @click="createTemplate"
                icon="ScrollText"
                title="New Template"
                description="Create a contract template"
                :permissions="permissions.can_create_templates"
              />

              <!-- Team Actions -->
              <QuickActionItem
                @click="inviteTeamMember"
                icon="UserPlus"
                title="Invite Team Member"
                description="Send an invitation to join"
                :permissions="permissions.can_invite_team"
              />

              <!-- Company Actions -->
              <QuickActionItem
                v-if="permissions.can_create_companies"
                @click="createCompany"
                icon="Building"
                title="New Company"
                description="Create a new company"
                :permissions="permissions.can_create_companies"
              />
            </div>

            <Separator class="my-2" />

            <!-- Settings & More -->
            <div class="py-1">
              <QuickActionItem
                @click="openSettings"
                icon="Settings"
                title="Company Settings"
                description="Manage company preferences"
                :permissions="permissions.can_manage_settings"
                variant="secondary"
              />
            </div>
          </div>
        </div>
      </Transition>

      <!-- Backdrop -->
      <div
        v-if="isOpen"
        @click="toggleMenu"
        class="fixed inset-0 -z-10"
      />
    </div>
  </div>
</template>
