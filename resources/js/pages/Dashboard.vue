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

// Extract the data from the resource wrapper
const dashboardData = computed(() => props.dashboard.data)

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
  <AppLayout title="Dashboard">
    <Head title="Dashboard" />

    <div class="flex-1 space-y-6 p-6">
      <!-- Welcome Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
          <p class="text-muted-foreground">
            Welcome back! Here's what's happening with {{ company.name }} today.
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Badge variant="outline" class="capitalize">
            {{ company.subscription_plan }} Plan
          </Badge>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Billboards -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Billboards</CardTitle>
            <MapPin class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ dashboardData.stats.total_billboards.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="dashboardData.stats.total_billboards.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
              <span :class="dashboardData.stats.total_billboards.trend === 'up' ? 'text-green-500' : 'text-red-500'">
                {{ dashboardData.stats.total_billboards.change > 0 ? '+' : '' }}{{ dashboardData.stats.total_billboards.change }}%
              </span>
              <span class="ml-1">from last month</span>
            </div>
          </CardContent>
        </Card>

        <!-- Active Contracts -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Contracts</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ dashboardData.stats.active_contracts.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="dashboardData.stats.active_contracts.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
              <span :class="dashboardData.stats.active_contracts.trend === 'up' ? 'text-green-500' : 'text-red-500'">
                {{ dashboardData.stats.active_contracts.change > 0 ? '+' : '' }}{{ dashboardData.stats.active_contracts.change }}%
              </span>
              <span class="ml-1">from last month</span>
            </div>
          </CardContent>
        </Card>

        <!-- Monthly Revenue -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">${{ dashboardData.stats.monthly_revenue.value }}</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="dashboardData.stats.monthly_revenue.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
              <span :class="dashboardData.stats.monthly_revenue.trend === 'up' ? 'text-green-500' : 'text-red-500'">
                {{ dashboardData.stats.monthly_revenue.change > 0 ? '+' : '' }}{{ dashboardData.stats.monthly_revenue.change }}%
              </span>
              <span class="ml-1">from last month</span>
            </div>
          </CardContent>
        </Card>

        <!-- Occupancy Rate -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Occupancy Rate</CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ dashboardData.stats.occupancy_rate.value }}%</div>
            <div class="flex items-center text-xs text-muted-foreground">
              <TrendingUp v-if="dashboardData.stats.occupancy_rate.trend === 'up'" class="mr-1 h-3 w-3 text-green-500" />
              <TrendingDown v-else class="mr-1 h-3 w-3 text-red-500" />
              <span :class="dashboardData.stats.occupancy_rate.trend === 'up' ? 'text-green-500' : 'text-red-500'">
                {{ dashboardData.stats.occupancy_rate.change > 0 ? '+' : '' }}{{ dashboardData.stats.occupancy_rate.change }}%
              </span>
              <span class="ml-1">from last month</span>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Section -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <!-- Revenue Trend Chart -->
        <Card class="col-span-4">
          <CardHeader>
            <CardTitle>Revenue Trend</CardTitle>
            <CardDescription>Monthly revenue over the past 12 months</CardDescription>
          </CardHeader>
          <CardContent>
            <VueApexCharts
              type="area"
              height="350"
              :options="revenueChartOptions"
              :series="dashboardData.charts.revenue_trend.series"
            />
          </CardContent>
        </Card>

        <!-- Billboard Status Chart -->
        <Card class="col-span-3">
          <CardHeader>
            <CardTitle>Billboard Status</CardTitle>
            <CardDescription>Current status distribution</CardDescription>
          </CardHeader>
          <CardContent>
            <VueApexCharts
              type="donut"
              height="300"
              :options="billboardStatusOptions"
              :series="dashboardData.charts.billboard_status.series"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Performance and Contract Status -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Monthly Performance -->
        <Card>
          <CardHeader>
            <CardTitle>Monthly Performance</CardTitle>
            <CardDescription>New contracts and revenue over the past 6 months</CardDescription>
          </CardHeader>
          <CardContent>
            <VueApexCharts
              type="line"
              height="350"
              :options="performanceChartOptions"
              :series="dashboardData.charts.monthly_performance.series"
            />
          </CardContent>
        </Card>

        <!-- Contract Status -->
        <Card>
          <CardHeader>
            <CardTitle>Contract Status</CardTitle>
            <CardDescription>Distribution of contract statuses</CardDescription>
          </CardHeader>
          <CardContent>
            <VueApexCharts
              type="pie"
              height="350"
              :options="contractStatusOptions"
              :series="dashboardData.charts.contract_status.series"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Recent Activities and Top Billboards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <!-- Recent Activities -->
        <Card class="col-span-4">
          <CardHeader>
            <CardTitle>Recent Activities</CardTitle>
            <CardDescription>Latest contract activities and updates</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="activity in dashboardData.recent_activities"
                :key="activity.id"
                class="flex items-center space-x-4 rounded-lg border p-3"
              >
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                  <Activity class="h-4 w-4 text-primary" />
                </div>
                <div class="flex-1 space-y-1">
                  <p class="text-sm font-medium">{{ activity.title }}</p>
                  <p class="text-xs text-muted-foreground">{{ activity.description }}</p>
                  <div class="flex items-center space-x-2 text-xs text-muted-foreground">
                    <span>{{ activity.created_at_human }}</span>
                    <span>•</span>
                    <span>by {{ activity.created_by }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium">${{ activity.amount }}</p>
                  <p class="text-xs text-muted-foreground">{{ activity.billboards_count }} billboard(s)</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Top Performing Billboards -->
        <Card class="col-span-3">
          <CardHeader>
            <CardTitle>Top Performing Billboards</CardTitle>
            <CardDescription>Highest revenue generating billboards</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="billboard in dashboardData.top_performing_billboards"
                :key="billboard.id"
                class="flex items-center space-x-4"
              >
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                  <Building2 class="h-4 w-4 text-blue-600" />
                </div>
                <div class="flex-1 space-y-1">
                  <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">{{ billboard.name }}</p>
                    <Badge :variant="getStatusBadgeVariant(billboard.status)" class="text-xs">
                      {{ billboard.status }}
                    </Badge>
                  </div>
                  <p class="text-xs text-muted-foreground">{{ billboard.location }}</p>
                  <p class="text-xs text-muted-foreground">{{ billboard.active_contracts }} active contract(s)</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium">${{ billboard.total_revenue }}</p>
                  <p class="text-xs text-muted-foreground">${{ billboard.monthly_rate }}/mo</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Upcoming Expirations -->
      <Card v-if="dashboardData.upcoming_expirations.length > 0">
        <CardHeader>
          <CardTitle class="flex items-center space-x-2">
            <AlertTriangle class="h-5 w-5 text-orange-500" />
            <span>Upcoming Contract Expirations</span>
          </CardTitle>
          <CardDescription>Contracts expiring within the next 30 days</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div
              v-for="contract in dashboardData.upcoming_expirations"
              :key="contract.id"
              class="flex items-center justify-between rounded-lg border p-3"
            >
              <div class="flex items-center space-x-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100">
                  <Calendar class="h-4 w-4 text-orange-600" />
                </div>
                <div>
                  <p class="text-sm font-medium">{{ contract.client_name }}</p>
                  <p class="text-xs text-muted-foreground">Contract {{ contract.contract_number }}</p>
                  <p class="text-xs text-muted-foreground">{{ contract.billboards_count }} billboard(s)</p>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <p class="text-sm font-medium">{{ contract.end_date }}</p>
                  <p class="text-xs text-muted-foreground">{{ contract.days_remaining }} days remaining</p>
                </div>
                <Badge :variant="getUrgencyBadgeVariant(contract.urgency)">
                  {{ contract.urgency }} priority
                </Badge>
                <p class="text-sm font-medium">${{ contract.total_amount }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Revenue Breakdown -->
      <Card>
        <CardHeader>
          <CardTitle>Revenue Breakdown</CardTitle>
          <CardDescription>Revenue analysis by billboard size and projections</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-3">
            <div class="space-y-2">
              <p class="text-sm font-medium">Current Month</p>
              <p class="text-2xl font-bold">${{ dashboardData.revenue_breakdown.current_month }}</p>
            </div>
            <div class="space-y-2">
              <p class="text-sm font-medium">Projected Annual</p>
              <p class="text-2xl font-bold">${{ dashboardData.revenue_breakdown.projected_annual }}</p>
            </div>
            <div class="space-y-2">
              <p class="text-sm font-medium">Revenue by Size</p>
              <div class="space-y-1">
                <div
                  v-for="item in dashboardData.revenue_breakdown.by_size"
                  :key="item.size"
                  class="flex justify-between text-xs"
                >
                  <span>{{ item.size }}</span>
                  <span>${{ item.revenue }}</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
