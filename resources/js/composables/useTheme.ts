import { ref, onMounted, watch } from 'vue'

export function useTheme() {
  const theme = ref<'light' | 'dark'>('light')
  
  const isDark = () => theme.value === 'dark'
  const isLight = () => theme.value === 'light'
  
  const setTheme = (newTheme: 'light' | 'dark') => {
    theme.value = newTheme
    localStorage.setItem('theme', newTheme)
    
    // Apply theme to document
    if (newTheme === 'dark') {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }
  
  const toggleTheme = () => {
    setTheme(isDark() ? 'light' : 'dark')
  }
  
  // Initialize theme based on local storage or system preference
  onMounted(() => {
    const storedTheme = localStorage.getItem('theme') as 'light' | 'dark' | null
    
    if (storedTheme) {
      setTheme(storedTheme)
    } else {
      // Check for system preference
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      setTheme(prefersDark ? 'dark' : 'light')
    }
    
    // Watch for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      if (!localStorage.getItem('theme')) {
        setTheme(e.matches ? 'dark' : 'light')
      }
    })
  })
  
  return {
    theme,
    isDark,
    isLight,
    setTheme,
    toggleTheme
  }
}
