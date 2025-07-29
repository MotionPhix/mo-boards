<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useTheme } from '../composables/useTheme'
import {
  DollarSign,
  FileText,
  MapPin,
  TrendingUp,
  TrendingDown,
  Activity,
  Calendar,
  AlertTriangle,
  Eye,
  Building2
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import VueApexCharts from 'vue3-apexcharts'

interface DashboardProps {
  dashboard: {
    data: {
      stats: {
        total_billboards: { value: number; change: number; trend: string }
        active_contracts: { value: number; change: number; trend: string }
        monthly_revenue: { value: string; raw_value: number; change: number; trend: string }
        occupancy_rate: { value: number; change: number; trend: string }
      }
      charts: {
        revenue_trend: { categories: string[]; series: any[] }
        billboard_status: { series: number[]; labels: string[] }
        contract_status: { series: number[]; labels: string[] }
        monthly_performance: { categories: string[]; series: any[] }
      }
      recent_activities: any[]
      top_performing_billboards: any[]
      upcoming_expirations: any[]
      revenue_breakdown: {
        current_month: string
        by_dimension: any[]
        projected_annual: string
      }
    }
  }
  company: {
    id: number
    name: string
    subscription_plan: string
  }
}

const props = defineProps<DashboardProps>()

// Use theme
const { isDark, toggleTheme } = useTheme()

// Computed values for dashboard data
const stats = computed(() => props.dashboard.data.stats)
const charts = computed(() => props.dashboard.data.charts)
const recentActivities = computed(() => props.dashboard.data.recent_activities)
const topPerformingBillboards = computed(() => props.dashboard.data.top_performing_billboards)
const upcomingExpirations = computed(() => props.dashboard.data.upcoming_expirations)
const revenueBreakdown = computed(() => props.dashboard.data.revenue_breakdown)
const dashboardData = computed(() => props.dashboard.data)

// SEO meta data
const pageTitle = computed(() => `Dashboard - ${props.company.name} | BillboardPro`)
const pageDescription = computed(() =>
  `Overview of ${props.company.name}'s billboard management dashboard. Track ${stats.value.total_billboards.value} billboards, ${stats.value.active_contracts.value} active contracts, and monthly revenue of ${stats.value.monthly_revenue.value}.`
)

// Breadcrumbs for the dashboard page
const breadcrumbs = [
  { label: 'Dashboard' }
]

// Chart options
const revenueChartOptions = computed(() => ({
  chart: {
    type: 'area',
    height: 350,
    toolbar: { show: false },
    background: 'transparent',
  },
  theme: {
    mode: 'light', // Will be dynamic based on theme
  },
  dataLabels: { enabled: false },
  stroke: {
    curve: 'smooth',
    width: 2,
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.7,
      opacityTo: 0.3,
    },
  },
  colors: ['#3b82f6'],
  xaxis: {
    categories: dashboardData.value.charts.revenue_trend.categories,
  },
  yaxis: {
    labels: {
      formatter: (value: number) => `$${value.toLocaleString()}`,
    },
  },
  tooltip: {
    y: {
      formatter: (value: number) => `$${value.toLocaleString()}`,
    },
  },
  grid: {
    borderColor: '#e5e7eb',
  },
}))

const billboardStatusOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 300,
  },
  labels: dashboardData.value.charts.billboard_status.labels,
  colors: ['#10b981', '#3b82f6', '#f59e0b'],
  legend: {
    position: 'bottom',
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
      },
    },
  },
  dataLabels: {
    enabled: true,
    formatter: (val: number) => `${val.toFixed(1)}%`,
  },
}))

const contractStatusOptions = computed(() => ({
  chart: {
    type: 'pie',
    height: 300,
  },
  labels: dashboardData.value.charts.contract_status.labels,
  colors: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
  legend: {
    position: 'bottom',
  },
  dataLabels: {
    enabled: true,
    formatter: (val: number) => `${val.toFixed(1)}%`,
  },
}))

