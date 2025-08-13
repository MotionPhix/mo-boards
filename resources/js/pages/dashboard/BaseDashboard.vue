<script setup lang="ts">
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useTheme } from '../../composables/useTheme'
import {
  DollarSign,
  FileText,
  TrendingUp,
  TrendingDown,
  Activity,
  AlertTriangle,
  Eye,
  Building2,
  Users,
  Shield,
  Crown
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import VueApexCharts from 'vue3-apexcharts'

export interface BaseDashboardProps {
  dashboard: {
    stats: {
      total_billboards: { value: number; change: number; trend: string }
      active_contracts: { value: number; change: number; trend: string }
      monthly_revenue?: { value: string; raw_value: number; change: number; trend: string }
      occupancy_rate: { value: number; change: number; trend: string }
    }
    charts?: {
      revenue_trend?: { categories: string[]; series: any[] }
      billboard_status?: { series: number[]; labels: string[] }
      contract_status?: { series: number[]; labels: string[] }
      monthly_performance?: { categories: string[]; series: any[] }
    }
    recent_activities: any[]
    top_performing_billboards: any[]
    upcoming_expirations: any[]
    revenue_breakdown?: {
      current_month?: string
      by_dimension?: any[]
      projected_annual?: string
    }
  }
  company: {
    id: number
    name: string
    subscription_plan: string
  }
}

const props = defineProps<BaseDashboardProps>()

// Get page props for user and permissions
const page = usePage()
const user = computed(() => page.props.auth?.user)
const userAbilities = computed(() => page.props.auth?.user?.abilities || {})

// Use theme
const { isDark } = useTheme()

// User role detection computed properties
const userRole = computed(() => {
  if (!user.value) return null

  if (userAbilities.value.is_super_admin) return 'super_admin'
  if (userAbilities.value.is_company_owner) return 'company_owner'
  if (userAbilities.value.is_manager) return 'manager'
  if (userAbilities.value.is_editor) return 'editor'
  if (userAbilities.value.is_viewer) return 'viewer'

  return 'viewer' // Default fallback
})

const userDisplayName = computed(() => {
  const roleMap = {
    super_admin: 'Super Administrator',
    company_owner: 'Company Owner',
    manager: 'Manager',
    editor: 'Editor',
    viewer: 'Viewer'
  }
  return roleMap[userRole.value] || 'Team Member'
})

const roleIcon = computed(() => {
  const iconMap = {
    super_admin: Crown,
    company_owner: Building2,
    manager: Users,
    editor: FileText,
    viewer: Eye
  }
  return iconMap[userRole.value] || Eye
})

const roleColor = computed(() => {
  const colorMap = {
    super_admin: 'text-purple-600 dark:text-purple-400',
    company_owner: 'text-blue-600 dark:text-blue-400',
    manager: 'text-green-600 dark:text-green-400',
    editor: 'text-orange-600 dark:text-orange-400',
    viewer: 'text-gray-600 dark:text-gray-400'
  }
  return colorMap[userRole.value] || 'text-gray-600 dark:text-gray-400'
})

// Computed values for dashboard data
const stats = computed(() => props.dashboard.stats)
const charts = computed(() => props.dashboard.charts || {})
const recentActivities = computed(() => props.dashboard.recent_activities)
const topPerformingBillboards = computed(() => props.dashboard.top_performing_billboards)
const upcomingExpirations = computed(() => props.dashboard.upcoming_expirations)
const revenueBreakdown = computed(() => props.dashboard.revenue_breakdown)

// SEO meta data
const pageTitle = computed(() => `Dashboard - ${props.company.name} | BillboardPro`)
const pageDescription = computed(() => {
  const baseDescription = `Overview of ${props.company.name}'s billboard management dashboard. Track ${stats.value.total_billboards.value} billboards, ${stats.value.active_contracts.value} active contracts`
  const revenueText = stats.value.monthly_revenue ? `, and monthly revenue of ${stats.value.monthly_revenue.value}` : ''
  return `${baseDescription}${revenueText}.`
})

// Breadcrumbs for the dashboard page
const breadcrumbs = [
  { label: 'Dashboard' }
]

// Utility functions
const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'active': return 'default'
    case 'available': return 'secondary'
    case 'maintenance': return 'destructive'
    case 'removed': return 'outline'
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

// Chart options factory
const createRevenueChartOptions = (categories: string[]) => ({
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
    categories,
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
    mode: isDark.value ? 'dark' : 'light'
  },
  tooltip: {
    theme: isDark.value ? 'dark' : 'light',
    y: {
      formatter: (val: number) => `$${val.toLocaleString()}`
    }
  }
})

