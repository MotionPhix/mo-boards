<script setup lang="ts">
import BaseDashboard, { type BaseDashboardProps } from './BaseDashboard.vue'
import { Activity, AlertTriangle, Building2, DollarSign, FileText, TrendingDown, TrendingUp } from 'lucide-vue-next'
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card'
import VueApexCharts from 'vue3-apexcharts'
import { Badge } from '@/components/ui/badge'

const props = defineProps<BaseDashboardProps>()
</script>

<template>
  <BaseDashboard v-bind="props">
    <template #welcome-message>
      You have full access to manage {{ company.name }} and all its resources.
    </template>

    <template #dashboard-content="sp">
      <!-- Stats Cards - Company Owner sees everything for their company -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Billboards Card -->
        <Card class="bg-card text-card-foreground">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Billboards</CardTitle>
            <Building2 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ sp.stats.total_billboards.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="sp.stats.total_billboards.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
              <span :class="sp.stats.total_billboards.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
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
              <span :class="sp.stats.active_contracts.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.active_contracts.change > 0 ? '+' : '' }}{{ sp.stats.active_contracts.change }}%
              </span> from last month
            </div>
          </CardContent>
        </Card>

        <!-- Monthly Revenue Card -->
        <Card v-if="sp.stats.monthly_revenue" class="bg-card text-card-foreground">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ sp.stats.monthly_revenue.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="sp.stats.monthly_revenue.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
              <span :class="sp.stats.monthly_revenue.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.monthly_revenue.change > 0 ? '+' : '' }}{{ sp.stats.monthly_revenue.change }}%
              </span> from last month
            </div>
          </CardContent>
        </Card>

        <!-- Occupancy Rate Card -->
        <Card class="bg-card text-card-foreground">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Occupancy Rate</CardTitle>
            <Activity class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ sp.stats.occupancy_rate.value }}%</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="sp.stats.occupancy_rate.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
              <span :class="sp.stats.occupancy_rate.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
                {{ sp.stats.occupancy_rate.change > 0 ? '+' : '' }}{{ sp.stats.occupancy_rate.change }}%
              </span> from last month
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Section -->
      <div v-if="sp.charts" class="grid gap-6 md:grid-cols-2 lg:grid-cols-7 mt-6">
        <!-- Revenue Trend Chart -->
        <Card v-if="sp.charts.revenue_trend" class="col-span-full md:col-span-4 bg-card text-card-foreground">
          <CardHeader>
            <CardTitle>Revenue Trend</CardTitle>
            <CardDescription>Monthly revenue performance over time</CardDescription>
          </CardHeader>
          <CardContent class="pl-2">
            <VueApexCharts
              type="area"
              height="350"
              :options="sp.createRevenueChartOptions(sp.charts.revenue_trend.categories)"
              :series="sp.charts.revenue_trend.series"
            />
          </CardContent>
        </Card>

        <!-- Billboard Status Chart -->
        <Card v-if="sp.charts.billboard_status" class="col-span-full md:col-span-3 bg-card text-card-foreground">
          <CardHeader>
            <CardTitle>Billboard Status</CardTitle>
            <CardDescription>Current status distribution</CardDescription>
          </CardHeader>
          <CardContent>
            <VueApexCharts
              type="donut"
              height="350"
              :options="sp.createDonutChartOptions(sp.charts.billboard_status.labels)"
              :series="sp.charts.billboard_status.series"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Recent Activities and Upcoming Expirations -->
      <div class="grid gap-6 md:grid-cols-2 mt-6">
        <!-- Recent Activities -->
        <Card class="bg-card text-card-foreground">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Activity class="h-4 w-4 text-primary" />
              Recent Activities
            </CardTitle>
            <CardDescription>Latest updates and changes</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="activity in sp.recentActivities.slice(0, 5)" :key="activity.id" class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-primary rounded-full"></div>
                <div class="flex-1 space-y-1">
                  <p class="text-sm font-medium">{{ activity.title }}</p>
                  <p class="text-xs text-muted-foreground">
                    {{ activity.description }} • {{ activity.created_at_human }}
                  </p>
                </div>
                <Badge v-if="activity.amount">
                  {{ sp.formatCurrency(activity.amount) }}
                </Badge>
              </div>
            </div>
            <div v-if="sp.recentActivities.length === 0" class="py-4 text-center text-muted-foreground">
              No recent activities found
            </div>
          </CardContent>
        </Card>

        <!-- Upcoming Expirations -->
        <Card class="bg-card text-card-foreground">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <AlertTriangle class="h-4 w-4 text-warning" />
              Upcoming Expirations
            </CardTitle>
            <CardDescription>Contracts expiring soon</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="expiration in sp.upcomingExpirations.slice(0, 5)" :key="expiration.id" class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">{{ expiration.contract_number }}</p>
                  <p class="text-xs text-muted-foreground">{{ expiration.client_name }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium">{{ expiration.end_date }}</p>
                  <Badge :variant="sp.getUrgencyBadgeVariant(expiration.urgency)" class="text-xs">
                    {{ expiration.days_remaining }} days
                  </Badge>
                </div>
              </div>
            </div>
            <div v-if="sp.upcomingExpirations.length === 0" class="py-4 text-center text-muted-foreground">
              No upcoming expirations
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Top Performing Billboards -->
      <div class="mt-6">
        <Card class="bg-card text-card-foreground">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Building2 class="h-4 w-4 text-primary" />
              Top Performing Billboards
            </CardTitle>
            <CardDescription>Billboards generating the most revenue</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b text-muted-foreground">
                    <th class="text-left font-medium py-2">Billboard</th>
                    <th class="text-left font-medium py-2">Location</th>
                    <th class="text-center font-medium py-2">Monthly Rate</th>
                    <th class="text-center font-medium py-2">Active Contracts</th>
                    <th class="text-right font-medium py-2">Revenue</th>
                    <th class="text-right font-medium py-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="billboard in sp.topPerformingBillboards" :key="billboard.id" class="border-b border-border">
                    <td class="py-3">
                      <div class="font-medium">{{ billboard.name }}</div>
                      <div class="text-xs text-muted-foreground">{{ billboard.code }}</div>
                    </td>
                    <td class="py-3">{{ billboard.location }}</td>
                    <td class="py-3 text-center">{{ billboard.monthly_rate }}</td>
                    <td class="py-3 text-center">{{ billboard.active_contracts }}</td>
                    <td class="py-3 text-right font-medium">{{ billboard.total_revenue || 'N/A' }}</td>
                    <td class="py-3 text-right">
                      <Badge :variant="sp.getStatusBadgeVariant(billboard.status)">
                        {{ billboard.status }}
                      </Badge>
                    </td>
                  </tr>
                </tbody>
              </table>
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