const performanceChartOptions = computed(() => ({
  chart: {
    type: 'line',
    height: 350,
    toolbar: { show: false },
  },
  stroke: {
    width: [0, 4],
    curve: 'smooth',
  },
  plotOptions: {
    bar: {
      columnWidth: '50%',
    },
  },
  colors: ['#3b82f6', '#10b981'],
  xaxis: {
    categories: dashboardData.value.charts.monthly_performance.categories,
  },
  yaxis: [
    {
      title: {
        text: 'New Contracts',
      },
    },
    {
      opposite: true,
      title: {
        text: 'Revenue ($)',
      },
      labels: {
        formatter: (value: number) => `$${value.toLocaleString()}`,
      },
    },
  ],
  tooltip: {
    shared: true,
    intersect: false,
    y: {
      formatter: (value: number, { seriesIndex }: any) => {
        return seriesIndex === 0 ? `${value} contracts` : `$${value.toLocaleString()}`
      },
    },
  },
  legend: {
    position: 'top',
  },
}))

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'active': return 'default'
    case 'maintenance': return 'secondary'
    case 'inactive': return 'destructive'
    default: return 'outline'
  }
}

const getUrgencyBadgeVariant = (urgency: string) => {
  switch (urgency) {
    case 'high': return 'destructive'
    case 'medium': return 'secondary'
    case 'low': return 'default'
    default: return 'outline'
  }
}

const formatCurrency = (amount: string | number) => {
  const value = typeof amount === 'string' ? parseFloat(amount.replace(/,/g, '')) : amount
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
}
</script>

