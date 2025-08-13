<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
const ziggyRoute = (window as any).route as (...args: any[]) => string;
</script>

<template>
  <AppLayout
      title="Appearance settings"
      :breadcrumbs="[
        { label: 'Profile settings', href: ziggyRoute('profile.edit') },
        { label: 'Appearance settings', href: ziggyRoute('appearance') }
      ]">
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
