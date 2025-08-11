<script setup lang="ts">
import { UserPlus, Mail } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { toast } from 'vue-sonner'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'

interface Role {
  id: number
  name: string
  description: string
  permissions: string[]
}

interface Props {
  roles: Role[]
}

defineProps<Props>()

const inviteModal = ref(null)

const form = useForm({
  name: '',
  email: '',
  role: 'editor',
})

// Submit the invitation
const submitInvitation = () => {

  form.post(route('team.invite'), {
    onSuccess: () => {
      toast.success('Team member invited', {
        description: `${form.name} has been invited to join the team`,
      })

      // Reset form for next invitation
      form.resetAndClearErrors()
    },

    onError: (errors) => {
      toast.error('Failed to invite team member', {
        description: Object.values(errors).join(', '),
      })
    },

    onFinish: () => {
      inviteModal.value?.close()
    }
  })
}

// Role display helpers
const getRoleDisplay = (role: string) => {
  return {
    'manager': 'Manager',
    'editor': 'Editor',
    'viewer': 'Viewer'
  }[role] || role.charAt(0).toUpperCase() + role.slice(1)
}
</script>

<template>
  <ModalUi max-width="md" ref="inviteModal">
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <UserPlus class="h-6 w-6 text-primary shrink-0" />
          Invite Team Member
        </CardTitle>

        <CardDescription>
          Send an invitation to a new team member. They will receive an email with instructions to join.
        </CardDescription>
      </CardHeader>

      <CardContent>
        <form @submit.prevent="submitInvitation" class="space-y-6">
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="John Doe"
              :class="{ 'border-destructive': form.errors.name }"
              required
            />

            <InputError :message="form.errors.name" />
          </div>

          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="john@example.com"
              :class="{ 'border-destructive': form.errors.email }"
              required
            />

            <InputError :message="form.errors.email" />
          </div>

          <div class="space-y-2">
            <Label for="role">Role</Label>
            <Select v-model="form.role">
              <SelectTrigger :class="{ 'border-destructive': form.errors.role }" class="w-full">
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="role in roles"
                  :key="role.id"
                  :value="role.name">
                  {{ getRoleDisplay(role.name) }}
                </SelectItem>
              </SelectContent>
            </Select>

            <InputError :message="form.errors.role" />
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <Button
              type="submit"
              :disabled="form.processing">
              <Mail class="mr-2 h-4 w-4" />
              {{ form.processing ? 'Sending...' : 'Send Invitation' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </ModalUi>
</template>
