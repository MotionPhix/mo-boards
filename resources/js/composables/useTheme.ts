import { ref, computed, watch } from 'vue'

export type ThemeMode = 'light' | 'dark' | 'system'
export type ResolvedTheme = 'light' | 'dark'

const THEME_COOKIE_NAME = 'theme'
const THEME_STORAGE_KEY = 'theme-preference'

// Reactive theme state
const storedTheme = ref<ThemeMode>('system')

// Get cookie value
function getCookie(name: string): string | null {
  if (typeof document === 'undefined') return null
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) return parts.pop()?.split(';').shift() || null
  return null
}

// Set cookie value
function setCookie(name: string, value: string) {
  if (typeof document === 'undefined') return
  const maxAge = 365 * 24 * 60 * 60 // 1 year
  document.cookie = `${name}=${value}; path=/; max-age=${maxAge}; SameSite=Lax`
}

// Get system preference
function getSystemPreference(): 'light' | 'dark' {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

// Initialize theme from storage/cookie
function initializeThemeState() {
  if (typeof window === 'undefined') return

  // Try localStorage first, then cookie, then default to system
  const localTheme = localStorage.getItem(THEME_STORAGE_KEY) as ThemeMode
  const cookieTheme = getCookie(THEME_COOKIE_NAME) as ThemeMode

  const theme = localTheme || cookieTheme || 'system'

  if (['light', 'dark', 'system'].includes(theme)) {
    storedTheme.value = theme
  }
}

export function useTheme() {
  // Initialize on first use
  if (typeof window !== 'undefined' && storedTheme.value === 'system' && !localStorage.getItem(THEME_STORAGE_KEY)) {
    initializeThemeState()
  }

  // Computed resolved theme
  const resolvedTheme = computed<ResolvedTheme>(() => {
    if (storedTheme.value === 'system') {
      return getSystemPreference()
    }
    return storedTheme.value
  })

  // Apply theme to DOM
  const applyTheme = (theme: ResolvedTheme) => {
    if (typeof document === 'undefined') return

    const root = document.documentElement

    if (theme === 'dark') {
      root.classList.add('dark')
      root.classList.remove('light')
    } else {
      root.classList.add('light')
      root.classList.remove('dark')
    }

    root.setAttribute('data-theme', theme)

    // Update meta theme-color for mobile browsers
    const metaThemeColor = document.querySelector('meta[name="theme-color"]')
    const color = theme === 'dark' ? '#0f172a' : '#ffffff'

    if (metaThemeColor) {
      metaThemeColor.setAttribute('content', color)
    }
  }

  // Set theme with persistence
  const setTheme = (newTheme: ThemeMode) => {
    storedTheme.value = newTheme

    // Persist to localStorage
    if (typeof window !== 'undefined') {
      localStorage.setItem(THEME_STORAGE_KEY, newTheme)
    }

    // Set cookie for SSR
    setCookie(THEME_COOKIE_NAME, newTheme)

    // Send to server to update user preference (if logged in)
    if (typeof window !== 'undefined') {
      fetch(route('settings.theme'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          theme: newTheme
        })
      }).catch(() => {
        // Silently fail - theme still works client-side
        console.warn('Failed to save theme preference to server')
      })
    }
  }

  // Toggle between light and dark
  const toggleTheme = () => {
    const current = resolvedTheme.value
    setTheme(current === 'dark' ? 'light' : 'dark')
  }

  // Watch for changes and apply theme
  watch(resolvedTheme, (newTheme) => {
    applyTheme(newTheme)
  }, { immediate: true })

  // Listen for system theme changes when in system mode
  if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    const handleSystemChange = () => {
      if (storedTheme.value === 'system') {
        applyTheme(resolvedTheme.value)
      }
    }

    mediaQuery.addEventListener('change', handleSystemChange)
  }

  return {
    // Current theme mode (light/dark/system)
    theme: storedTheme,

    // Resolved theme (light/dark only)
    resolvedTheme: computed(() => resolvedTheme.value),

    // System preference
    systemTheme: computed(() => getSystemPreference()),

    // Computed state helpers
    isSystem: computed(() => storedTheme.value === 'system'),
    isDark: computed(() => resolvedTheme.value === 'dark'),
    isLight: computed(() => resolvedTheme.value === 'light'),

    // Actions
    setTheme,
    toggleTheme,
    applyTheme,
  }
}

// Initialize theme immediately (for SSR and first load)
export function initializeTheme() {
  if (typeof window === 'undefined') return

  // Get theme from cookie first (SSR compatibility)
  const cookieTheme = getCookie(THEME_COOKIE_NAME) as ThemeMode
  const storageTheme = localStorage.getItem(THEME_STORAGE_KEY) as ThemeMode

  // Determine theme to apply
  const themeToApply = storageTheme || cookieTheme || 'system'

  // Apply immediately to prevent flash
  const resolvedTheme: ResolvedTheme = themeToApply === 'system'
    ? getSystemPreference()
    : themeToApply

  // Apply theme class immediately
  const root = document.documentElement
  root.classList.remove('light', 'dark')
  root.classList.add(resolvedTheme)
  root.setAttribute('data-theme', resolvedTheme)

  // Set the reactive state
  if (['light', 'dark', 'system'].includes(themeToApply)) {
    storedTheme.value = themeToApply
  }
}

// Export for backward compatibility
export { useTheme as useAppearance }
