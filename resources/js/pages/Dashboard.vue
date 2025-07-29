<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
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
        by_size: any[]
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

// Computed values for dashboard data
const stats = computed(() => props.dashboard.data.stats)
const charts = computed(() => props.dashboard.data.charts)
const recentActivities = computed(() => props.dashboard.data.recent_activities)
const topPerformingBillboards = computed(() => props.dashboard.data.top_performing_billboards)
const upcomingExpirations = computed(() => props.dashboard.data.upcoming_expirations)
const revenueBreakdown = computed(() => props.dashboard.data.revenue_breakdown)

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
    <!-- Dashboard Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
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
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Billboards</CardTitle>
          <Building2 class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.total_billboards.value }}</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.total_billboards.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
            {{ stats.total_billboards.change > 0 ? '+' : '' }}{{ stats.total_billboards.change }}% from last month
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active Contracts</CardTitle>
          <FileText class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.active_contracts.value }}</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.active_contracts.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
            {{ stats.active_contracts.change > 0 ? '+' : '' }}{{ stats.active_contracts.change }}% from last month
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
          <DollarSign class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.monthly_revenue.value }}</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.monthly_revenue.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
            {{ stats.monthly_revenue.change > 0 ? '+' : '' }}{{ stats.monthly_revenue.change }}% from last month
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Occupancy Rate</CardTitle>
          <Activity class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.occupancy_rate.value }}%</div>
          <div class="flex items-center text-xs text-muted-foreground">
            <TrendingUp v-if="stats.occupancy_rate.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
            <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
            {{ stats.occupancy_rate.change > 0 ? '+' : '' }}{{ stats.occupancy_rate.change }}% from last month
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
      <Card class="col-span-4">
        <CardHeader>
          <CardTitle>Revenue Trend</CardTitle>
          <CardDescription>Monthly revenue performance over time</CardDescription>
        </CardHeader>
        <CardContent class="pl-2">
          <VueApexCharts
            type="area"
            height="350"
            :options="{
              chart: { toolbar: { show: false } },
              dataLabels: { enabled: false },
              stroke: { curve: 'smooth' },
              xaxis: { categories: charts.revenue_trend.categories },
              theme: { mode: 'light' }
            }"
            :series="charts.revenue_trend.series"
          />
        </CardContent>
      </Card>

      <Card class="col-span-3">
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
              legend: { position: 'bottom' },
              theme: { mode: 'light' }
            }"
            :series="charts.billboard_status.series"
          />
        </CardContent>
      </Card>
    </div>

    <!-- Recent Activities and Upcoming Expirations -->
    <div class="grid gap-4 md:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Activity class="h-4 w-4" />
            Recent Activities
          </CardTitle>
          <CardDescription>Latest updates and changes</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="activity in recentActivities.slice(0, 5)" :key="activity.id" class="flex items-center space-x-4">
              <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
              <div class="flex-1 space-y-1">
                <p class="text-sm font-medium">{{ activity.description }}</p>
                <p class="text-xs text-muted-foreground">{{ activity.created_at }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <AlertTriangle class="h-4 w-4 text-orange-500" />
            Upcoming Expirations
          </CardTitle>
          <CardDescription>Contracts expiring soon</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="expiration in upcomingExpirations.slice(0, 5)" :key="expiration.id" class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium">{{ expiration.billboard_title }}</p>
                <p class="text-xs text-muted-foreground">{{ expiration.client_name }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium">{{ expiration.expires_at }}</p>
                <Badge variant="outline" class="text-xs">
                  {{ expiration.days_remaining }} days
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
