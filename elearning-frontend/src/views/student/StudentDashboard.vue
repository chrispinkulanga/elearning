<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Student Dashboard</h1>
        <p class="mt-2 text-gray-600">Track your learning progress and achievements</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Enrolled Courses</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.enrolledCourses }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Completed Courses</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.completedCourses }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Study Hours</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.studyHours }}h</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Certificates</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.certificates }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Current Courses -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Currently Learning</h3>
                <router-link to="/courses" class="btn btn-primary">
                  Browse Courses
                </router-link>
              </div>
            </div>
            <div class="p-6">
              <div v-if="loading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>

              <div v-else-if="enrolledCourses.length > 0" class="space-y-4">
                <div
                  v-for="enrollment in enrolledCourses"
                  :key="enrollment.id"
                  class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                >
                  <div class="flex-shrink-0 w-16 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                    <img
                      v-if="enrollment.course?.image"
                      :src="enrollment.course.image"
                      :alt="enrollment.course.title"
                      class="w-full h-full object-cover rounded-lg"
                    />
                    <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                  </div>

                  <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ enrollment.course?.title }}</h4>
                    <p class="text-sm text-gray-500">{{ enrollment.course?.instructor?.name }}</p>
                    
                    <!-- Progress Bar -->
                    <div class="mt-2">
                      <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Progress</span>
                        <span>{{ Math.round(enrollment.progress_percentage || 0) }}%</span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                          class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                          :style="{ width: `${enrollment.progress_percentage || 0}%` }"
                        ></div>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center space-x-2">
                    <router-link
                      :to="`/courses/${enrollment.course?.slug}/lessons`"
                      class="btn btn-sm btn-primary"
                    >
                      Continue
                    </router-link>
                    <button
                      v-if="enrollment.course?.allow_certificates && enrollment.progress_percentage >= 100"
                      @click="downloadCertificate(enrollment)"
                      class="btn btn-sm btn-secondary"
                    >
                      Certificate
                    </button>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No courses enrolled</h3>
                <p class="mt-1 text-sm text-gray-500">Start your learning journey by enrolling in a course.</p>
                <div class="mt-6">
                  <router-link to="/courses" class="btn btn-primary">
                    Browse Courses
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity & Certificates -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Recent Activity -->
          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            </div>
            <div class="p-6">
              <div v-if="loading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>

              <div v-else-if="recentActivity.length > 0" class="space-y-4">
                <div
                  v-for="activity in recentActivity"
                  :key="activity.id"
                  class="flex items-start space-x-3"
                >
                  <div class="flex-shrink-0 w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">{{ activity.description }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activity</h3>
                <p class="mt-1 text-sm text-gray-500">Your learning activity will appear here.</p>
              </div>
            </div>
          </div>

          <!-- Certificates -->
          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Certificates</h3>
            </div>
            <div class="p-6">
              <div v-if="loading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>

              <div v-else-if="certificates.length > 0" class="space-y-4">
                <div
                  v-for="certificate in certificates"
                  :key="certificate.id"
                  class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg"
                >
                  <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ certificate.course?.title }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(certificate.issued_at) }}</p>
                  </div>
                  <button
                    @click="downloadCertificate(certificate)"
                    class="btn btn-sm btn-secondary"
                  >
                    Download
                  </button>
                </div>
              </div>

              <div v-else class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No certificates yet</h3>
                <p class="mt-1 text-sm text-gray-500">Complete courses to earn certificates.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-6">
        <router-link
          to="/courses"
          class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Browse Courses</h3>
              <p class="text-sm text-gray-600">Discover new courses to learn</p>
            </div>
          </div>
        </router-link>

        <router-link
          to="/my-courses"
          class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">My Courses</h3>
              <p class="text-sm text-gray-600">Continue your learning journey</p>
            </div>
          </div>
        </router-link>

        <router-link
          to="/forum"
          class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Community</h3>
              <p class="text-sm text-gray-600">Join discussions and ask questions</p>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { studentAPI, userAPI } from '@/services/api'

const toast = useToast()

const loading = ref(false)
const stats = ref({
  enrolledCourses: 0,
  completedCourses: 0,
  studyHours: 0,
  certificates: 0
})
const enrolledCourses = ref([])
const recentActivity = ref([])
const certificates = ref([])

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const downloadCertificate = async (enrollment) => {
  try {
    const response = await studentAPI.downloadCertificate(enrollment.id)
    // Handle certificate download
    toast.success('Certificate downloaded successfully!')
  } catch (error) {
    console.error('Error downloading certificate:', error)
    toast.error('Failed to download certificate')
  }
}

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const [statsSettled, enrollmentsSettled, certificatesSettled] = await Promise.allSettled([
      studentAPI.getDashboardStats(),
      userAPI.getEnrollments(),
      studentAPI.getCertificates()
    ])

    // Stats mapping
    if (statsSettled.status === 'fulfilled') {
      const payload = statsSettled.value.data?.data || statsSettled.value.data || {}
      const s = payload.stats || {}
      stats.value = {
        enrolledCourses: s.total_courses || 0,
        completedCourses: s.completed_courses || 0,
        studyHours: 0,
        certificates: s.certificates_earned || 0
      }
    }

    // Enrollments list
    if (enrollmentsSettled.status === 'fulfilled') {
      const p = enrollmentsSettled.value.data?.data
      enrolledCourses.value = p?.data || []
    } else {
      enrolledCourses.value = []
    }

    // Certificates list (optional)
    if (certificatesSettled.status === 'fulfilled') {
      certificates.value = certificatesSettled.value.data?.data?.data || certificatesSettled.value.data?.data || []
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no data exists)
    if (error.response?.status >= 400) {
      toast.error('Failed to load dashboard data')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script> 