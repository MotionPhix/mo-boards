<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { useTheme } from '@/composables/useTheme'
import { 
  Users, UserPlus, Mail, Check, X, 
  Trash2, Edit, ShieldCheck, Shield, AlertTriangle
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'

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
const toast = useToast()
const theme = useTheme()
const page = usePage()

// State for invite form
const inviteDialogOpen = ref(false)
const editMemberDialogOpen = ref(false)
const selectedMember = ref<TeamMember | null>(null)

const form = ref({
  name: '',
  email: '',
  role: 'editor',
})

const formErrors = ref<Record<string, string>>({})

// Reset the form
const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    role: 'editor',
  }
  formErrors.value = {}
}

// Submit the invitation
const submitInvitation = () => {
  router.post(route('team.invite'), form.value, {
    onSuccess: () => {
      inviteDialogOpen.value = false
      resetForm()
      toast.toast({
        title: 'Team member invited',
        description: `${form.value.name} has been invited to join the team`,
      })
    },
    onError: (errors) => {
      formErrors.value = errors
    }
  })
}

// Open edit dialog for a team member
const editMember = (member: TeamMember) => {
  selectedMember.value = member
  form.value.role = member.role
  editMemberDialogOpen.value = true
}

// Update team member
const updateMember = () => {
  if (!selectedMember.value) return
  
  router.put(route('team.update', { member: selectedMember.value.id }), {
    role: form.value.role
  }, {
    onSuccess: () => {
      editMemberDialogOpen.value = false
      selectedMember.value = null
      toast.toast({
        title: 'Team member updated',
        description: 'The team member role has been updated successfully',
      })
    },
    onError: (errors) => {
      formErrors.value = errors
    }
  })
}

// Remove a team member
const removeMember = (member: TeamMember) => {
  if (confirm(`Are you sure you want to remove ${member.name} from the team?`)) {
    router.delete(route('team.destroy', { member: member.id }), {
      onSuccess: () => {
        toast.toast({
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
        toast.toast({
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
    'company_owner': 'Company Owner',
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
        
        <Button v-if="userPermissions.canInviteUsers" @click="inviteDialogOpen = true" class="shadow-sm">
          <UserPlus class="h-4 w-4 mr-2" />
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
                  <TableHead class="text-right">Actions</TableHead>
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
                      <Button 
                        v-if="member.can.edit && userPermissions.canUpdateRoles" 
                        @click="editMember(member)" 
                        variant="ghost" 
                        size="sm" 
                        class="h-8 w-8 p-0"
                      >
                        <span class="sr-only">Edit</span>
                        <Edit class="h-4 w-4" />
                      </Button>
                      <Button 
                        v-if="member.can.delete && userPermissions.canRemoveUsers" 
                        @click="removeMember(member)" 
                        variant="ghost" 
                        size="sm" 
                        class="h-8 w-8 p-0 text-destructive hover:bg-destructive/10"
                      >
                        <span class="sr-only">Remove</span>
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div v-if="team.data.length === 0" class="py-8 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-muted mb-4">
              <Users class="h-6 w-6 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-medium">No team members</h3>
            <p class="text-muted-foreground mt-2 mb-4 max-w-md mx-auto">
              You don't have any team members yet. Start by inviting colleagues to collaborate.
            </p>
            <Button v-if="userPermissions.canInviteUsers" @click="inviteDialogOpen = true">
              <UserPlus class="h-4 w-4 mr-2" />
              Invite Your First Team Member
            </Button>
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
                  <TableHead>Email</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>Sent</TableHead>
                  <TableHead>Expires</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="invitation in invitations" :key="invitation.id" class="hover:bg-muted/50">
                  <TableCell>{{ invitation.name }}</TableCell>
                  <TableCell>{{ invitation.email }}</TableCell>
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
                        class="h-8 w-8 p-0 text-destructive hover:bg-destructive/10"
                      >
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
      <Card class="mt-8 bg-card text-card-foreground">
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
            <div v-for="role in props.roles" :key="role.id" class="space-y-2">
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

    <!-- Invite User Dialog -->
    <Dialog v-model:open="inviteDialogOpen" @update:open="resetForm">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Invite Team Member</DialogTitle>
          <DialogDescription>
            Send an invitation to a new team member. They will receive an email with instructions to join.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="John Doe"
              :class="{ 'border-destructive': formErrors.name }"
            />
            <p v-if="formErrors.name" class="text-xs text-destructive mt-1">{{ formErrors.name }}</p>
          </div>
          <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="john@example.com"
              :class="{ 'border-destructive': formErrors.email }"
            />
            <p v-if="formErrors.email" class="text-xs text-destructive mt-1">{{ formErrors.email }}</p>
          </div>
          <div class="grid gap-2">
            <Label for="role">Role</Label>
            <Select v-model="form.role">
              <SelectTrigger :class="{ 'border-destructive': formErrors.role }">
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="company_owner">Company Owner</SelectItem>
                <SelectItem value="manager">Manager</SelectItem>
                <SelectItem value="editor">Editor</SelectItem>
                <SelectItem value="viewer">Viewer</SelectItem>
              </SelectContent>
            </Select>
            <p v-if="formErrors.role" class="text-xs text-destructive mt-1">{{ formErrors.role }}</p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="inviteDialogOpen = false">Cancel</Button>
          <Button @click="submitInvitation">
            <Mail class="mr-2 h-4 w-4" />
            Send Invitation
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Edit Member Dialog -->
    <Dialog v-model:open="editMemberDialogOpen">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Edit Team Member</DialogTitle>
          <DialogDescription>
            Update the role for {{ selectedMember?.name }}
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="edit-role">Role</Label>
            <Select v-model="form.role">
              <SelectTrigger :class="{ 'border-destructive': formErrors.role }">
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="company_owner">Company Owner</SelectItem>
                <SelectItem value="manager">Manager</SelectItem>
                <SelectItem value="editor">Editor</SelectItem>
                <SelectItem value="viewer">Viewer</SelectItem>
              </SelectContent>
            </Select>
            <p v-if="formErrors.role" class="text-xs text-destructive mt-1">{{ formErrors.role }}</p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="editMemberDialogOpen = false">Cancel</Button>
          <Button @click="updateMember">
            <Check class="mr-2 h-4 w-4" />
            Update Role
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
