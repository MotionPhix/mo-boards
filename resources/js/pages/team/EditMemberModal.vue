<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Edit, Check, Shield, ShieldCheck } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'

interface TeamMember {
  id: number
  name: string
  email: string
  role: string
  is_owner: boolean
  avatar: string
  joined_at: string
  can: {
    edit: boolean
    delete: boolean
  }
}

interface Role {
  id: number
  name: string
  description: string
  permissions: string[]
}

interface Props {
  member: TeamMember
  roles: Role[]
}

const props = defineProps<Props>()


const form = ref({
  role: props.member.role
})

const formErrors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

// Watch for prop changes to update form
watch(() => props.member.role, (newRole) => {
  form.value.role = newRole
}, { immediate: true })

// Submit the update
const updateMember = () => {
  isSubmitting.value = true
  formErrors.value = {}

  router.put(route('team.update', { member: props.member.id }), {
    role: form.value.role
  }, {
    onSuccess: () => {
      toast.success('Team member updated', { description: 'The team member role has been updated successfully' })
    },
    onError: (errors) => {
      formErrors.value = errors
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

// Role display helpers
const getRoleDisplay = (role: string) => {
  return {
    'company_owner': 'Owner',
    'manager': 'Manager',
    'editor': 'Editor',
    'viewer': 'Viewer'
  }[role] || role.charAt(0).toUpperCase() + role.slice(1)
}

const getRoleBadgeVariant = (role: string): "default" | "destructive" | "outline" | "secondary" => {
  const mapping: Record<string, "default" | "destructive" | "outline" | "secondary"> = {
    'owner': 'destructive',
    'company_owner': 'destructive',
    'admin': 'secondary',
    'manager': 'default',
    'member': 'outline',
    'editor': 'secondary',
    'viewer': 'outline'
  };

  return mapping[role] || 'outline';
}

const getRoleIcon = (role: string) => {
  return role === 'company_owner' || role === 'admin' || role === 'manager'
    ? ShieldCheck
    : Shield
}
</script>

<template>
  <ModalUi max-width="lg">
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Edit class="h-5 w-5 text-primary" />
          Edit {{ member.name }}
        </CardTitle>
        <CardDescription>
          Update the role for {{ member.name }}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <!-- Current Member Info -->
          <div class="p-4 bg-muted/30 rounded-lg">
            <div class="flex items-start gap-3 mb-2">
              <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium">
                  {{ member.name.charAt(0) }}
                </span>
              </div>

              <div>
                <div class="font-medium">
                  {{ member.name }}
                </div>

                <div class="text-sm text-muted-foreground">
                  {{ member.email }}
                </div>

                <Separator class="my-2" />

                <div class="flex items-center gap-2">
                  <span class="text-sm text-muted-foreground">Current role:</span>
                  <Badge :variant="getRoleBadgeVariant(member.role)" class="flex items-center gap-1 w-fit">
                    <component :is="getRoleIcon(member.role)" class="h-3 w-3" />
                    {{ getRoleDisplay(member.role) }}
                  </Badge>
                </div>

              </div>
            </div>
          </div>

          <form @submit.prevent="updateMember" class="space-y-4">
            <div class="space-y-2">
              <Label for="edit-role">New Role</Label>
              <Select v-model="form.role">
                <SelectTrigger :class="{ 'border-destructive': formErrors.role }" class="w-full">
                  <SelectValue placeholder="Select a role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="role in roles"
                    :key="role.id"
                    :value="role.name"
                  >
                    {{ getRoleDisplay(role.name) }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="formErrors.role" class="text-xs text-destructive">{{ formErrors.role }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
              <Button
                type="submit"
                :disabled="isSubmitting">
                <Check class="h-4 w-4" />
                {{ isSubmitting ? 'Updating...' : 'Update Role' }}
              </Button>
            </div>
          </form>
        </div>
      </CardContent>
    </Card>
  </ModalUi>
</template>
