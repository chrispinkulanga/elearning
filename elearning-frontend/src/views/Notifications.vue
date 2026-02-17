<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="mt-2 text-gray-600">Stay updated with your latest activities</p>
          </div>
          <div class="flex items-center space-x-3">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              :disabled="markingAllAsRead"
              class="btn btn-secondary"
            >
              <span v-if="markingAllAsRead" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ markingAllAsRead ? 'Marking...' : 'Mark all as read' }}
            </button>
            <button
              @click="refreshNotifications"
              :disabled="loading"
              class="btn btn-outline"
            >
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="notifications.length === 0" class="text-center py-12">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
        <p class="text-gray-600">You're all caught up! Check back later for new updates.</p>
      </div>

             <!-- Notifications List -->
       <div v-else class="space-y-4">
         <div
           v-for="notification in notifications"
           :key="notification.id"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200"
           :class="{ 
             'bg-blue-50 border-blue-200': !notification.read_at,
             'cursor-pointer hover:border-primary-300 hover:shadow-lg': notification.data?.action_url
           }"
           @click="handleNotificationClick(notification)"
         >
          <div class="flex items-start space-x-4">
            <!-- Notification Icon -->
            <div class="flex-shrink-0">
              <div
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center',
                  getNotificationIcon(notification.data?.type || 'default').bg
                ]"
              >
                <component
                  :is="getNotificationIcon(notification.data?.type || 'default').icon"
                  class="w-5 h-5"
                  :class="getNotificationIcon(notification.data?.type || 'default').color"
                />
              </div>
            </div>

            <!-- Notification Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="text-lg font-medium text-gray-900 mb-1">
                    {{ notification.data?.title || 'Notification' }}
                  </h3>
                  <p class="text-gray-700 mb-2">
                    {{ notification.data?.message || 'You have a new notification' }}
                  </p>
                  <p class="text-sm text-gray-500">
                    {{ formatDate(notification.created_at) }}
                  </p>
                </div>
                
                                 <!-- Unread Badge and Clickable Indicator -->
                 <div class="flex-shrink-0 ml-4 flex items-center space-x-2">
                   <div v-if="!notification.read_at" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                     New
                   </div>
                   <div v-if="notification.data?.action_url" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                     <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                     </svg>
                     Clickable
                   </div>
                 </div>
              </div>

                             <!-- Action Buttons -->
               <div class="mt-4 flex items-center space-x-3" @click.stop>
                 <button
                   v-if="!notification.read_at"
                   @click="markAsRead(notification.id)"
                   :disabled="markingAsRead === notification.id"
                   class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                 >
                   <span v-if="markingAsRead === notification.id" class="animate-spin rounded-full h-3 w-3 border-b-2 border-primary-600 inline-block mr-1"></span>
                   Mark as read
                 </button>
                 <button
                   @click="deleteNotification(notification.id)"
                   :disabled="deleting === notification.id"
                   class="text-sm text-red-600 hover:text-red-700 font-medium"
                 >
                   <span v-if="deleting === notification.id" class="animate-spin rounded-full h-3 w-3 border-b-2 border-red-600 inline-block mr-1"></span>
                   Delete
                 </button>
                 <div
                   v-if="notification.data?.action_url"
                   class="flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium"
                 >
                   <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                   </svg>
                   Click to view reply
                 </div>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Load More Button -->
      <div v-if="hasMore && !loading" class="mt-8 text-center">
        <button
          @click="loadMore"
          :disabled="loadingMore"
          class="btn btn-outline"
        >
          <span v-if="loadingMore" class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-600 mr-2"></span>
          {{ loadingMore ? 'Loading...' : 'Load More' }}
        </button>
      </div>

      <!-- Pagination Info -->
      <div v-if="notifications.length > 0" class="mt-8 text-center text-sm text-gray-500">
        Showing {{ notifications.length }} of {{ totalNotifications }} notifications
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import { notificationAPI } from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const loadingMore = ref(false)
const markingAllAsRead = ref(false)
const markingAsRead = ref(null)
const deleting = ref(null)
const notifications = ref([])
const unreadCount = ref(0)
const totalNotifications = ref(0)
const currentPage = ref(1)
const hasMore = ref(true)

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
  } else if (diffInHours < 48) {
    return 'Yesterday'
  } else {
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const fetchNotifications = async (page = 1, append = false) => {
  if (page === 1) {
    loading.value = true
  } else {
    loadingMore.value = true
  }

  try {
    const response = await notificationAPI.getAll({ 
      page: page,
      per_page: 20 
    })
    
    // Handle Laravel pagination format
    let notificationData
    if (response.data.data && response.data.data.data) {
      notificationData = response.data.data.data
      totalNotifications.value = response.data.data.total
      hasMore.value = response.data.data.current_page < response.data.data.last_page
    } else if (response.data.data) {
      notificationData = response.data.data
      totalNotifications.value = notificationData.length
      hasMore.value = false
    } else {
      notificationData = response.data
      totalNotifications.value = notificationData.length
      hasMore.value = false
    }
    
    if (append) {
      notifications.value = [...notifications.value, ...notificationData]
    } else {
      notifications.value = notificationData
    }
    
    // Update unread count
    unreadCount.value = notifications.value.filter(n => !n.read_at).length
    
  } catch (error) {
    console.error('Error fetching notifications:', error)
    toast.error('Failed to load notifications')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const refreshNotifications = async () => {
  currentPage.value = 1
  await fetchNotifications(1, false)
}

const loadMore = async () => {
  currentPage.value++
  await fetchNotifications(currentPage.value, true)
}

const markAsRead = async (notificationId) => {
  markingAsRead.value = notificationId
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
  } finally {
    markingAsRead.value = null
  }
}

const markAllAsRead = async () => {
  markingAllAsRead.value = true
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
  } finally {
    markingAllAsRead.value = false
  }
}

const deleteNotification = async (notificationId) => {
  deleting.value = notificationId
  try {
    await notificationAPI.deleteNotification(notificationId)
    
    // Update local state
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification && !notification.read_at) {
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
    notifications.value = notifications.value.filter(n => n.id !== notificationId)
    totalNotifications.value = Math.max(0, totalNotifications.value - 1)
    
    toast.success('Notification deleted')
  } catch (error) {
    console.error('Error deleting notification:', error)
    toast.error('Failed to delete notification')
  } finally {
    deleting.value = null
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

const handleNotificationClick = async (notification) => {
  // If notification has an action URL, navigate to it
  if (notification.data?.action_url) {
    // Mark as read if it's unread
    if (!notification.read_at) {
      await markAsRead(notification.id)
    }
    
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

onMounted(() => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to view notifications')
    return
  }
  
  fetchNotifications()
})
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
}

.btn-primary {
  @apply bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500;
}

.btn-secondary {
  @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
}

.btn-outline {
  @apply border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-primary-500;
}
</style>
