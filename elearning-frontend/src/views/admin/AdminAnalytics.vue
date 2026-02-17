<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
          <p class="text-gray-600 mt-1">Platform performance insights and statistics</p>
          </div>
          <div class="flex space-x-3">
            <select 
              v-model="selectedPeriod" 
            @change="refreshData"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="30">Last 30 days</option>
            <option value="60">Last 60 days</option>
            <option value="90">Last 90 days</option>
            </select>
            <button 
            @click="refreshData"
              :disabled="loading"
            class="btn btn-primary flex items-center"
            >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            Refresh
            </button>
        </div>
      </div>

      <!-- Overview Stats -->
      <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <!-- Total Users -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6 border border-gray-200">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-2 sm:ml-4">
                  <p class="text-xs sm:text-sm font-medium text-gray-600">Total Users</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ overview.total_users || 0 }}</p>
              <p class="text-xs sm:text-sm text-gray-500">
                <span :class="overview.user_growth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ overview.user_growth >= 0 ? '+' : '' }}{{ overview.user_growth?.toFixed(1) || 0 }}%
                </span>
                <span class="hidden sm:inline">from last month</span>
              </p>
                </div>
              </div>
            </div>

        <!-- Total Courses -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Courses</p>
              <p class="text-2xl font-bold text-gray-900">{{ overview.total_courses || 0 }}</p>
              <p class="text-sm text-gray-500">{{ overview.approved_courses || 0 }} approved</p>
                </div>
              </div>
            </div>

        <!-- Total Enrollments -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
              <div class="flex items-center">
                <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Enrollments</p>
              <p class="text-2xl font-bold text-gray-900">{{ overview.total_enrollments || 0 }}</p>
              <p class="text-sm text-gray-500">{{ overview.new_enrollments_this_month || 0 }} this month</p>
                </div>
              </div>
            </div>

        <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
              <div class="flex items-center">
                <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Revenue</p>
              <p class="text-2xl font-bold text-gray-900">${{ (overview.total_revenue || 0).toLocaleString() }}</p>
              <p class="text-sm text-gray-500">
                <span :class="overview.revenue_growth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ overview.revenue_growth >= 0 ? '+' : '' }}{{ overview.revenue_growth?.toFixed(1) || 0 }}%
                </span>
                from last month
              </p>
                </div>
              </div>
            </div>
          </div>

      <!-- Charts and Detailed Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Enrollment Trends Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Enrollment Trends (Last 6 Months)</h3>
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
          <div v-else-if="overview.enrollment_trend && overview.enrollment_trend.length > 0" class="space-y-3">
            <div v-for="trend in overview.enrollment_trend" :key="trend.date" class="flex items-center justify-between">
              <span class="text-sm text-gray-600">{{ trend.date }}</span>
              <div class="flex items-center space-x-2">
                <div class="w-32 bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full" 
                    :style="{ width: `${Math.min((trend.enrollments / Math.max(...overview.enrollment_trend.map(t => t.enrollments))) * 100, 100)}%` }"
                    ></div>
                  </div>
                <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ trend.enrollments }}</span>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No enrollment data available
          </div>
        </div>

        <!-- Course Status Distribution -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Course Status Distribution</h3>
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Approved</span>
              <div class="flex items-center space-x-2">
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div class="bg-green-600 h-2 rounded-full" :style="{ width: `${(overview.approved_courses / Math.max(overview.total_courses, 1)) * 100}%` }"></div>
                </div>
                <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ overview.approved_courses || 0 }}</span>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Pending</span>
              <div class="flex items-center space-x-2">
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div class="bg-yellow-600 h-2 rounded-full" :style="{ width: `${(overview.pending_courses / Math.max(overview.total_courses, 1)) * 100}%` }"></div>
                </div>
                <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ overview.pending_courses || 0 }}</span>
                  </div>
                </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Total</span>
              <span class="text-lg font-bold text-gray-900">{{ overview.total_courses || 0 }}</span>
            </div>
                </div>
              </div>
            </div>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
              <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
              </div>
              <div class="p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
            <div v-else-if="overview.recent_users && overview.recent_users.length > 0" class="space-y-4">
              <div
                v-for="user in overview.recent_users"
                :key="user.id"
                class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50"
              >
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                  <p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
                      </div>
                <div class="flex-shrink-0">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ user.status || 'active' }}
                  </span>
                    </div>
                  </div>
                </div>
            <div v-else class="text-center py-8 text-gray-500">
              No recent users
              </div>
            </div>
          </div>

        <!-- Recent Courses -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
              <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Courses</h3>
              </div>
              <div class="p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
            <div v-else-if="overview.recent_courses && overview.recent_courses.length > 0" class="space-y-4">
                  <div 
                v-for="course in overview.recent_courses"
                    :key="course.id"
                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
                  >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <h4 class="text-sm font-medium text-gray-900">{{ course.title }}</h4>
                    <p class="text-sm text-gray-500 mt-1">
                      by {{ course.instructor?.name || 'Unknown Instructor' }}
                    </p>
                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                      <span>{{ course.enrollments_count || 0 }} enrollments</span>
                      <span class="capitalize">{{ course.status }}</span>
                    </div>
                    </div>
                    </div>
                  </div>
                </div>
            <div v-else class="text-center py-8 text-gray-500">
              No recent courses
            </div>
                </div>
              </div>
            </div>

      <!-- Course Categories Distribution -->
      <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
              <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Course Categories Distribution</h3>
              </div>
              <div class="p-6">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          <div v-else-if="overview.category_distribution && overview.category_distribution.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="category in overview.category_distribution"
              :key="category.name"
              class="bg-gray-50 rounded-lg p-4"
            >
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-900">{{ category.name }}</span>
                <span class="text-lg font-bold text-blue-600">{{ category.value }}</span>
              </div>
              <div class="mt-2 bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-blue-600 h-2 rounded-full" 
                  :style="{ width: `${(category.value / Math.max(...overview.category_distribution.map(c => c.value))) * 100}%` }"
                ></div>
                    </div>
                  </div>
                </div>
          <div v-else class="text-center py-8 text-gray-500">
            No category data available
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { adminAPI } from '@/services/api'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const selectedPeriod = ref(30)
const overview = ref({
  total_users: 0,
  total_courses: 0,
  total_enrollments: 0,
  total_revenue: 0,
  approved_courses: 0,
  pending_courses: 0,
  new_users_this_month: 0,
  new_enrollments_this_month: 0,
  revenue_this_month: 0,
  user_growth: 0,
  revenue_growth: 0,
  enrollment_trend: [],
  category_distribution: [],
  recent_users: [],
  recent_courses: []
})

