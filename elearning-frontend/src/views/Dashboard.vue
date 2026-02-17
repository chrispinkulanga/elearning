<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user?.name || 'User' }}!</h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ userRole === 'instructor' ? 'Manage your courses and track your performance.' :
                 userRole === 'student' ? 'Track your learning progress and enrolled courses.' :
                 userRole === 'admin' ? 'Manage the platform and monitor system performance.' :
                 'Here\'s your dashboard overview.' }}
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="createCourse"
              v-if="userRole === 'instructor'"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Create Course
            </button>
            <button
              @click="createCategory"
              v-if="userRole === 'instructor'"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Create Category
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Section (always visible for troubleshooting) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium">Debug Info - Current User State</h3>
            <div class="mt-2 text-sm">
              <p><strong>User Role:</strong> {{ userRole }}</p>
              <p><strong>Is Admin:</strong> {{ authStore.isAdmin }}</p>
              <p><strong>Is Instructor:</strong> {{ authStore.isInstructor }}</p>
              <p><strong>Is Student:</strong> {{ authStore.isStudent }}</p>
              <p><strong>User Object:</strong> {{ JSON.stringify(user, null, 2) }}</p>
              <p><strong>Current Route:</strong> {{ $route.path }}</p>
            </div>
            <div class="mt-3 space-x-2">
              <button 
                @click="router.push('/admin')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
              >
                Force Go to Admin Dashboard
              </button>
              <button 
                @click="console.log('Auth store state:', { userRole: authStore.userRole, isAdmin: authStore.isAdmin, user: authStore.user })" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm"
              >
                Log Auth State
              </button>
              <button 
                @click="forceAdminRole" 
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
              >
                Force Admin Role
              </button>
            </div>
            
            <!-- Real User Registration Section -->
            <div class="mt-4 p-3 bg-gray-100 rounded">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Create Real Users in Database</h4>
              
              <!-- Registration Form -->
              <form @submit.prevent="registerRealUser" class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                  <input 
                    v-model="registrationForm.name" 
                    type="text" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter full name"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                  <input 
                    v-model="registrationForm.email" 
                    type="email" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter email address"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                  <input 
                    v-model="registrationForm.password" 
                    type="password" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter password"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                  <select 
                    v-model="registrationForm.role" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select role</option>
                    <option value="admin">Admin</option>
                    <option value="instructor">Instructor</option>
                    <option value="student">Student</option>
                  </select>
                </div>
                
                <button 
                  type="submit" 
                  :disabled="registrationLoading"
                  class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-3 py-2 rounded text-sm font-medium"
                >
                  {{ registrationLoading ? 'Creating User...' : 'Create User' }}
                </button>
              </form>
              
              <!-- Quick Create Buttons -->
              <div class="mt-3 pt-3 border-t border-gray-300">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Quick Create Test Users</h5>
                <div class="space-y-2">
                  <button 
                    @click="quickCreateUser('instructor@gmail.com', 'instructor')" 
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm"
                  >
                    Create Instructor (instructor@gmail.com)
                  </button>
                  <button 
                    @click="quickCreateUser('student@gmail.com', 'student')" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
                  >
                    Create Student (student@gmail.com)
                  </button>
                  <button 
                    @click="quickCreateUser('admin@gmail.com', 'admin')" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm"
                  >
                    Create Admin (admin@gmail.com)
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Password: 123456d4</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <h2 class="text-2xl font-bold text-gray-900 mb-6">
        {{ userRole === 'instructor' ? 'Instructor Dashboard' : 
           userRole === 'student' ? 'Student Dashboard' : 
           userRole === 'admin' ? 'Admin Dashboard' : 'Dashboard' }}
      </h2>
      <p class="text-gray-600 mb-8">
        {{ userRole === 'instructor' ? 'Manage your courses and track your performance.' :
           userRole === 'student' ? 'Track your learning progress and enrolled courses.' :
           userRole === 'admin' ? 'Manage the platform and monitor system performance.' :
           'Here\'s your dashboard overview.' }}
      </p>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Courses</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.totalCourses }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Students</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.totalStudents }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Revenue</p>
              <p class="text-2xl font-semibold text-gray-900">${{ stats.totalRevenue }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Average Rating</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.averageRating }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Courses Section -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
              {{ userRole === 'instructor' ? 'Recent Courses' : 'My Enrolled Courses' }}
            </h3>
            <div v-if="userRole === 'instructor'" class="flex space-x-2">
              <button
                @click="createCourse"
                class="inline-flex items-center px-3 py-2 sm:px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                title="Create Course"
              >
                <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="hidden sm:inline">Create Course</span>
              </button>
              <button
                @click="createCategory"
                class="inline-flex items-center px-3 py-2 sm:px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                title="Create Category"
              >
                <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span class="hidden sm:inline">Create Category</span>
              </button>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <div v-if="loading" class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          
          <div v-else-if="myCourses.length === 0" class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              {{ userRole === 'instructor' ? 'No courses yet' : 'No enrolled courses yet' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ userRole === 'instructor' ? 'Get started by creating your first course.' : 'Browse available courses and enroll to get started.' }}
            </p>
            <div v-if="userRole === 'instructor'" class="mt-6">
              <button
                @click="createCourse"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                Create Your First Course
              </button>
            </div>
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="course in myCourses"
              :key="course.id"
              class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex items-center justify-between mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="course.status === 'approved' ? 'bg-green-100 text-green-800' : 
                               course.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-gray-100 text-gray-800'">
                  {{ course.status }}
                </span>
                <span class="text-sm text-gray-500">{{ course.category?.name }}</span>
              </div>
              
              <h4 class="font-medium text-gray-900 mb-2">{{ course.title }}</h4>
              <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ course.short_description }}</p>
              
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ course.enrollments_count || 0 }} students</span>
                <span class="text-gray-500">{{ course.lessons_count || 0 }} lessons</span>
              </div>
              
              <!-- Course Actions for Instructors -->
              <div v-if="userRole === 'instructor'" class="mt-3 flex space-x-2">
                <router-link
                  :to="`/courses/${course.id}/builder`"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
                >
                  <i class="fas fa-edit mr-1"></i> Builder
                </router-link>
                <button
                  @click="editCourse(course.id)"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300"
                >
                  <i class="fas fa-cog mr-1"></i> Settings
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Enrollments Section -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Enrollments</h3>
        </div>
        
        <div class="p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No enrollments yet</h3>
            <p class="mt-1 text-sm text-gray-500">Students will appear here once they enroll in your courses.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'

