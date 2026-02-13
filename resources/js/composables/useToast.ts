import { ref, readonly } from 'vue'

export interface Toast {
    id: string
    type: 'success' | 'error' | 'warning' | 'info'
    title: string
    message?: string
    duration?: number
}

const toasts = ref<Toast[]>([])

let toastId = 0

function generateId(): string {
    return `toast-${++toastId}-${Date.now()}`
}

function addToast(toast: Omit<Toast, 'id'>) {
    const id = generateId()
    const duration = toast.duration ?? 5000

    toasts.value.push({ ...toast, id })

    if (duration > 0) {
        setTimeout(() => {
            removeToast(id)
        }, duration)
    }

    return id
}

function removeToast(id: string) {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
        toasts.value.splice(index, 1)
    }
}

function success(title: string, message?: string, duration?: number) {
    return addToast({ type: 'success', title, message, duration })
}

function error(title: string, message?: string, duration?: number) {
    return addToast({ type: 'error', title, message, duration: duration ?? 8000 })
}

function warning(title: string, message?: string, duration?: number) {
    return addToast({ type: 'warning', title, message, duration })
}

function info(title: string, message?: string, duration?: number) {
    return addToast({ type: 'info', title, message, duration })
}

export function useToast() {
    return {
        toasts: readonly(toasts),
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
    }
}

// Global instance for use outside of Vue components
export const toast = {
    success,
    error,
    warning,
    info,
    remove: removeToast,
}
