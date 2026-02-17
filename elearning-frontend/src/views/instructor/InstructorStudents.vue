<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Enrolled Students</h1>
        <p class="mt-2 text-gray-600">Manage and track your students' progress</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <input
                v-model="studentSearch"
                type="text"
                placeholder="Search students by name or email..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                @input="searchStudents"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>
          <div class="sm:w-64">
            <select
              v-model="selectedCourse"
              @change="filterStudents"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">All Courses</option>
              <option v-for="course in availableCourses" :key="course.id" :value="course.id">
                {{ course.title }}
              </option>
            </select>
          </div>
          <div class="sm:w-48">
            <select
              v-model="selectedStatus"
              @change="filterStudents"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Students Table -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
              Students ({{ enrolledStudents.length }})
            </h3>
            <div class="flex items-center space-x-2">
              <button
                @click="exportStudents"
                class="btn btn-secondary flex items-center"
                :disabled="enrolledStudents.length === 0"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
              </button>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          </div>

          <div v-else-if="enrolledStudents.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="enrollment in enrolledStudents" :key="enrollment.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                          <span class="text-sm font-medium text-primary-600">
                            {{ enrollment.student?.name?.charAt(0)?.toUpperCase() }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ enrollment.student?.name }}</div>
                        <div class="text-sm text-gray-500">{{ enrollment.student?.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ enrollment.course?.title }}</div>
                    <div class="text-sm text-gray-500">{{ enrollment.course?.category?.name || 'Uncategorized' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(enrollment.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                        <div 
                          class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
                          :style="{ width: `${enrollment.progress_percentage || 0}%` }"
                        ></div>
                      </div>
                      <span class="text-sm text-gray-600 min-w-0">{{ enrollment.progress_percentage || 0 }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="[
                        enrollment.status === 'active' 
                          ? 'bg-green-100 text-green-800' 
                          : enrollment.status === 'completed'
                          ? 'bg-blue-100 text-blue-800'
                          : 'bg-gray-100 text-gray-800',
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full'
                      ]"
                    >
                      {{ enrollment.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <button
                        @click="viewStudentProgress(enrollment)"
                        class="text-primary-600 hover:text-primary-900"
                        title="View Progress"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button
                        @click="sendMessage(enrollment)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Send Message"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ studentSearch || selectedCourse || selectedStatus 
                ? 'Try adjusting your search criteria.' 
                : 'No students have enrolled in your courses yet.' 
              }}
            </p>
            <div v-if="!studentSearch && !selectedCourse && !selectedStatus" class="mt-6">
              <router-link to="/instructor/create-course" class="btn btn-primary">
                Create Your First Course
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { instructorAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()

// Loading states
const loading = ref(false)

// Data
const enrolledStudents = ref([])
const availableCourses = ref([])

// Filters
const studentSearch = ref('')
const selectedCourse = ref('')
const selectedStatus = ref('')

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const fetchStudents = async () => {
  loading.value = true
  try {
    const params = {
      search: studentSearch.value,
      course_id: selectedCourse.value,
      status: selectedStatus.value,
      per_page: 100
    }
    
    console.log('🔄 Fetching students with params:', params)
    
    const response = await instructorAPI.getStudents(params)
    enrolledStudents.value = response.data.enrollments.data || []
    availableCourses.value = response.data.courses || []
    
    console.log('👥 Students loaded:', enrolledStudents.value.length)
    
  } catch (error) {
    console.error('❌ Error fetching students:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no students are enrolled)
    if (error.response?.status >= 400) {
      toast.error('Failed to load students data')
    }
    enrolledStudents.value = []
  } finally {
    loading.value = false
  }
}

const searchStudents = () => {
  fetchStudents()
}

const filterStudents = () => {
  fetchStudents()
}

const viewStudentProgress = (enrollment) => {
  // TODO: Implement student progress view
  toast.info(`Viewing progress for ${enrollment.student?.name}`)
}

const sendMessage = (enrollment) => {
  // TODO: Implement messaging system
  toast.info(`Sending message to ${enrollment.student?.name}`)
}

const exportStudents = () => {
  // TODO: Implement CSV export
  toast.info('Exporting students data...')
}

onMounted(() => {
  fetchStudents()
})
</script>
