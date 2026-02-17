<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Analytics</h1>
        <p class="mt-2 text-gray-600">Detailed performance metrics and insights</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Courses</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalCourses }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Students</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalStudents }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">New This Month</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.newStudentsThisMonth }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Revenue</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">${{ stats.totalRevenue }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Completion Rate</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.completionRate }}%</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Avg Rating</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.averageRating }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Course Performance Chart -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Course Performance</h3>
          </div>
          <div class="p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            </div>
            <div v-else-if="coursePerformance.length > 0" class="space-y-4">
              <div
                v-for="course in coursePerformance.slice(0, 5)"
                :key="course.id"
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
              >
                <div class="flex-1">
                  <h4 class="text-sm font-medium text-gray-900">{{ course.title }}</h4>
                  <p class="text-xs text-gray-500">{{ course.category }}</p>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                  <div class="text-center">
                    <div class="font-medium text-gray-900">{{ course.enrollments }}</div>
                    <div class="text-gray-500">Enrollments</div>
                  </div>
                  <div class="text-center">
                    <div class="font-medium text-gray-900">{{ course.completion_rate }}%</div>
                    <div class="text-gray-500">Completion</div>
                  </div>
                  <div class="text-center">
                    <div class="font-medium text-gray-900">${{ course.revenue }}</div>
                    <div class="text-gray-500">Revenue</div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No course performance data available</p>
            </div>
          </div>
        </div>

        <!-- Revenue Overview -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Revenue Overview</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">${{ stats.totalRevenue }}</div>
                <div class="text-sm text-gray-500">Total Revenue</div>
              </div>
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">${{ stats.monthlyRevenue }}</div>
                <div class="text-sm text-gray-500">This Month</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Enrollment Trends -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Enrollment Trends</h3>
          </div>
          <div class="p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            </div>
            <div v-else-if="enrollmentTrends.length > 0" class="space-y-4">
              <div
                v-for="trend in enrollmentTrends.slice(0, 7)"
                :key="trend.date"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div class="text-sm text-gray-600">{{ formatDate(trend.date) }}</div>
                <div class="text-sm font-medium text-gray-900">{{ trend.count }} enrollments</div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No enrollment trend data available</p>
            </div>
          </div>
        </div>

        <!-- Top Performing Courses -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Performing Courses</h3>
          </div>
          <div class="p-6">
            <div v-if="loading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            </div>
            <div v-else-if="topCourses.length > 0" class="space-y-4">
              <div
                v-for="(course, index) in topCourses"
                :key="course.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-xs font-medium text-primary-600">{{ index + 1 }}</span>
                  </div>
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">{{ course.title }}</h4>
                    <p class="text-xs text-gray-500">{{ course.enrollments }} enrollments</p>
                  </div>
                </div>
                <div class="text-sm font-medium text-gray-900">${{ course.revenue }}</div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No course data available</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { instructorAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()

// Loading states
const loading = ref(false)

// Data
const stats = ref({
  totalCourses: 0,
  totalStudents: 0,
  newStudentsThisMonth: 0,
  totalRevenue: 0,
  monthlyRevenue: 0,
  totalEnrollments: 0,
  completionRate: 0,
  averageRating: 0
})
const coursePerformance = ref([])
const enrollmentTrends = ref([])
const topCourses = ref([])

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const fetchAnalyticsData = async () => {
  loading.value = true
  try {
    console.log('🔄 Fetching analytics data...')
    
    const [statsResponse, analyticsResponse] = await Promise.allSettled([
      instructorAPI.getDashboardStats(),
      instructorAPI.getAnalytics({ period: 30 })
    ])
    
    // Handle stats data
    if (statsResponse.status === 'fulfilled') {
      const statsData = statsResponse.value.data?.data || statsResponse.value.data || {}
      const backendStats = statsData.stats || {}
      
      stats.value = {
        totalCourses: backendStats.total_courses || 0,
        totalStudents: backendStats.total_students || 0,
        newStudentsThisMonth: backendStats.new_students_this_month || 0,
        totalRevenue: backendStats.total_revenue || 0,
        monthlyRevenue: backendStats.monthly_revenue || 0,
        totalEnrollments: backendStats.total_enrollments || 0,
        completionRate: backendStats.completion_rate || 0,
        averageRating: backendStats.average_rating || 0
      }
    }
    
    // Handle analytics data
    if (analyticsResponse.status === 'fulfilled') {
      const analyticsData = analyticsResponse.value.data || {}
      coursePerformance.value = analyticsData.course_performance || []
      enrollmentTrends.value = analyticsData.enrollment_trends || []
      topCourses.value = analyticsData.top_courses || []
    }
    
    console.log('📊 Analytics data loaded:', {
      stats: stats.value,
      coursePerformance: coursePerformance.value.length,
      enrollmentTrends: enrollmentTrends.value.length,
      topCourses: topCourses.value.length
    })
    
  } catch (error) {
    console.error('❌ Error fetching analytics data:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no analytics data exists)
    if (error.response?.status >= 400) {
      toast.error('Failed to load analytics data')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAnalyticsData()
})
</script>