const createDonutChartOptions = (labels: string[]) => ({
  labels,
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
    mode: isDark.value ? 'dark' : 'light'
  }
})

// Export these for use in child components
defineExpose({
  // Data
  stats,
  charts,
  recentActivities,
  topPerformingBillboards,
  upcomingExpirations,
  revenueBreakdown,
  user,
  userAbilities,
  userRole,
  userDisplayName,
  roleIcon,
  roleColor,
  company: props.company,
  isDark,

  // Utilities
  getStatusBadgeVariant,
  getUrgencyBadgeVariant,
  formatCurrency,
  createRevenueChartOptions,
  createDonutChartOptions,

  // Components (re-export for convenience)
  DollarSign,
  FileText,
  TrendingUp,
  TrendingDown,
  Activity,
  AlertTriangle,
  Eye,
  Building2,
  Users,
  Shield,
  Crown,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Badge,
  VueApexCharts
})
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
    <meta name="robots" content="noindex, nofollow" />
  </Head>

  <AppLayout :title="'Dashboard'" :breadcrumbs="breadcrumbs">
    <div class="max-w-5xl">
      <!-- Dashboard Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Dashboard</h1>
          <p class="text-muted-foreground">
            Welcome back, {{ user?.name }}! Here's what's happening with {{ company.name }}.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <!-- User Role Badge -->
          <Badge variant="outline" class="text-xs flex items-center gap-1">
            <component :is="roleIcon" class="h-3 w-3" :class="roleColor" />
            {{ userDisplayName }}
          </Badge>
          <Badge variant="outline" class="text-xs">
            {{ company.subscription_plan }}
          </Badge>
        </div>
      </div>

      <!-- Role-based Welcome Message -->
      <div v-if="userRole" class="mb-6 p-4 rounded-lg border bg-card">
        <div class="flex items-center gap-2 mb-2">
          <component :is="roleIcon" class="h-5 w-5" :class="roleColor" />
          <h3 class="font-semibold">{{ userDisplayName }} Dashboard</h3>
        </div>
        <p class="text-sm text-muted-foreground">
          <slot name="welcome-message">
            <!-- Default welcome message for unknown roles -->
            You have access to view dashboard information for {{ company.name }}.
          </slot>
        </p>
      </div>

      <!-- Main Dashboard Content Slot with slot props -->
      <slot
        name="dashboard-content"
        :stats="stats"
        :charts="charts"
        :recent-activities="recentActivities"
        :top-performing-billboards="topPerformingBillboards"
        :upcoming-expirations="upcomingExpirations"
        :revenue-breakdown="revenueBreakdown"
        :user="user"
        :user-abilities="userAbilities"
        :user-role="userRole"
        :user-display-name="userDisplayName"
        :role-icon="roleIcon"
        :role-color="roleColor"
        :company="company"
        :is-dark="isDark"
        :get-status-badge-variant="getStatusBadgeVariant"
        :get-urgency-badge-variant="getUrgencyBadgeVariant"
        :format-currency="formatCurrency"
        :create-revenue-chart-options="createRevenueChartOptions"
        :create-donut-chart-options="createDonutChartOptions"
        :Card="Card"
        :CardContent="CardContent"
        :CardDescription="CardDescription"
        :CardHeader="CardHeader"
        :CardTitle="CardTitle"
        :Badge="Badge"
        :VueApexCharts="VueApexCharts"
        :DollarSign="DollarSign"
        :FileText="FileText"
        :TrendingUp="TrendingUp"
        :TrendingDown="TrendingDown"
        :Activity="Activity"
        :AlertTriangle="AlertTriangle"
        :Eye="Eye"
        :Building2="Building2"
        :Users="Users"
        :Shield="Shield"
        :Crown="Crown"
      />
    </div>
  </AppLayout>
</template>
