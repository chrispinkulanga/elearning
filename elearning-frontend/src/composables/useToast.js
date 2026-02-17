import { ref } from 'vue'

const toasts = ref([])
let nextId = 1

export function useToast() {
  const show = (message, type = 'info', duration = 5000) => {
    const id = nextId++
    const toast = {
      id,
      message,
      type,
      duration,
      timestamp: Date.now()
    }

    console.log(`Toast: Creating ${type} toast:`, { id, message, duration })
    toasts.value.push(toast)
    console.log(`Toast: Current toasts array:`, toasts.value)

    // Auto remove after duration
    setTimeout(() => {
      console.log(`Toast: Auto-removing toast ${id}`)
      remove(id)
    }, duration)

    return id
  }

  const success = (message, duration) => show(message, 'success', duration)
  const error = (message, duration) => show(message, 'error', duration)
  const warning = (message, duration) => show(message, 'warning', duration)
  const info = (message, duration) => show(message, 'info', duration)

  const remove = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const clear = () => {
    toasts.value = []
  }

  return {
    toasts,
    show,
    success,
    error,
    warning,
    info,
    remove,
    clear
  }
}
