<script setup lang="ts">
import BaseDashboard, { type BaseDashboardProps } from './BaseDashboard.vue'

const props = defineProps<BaseDashboardProps>()
</script>

<template>
  <BaseDashboard v-bind="props">
    <template #welcome-message>
      You have read-only access to view billboards and contracts for {{ props.company.name }}.
    </template>

    <template #dashboard-content="sp">
      <!-- Stats Cards - Viewer sees only basic stats -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Total Billboards Card -->
        <sp.Card class="bg-card text-card-foreground">
          <sp.CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <sp.CardTitle class="text-sm font-medium">Total Billboards</sp.CardTitle>
            <sp.Building2 class="h-4 w-4 text-muted-foreground" />
          </sp.CardHeader>
          <sp.CardContent>
            <div class="text-2xl font-bold">{{ sp.stats.total_billboards.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <sp.TrendingUp v-if="sp.stats.total_billboards.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
              <sp.TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
              <span :class="sp.stats.total_billboards.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.total_billboards.change > 0 ? '+' : '' }}{{ sp.stats.total_billboards.change }}%
              </span> from last month
            </div>
          </sp.CardContent>
        </sp.Card>

        <!-- Active Contracts Card -->
        <sp.Card class="bg-card text-card-foreground">
          <sp.CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <sp.CardTitle class="text-sm font-medium">Active Contracts</sp.CardTitle>
            <sp.FileText class="h-4 w-4 text-muted-foreground" />
          </sp.CardHeader>
          <sp.CardContent>
            <div class="text-2xl font-bold">{{ sp.stats.active_contracts.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <sp.TrendingUp v-if="sp.stats.active_contracts.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
              <sp.TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
              <span :class="sp.stats.active_contracts.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.active_contracts.change > 0 ? '+' : '' }}{{ sp.stats.active_contracts.change }}%
              </span> from last month
            </div>
          </sp.CardContent>
        </sp.Card>
      </div>

      <!-- Limited Access Message -->
      <div class="mt-6">
        <sp.Card class="bg-muted/20 text-card-foreground">
          <sp.CardContent class="pt-6">
            <div class="text-center">
              <sp.Shield class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
              <h3 class="text-lg font-semibold mb-2">Limited Dashboard Access</h3>
              <p class="text-muted-foreground">
                Your current role provides limited access to dashboard statistics.
                Contact your company administrator for enhanced permissions.
              </p>
            </div>
          </sp.CardContent>
        </sp.Card>
      </div>

      <!-- Recent Activities - Limited view -->
      <div class="mt-6">
        <sp.Card class="bg-card text-card-foreground">
          <sp.CardHeader>
            <sp.CardTitle class="flex items-center gap-2">
              <sp.Activity class="h-4 w-4 text-primary" />
              Recent Activities
            </sp.CardTitle>
            <sp.CardDescription>Latest updates you can view</sp.CardDescription>
          </sp.CardHeader>
          <sp.CardContent>
            <div class="space-y-4">
              <div v-for="activity in sp.recentActivities.slice(0, 3)" :key="activity.id" class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-primary rounded-full"></div>
                <div class="flex-1 space-y-1">
                  <p class="text-sm font-medium">{{ activity.title }}</p>
                  <p class="text-xs text-muted-foreground">
                    {{ activity.description }} • {{ activity.created_at_human }}
                  </p>
                </div>
                <!-- No amount or sensitive data shown -->
              </div>
            </div>
            <div v-if="sp.recentActivities.length === 0" class="py-4 text-center text-muted-foreground">
              No recent activities available
            </div>
          </sp.CardContent>
        </sp.Card>
      </div>

      <!-- Billboard List - Basic view -->
      <div class="mt-6">
        <sp.Card class="bg-card text-card-foreground">
          <sp.CardHeader>
            <sp.CardTitle class="flex items-center gap-2">
              <sp.Building2 class="h-4 w-4 text-primary" />
              Billboard Overview
            </sp.CardTitle>
            <sp.CardDescription>Current billboard status</sp.CardDescription>
          </sp.CardHeader>
          <sp.CardContent>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b text-muted-foreground">
                    <th class="text-left font-medium py-2">Billboard</th>
                    <th class="text-left font-medium py-2">Location</th>
                    <th class="text-center font-medium py-2">Active Contracts</th>
                    <th class="text-right font-medium py-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="billboard in sp.topPerformingBillboards.slice(0, 5)" :key="billboard.id" class="border-b border-border">
                    <td class="py-3">
                      <div class="font-medium">{{ billboard.name }}</div>
                      <div class="text-xs text-muted-foreground">{{ billboard.code }}</div>
                    </td>
                    <td class="py-3">{{ billboard.location }}</td>
                    <td class="py-3 text-center">{{ billboard.active_contracts }}</td>
                    <td class="py-3 text-right">
                      <sp.Badge :variant="sp.getStatusBadgeVariant(billboard.status)">
                        {{ billboard.status }}
                      </sp.Badge>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="sp.topPerformingBillboards.length === 0" class="py-4 text-center text-muted-foreground">
              No billboards data available
            </div>
          </sp.CardContent>
        </sp.Card>
      </div>
    </template>
  </BaseDashboard>
</template>
