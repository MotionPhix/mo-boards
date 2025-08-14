import type { App } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

type ToastPrefs = {
  enabled?: boolean
  flash?: boolean
  errors?: boolean
  dedupeWindowMs?: number
}

function getPrefs(obj: any, fallbackDedupe: number): Required<ToastPrefs> {
  const prefs: ToastPrefs = (obj && typeof obj === 'object' ? obj : {}) as ToastPrefs
  return {
    enabled: prefs.enabled ?? true,
    flash: prefs.flash ?? true,
    errors: prefs.errors ?? true,
    dedupeWindowMs: prefs.dedupeWindowMs ?? fallbackDedupe,
  }
}

function createDedupe(dedupeWindowMs: () => number) {
  const lastShown = new Map<string, number>()
  return (key: string) => {
    const now = Date.now()
    const win = Math.max(0, dedupeWindowMs())
    const last = lastShown.get(key) ?? 0
    if (now - last < win) return false
    lastShown.set(key, now)
    return true
  }
}

function showFlash(flash: any, shouldShow: (key: string) => boolean) {
  if (!flash) return

  if (flash.success) {
    const msg = String(flash.success)
    if (shouldShow(`success:${msg}`)) toast.success(msg)
  }
  if (flash.error) {
    const msg = String(flash.error)
    if (shouldShow(`error:${msg}`)) toast.error(msg)
  }
  if (flash.warning) {
    const msg = String(flash.warning)
    if (shouldShow(`warning:${msg}`)) toast.warning(msg)
  }
  if (flash.info) {
    const msg = String(flash.info)
    if (shouldShow(`info:${msg}`)) toast.info(msg)
  }
}

function summarizeErrors(errors: any): string {
  if (!errors) return 'Validation failed.'
  if (typeof errors === 'string') return errors
  if (Array.isArray(errors)) {
    const first = errors.find(Boolean)
    return typeof first === 'string' ? first : 'Validation failed.'
  }
  if (typeof errors === 'object') {
    const messages: string[] = []
    for (const [, val] of Object.entries(errors)) {
      if (!val) continue
      if (Array.isArray(val)) {
        const m = val.find(Boolean)
        if (m) messages.push(String(m))
      } else if (typeof val === 'string') {
        messages.push(val)
      } else if (typeof (val as any).message === 'string') {
        messages.push((val as any).message)
      }
    }
    if (messages.length === 0) return 'Validation failed.'
    const unique = [...new Set(messages)]
    const head = unique.slice(0, 2).join(' ')
    const extra = unique.length - 2
    return extra > 0 ? `${head} (+${extra} more)` : head
  }
  return 'Validation failed.'
}

export default {
  install(app: App, options?: { initialFlash?: any; dedupeWindowMs?: number }) {
    // Show initial flash messages on first load
    const fallbackDedupe = Math.max(0, options?.dedupeWindowMs ?? 1200)
    const shouldShow = createDedupe(() => fallbackDedupe)
    if (options?.initialFlash) {
      showFlash(options.initialFlash, shouldShow)
    }

    // Preferred: listen via Inertia router events if available
    const inertiaHandler = (event: any) => {
      const pageProps = event?.detail?.page?.props
      const prefs = getPrefs(pageProps?.toast, fallbackDedupe)
      if (!prefs.enabled || !prefs.flash) return
      const should = createDedupe(() => prefs.dedupeWindowMs)
      const flash = pageProps?.flash
      showFlash(flash, should)
    }

    try {
      // Some adapters expose router.on; guard for safety
      ;(router as any)?.on?.('success', inertiaHandler)
      ;(router as any)?.on?.('invalid', (event: any) => {
        const pageProps = event?.detail?.page?.props
        const prefs = getPrefs(pageProps?.toast, fallbackDedupe)
        if (!prefs.enabled || !prefs.errors) return
        const should = createDedupe(() => prefs.dedupeWindowMs)
        const errors = event?.detail?.errors ?? pageProps?.errors
        const message = summarizeErrors(errors)
        if (should(`invalid:${message}`)) {
          toast.error(message, { description: 'Please correct the highlighted fields and try again.' })
        }
      })
      ;(router as any)?.on?.('error', (event: any) => {
        const pageProps = event?.detail?.page?.props
        const prefs = getPrefs(pageProps?.toast, fallbackDedupe)
        if (!prefs.enabled || !prefs.errors) return
        const should = createDedupe(() => prefs.dedupeWindowMs)
        const status = event?.detail?.response?.status
        const statusText = event?.detail?.response?.statusText
        const message = status ? `${status} ${statusText || 'Request failed'}` : 'Something went wrong.'
        if (should(`error:${message}`)) {
          toast.error(message)
        }
      })
    } catch (_) {
      // no-op
    }

    // Fallback: listen to DOM events dispatched by Inertia
    window.addEventListener('inertia:success', inertiaHandler as EventListener)
    window.addEventListener('inertia:invalid', ((e: any) => {
      const pageProps = e?.detail?.page?.props
      const prefs = getPrefs(pageProps?.toast, fallbackDedupe)
      if (!prefs.enabled || !prefs.errors) return
      const should = createDedupe(() => prefs.dedupeWindowMs)
      const errors = e?.detail?.errors ?? pageProps?.errors
      const message = summarizeErrors(errors)
      if (should(`invalid:${message}`)) {
        toast.error(message, { description: 'Please correct the highlighted fields and try again.' })
      }
    }) as EventListener)
    window.addEventListener('inertia:error', ((e: any) => {
      const pageProps = e?.detail?.page?.props
      const prefs = getPrefs(pageProps?.toast, fallbackDedupe)
      if (!prefs.enabled || !prefs.errors) return
      const should = createDedupe(() => prefs.dedupeWindowMs)
      const status = e?.detail?.response?.status
      const statusText = e?.detail?.response?.statusText
      const message = status ? `${status} ${statusText || 'Request failed'}` : 'Something went wrong.'
      if (should(`error:${message}`)) {
        toast.error(message)
      }
    }) as EventListener)
  },
}
