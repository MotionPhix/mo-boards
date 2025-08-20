<script setup lang="ts">
import { Activity, Building2, FileText, Shield, TrendingDown, TrendingUp } from 'lucide-vue-next';
import BaseDashboard, { type BaseDashboardProps } from './BaseDashboard.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

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
        <Card class="bg-card text-card-foreground">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Billboards</CardTitle>
            <Building2 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ sp.stats.total_billboards.value }}
            </div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp
                v-if="sp.stats.total_billboards.trend === 'up'"
                class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400"
              />

              <TrendingDown v-else
                class="mr-1 h-3 w-3 text-red-500 dark:text-red-400"
              />

              <span
                :class="sp.stats.total_billboards.change >= 0
                  ? 'text-emerald-500 dark:text-emerald-400'
                  : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.total_billboards.change > 0 ? '+' : '' }}{{ sp.stats.total_billboards.change }}%
              </span> from last month
            </div>
          </CardContent>
        </Card>

        <!-- Active Contracts Card -->
        <Card class="bg-card text-card-foreground">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Contracts</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ sp.stats.active_contracts.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="sp.stats.active_contracts.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
              <span
                :class="sp.stats.active_contracts.change >= 0
                ? 'text-emerald-500 dark:text-emerald-400'
                : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.active_contracts.change > 0 ? '+' : '' }}{{ sp.stats.active_contracts.change }}%
              </span> from last month
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Limited Access Message -->
      <div class="mt-6">
        <Card class="bg-muted/20 text-card-foreground">
          <CardContent class="pt-6">
            <div class="text-center">
              <Shield class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
              <h3 class="text-lg font-semibold mb-2">Limited Dashboard Access</h3>
              <p class="text-muted-foreground">
                Your current role provides limited access to dashboard statistics.
                Contact your company administrator for enhanced permissions.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Activities - Limited view -->
      <div class="mt-6">
        <Card class="bg-card text-card-foreground">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Activity class="h-4 w-4 text-primary" />
              Recent Activities
            </CardTitle>
            <CardDescription>Latest updates you can view</CardDescription>
          </CardHeader>

          <CardContent>
            <div class="space-y-4">
              <div
                v-for="activity in sp.recentActivities.slice(0, 3)"
                :key="activity.id" class="flex items-center space-x-4">
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
          </CardContent>
        </Card>
      </div>

      <!-- Billboard List - Basic view -->
      <div class="mt-6">
        <Card class="bg-card text-card-foreground">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Building2 class="h-4 w-4 text-primary" />
              Billboard Overview
            </CardTitle>
            <CardDescription>Current billboard status</CardDescription>
          </CardHeader>

          <CardContent>
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Billboard</TableHead>
                    <TableHead>Location</TableHead>
                    <TableHead>Active Contracts</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow
                    v-for="billboard in sp.topPerformingBillboards.slice(0, 5)"
                    :key="billboard.id" class="border-b border-border">
                    <TableCell>
                      <div class="font-medium">{{ billboard.name }}</div>
                      <div class="text-xs text-muted-foreground">{{ billboard.code }}</div>
                    </TableCell>

                    <TableCell>{{ billboard.location }}</TableCell>

                    <TableCell>
                      {{ billboard.active_contracts }}
                    </TableCell>

                    <TableCell>
                      <Badge :variant="sp.getStatusBadgeVariant(billboard.status)">
                        {{ billboard.status }}
                      </Badge>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <div v-if="sp.topPerformingBillboards.length === 0" class="py-4 text-center text-muted-foreground">
              No billboards data available
            </div>
          </CardContent>
        </Card>
      </div>
    </template>
  </BaseDashboard>
</template>
