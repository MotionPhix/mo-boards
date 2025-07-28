<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Invite Team Member</DialogTitle>
        <DialogDescription>
          Send an invitation to join your company
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label htmlFor="email">Email Address *</Label>
          <Input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="colleague@company.com"
            class="mt-1"
            :class="{ 'border-red-500': form.errors.email }"
            required
          />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <div>
          <Label htmlFor="role">Role *</Label>
          <Select
            :model-value="form.role"
            @update:model-value="form.role = $event"
          >
            <SelectTrigger class="mt-1">
              <SelectValue placeholder="Select role" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="manager">
                <div>
                  <div class="font-medium">Manager</div>
                  <div class="text-xs text-muted-foreground">Full access to company data</div>
                </div>
              </SelectItem>
              <SelectItem value="editor">
                <div>
                  <div class="font-medium">Editor</div>
                  <div class="text-xs text-muted-foreground">Can edit billboards and campaigns</div>
                </div>
              </SelectItem>
              <SelectItem value="viewer">
                <div>
                  <div class="font-medium">Viewer</div>
                  <div class="text-xs text-muted-foreground">Read-only access</div>
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
          <div v-if="form.errors.role" class="text-red-600 text-sm mt-1">
            {{ form.errors.role }}
          </div>
        </div>

        <div>
          <Label htmlFor="message">Personal Message (Optional)</Label>
          <Textarea
            id="message"
            v-model="form.message"
            placeholder="Add a personal note to the invitation..."
            class="mt-1"
            rows="3"
          />
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="$emit('update:open', false)"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            Send Invitation
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Loader2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'

interface Props {
  open: boolean
}

defineProps<Props>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'invite': [data: { email: string; role: string; message?: string }]
}>()

const form = useForm({
  email: '',
  role: '',
  message: '',
})

const submit = () => {
  form.post(route('team.invite'), {
    onSuccess: () => {
      emit('invite', {
        email: form.email,
        role: form.role,
        message: form.message
      })
      form.reset()
    }
  })
}
</script>
