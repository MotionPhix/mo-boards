<script lang="ts">
export const description = 'A sidebar that collapses to icons.'
export const iframeHeight = '800px'
export const containerClass = 'w-full h-full'
</script>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppSidebar from '@/components/AppSidebar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Separator } from '@/components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar'
import FlashMessages from '@/components/FlashMessages.vue'
import ToastProvider from '@/components/ToastProvider.vue'
import Sonner from '@/components/ui/sonner.vue'

interface Props {
  title?: string
  breadcrumbs?: Array<{
    label: string
    href?: string
  }>
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Dashboard',
  breadcrumbs: () => []
})

const page = usePage()

// Generate breadcrumbs based on current route if not provided
const computedBreadcrumbs = computed(() => {
  if (props.breadcrumbs.length > 0) {
    return props.breadcrumbs
  }

  const url = page.url
  const segments = url.split('/').filter(Boolean)

  const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') }
  ]

  if (segments.length > 1) {
    const section = segments[1]

    // Map URL segments to readable labels
    const sectionMap: Record<string, string> = {
      'billboards': 'Billboards',
      'contracts': 'Contracts',
      'contract-templates': 'Contract Templates',
      'team': 'Team',
      'companies': 'Companies',
      'profile': 'Profile'
    }

    if (sectionMap[section]) {
      const sectionRoute = `${section}.index`
      breadcrumbs.push({
        label: sectionMap[section],
        href: route().has(sectionRoute) ? route(sectionRoute) : undefined
      })

      // Add specific page breadcrumb if on a detail/edit page
      if (segments.length > 2) {
        const action = segments[2]
        const actionMap: Record<string, string> = {
          'create': 'Create',
          'edit': 'Edit',
          'show': 'View'
        }

        if (actionMap[action]) {
          breadcrumbs.push({
            label: actionMap[action]
          })
        } else if (!isNaN(Number(action))) {
          // If it's an ID, it's likely a show page
          breadcrumbs.push({
            label: 'View'
          })
        }
      }
    }
  }

  return breadcrumbs
})

const currentCompany = computed(() => page.props.auth?.user?.current_company)
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <!-- Header with breadcrumbs -->
      <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="mr-2 h-4" />

          <!-- Breadcrumbs -->
          <Breadcrumb>
            <BreadcrumbList>
              <template v-for="(crumb, index) in computedBreadcrumbs" :key="index">
                <BreadcrumbItem v-if="index < computedBreadcrumbs.length - 1">
                  <BreadcrumbLink v-if="crumb.href" :href="crumb.href">
                    {{ crumb.label }}
                  </BreadcrumbLink>
                  <span v-else>{{ crumb.label }}</span>
                </BreadcrumbItem>

                <BreadcrumbSeparator v-if="index < computedBreadcrumbs.length - 1" />

                <BreadcrumbItem v-if="index === computedBreadcrumbs.length - 1">
                  <BreadcrumbPage>{{ crumb.label }}</BreadcrumbPage>
                </BreadcrumbItem>
              </template>
            </BreadcrumbList>
          </Breadcrumb>

          <!-- Company indicator -->
          <div v-if="currentCompany" class="ml-auto">
            <span class="text-xs bg-muted px-2 py-1 rounded-md font-medium">
              {{ currentCompany.name }}
            </span>
          </div>
        </div>
      </header>

      <!-- Flash Messages -->
      <FlashMessages />

      <!-- Page Content -->
      <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <slot />
      </div>
    </SidebarInset>
    
    <!-- Toast notifications -->
    <ToastProvider />
    
    <!-- Sonner Toast -->
    <Sonner theme="system" />
  </SidebarProvider>
</template>
