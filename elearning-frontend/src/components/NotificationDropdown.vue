<template>
  <div class="relative">
    <!-- Notification Bell -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-md"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path>
      </svg>
      
      <!-- Notification Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-sm text-primary-600 hover:text-primary-700"
          >
            Mark all as read
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="max-h-96 overflow-y-auto">
        <div v-if="loading" class="p-4 text-center">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600 mx-auto"></div>
        </div>

        <div v-else-if="notifications.length === 0" class="p-4 text-center text-gray-500">
          <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path>
          </svg>
          <p class="text-sm">No notifications</p>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="p-4 hover:bg-gray-50 transition-colors duration-150"
            :class="{ 
              'bg-blue-50': !notification.read_at,
              'cursor-pointer': notification.data?.action_url
            }"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start space-x-3">
              <!-- Notification Icon -->
              <div class="flex-shrink-0">
                <div
                  :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center',
                    getNotificationIcon(notification.type).bg
                  ]"
                >
                  <component
                    :is="getNotificationIcon(notification.type).icon"
                    class="w-4 h-4"
                    :class="getNotificationIcon(notification.type).color"
                  />
                </div>
              </div>

              <!-- Notification Content -->
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900">
                  {{ notification.data.message }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ formatDate(notification.created_at) }}
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex-shrink-0 flex items-center space-x-1" @click.stop>
                <button
                  v-if="!notification.read_at"
                  @click="markAsRead(notification.id)"
                  class="text-xs text-primary-600 hover:text-primary-700"
                >
                  Mark read
                </button>
                <button
                  @click="deleteNotification(notification.id)"
                  class="text-xs text-red-600 hover:text-red-700"
                >
                  Delete
                </button>
                <div v-if="notification.data?.action_url" class="text-xs text-green-600">
                  <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                  </svg>
                  Click
                </div>
              </div>
            </div>

            <!-- Notification Actions -->
            <div v-if="notification.data.action_url" class="mt-2">
              <div class="flex items-center text-xs text-primary-600 hover:text-primary-700">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Click to view reply
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="notifications.length > 0" class="p-4 border-t border-gray-200">
        <router-link
          to="/notifications"
          class="text-sm text-primary-600 hover:text-primary-700 text-center block"
        >
          View all notifications
        </router-link>
      </div>
    </div>

    <!-- Backdrop -->
    <div
      v-if="isOpen"
      @click="closeDropdown"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { notificationAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()

const isOpen = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

// Notification icons mapping
const notificationIcons = {
  'course_enrollment': {
    icon: 'svg',
    bg: 'bg-green-100',
    color: 'text-green-600',
    path: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'
  },
  'course_completion': {
    icon: 'svg',
    bg: 'bg-blue-100',
    color: 'text-blue-600',
    path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
  },
  'new_announcement': {
    icon: 'svg',
    bg: 'bg-yellow-100',
    color: 'text-yellow-600',
    path: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'
  },
  'forum_reply': {
    icon: 'svg',
    bg: 'bg-purple-100',
    color: 'text-purple-600',
    path: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
  },
  'payment_success': {
    icon: 'svg',
    bg: 'bg-green-100',
    color: 'text-green-600',
    path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
  },
  'default': {
    icon: 'svg',
    bg: 'bg-gray-100',
    color: 'text-gray-600',
    path: 'M15 17h5l-5 5v-5zM4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'
  }
}

const getNotificationIcon = (type) => {
  return notificationIcons[type] || notificationIcons.default
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))
  
  if (diffInHours < 1) {
    return 'Just now'
  } else if (diffInHours < 24) {
    return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`
  } else {
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchNotifications()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const [notificationsResponse, unreadCountResponse] = await Promise.all([
      notificationAPI.getAll({ per_page: 10 }),
      notificationAPI.getUnreadCount()
    ])
    
    // Handle notifications response - Laravel pagination format
    if (notificationsResponse.data.data && notificationsResponse.data.data.data) {
      notifications.value = notificationsResponse.data.data.data
    } else if (notificationsResponse.data.data) {
      notifications.value = notificationsResponse.data.data
    } else {
      notifications.value = notificationsResponse.data
    }
    
    // Handle unread count response
    unreadCount.value = unreadCountResponse.data.data?.count || 0
  } catch (error) {
    console.error('Error fetching notifications:', error)
    toast.error('Failed to load notifications')
  } finally {
    loading.value = false
  }
}

const markAsRead = async (notificationId) => {
  try {
    await notificationAPI.markAsRead(notificationId)
    
    // Update local state
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
    
    toast.success('Notification marked as read')
  } catch (error) {
    console.error('Error marking notification as read:', error)
    toast.error('Failed to mark notification as read')
  }
}

const markAllAsRead = async () => {
  try {
    await notificationAPI.markAllAsRead()
    
    // Update local state
    notifications.value.forEach(notification => {
      notification.read_at = new Date().toISOString()
    })
    unreadCount.value = 0
    
    toast.success('All notifications marked as read')
  } catch (error) {
    console.error('Error marking all notifications as read:', error)
    toast.error('Failed to mark all notifications as read')
  }
}

const deleteNotification = async (notificationId) => {
  try {
    await notificationAPI.deleteNotification(notificationId)
    
    // Update local state
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification && !notification.read_at) {
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
    notifications.value = notifications.value.filter(n => n.id !== notificationId)
    
    toast.success('Notification deleted')
  } catch (error) {
    console.error('Error deleting notification:', error)
    toast.error('Failed to delete notification')
  }
}

// Helper function to scroll to an element with retry logic
const scrollToElement = (elementId) => {
  console.log('Attempting to scroll to element:', elementId)
  
  const tryScroll = (attempt = 1, maxAttempts = 5) => {
    const element = document.getElementById(elementId)
    console.log(`Attempt ${attempt}: Element found:`, element)
    
    if (element) {
      console.log('Scrolling to element:', element)
      element.scrollIntoView({ behavior: 'smooth', block: 'center' })
      // Add a temporary highlight effect
      element.style.backgroundColor = '#fef3c7'
      element.style.border = '2px solid #f59e0b'
      setTimeout(() => {
        element.style.backgroundColor = ''
        element.style.border = ''
      }, 3000)
    } else if (attempt < maxAttempts) {
      console.log(`Element not found, retrying in ${attempt * 200}ms...`)
      setTimeout(() => tryScroll(attempt + 1, maxAttempts), attempt * 200)
    } else {
      console.log('Element not found after maximum attempts')
    }
  }
  
  // Wait a bit for the page to load
  setTimeout(() => {
    tryScroll()
  }, 500)
}

// Handle notification click
const handleNotificationClick = async (notification) => {
  // If notification has an action URL, navigate to it
  if (notification.data?.action_url) {
    // Mark as read if it's unread
    if (!notification.read_at) {
      await markAsRead(notification.id)
    }
    
    // Close dropdown
    closeDropdown()
    
    // Handle URLs with hash fragments for scrolling to specific replies
    const url = notification.data.action_url
    if (url.includes('#')) {
      const [path, hash] = url.split('#')
      
      // Check if we're already on the target page
      if (router.currentRoute.value.path === path) {
        // Already on the page, just scroll to the element
        scrollToElement(hash)
      } else {
        // Navigate to the page first, then scroll
        router.push(path).then(() => {
          // Wait for the page to load, then scroll to the element
          setTimeout(() => {
            scrollToElement(hash)
          }, 1000) // Longer delay for page navigation
        })
      }
    } else {
      router.push(url)
    }
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (isOpen.value && !event.target.closest('.relative')) {
    closeDropdown()
  }
}

// Fetch initial unread count
const fetchUnreadCount = async () => {
  try {
    const response = await notificationAPI.getUnreadCount()
    unreadCount.value = response.data.data?.count || 0
  } catch (error) {
    console.error('Error fetching unread count:', error)
  }
}

// Fetch initial data
onMounted(() => {
  fetchUnreadCount()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script> 