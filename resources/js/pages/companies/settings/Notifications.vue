<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySettingsLayout from '@/layouts/company/SettingsLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { useForm } from '@inertiajs/vue3'

interface Props {
  company: Record<string, any>
  options: Record<string, any>
}
const props = defineProps<Props>()

const form = useForm({
  section: 'notifications',
  notification_settings: {
    email_contracts: props.company?.notification_settings?.email_contracts ?? true,
    email_payments: props.company?.notification_settings?.email_payments ?? true,
    email_billboards: props.company?.notification_settings?.email_billboards ?? true,
    email_team: props.company?.notification_settings?.email_team ?? true,
    slack_webhook: props.company?.notification_settings?.slack_webhook || '',
  },
})

const submit = () => {
  form.post(r('companies.settings.update'), { preserveScroll: true })
}

const r = (name: string, params?: Record<string, any>, absolute = false): string =>
  ((window as any).route?.(name, params, absolute) as string) ?? '#'
</script>

<template>
  <AppLayout
    title="Company Settings — Notifications"
    :breadcrumbs="[
      { label: 'Companies', href: r('companies.index') },
      { label: 'Settings', href: r('companies.settings') },
      { label: 'Notifications', href: r('companies.settings.notifications') },
    ]"
  >
    <CompanySettingsLayout>
      <form @submit.prevent="submit">
        <input type="hidden" name="section" value="notifications" />
        <div class="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle>Notification Settings</CardTitle>
              <CardDescription>Configure when and how you receive notifications</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div class="space-y-4">
                <div class="flex items-center space-x-2">
                  <Checkbox id="email_contracts" v-model="form.notification_settings.email_contracts" />
                  <Label for="email_contracts"> Email notifications for contract changes </Label>
                </div>
                <div class="flex items-center space-x-2">
                  <Checkbox id="email_payments" v-model="form.notification_settings.email_payments" />
                  <Label for="email_payments"> Email notifications for payment updates </Label>
                </div>
                <div class="flex items-center space-x-2">
                  <Checkbox id="email_billboards" v-model="form.notification_settings.email_billboards" />
                  <Label for="email_billboards"> Email notifications for billboard changes </Label>
                </div>
                <div class="flex items-center space-x-2">
                  <Checkbox id="email_team" v-model="form.notification_settings.email_team" />
                  <Label for="email_team"> Email notifications for team changes </Label>
                </div>
              </div>

              <div>
                <Label for="slack_webhook">Slack Webhook URL (Optional)</Label>
                <Input id="slack_webhook" v-model="form.notification_settings.slack_webhook" type="url" placeholder="https://hooks.slack.com/services/..." class="mt-1" />
                <p class="mt-1 text-xs text-gray-500">Receive important notifications in your Slack channel</p>
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
              Save Notifications
            </Button>
          </div>
        </div>
      </form>
    </CompanySettingsLayout>
  </AppLayout>
</template>