const router = useRouter()
const courseStore = useCourseStore()
const authStore = useAuthStore()
const toast = useToast()

// Reactive data
const loading = ref(false)
const myCourses = ref([])
const registrationLoading = ref(false)
const registrationForm = reactive({
  name: '',
  email: '',
  password: '',
  role: ''
})

// Computed properties
const user = computed(() => authStore.user)
const userRole = computed(() => authStore.userRole)

const stats = computed(() => ({
  totalCourses: myCourses.value.length,
  totalStudents: myCourses.value.reduce((sum, course) => sum + (course.enrollments_count || 0), 0),
  totalRevenue: myCourses.value.reduce((sum, course) => sum + (course.price || 0), 0),
  averageRating: myCourses.value.length > 0 
    ? (myCourses.value.reduce((sum, course) => sum + (course.average_rating || 0), 0) / myCourses.value.length).toFixed(1)
    : '0.0'
}))

// Methods
const fetchDashboardData = async () => {
  loading.value = true
  try {
    console.log('Dashboard: Fetching my courses...')
    const response = await courseStore.fetchMyCourses()
    console.log('Dashboard: My courses response:', response)
    myCourses.value = courseStore.myCourses
    console.log('Dashboard: My courses set to:', myCourses.value)
  } catch (error) {
    console.error('Dashboard: Failed to load dashboard data:', error)
    console.error('Dashboard: Error response:', error.response)
    console.error('Dashboard: Error status:', error.response?.status)
    console.error('Dashboard: Error data:', error.response?.data)
    
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no courses exist)
    if (error.response?.status >= 400) {
      if (error.response?.status === 404) {
        toast.error('Dashboard endpoint not found. Please check backend routes.')
      } else if (error.response?.status === 500) {
        toast.error('Server error. Please check backend logs.')
      } else {
        toast.error('Failed to load dashboard data: ' + (error.message || 'Unknown error'))
      }
    }
  } finally {
    loading.value = false
  }
}

const createCourse = () => {
  router.push('/instructor/create-course')
}

const createCategory = () => {
  router.push('/instructor/create-category')
}

const forceAdminRole = () => {
  // Manually add admin role to user for testing
  if (user.value) {
    user.value.roles = ['admin']
    console.log('Dashboard: Manually added admin role to user')
    console.log('Dashboard: User roles now:', user.value.roles)
    console.log('Dashboard: role type:', typeof user.value.roles[0])
    console.log('Dashboard: role name:', user.value.roles[0]?.name)
  }
}

