<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Badge } from '@/components/ui/badge'
import { Bell, Check, X } from 'lucide-vue-next'
import { useEcho } from '@laravel/echo-vue'

interface NotificationItem {
  id: number
  type: string
  level: 'info' | 'success' | 'warning' | 'error'
  title: string
  message: string
  data?: Record<string, any>
  is_read: boolean
  is_dismissed: boolean
  created_at: string
  read_at?: string | null
}

const page = usePage()
const auth = computed(() => (page.props as any).auth)

const notifications = ref<NotificationItem[]>([])
const unreadCount = ref(0)
const loading = ref(false)

// Use API routes directly
const listUrl = '/api/notifications'
const unreadUrl = '/api/notifications/unread-count'
const markAllUrl = '/api/notifications/read-all'

const csrf = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''

// Helper to get auth headers for API requests
const getAuthHeaders = () => {
  return {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrf(),
    'X-Requested-With': 'XMLHttpRequest',
  }
}

const fetchUnreadCount = async () => {
  const res = await fetch(unreadUrl, { 
    headers: getAuthHeaders(), 
    credentials: 'same-origin' 
  })
  if (res.ok) {
    const data = await res.json()
    unreadCount.value = data.unread_count ?? 0
  }
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const res = await fetch(listUrl, { 
      headers: getAuthHeaders(), 
      credentials: 'same-origin' 
    })
    if (res.ok) {
      const data = await res.json()
      notifications.value = data.notifications ?? []
      unreadCount.value = data.unread_count ?? unreadCount.value
    }
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id: number) => {
  const url = `/api/notifications/${id}/read`
  const res = await fetch(url, {
    method: 'POST',
    headers: getAuthHeaders(),
    credentials: 'same-origin',
  })
  if (res.ok) {
    const idx = notifications.value.findIndex(n => n.id === id)
    if (idx !== -1) {
      if (!notifications.value[idx].is_read) unreadCount.value = Math.max(0, unreadCount.value - 1)
      notifications.value[idx].is_read = true
      notifications.value[idx].read_at = new Date().toISOString()
    }
  }
}

const dismiss = async (id: number) => {
  const url = `/api/notifications/${id}/dismiss`
  const res = await fetch(url, {
    method: 'POST',
    headers: getAuthHeaders(),
    credentials: 'same-origin',
  })
  if (res.ok) {
    const idx = notifications.value.findIndex(n => n.id === id)
    if (idx !== -1) {
      if (!notifications.value[idx].is_read) unreadCount.value = Math.max(0, unreadCount.value - 1)
      notifications.value.splice(idx, 1)
    }
  }
}

const markAllAsRead = async () => {
  const res = await fetch(markAllUrl, {
    method: 'POST',
    headers: getAuthHeaders(),
    credentials: 'same-origin',
  })
  if (res.ok) {
    notifications.value = notifications.value.map(n => ({ ...n, is_read: true, read_at: new Date().toISOString() }))
    unreadCount.value = 0
  }
}

// Realtime subscriptions
const userId = computed(() => auth.value?.user?.id)
const companyId = computed(() => auth.value?.user?.current_company_id)

// Listen for custom broadcast event name (leading dot when broadcastAs is used)
if (userId.value) {
  useEcho(`user.${userId.value}`, ['.SystemNotificationCreated'], (e: any) => {
    const n: NotificationItem = {
      id: e.id,
      type: e.type,
      level: e.level,
      title: e.title,
      message: e.message,
      data: e.data,
      is_read: false,
      is_dismissed: false,
      created_at: e.created_at ?? new Date().toISOString(),
    }
    notifications.value.unshift(n)
    unreadCount.value += 1
  })
}

if (companyId.value) {
  useEcho(`company.${companyId.value}`, ['.SystemNotificationCreated'], (e: any) => {
    const n: NotificationItem = {
      id: e.id,
      type: e.type,
      level: e.level,
      title: e.title,
      message: e.message,
      data: e.data,
      is_read: false,
      is_dismissed: false,
      created_at: e.created_at ?? new Date().toISOString(),
    }
    notifications.value.unshift(n)
    unreadCount.value += 1
  })
}

onMounted(async () => {
  await Promise.all([fetchUnreadCount(), fetchNotifications()])
})

const levelVariant = (level: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  switch (level) {
    case 'success': return 'default'
    case 'warning': return 'secondary'
    case 'error': return 'destructive'
    default: return 'outline'
  }
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <div>
        <Button variant="ghost" size="icon" class="relative">
          <Bell class="h-5 w-5" />
          <span v-if="unreadCount > 0" class="absolute -right-1 -top-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </Button>
      </div>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-96 p-0">
      <div class="flex items-center justify-between px-4 py-3">
        <DropdownMenuLabel class="p-0 text-base font-semibold">Notifications</DropdownMenuLabel>
        <div class="flex items-center gap-2">
          <Button variant="ghost" size="sm" class="h-7 px-2" @click="markAllAsRead">
            <Check class="mr-1 h-4 w-4" /> Mark all read
          </Button>
        </div>
      </div>
      <DropdownMenuSeparator />
      <div class="max-h-96 overflow-auto">
        <div v-if="loading" class="p-4 text-sm text-muted-foreground">Loading...</div>
        <div v-else-if="notifications.length === 0" class="p-4 text-sm text-muted-foreground">No notifications</div>
        <ul v-else class="divide-y">
          <li v-for="n in notifications" :key="n.id" class="p-3 hover:bg-accent/40">
            <div class="flex items-start gap-3">
              <Badge :variant="levelVariant(n.level)" class="mt-0.5 text-xs">{{ n.level }}</Badge>
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <p class="truncate text-sm font-medium">{{ n.title }}</p>
                  <div class="shrink-0">
                    <Button variant="ghost" size="icon" class="h-6 w-6" @click="dismiss(n.id)">
                      <X class="h-3.5 w-3.5" />
                    </Button>
                  </div>
                </div>
                <p class="mt-1 line-clamp-3 text-sm text-muted-foreground">{{ n.message }}</p>
                <div class="mt-2 flex items-center gap-2">
                  <Button v-if="!n.is_read" variant="secondary" size="sm" class="h-7 px-2" @click="markAsRead(n.id)">
                    <Check class="mr-1 h-3.5 w-3.5" /> Mark as read
                  </Button>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
