<template>
  <AppLayout title="Team Management">
    <div class="py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Team Management</h1>
            <p class="mt-2 text-gray-600">Manage your team members and their permissions</p>
          </div>
          <Button @click="showInviteModal = true">
            <Plus class="mr-2 h-4 w-4" />
            Invite User
          </Button>
        </div>

        <!-- Company Selector -->
        <CompanySelector :companies="$page.props.companies" :current-company="currentCompany" class="mb-6" />

        <!-- Team Members Table -->
        <Card>
          <CardHeader>
            <CardTitle>Team Members</CardTitle>
            <CardDescription> Manage access and permissions for your team </CardDescription>
          </CardHeader>
          <CardContent class="p-0">
            <TeamsTable
              :members="teamMembers"
              :current-user="$page.props.user"
              @edit-role="editRole"
              @remove-member="removeMember"
              @resend-invite="resendInvite"
            />
          </CardContent>
        </Card>

        <!-- Invite User Modal -->
        <InviteUserModal v-model:open="showInviteModal" @invite="handleInvite" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import CompanySelector from '@/Components/CompanySelector.vue';
import TeamsTable from '@/Components/TeamsTable.vue';
import InviteUserModal from '@/Components/InviteUserModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { Company, TeamMember } from '@/types';

interface Props {
  currentCompany: Company;
  teamMembers: TeamMember[];
}

defineProps<Props>();

const showInviteModal = ref(false);

const handleInvite = (data: { email: string; role: string; message?: string }) => {
  router.post(route('team.invite'), data, {
    onSuccess: () => {
      showInviteModal.value = false;
    },
  });
};

const editRole = (member: TeamMember) => {
  // Handle role editing
  console.log('Edit role for:', member);
};

const removeMember = (member: TeamMember) => {
  if (confirm(`Are you sure you want to remove ${member.name} from the team?`)) {
    router.delete(route('team.destroy', member.id));
  }
};

const resendInvite = (member: TeamMember) => {
  router.post(route('team.resend-invite', member.id));
};
</script>