<template>
  <!-- SEO Head Tags -->
  <Head>
    <title>{{ pageTitle }}</title>
    <meta name="description" :content="pageDescription" />
    <meta property="og:title" :content="pageTitle" />
    <meta property="og:description" :content="pageDescription" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" :content="pageTitle" />
    <meta name="twitter:description" :content="pageDescription" />
    <meta name="robots" content="noindex, nofollow" /> <!-- Private dashboard should not be indexed -->
  </Head>

  <AppLayout :title="'Dashboard'" :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-4xl w-full px-4 sm:px-6 lg:px-8">
      <!-- Dashboard Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Dashboard</h1>
          <p class="text-muted-foreground">
            Welcome back! Here's what's happening with {{ company.name }}.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Badge variant="outline" class="text-xs">
            {{ company.subscription_plan }}
          </Badge>
        </div>
      </div>

      <!-- Stats Cards -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card class="bg-card text-card-foreground">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Billboards</CardTitle>
          <Building2 class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.total_billboards.value }}</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.total_billboards.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
            <span :class="stats.total_billboards.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
              {{ stats.total_billboards.change > 0 ? '+' : '' }}{{ stats.total_billboards.change }}%
            </span> from last month
          </div>
        </CardContent>
      </Card>

      <Card class="bg-card text-card-foreground">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active Contracts</CardTitle>
          <FileText class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.active_contracts.value }}</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.active_contracts.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
            <span :class="stats.active_contracts.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
              {{ stats.active_contracts.change > 0 ? '+' : '' }}{{ stats.active_contracts.change }}%
            </span> from last month
          </div>
        </CardContent>
      </Card>

      <Card class="bg-card text-card-foreground">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
          <DollarSign class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.monthly_revenue.value }}</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.monthly_revenue.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
            <span :class="stats.monthly_revenue.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
              {{ stats.monthly_revenue.change > 0 ? '+' : '' }}{{ stats.monthly_revenue.change }}%
            </span> from last month
          </div>
        </CardContent>
      </Card>

      <Card class="bg-card text-card-foreground">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Occupancy Rate</CardTitle>
          <Activity class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.occupancy_rate.value }}%</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.occupancy_rate.trend === 'up'" class="mr-1 h-3 w-3 text-emerald-500 dark:text-emerald-400" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500 dark:text-red-400" />
            <span :class="stats.occupancy_rate.change >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'">
              {{ stats.occupancy_rate.change > 0 ? '+' : '' }}{{ stats.occupancy_rate.change }}%
            </span> from last month
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7 mt-6">
      <Card class="col-span-full md:col-span-4 bg-card text-card-foreground">
        <CardHeader>
          <CardTitle>Revenue Trend</CardTitle>
          <CardDescription>Monthly revenue performance over time</CardDescription>
        </CardHeader>
        <CardContent class="pl-2">
          <VueApexCharts
            type="area"
            height="350"
            :options="{
              chart: { 
                toolbar: { show: false },
                background: 'transparent',
                fontFamily: 'inherit'
              },
              dataLabels: { enabled: false },
              stroke: { 
                curve: 'smooth',
                width: 3
              },
              fill: {
                type: 'gradient',
                gradient: {
                  shadeIntensity: 1,
                  opacityFrom: 0.7,
                  opacityTo: 0.3,
                  stops: [0, 90, 100]
                }
              },
              colors: ['#3b82f6'],
              xaxis: { 
                categories: charts.revenue_trend.categories,
                labels: {
                  style: {
                    cssClass: 'text-xs text-muted-foreground'
                  }
                }
              },
              yaxis: {
                labels: {
                  formatter: (val: number) => `$${val.toLocaleString()}`,
                  style: {
                    cssClass: 'text-xs text-muted-foreground'
                  }
                }
              },
              grid: {
                borderColor: 'var(--border)',
                strokeDashArray: 4,
              },
              theme: { 
                mode: isDark() ? 'dark' : 'light' 
              },
              tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                  formatter: (val: number) => `$${val.toLocaleString()}`
                }
              }
            }"
            :series="charts.revenue_trend.series"
          />
        </CardContent>
      </Card>

      <Card class="col-span-full md:col-span-3 bg-card text-card-foreground">
        <CardHeader>
          <CardTitle>Billboard Status</CardTitle>
          <CardDescription>Current status distribution</CardDescription>
        </CardHeader>
        <CardContent>
          <VueApexCharts
            type="donut"
            height="350"
            :options="{
              labels: charts.billboard_status.labels,
              colors: ['#10b981', '#3b82f6', '#f59e0b'],
              legend: { 
                position: 'bottom',
                fontFamily: 'inherit',
                fontSize: '14px',
              },
              plotOptions: {
                pie: {
                  donut: {
                    size: '70%',
                    labels: {
                      show: true,
                      total: {
                        showAlways: true,
                        show: true,
                        label: 'Total',
                        formatter: function (w: any) {
                          return w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0)
                        }
                      }
                    }
                  }
                }
              },
              dataLabels: {
                enabled: true,
                formatter: (val: number) => `${val.toFixed(1)}%`,
              },
              theme: { 
                mode: isDark() ? 'dark' : 'light' 
              }
            }"
            :series="charts.billboard_status.series"
          />
        </CardContent>
      </Card>
    </div>

    <!-- Recent Activities and Upcoming Expirations -->
    <div class="grid gap-6 md:grid-cols-2 mt-6">
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
            <div v-for="activity in recentActivities.slice(0, 5)" :key="activity.id" class="flex items-center space-x-4">
              <div class="w-2 h-2 bg-primary rounded-full"></div>
              <div class="flex-1 space-y-1">
                <p class="text-sm font-medium">{{ activity.title }}</p>
                <p class="text-xs text-muted-foreground">
                  {{ activity.description }} • {{ activity.created_at_human }}
                </p>
              </div>
              <Badge>
                {{ formatCurrency(activity.amount) }}
              </Badge>
            </div>
          </div>
          <div v-if="recentActivities.length === 0" class="py-4 text-center text-muted-foreground">
            No recent activities found
          </div>
        </CardContent>
      </Card>

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
            <div v-for="expiration in upcomingExpirations.slice(0, 5)" :key="expiration.id" class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium">{{ expiration.contract_number }}</p>
                <p class="text-xs text-muted-foreground">{{ expiration.client_name }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium">{{ expiration.end_date }}</p>
                <Badge :variant="getUrgencyBadgeVariant(expiration.urgency)" class="text-xs">
                  {{ expiration.days_remaining }} days
                </Badge>
              </div>
            </div>
          </div>
          <div v-if="upcomingExpirations.length === 0" class="py-4 text-center text-muted-foreground">
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
                <tr v-for="billboard in topPerformingBillboards" :key="billboard.id" class="border-b border-border">
                  <td class="py-3">
                    <div class="font-medium">{{ billboard.name }}</div>
                    <div class="text-xs text-muted-foreground">{{ billboard.code }}</div>
                  </td>
                  <td class="py-3">{{ billboard.location }}</td>
                  <td class="py-3 text-center">{{ billboard.monthly_rate }}</td>
                  <td class="py-3 text-center">{{ billboard.active_contracts }}</td>
                  <td class="py-3 text-right font-medium">{{ billboard.total_revenue }}</td>
                  <td class="py-3 text-right">
                    <Badge :variant="getStatusBadgeVariant(billboard.status)">
                      {{ billboard.status }}
                    </Badge>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="topPerformingBillboards.length === 0" class="py-4 text-center text-muted-foreground">
            No billboards data available
          </div>
        </CardContent>
      </Card>
    </div>
    </div>
  </AppLayout>
</template>
