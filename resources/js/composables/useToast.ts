import { reactive, ref } from 'vue'

export interface Toast {
  id: string
  title?: string
  description?: string
  action?: {
    label: string
    onClick: () => void
  }
  variant?: 'default' | 'destructive'
  duration?: number
}

interface ToastOptions {
  title?: string
  description?: string
  action?: {
    label: string
    onClick: () => void
  }
  variant?: 'default' | 'destructive'
  duration?: number
}

const toasts = ref<Toast[]>([])
let toastCount = 0

export function useToast() {
  const toast = (options: ToastOptions) => {
    const id = `toast-${++toastCount}`
    const newToast: Toast = {
      id,
      title: options.title,
      description: options.description,
      action: options.action,
      variant: options.variant || 'default',
      duration: options.duration || 5000,
    }
    
    toasts.value = [...toasts.value, newToast]
    
    if (newToast.duration !== Infinity) {
      setTimeout(() => {
        dismiss(id)
      }, newToast.duration)
    }
    
    return id
  }
  
  const dismiss = (id: string) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }
  
  return {
    toast,
    toasts,
    dismiss,
  }
}
