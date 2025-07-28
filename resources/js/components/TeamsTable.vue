<template>
  <div class="overflow-x-auto">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Name</TableHead>
          <TableHead>Email</TableHead>
          <TableHead>Role</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Last Active</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="member in members" :key="member.id">
          <TableCell class="font-medium">
            {{ member.name }}
            <span v-if="member.id === currentUser.id" class="text-xs text-muted-foreground ml-2">
              (You)
            </span>
          </TableCell>
          <TableCell>{{ member.email }}</TableCell>
          <TableCell>
            <Badge :variant="getRoleVariant(member.roles[0]?.name)">
              {{ formatRole(member.roles[0]?.name) }}
              <Crown v-if="member.pivot?.is_owner" class="ml-1 h-3 w-3" />
            </Badge>
          </TableCell>
          <TableCell>
            <Badge :variant="member.last_active_at ? 'default' : 'secondary'">
              {{ member.last_active_at ? 'Active' : 'Pending' }}
            </Badge>
          </TableCell>
          <TableCell>
            {{ member.last_active_at ? formatDate(member.last_active_at) : 'Never' }}
          </TableCell>
          <TableCell class="text-right">
            <div class="flex justify-end space-x-2">
              <Button
                v-if="!member.last_active_at"
                variant="outline"
                size="sm"
                @click="$emit('resendInvite', member)"
              >
                Resend Invite
              </Button>
              <Button
                v-if="member.id !== currentUser.id"
                variant="outline"
                size="sm"
                @click="$emit('editRole', member)"
              >
                Edit Role
              </Button>
              <Button
                v-if="member.id !== currentUser.id && !member.pivot?.is_owner"
                variant="destructive"
                size="sm"
                @click="$emit('removeMember', member)"
              >
                Remove
              </Button>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

<script setup lang="ts">
import { Crown } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import type { TeamMember, User } from '@/types'

interface Props {
  members: TeamMember[]
  currentUser: User
}

defineProps<Props>()

defineEmits<{
  editRole: [member: TeamMember]
  removeMember: [member: TeamMember]
  resendInvite: [member: TeamMember]
}>()

const getRoleVariant = (role?: string) => {
  switch (role) {
    case 'company_owner':
      return 'default'
    case 'manager':
      return 'secondary'
    case 'editor':
      return 'outline'
    case 'viewer':
      return 'outline'
    default:
      return 'outline'
  }
}

const formatRole = (role?: string) => {
  if (!role) return 'No Role'
  return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}
</script>
