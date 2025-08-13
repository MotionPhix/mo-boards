<script setup lang="ts">
import { computed } from 'vue'
import { Link, Head, usePage } from '@inertiajs/vue3'
import AppSidebar from '@/components/AppSidebar.vue'
import QuickActionMenu from '@/components/QuickActionMenu.vue'
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
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
import { useTheme } from '@/composables/useTheme'

interface Props {
  title?: string
  breadcrumbs?: Array<{
    label: string
    href: string
  }>
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Dashboard',
  breadcrumbs: () => []
})

const page = usePage()

const { isDark } = useTheme()

// Generate breadcrumbs based on current route if not provided
const computedBreadcrumbs = computed(() => {
  if (props.breadcrumbs.length > 0) {
    return props.breadcrumbs
  }

  const url = page.url
  const segments = url.split('/').filter(Boolean)

  const ziggyRoute = (window as any).route as (...args: any[]) => string
  const breadcrumbs: { label: string; href: string }[] = [
    { label: 'Dashboard', href: ziggyRoute('dashboard') }
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
      const href = (route as any)().has(sectionRoute) ? (route as any)(sectionRoute) : '#'
      breadcrumbs.push({
        label: sectionMap[section],
        href
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
          breadcrumbs.push({ label: actionMap[action], href: href })
        } else if (!isNaN(Number(action))) {
          // If it's an ID, it's likely a show page
          breadcrumbs.push({ label: 'View', href })
        }
      }
    }
  }

  return breadcrumbs
})
</script>

<template>
  <Head>
    <title>{{ props.title }}</title>

    <link rel="icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/build/manifest.json" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/mstile-150x150.png" />
    <meta name="theme-color" content="#ffffff" />
  </Head>

  <!-- Sonner Toast -->
  <Toaster :theme="isDark ? 'dark' : 'light'" position="top-right" rich-colors :duration="5000" />

  <SidebarProvider>
    <AppSidebar />
    <SidebarInset class="overflow-x-hidden">
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
                  <BreadcrumbLink
                    v-if="crumb.href"
                    :href="crumb.href"
                    :as="Link">
                    {{ crumb.label }}
                  </BreadcrumbLink>
                  <span v-else>{{ crumb.label }}</span>
                </BreadcrumbItem>

                <BreadcrumbSeparator
                  v-if="index < computedBreadcrumbs.length - 1"
                />

                <BreadcrumbItem v-if="index === computedBreadcrumbs.length - 1">
                  <BreadcrumbPage>{{ crumb.label }}</BreadcrumbPage>
                </BreadcrumbItem>
              </template>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
      </header>

  <!-- Flash messages now handled globally via plugin -->

      <!-- Page Content -->
      <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <slot />
      </div>
    </SidebarInset>

    <!-- Quick Action Menu -->
    <QuickActionMenu />
  </SidebarProvider>
</template>