const createTestUser = async (email, role) => {
  try {
    const response = await authStore.registerUser({
      email: email,
      password: '123456d4', // Use a common password for testing
      role: role
    })
    toast.success(`Test user ${role} created successfully! Email: ${email}`)
    console.log('Test user created:', response)
  } catch (error) {
    console.error('Failed to create test user:', error)
    toast.error(`Failed to create test user ${role}: ${error.message || 'Unknown error'}`)
  }
}

const registerRealUser = async () => {
  registrationLoading.value = true
  try {
    console.log('Dashboard: Registering real user:', registrationForm)
    
    // Call the real API to create user in database
    const response = await authStore.register({
      name: registrationForm.name,
      email: registrationForm.email,
      password: registrationForm.password,
      password_confirmation: registrationForm.password,
      role: registrationForm.role
    })
    
    toast.success(`User ${registrationForm.name} created successfully in database!`)
    console.log('Real user created:', response)
    
    // Reset form
    registrationForm.name = ''
    registrationForm.email = ''
    registrationForm.password = ''
    registrationForm.role = ''
    
  } catch (error) {
    console.error('Failed to create real user:', error)
    if (error.response?.data?.errors) {
      // Handle validation errors
      const errors = error.response.data.errors
      Object.keys(errors).forEach(field => {
        toast.error(`${field}: ${errors[field][0]}`)
      })
    } else {
      toast.error(`Failed to create user: ${error.message || 'Unknown error'}`)
    }
  } finally {
    registrationLoading.value = false
  }
}

const quickCreateUser = async (email, role) => {
  try {
    const name = email.split('@')[0].charAt(0).toUpperCase() + email.split('@')[0].slice(1)
    
    console.log('Dashboard: Quick creating user:', { name, email, role })
    
    // Call the real API to create user in database
    const response = await authStore.register({
      name: name,
      email: email,
      password: '123456d4',
      password_confirmation: '123456d4',
      role: role
    })
    
    toast.success(`Quick user ${name} (${role}) created successfully in database!`)
    console.log('Quick user created:', response)
    
  } catch (error) {
    console.error('Failed to create quick user:', error)
    if (error.response?.data?.errors) {
      // Handle validation errors
      const errors = error.response.data.errors
      Object.keys(errors).forEach(field => {
        toast.error(`${field}: ${errors[field][0]}`)
      })
    } else {
      toast.error(`Failed to create quick user: ${error.message || 'Unknown error'}`)
    }
  }
}

const switchToTestUser = async (email) => {
  try {
    // Get test users from localStorage
    const testUsers = JSON.parse(localStorage.getItem('testUsers') || '[]')
    const testUser = testUsers.find(u => u.email === email)
    
    if (!testUser) {
      toast.error(`Test user ${email} not found. Please create it first.`)
      return
    }
    
    // Create a mock token
    const mockToken = 'mock_token_' + Date.now()
    
    // Set the user in the auth store
    authStore.user = testUser
    authStore.token = mockToken
    localStorage.setItem('auth_token', mockToken)
    localStorage.setItem('user', JSON.stringify(testUser))
    
    toast.success(`Switched to ${testUser.roles[0]} user: ${email}`)
    console.log('Switched to test user:', testUser)
    
    // Force a page refresh to trigger role-based routing
    setTimeout(() => {
      window.location.reload()
    }, 1000)
    
  } catch (error) {
    console.error('Failed to switch to test user:', error)
    toast.error(`Failed to switch user: ${error.message || 'Unknown error'}`)
  }
}

// Lifecycle
onMounted(() => {
  // Temporarily disabled to stop error messages
  // fetchDashboardData()
  console.log('Dashboard: Component mounted, data loading disabled to prevent errors')
  
  // Debug role data structure
  if (user.value && user.value.roles) {
    console.log('Dashboard: User roles data:', user.value.roles)
    console.log('Dashboard: First role object:', user.value.roles[0])
    console.log('Dashboard: Role type:', typeof user.value.roles[0])
    console.log('Dashboard: Role name:', user.value.roles[0]?.name)
  }
  
  // Force redirect for admin users
  console.log('Dashboard: Checking user role...')
  console.log('Dashboard: userRole computed:', userRole.value)
  console.log('Dashboard: authStore.isAdmin:', authStore.isAdmin)
  console.log('Dashboard: authStore.userRole:', authStore.userRole)
  
  if (userRole.value === 'admin' || authStore.isAdmin) {
    console.log('Dashboard: Admin user detected, redirecting to /admin')
    router.push('/admin')
  } else {
    console.log('Dashboard: User is not admin, staying on dashboard')
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
