<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { computed } from 'vue'

const page = usePage()
const member = computed(() => page.props.member)

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
</script>

<template>
  <Head>
    <title>Team Member - {{ member.name }}</title>
    <meta name="description" :content="`Details for team member ${member.name}`" />
  </Head>
  <AppLayout title="Team Member Details">
    <div class="max-w-xl mx-auto mt-8">
      <Card>
        <CardHeader>
          <CardTitle>Team Member Details</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex items-center gap-4 mb-6">
            <Avatar>
              <AvatarImage v-if="member.avatar" :src="member.avatar" alt="Avatar" />
              <AvatarFallback>{{ member.name.charAt(0) }}</AvatarFallback>
            </Avatar>
            <div>
              <div class="font-bold text-lg">{{ member.name }}</div>
              <div class="text-muted-foreground">{{ member.email }}</div>
            </div>
          </div>
          <div class="mb-4">
            <Badge>{{ getRoleDisplay(member.role) }}</Badge>
          </div>
          <div class="mb-2">
            <span class="font-medium">Joined:</span> {{ member.joined_at }}
          </div>
          <div v-if="member.is_owner" class="mb-2">
            <span class="font-medium text-primary">Company Owner</span>
          </div>
          <div class="mb-2">
            <span class="font-medium">Phone:</span>
            <span v-if="member.phone">{{ member.phone }}</span>
            <span v-else class="text-muted-foreground">Not provided</span>
          </div>
          <div class="mb-2">
            <span class="font-medium">Email Verified:</span>
            <span v-if="member.email_verified_at">Yes ({{ member.email_verified_at }})</span>
            <span v-else class="text-muted-foreground">No</span>
          </div>
          <div class="mb-2">
            <span class="font-medium">Last Active:</span>
            <span v-if="member.last_active_at">{{ member.last_active_at }}</span>
            <span v-else class="text-muted-foreground">Unknown</span>
          </div>
          <div class="mb-2">
            <span class="font-medium">Theme Preference:</span>
            <span v-if="member.theme_preference">{{ member.theme_preference }}</span>
            <span v-else class="text-muted-foreground">Default</span>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
