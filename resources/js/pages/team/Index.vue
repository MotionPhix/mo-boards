<script setup lang="ts">
import { computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import {
  Users, UserPlus, Mail, X,
  Trash2, Pencil as Edit, ShieldCheck, Shield,
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardDescription, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import { ModalLink } from '@inertiaui/modal-vue'

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

interface TeamInvitation {
  id: number
  name: string
  email: string
  role: string
  expires_at: string
  created_at: string
  can: {
    cancel: boolean
  }
}

interface Props {
  team: {
    data: TeamMember[]
  }
  invitations: TeamInvitation[]
  company: {
    id: number
    name: string
  }
  roles: Role[]
  // Removed userPermissions prop - we now get these from centralized abilities
}

const props = defineProps<Props>()
const page = usePage()

console.log('Team Management Page Props:', props)

// Use centralized abilities from the middleware/AuthorizationService
const abilities = computed(() => (page.props as any).auth?.user?.abilities || {})
const subscription = computed(() => (page.props as any).subscription || null)

// Team management permissions - now using centralized abilities
const canInviteUsers = computed(() => abilities.value.can_invite_team_members ?? false)
const canRemoveUsers = computed(() => abilities.value.can_remove_team_members ?? false)
const canUpdateRoles = computed(() => abilities.value.can_update_team_roles ?? false)
const canManageInvitations = computed(() => abilities.value.can_manage_invitations ?? false)

// Display current team status from subscription data
const teamStatus = computed(() => {
  if (!subscription.value?.team) return null

  return {
    current: subscription.value.team.current,
    limit: subscription.value.team.limit,
    canInviteMore: subscription.value.team.can_invite_more,
    planName: subscription.value.plan
  }
})

// Role icon helper
const getRoleIcon = (role: string, isOwner: boolean) => {
  if (isOwner) return ShieldCheck
  return role === 'manager' || role === 'company_owner' ? ShieldCheck : Shield
}

// Role badge variant
const getRoleBadgeVariant = (role: string, isOwner: boolean) => {
  if (isOwner) return 'default'
  switch (role) {
    case 'manager': return 'secondary'
    case 'editor': return 'outline'
    case 'viewer': return 'outline'
    default: return 'outline'
  }
}

// Cancel invitation
const cancelInvitation = (invitation: TeamInvitation) => {
  if (!canManageInvitations.value) {
    toast.error('Unauthorized', {
      description: 'You do not have permission to cancel invitations'
    })
    return
  }

  router.delete(route('team.cancel-invitation', invitation.id), {
    onSuccess: () => {
      toast.success('Invitation cancelled', {
        description: `The invitation for ${invitation.name} has been cancelled`
      })
    },
    onError: () => {
      toast.error('Failed to cancel invitation', {
        description: 'Please try again later'
      })
    }
  })
}

// Remove team member
const removeMember = (member: TeamMember) => {
  if (!canRemoveUsers.value) {
    toast.error('Unauthorized', {
      description: 'You do not have permission to remove team members'
    })
    return
  }

  if (member.is_owner) {
    toast.error('Cannot remove owner', {
      description: 'Company owners cannot be removed from the team'
    })
    return
  }

  router.delete(route('team.destroy', member.id), {
    onSuccess: () => {
      toast.success('Team member removed', {
        description: `${member.name} has been removed from the team`
      })
    },
    onError: () => {
      toast.error('Failed to remove team member', {
        description: 'Please try again later'
      })
    }
  })
}

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', href: route('dashboard') },
  { label: 'Team Management' }
]

// SEO metadata
const pageTitle = computed(() => `Team Management - ${props.company.name}`)
const pageDescription = computed(() => `Manage team members for ${props.company.name}. Invite users, assign roles, and manage permissions.`)
</script>

<template>
  <Head>
    <title>{{ pageTitle }}</title>
    <meta name="description" :content="pageDescription" />
  </Head>

  <AppLayout
    title="Team Management"
    :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Team Management</h1>
          <p class="text-muted-foreground">
            Manage your team members and their access levels
          </p>

          <!-- Team Status Display -->
          <div v-if="teamStatus" class="mt-2 text-sm text-muted-foreground">
            Team Members: {{ teamStatus.current }}{{ teamStatus.limit ? `/${teamStatus.limit}` : '' }}
            <span v-if="teamStatus.limit" class="ml-2">
              ({{ teamStatus.planName }} plan)
            </span>
          </div>
        </div>

        <!-- Invite User Button - uses centralized ability -->
        <Button
          v-if="canInviteUsers"
          :href="route('team.invite-modal')"
          :as="ModalLink">
          <UserPlus />
          Invite User
        </Button>

        <!-- Show upgrade message if cannot invite -->
        <div v-else-if="!canInviteUsers && teamStatus && !teamStatus.canInviteMore"
             class="text-center">
          <p class="text-sm text-muted-foreground mb-2">
            Reached team limit ({{ teamStatus.current }}/{{ teamStatus.limit }})
          </p>
          <Button variant="outline" size="sm">
            Upgrade Plan
          </Button>
        </div>
      </div>

      <!-- Pending Invitations -->
      <Card v-if="invitations.length > 0" class="mb-8 bg-card text-card-foreground">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Mail class="h-5 w-5 text-amber-500" />
            Pending Invitations
          </CardTitle>
          <CardDescription>
            Team member invitations that haven't been accepted yet
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="invitation in invitations" :key="invitation.id"
                 class="flex items-center justify-between p-4 border border-border rounded-lg bg-muted/30">
              <div class="flex items-center gap-4">
                <Avatar class="h-10 w-10">
                  <AvatarFallback class="bg-amber-100 text-amber-600">
                    {{ invitation.name.charAt(0).toUpperCase() }}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <div class="flex items-center gap-2">
                    <p class="font-medium">{{ invitation.name }}</p>
                    <Badge variant="outline" class="text-amber-600 border-amber-200">
                      Pending
                    </Badge>
                  </div>
                  <p class="text-sm text-muted-foreground">{{ invitation.email }}</p>
                  <p class="text-xs text-muted-foreground">
                    Invited {{ invitation.created_at }} • Expires {{ invitation.expires_at }}
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <Badge :variant="getRoleBadgeVariant(invitation.role, false)">
                  {{ invitation.role }}
                </Badge>
                <Button
                  v-if="invitation.can.cancel && canManageInvitations"
                  variant="ghost"
                  size="sm"
                  @click="cancelInvitation(invitation)">
                  <X class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Team Members Card -->
      <Card class="bg-card text-card-foreground">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Users class="h-5 w-5 text-primary" />
            Active Team Members
          </CardTitle>
          <CardDescription>
            Manage your active team members and their permissions
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>Joined</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="member in team.data" :key="member.id">
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <Avatar class="h-10 w-10">
                        <AvatarImage v-if="member.avatar" :src="member.avatar" />
                        <AvatarFallback>
                          {{ member.name.charAt(0).toUpperCase() }}
                        </AvatarFallback>
                      </Avatar>
                      <div>
                        <p class="font-medium">{{ member.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ member.email }}</p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-2">
                      <component :is="getRoleIcon(member.role, member.is_owner)"
                                 class="h-4 w-4"
                                 :class="member.is_owner ? 'text-amber-500' : 'text-muted-foreground'" />
                      <Badge :variant="getRoleBadgeVariant(member.role, member.is_owner)">
                        {{ member.is_owner ? 'Owner' : member.role }}
                      </Badge>
                    </div>
                  </TableCell>
                  <TableCell class="text-muted-foreground">
                    {{ member.joined_at }}
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end gap-2">
                      <!-- Edit button - uses centralized ability -->
                      <Button
                        v-if="member.can.edit && canUpdateRoles"
                        variant="ghost"
                        size="sm"
                        :href="route('team.edit-modal', member.id)"
                        :as="ModalLink">
                        <Edit class="h-4 w-4" />
                      </Button>

                      <!-- Remove button - uses centralized ability -->
                      <Button
                        v-if="member.can.delete && canRemoveUsers && !member.is_owner"
                        variant="ghost"
                        size="sm"
                        @click="removeMember(member)">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
