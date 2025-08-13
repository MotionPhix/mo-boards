import type { App } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

function showFlash(flash: any) {
  if (!flash) return

  if (flash.success) {
    toast.success(String(flash.success))
  }
  if (flash.error) {
    toast.error(String(flash.error))
  }
  if (flash.warning) {
    toast.warning(String(flash.warning))
  }
  if (flash.info) {
    toast.info(String(flash.info))
  }
}

export default {
  install(app: App, options?: { initialFlash?: any }) {
    // Show initial flash messages on first load
    if (options?.initialFlash) {
      showFlash(options.initialFlash)
    }

    // Preferred: listen via Inertia router events if available
    const inertiaHandler = (event: any) => {
      const flash = event?.detail?.page?.props?.flash
      showFlash(flash)
    }

    try {
      // Some adapters expose router.on; guard for safety
      ;(router as any)?.on?.('success', inertiaHandler)
    } catch (_) {
      // no-op
    }

    // Fallback: listen to DOM events dispatched by Inertia
    window.addEventListener('inertia:success', inertiaHandler as EventListener)
  },
}