// Check if user has admin access
const hasAdminAccess = computed(() => {
  return authStore.isAuthenticated && authStore.isAdmin
})

// Fetch overview analytics data
const fetchOverviewData = async () => {
  if (!hasAdminAccess.value) {
    console.log('❌ User does not have admin access')
    toast.error('Access denied. Admin privileges required.')
    return
  }

  loading.value = true
  try {
    console.log('🚀 Fetching analytics overview data...')
    
    // Call the backend analytics API endpoint using adminAPI service
    const response = await adminAPI.getOverviewReport()
    console.log('✅ Analytics overview response:', response)
    
    if (response.data.status === 'success' && response.data.data) {
      overview.value = response.data.data
      console.log('✅ Analytics overview data loaded successfully')
    } else {
      throw new Error(response.data.message || 'Invalid response format')
    }
    
  } catch (error) {
    console.error('❌ Error fetching analytics overview:', error)
    
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no analytics data exists)
    if (error.response?.status >= 400) {
      // Handle specific error cases
      if (error.response?.status === 401) {
        toast.error('Authentication required. Please log in again.')
      } else if (error.response?.status === 403) {
        toast.error('Access denied. Please check your admin privileges.')
      } else {
        toast.error('Failed to load analytics data: ' + (error.message || 'Unknown error'))
      }
    }
  } finally {
    loading.value = false
  }
}

// Refresh all data
const refreshData = async () => {
  await fetchOverviewData()
}

// Component lifecycle
onMounted(async () => {
  console.log('🚀 AdminAnalytics component mounted')
  console.log('🔐 Auth state:', {
    isAuthenticated: authStore.isAuthenticated,
    isAdmin: authStore.isAdmin,
    userRole: authStore.userRole
  })
  
  if (hasAdminAccess.value) {
    await fetchOverviewData()
  } else {
    console.log('❌ User does not have admin access')
    toast.error('Access denied. Admin privileges required.')
    router.push('/')
  }
})
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}
</style>
