<template>
  <div class="fixed top-4 right-4 left-4 sm:left-auto z-50 space-y-2">
    <!-- Debug info -->
    <div v-if="toasts.length > 0" class="text-xs text-gray-500 bg-white p-2 rounded border">
      Debug: {{ toasts.length }} toast(s) active
    </div>
    
    <TransitionGroup
      name="toast"
      tag="div"
      class="space-y-2"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
          toast.type === 'success' ? 'ring-green-500' : '',
          toast.type === 'error' ? 'ring-red-500' : '',
          toast.type === 'warning' ? 'ring-yellow-500' : '',
          toast.type === 'info' ? 'ring-blue-500' : ''
        ]"
      >
        <div class="p-3 sm:p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <!-- Success Icon -->
              <svg
                v-if="toast.type === 'success'"
                class="h-5 w-5 sm:h-6 sm:w-6 text-green-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              
              <!-- Error Icon -->
              <svg
                v-else-if="toast.type === 'error'"
                class="h-5 w-5 sm:h-6 sm:w-6 text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              
              <!-- Warning Icon -->
              <svg
                v-else-if="toast.type === 'warning'"
                class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
              
              <!-- Info Icon -->
              <svg
                v-else
                class="h-5 w-5 sm:h-6 sm:w-6 text-blue-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            
            <div class="ml-1 sm:ml-3 w-0 flex-1 pt-0.5">
              <p class="text-xs sm:text-sm font-medium text-gray-900 line-clamp-2">
                {{ toast.message }}
              </p>
            </div>
            
            <div class="flex-shrink-0 flex">
              <button
                @click="remove(toast.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span class="sr-only">Close</span>
                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div
          v-if="toast.duration > 0"
          class="h-1 bg-gray-200"
        >
          <div
            class="h-full transition-all duration-300 ease-linear"
            :class="[
              toast.type === 'success' ? 'bg-green-500' : '',
              toast.type === 'error' ? 'bg-red-500' : '',
              toast.type === 'warning' ? 'bg-yellow-500' : '',
              toast.type === 'info' ? 'bg-blue-500' : ''
            ]"
            :style="{ width: `${getProgressWidth(toast)}%` }"
          ></div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, remove } = useToast()

const getProgressWidth = (toast) => {
  const elapsed = Date.now() - toast.timestamp
  const remaining = toast.duration - elapsed
  return Math.max(0, Math.min(100, (remaining / toast.duration) * 100))
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}

/* Ensure toasts are always visible */
.fixed {
  position: fixed !important;
}

.top-4 {
  top: 1rem !important;
}

.right-4 {
  right: 1rem !important;
}

.z-50 {
  z-index: 50 !important;
}

/* Make toasts more prominent */
.max-w-sm {
  max-width: 24rem !important;
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

.ring-1 {
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
}
</style>
