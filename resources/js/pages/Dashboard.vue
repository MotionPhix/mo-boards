<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { DollarSign, FileText, MapPin, TrendingUp } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import CompanySwitcher from '@/components/CompanySwitcher.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Modal } from '@/components/ui/modal'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const showCreateCompanyModal = ref(false)

const createCompanyForm = useForm({
  name: '',
  industry: '',
})

// Mock data - replace with real data from props or API
const recentContracts = ref([
  {
    id: 1,
    client_name: 'Acme Corp',
    billboard_location: 'Highway 101, Exit 15',
    amount: '2,500'
  },
  {
    id: 2,
    client_name: 'Tech Solutions Ltd',
    billboard_location: 'Downtown Plaza',
    amount: '3,200'
  },
  {
    id: 3,
    client_name: 'Green Energy Co',
    billboard_location: 'Industrial District',
    amount: '1,800'
  }
])

const topBillboards = ref([
  {
    id: 1,
    name: 'Highway Premium',
    location: 'I-95 North',
    views: 125
  },
  {
    id: 2,
    name: 'Downtown Digital',
    location: 'Main Street',
    views: 98
  },
  {
    id: 3,
    name: 'Shopping Center',
    location: 'Mall Entrance',
    views: 87
  }
])

const createCompany = () => {
  createCompanyForm.post(route('companies.store'), {
    onSuccess: () => {
      showCreateCompanyModal.value = false
      createCompanyForm.reset()
    }
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold">
                  Welcome back, {{ $page.props.auth.user.name }}!
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                  Here's what's happening with your billboards today.
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <CompanySwitcher
                  :companies="$page.props.auth.user.companies"
                  :current-company="$page.props.auth.user.current_company"
                  @create-company="showCreateCompanyModal = true"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Total Billboards
              </CardTitle>
              <MapPin class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">45</div>
              <p class="text-xs text-muted-foreground">
                +10% from last month
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Active Contracts
              </CardTitle>
              <FileText class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">32</div>
              <p class="text-xs text-muted-foreground">
                +5% from last month
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Monthly Revenue
              </CardTitle>
              <DollarSign class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">$12,450</div>
              <p class="text-xs text-muted-foreground">
                +12% from last month
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Occupancy Rate
              </CardTitle>
              <TrendingUp class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">78%</div>
              <p class="text-xs text-muted-foreground">
                +3% from last month
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Recent Contracts</CardTitle>
              <CardDescription>
                Latest contract activities and updates
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div
                  v-for="contract in recentContracts"
                  :key="contract.id"
                  class="flex items-center space-x-4"
                >
                  <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                      {{ contract.client_name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                      {{ contract.billboard_location }}
                    </p>
                  </div>
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    ${{ contract.amount }}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Billboard Performance</CardTitle>
              <CardDescription>
                Top performing billboards this month
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div
                  v-for="billboard in topBillboards"
                  :key="billboard.id"
                  class="flex items-center space-x-4"
                >
                  <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                      {{ billboard.name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                      {{ billboard.location }}
                    </p>
                  </div>
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    {{ billboard.views }}k views
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Create Company Modal -->
    <Modal :show="showCreateCompanyModal" @close="showCreateCompanyModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Create New Company
        </h3>
        <form @submit.prevent="createCompany" class="space-y-4">
          <div>
            <Label htmlFor="company_name">Company Name</Label>
            <Input
              id="company_name"
              v-model="createCompanyForm.name"
              type="text"
              class="mt-1"
              required
            />
          </div>
          <div>
            <Label htmlFor="industry">Industry</Label>
            <Select v-model="createCompanyForm.industry">
              <SelectTrigger class="mt-1">
                <SelectValue placeholder="Select industry" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="outdoor-advertising">Outdoor Advertising</SelectItem>
                <SelectItem value="marketing-agency">Marketing Agency</SelectItem>
                <SelectItem value="real-estate">Real Estate</SelectItem>
                <SelectItem value="retail">Retail</SelectItem>
                <SelectItem value="other">Other</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex justify-end space-x-2">
            <Button
              type="button"
              variant="outline"
              @click="showCreateCompanyModal = false"
            >
              Cancel
            </Button>
            <Button type="submit" :disabled="createCompanyForm.processing">
              Create Company
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>
