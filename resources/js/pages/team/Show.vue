<template>
  <Head>
    <title>{{ pageTitle }}</title>
    <meta name="description" :content="pageDescription" />
  </Head>

  <AppLayout :title="pageTitle" :breadcrumbs="breadcrumbs">
    <div class="max-w-4xl">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Team Member Details</h1>
          <p class="text-muted-foreground">
            View details and permissions for {{ member.name }}
          </p>
        </div>

        <div class="flex items-center gap-4">
          <!-- Edit button -->
          <ModalLink
            v-if="member.can.edit"
            :href="route('team.edit-modal', member.id)">
            <Button variant="outline">
              <Edit class="h-4 w-4 mr-2" />
              Edit Member
            </Button>
          </ModalLink>

          <!-- Delete button -->
          <Button
            v-if="member.can.delete"
            @click="removeMember"
            variant="destructive">
            <Trash2 class="h-4 w-4 mr-2" />
            Remove Member
          </Button>
        </div>
      </div>

      <!-- Member Profile Card -->
      <Card class="bg-card text-card-foreground">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <User class="h-5 w-5 text-primary" />
            Profile Information
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex items-start gap-6">
            <Avatar class="h-20 w-20">
              <AvatarImage v-if="member.avatar" :src="member.avatar" :alt="member.name" />
              <AvatarFallback>{{ member.name.charAt(0) }}</AvatarFallback>
            </Avatar>

            <div class="flex-1 space-y-4">
              <div>
                <h3 class="font-medium">{{ member.name }}</h3>
                <p class="text-sm text-muted-foreground">{{ member.email }}</p>
              </div>

              <div class="flex items-center gap-2">
                <Badge :variant="getRoleBadgeVariant(member.role.name)" class="flex items-center gap-1">
                  <component :is="getRoleIcon(member.role.name)" class="h-3 w-3" />
                  {{ getRoleDisplay(member.role.name) }}
                </Badge>

                <span class="text-sm text-muted-foreground">Joined {{ member.joined_at }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Role & Permissions Card -->
      <Card class="mt-8 bg-card text-card-foreground">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <ShieldCheck class="h-5 w-5 text-primary" />
            Role & Permissions
          </CardTitle>
          <CardDescription>
            {{ member.role.description }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-6">
            <div class="grid gap-4">
              <h4 class="text-sm font-medium">Granted Permissions</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div v-for="permission in member.role.permissions" :key="permission" class="flex items-center gap-2">
                  <Check class="h-4 w-4 text-primary" />
                  <span class="text-sm">{{ formatPermission(permission) }}</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import {
  User, Check, Trash2,
  Pencil as Edit, ShieldCheck, Shield
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardDescription, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { ModalLink } from '@inertiaui/modal-vue'

interface Props {
  member: {
    id: number
    name: string
    email: string
    role: {
      name: string
      description: string
      permissions: string[]
    }
    is_owner: boolean
    avatar: string
    joined_at: string
    can: {
      edit: boolean
      delete: boolean
    }
  }
  company: {
    id: number
    name: string
  }
}

const props = defineProps<Props>()

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

const getRoleDisplay = (role: string | null) => {
  if (!role) return 'Unknown'

  const roleDisplay = {
    'owner': 'Owner',
    'company_owner': 'Owner',
    'admin': 'Administrator',
    'manager': 'Manager',
    'member': 'Member',
    'editor': 'Editor',
    'viewer': 'Viewer'
  }[role]

  return roleDisplay || role.charAt(0).toUpperCase() + role.slice(1)
}

// Role icon
const getRoleIcon = (role: string) => {
  return role === 'owner' || role === 'admin'
    ? ShieldCheck
    : Shield
}

// Format permission string for display
const formatPermission = (permission: string) => {
  return permission
    .split('.')
    .map(part => part
      .split('_')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')
    )
    .join(' - ')
}

// Breadcrumbs
const breadcrumbs = [
  { label: 'Dashboard', href: route('dashboard') },
  { label: 'Team Management', href: route('team.index') },
  { label: props.member.name }
]

// SEO metadata
const pageTitle = computed(() => `${props.member.name} - Team Member Details - ${props.company.name}`)
const pageDescription = computed(() => `View details and permissions for team member ${props.member.name} at ${props.company.name}.`)

// Remove member
const removeMember = () => {
  if (confirm(`Are you sure you want to remove ${props.member.name} from the team?`)) {
    router.delete(route('team.destroy', { member: props.member.id }), {
      onSuccess: () => {
        toast({
          title: 'Team member removed',
          description: `${props.member.name} has been removed from the team`,
        })
        router.visit(route('team.index'))
      }
    })
  }
}</template>
