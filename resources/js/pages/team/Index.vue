<script setup lang="ts">
import { computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import {
  Users, UserPlus, Mail, X,
  Trash2, Pencil as Edit, ShieldCheck, Shield
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardDescription, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
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
  userPermissions: {
    canInviteUsers: boolean
    canRemoveUsers: boolean
    canUpdateRoles: boolean
  }
}

const props = defineProps<Props>()
const page = usePage()

// Get user abilities from auth
const auth = computed(() => page.props.auth as any)
const userAbilities = computed(() => auth.value?.user?.abilities || {})

// Get subscription status
const subscription = computed(() => page.props.subscription as any)

// Combine ability and subscription checks for inviting users
const canInviteUsers = computed(() => {
  return userAbilities.value.can_invite_team_members; // && subscription.value?.canInviteMoreTeamMembers
})
const canRemoveUsers = computed(() => userAbilities.value.can_remove_team_members)
const canUpdateRoles = computed(() => userAbilities.value.can_update_team_roles)

// Security: Show Role Permissions section only to users who can manage roles/permissions
const canViewRolePermissions = computed(() => {
  return userAbilities.value.is_super_admin ||
         userAbilities.value.is_company_owner ||
         userAbilities.value.can_manage_roles ||
         userAbilities.value.can_update_team_roles
})

// Remove a team member
const removeMember = (member: TeamMember) => {
  if (confirm(`Are you sure you want to remove ${member.name} from the team?`)) {
    router.delete(route('team.destroy', { member: member.id }), {
      onSuccess: () => {
        toast({
          title: 'Team member removed',
          description: `${member.name} has been removed from the team`,
        })
      }
    })
  }
}

// Cancel invitation
const cancelInvitation = (invitation: TeamInvitation) => {
  if (confirm(`Are you sure you want to cancel the invitation to ${invitation.name}?`)) {
    router.delete(route('team.cancel-invitation', { invitation: invitation.id }), {
      onSuccess: () => {
        toast({
          title: 'Invitation cancelled',
          description: `Invitation to ${invitation.name} has been cancelled`,
        })
      }
    })
  }
}

// Role display helpers
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

const getRoleDisplay = (role: string) => {
  return {
    'owner': 'Owner',
    'company_owner': 'Owner',
    'admin': 'Administrator',
    'manager': 'Manager',
    'member': 'Member',
    'editor': 'Editor',
    'viewer': 'Viewer'
  }[role] || role.charAt(0).toUpperCase() + role.slice(1)
}

// Role icon
const getRoleIcon = (role: string) => {
  return role === 'owner' || role === 'admin'
    ? ShieldCheck
    : Shield
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

  <AppLayout title="Team Management" :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Team Management</h1>
          <p class="text-muted-foreground">
            Manage your team members and their access levels
          </p>
        </div>

        <!-- Invite User Modal Link -->
        <Button
          v-if="canInviteUsers"
          :href="route('team.invite-modal')"
          :as="ModalLink">
          <UserPlus />
          Invite User
        </Button>
      </div>

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
                  <TableHead class="text-right" />
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-for="member in team.data" :key="member.id" class="hover:bg-muted/50">
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <Avatar>
                        <AvatarImage v-if="member.avatar" :src="member.avatar" alt="Avatar" />
                        <AvatarFallback>{{ member.name.charAt(0) }}</AvatarFallback>
                      </Avatar>
                      <div>
                        <div class="font-medium">{{ member.name }}</div>
                        <div class="text-sm text-muted-foreground">{{ member.email }}</div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getRoleBadgeVariant(member.role)" class="flex items-center gap-1 w-fit">
                      <component :is="getRoleIcon(member.role)" class="h-3 w-3" />
                      {{ getRoleDisplay(member.role) }}
                    </Badge>
                  </TableCell>
                  <TableCell>{{ member.joined_at }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <!-- Edit Member Modal Link -->
                      <ModalLink
                        v-if="member.role !== 'company_owner' &&member.can.edit && canUpdateRoles"
                        :href="`/team/${member.id}/edit`">
                        <Button
                          variant="ghost"
                          size="sm"
                          class="h-8 w-8 p-0">
                          <span class="sr-only">Edit</span>
                          <Edit class="h-4 w-4" />
                        </Button>
                      </ModalLink>

                      <Button
                        v-if="member.can.delete && canRemoveUsers"
                        @click="removeMember(member)"
                        variant="ghost"
                        size="sm"
                        class="h-8 w-8 p-0 text-destructive hover:bg-destructive/10">
                        <span class="sr-only">Remove</span>
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div
            v-if="team.data.length === 0"
            class="py-8 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-muted mb-4">
              <Users class="h-6 w-6 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-medium">No team members</h3>
            <p class="text-muted-foreground mt-2 mb-4 max-w-md mx-auto">
              You don't have any team members yet. Start by inviting colleagues to collaborate.
            </p>
            <ModalLink
              v-if="canInviteUsers"
              href="/team/invite"
              :close-button="true"
              :close-explicitly="false"
              max-width="md"
              padding-classes="p-6"
              position="center"
            >
              <Button>
                <UserPlus class="h-4 w-4 mr-2" />
                Invite Your First Team Member
              </Button>
            </ModalLink>
          </div>
        </CardContent>
      </Card>

      <!-- Pending Invitations Card -->
      <Card class="mt-8 bg-card text-card-foreground" v-if="invitations.length > 0">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Mail class="h-5 w-5 text-primary" />
            Pending Invitations
          </CardTitle>
          <CardDescription>
            Users who have been invited but haven't joined the team yet
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>Sent</TableHead>
                  <TableHead>Expires</TableHead>
                  <TableHead class="text-right"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="invitation in invitations" :key="invitation.id" class="hover:bg-muted/50">
                  <TableCell class="grid">
                    <strong>{{ invitation.name }}</strong>
                    <span class="text-muted-foreground">{{ invitation.email }}</span>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getRoleBadgeVariant(invitation.role)" class="flex items-center gap-1 w-fit">
                      <component :is="getRoleIcon(invitation.role)" class="h-3 w-3" />
                      {{ getRoleDisplay(invitation.role) }}
                    </Badge>
                  </TableCell>
                  <TableCell>{{ invitation.created_at }}</TableCell>
                  <TableCell>{{ invitation.expires_at }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <Button
                        v-if="invitation.can.cancel"
                        @click="cancelInvitation(invitation)"
                        variant="ghost"
                        size="sm"
                        class="h-8 w-8 p-0 text-destructive hover:bg-destructive/10">
                        <span class="sr-only">Cancel</span>
                        <X class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Role Permissions Card -->
      <Card v-if="canViewRolePermissions" class="mt-8 bg-card text-card-foreground">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <ShieldCheck class="h-5 w-5 text-primary" />
            Role Permissions
          </CardTitle>
          <CardDescription>
            Understanding access levels for each role
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="space-y-6">
            <div v-for="role in roles" :key="role.id" class="space-y-2">
              <div class="flex items-center gap-2">
                <Badge :variant="getRoleBadgeVariant(role.name)" class="flex items-center gap-1 w-fit">
                  <component :is="getRoleIcon(role.name)" class="h-3 w-3" />
                  {{ getRoleDisplay(role.name) }}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground">{{ role.description }}</p>
              <Separator class="my-2" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
