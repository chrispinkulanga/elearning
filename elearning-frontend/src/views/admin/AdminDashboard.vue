<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="text-gray-600 mt-1">Manage the eLearning platform</p>
          </div>
            <button 
              @click="refreshData" 
              :disabled="loading"
          class="btn btn-primary flex items-center"
            >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Refresh
            </button>
      </div>

      <!-- Stats Cards -->
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
                <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalUsers }}</p>
            </div>
          </div>
        </div>

        <!-- Total Courses -->
          <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Pending Courses -->
          <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Pending Courses</p>
                <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.pendingCourses }}</p>
            </div>
          </div>
        </div>

        <!-- Total Revenue -->
                                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6 border border-gray-200">
             <div class="flex items-center">
               <div class="flex-shrink-0">
                 <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                   <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                   </svg>
                 </div>
               </div>
               <div class="ml-2 sm:ml-4">
                 <p class="text-xs sm:text-sm font-medium text-gray-600">Total Revenue</p>
                 <p class="text-lg sm:text-2xl font-bold text-gray-900">${{ stats.totalRevenue }}</p>
             </div>
           </div>
         </div>
       </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
         <!-- Recent Users -->
         <div class="bg-white rounded-lg shadow-sm border border-gray-200">
           <div class="px-6 py-4 border-b border-gray-200">
             <div class="flex items-center justify-between">
               <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
               <router-link to="/admin/users" class="text-sm text-blue-600 hover:text-blue-700">
                 View All
               </router-link>
             </div>
           </div>
           <div class="p-6">
             <div v-if="loading" class="flex justify-center py-8">
               <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
             </div>
             <div v-else-if="recentUsers.length > 0" class="space-y-4">
               <div
                 v-for="user in recentUsers"
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
               No users found
             </div>
           </div>
         </div>

         <!-- Pending Courses -->
         <div class="bg-white rounded-lg shadow-sm border border-gray-200">
           <div class="px-6 py-4 border-b border-gray-200">
             <div class="flex items-center justify-between">
               <h3 class="text-lg font-medium text-gray-900">Pending Course Approvals</h3>
               <router-link to="/admin/courses" class="text-sm text-blue-600 hover:text-blue-700">
                 View All
               </router-link>
             </div>
           </div>
           <div class="p-6">
             <div v-if="courseLoading" class="flex justify-center py-8">
               <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
             </div>
             <div v-else-if="pendingCourses.length > 0" class="space-y-4">
               <div
                 v-for="course in pendingCourses"
                 :key="course.id"
                 class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
               >
                 <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900">{{ course.title }}</h4>
                    <p class="text-sm text-gray-500 mt-1">
                      by {{ course.instructor?.name || 'Unknown Instructor' }}
                    </p>
                   </div>
                  <div class="flex space-x-2 ml-4">
                     <button
                       @click="approveCourse(course.id)"
                       :disabled="course.approving"
                      class="btn btn-primary text-xs px-3 py-1"
                     >
                       <span v-if="course.approving">Approving...</span>
                       <span v-else>Approve</span>
                     </button>
                     <button
                       @click="rejectCourse(course.id)"
                       :disabled="course.rejecting"
                      class="btn btn-danger text-xs px-3 py-1"
                     >
                       <span v-if="course.rejecting">Rejecting...</span>
                       <span v-else>Reject</span>
                     </button>
                   </div>
                 </div>
               </div>
             </div>
             <div v-else class="text-center py-8 text-gray-500">
               No pending courses
             </div>
           </div>
         </div>
       </div>

       <!-- Quick Actions -->
      <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
         <router-link to="/admin/users" class="bg-white rounded-lg shadow-sm p-4 sm:p-6 hover:shadow-md transition-shadow duration-200 border border-gray-200">
           <div class="flex items-center">
             <div class="flex-shrink-0">
               <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                 <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                 </svg>
               </div>
             </div>
             <div class="ml-2 sm:ml-4">
               <h3 class="text-sm sm:text-lg font-medium text-gray-900">User Management</h3>
               <p class="text-xs sm:text-sm text-gray-600">Manage platform users</p>
             </div>
           </div>
         </router-link>

         <router-link to="/admin/courses" class="bg-white rounded-lg shadow-sm p-4 sm:p-6 hover:shadow-md transition-shadow duration-200 border border-gray-200">
           <div class="flex items-center">
             <div class="flex-shrink-0">
               <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                 <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                 </svg>
               </div>
             </div>
             <div class="ml-2 sm:ml-4">
               <h3 class="text-sm sm:text-lg font-medium text-gray-900">Course Management</h3>
              <p class="text-xs sm:text-sm text-gray-600">Manage platform courses</p>
             </div>
           </div>
         </router-link>

         <router-link to="/admin/analytics" class="bg-white rounded-lg shadow-sm p-4 sm:p-6 hover:shadow-md transition-shadow duration-200 border border-gray-200">
           <div class="flex items-center">
             <div class="flex-shrink-0">
               <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                 <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                 </svg>
               </div>
             </div>
             <div class="ml-2 sm:ml-4">
               <h3 class="text-sm sm:text-lg font-medium text-gray-900">Analytics</h3>
              <p class="text-xs sm:text-sm text-gray-600">View platform analytics</p>
             </div>
           </div>
         </router-link>

         <router-link to="/admin/alumni" class="bg-white rounded-lg shadow-sm p-4 sm:p-6 hover:shadow-md transition-shadow duration-200 border border-gray-200">
           <div class="flex items-center">
             <div class="flex-shrink-0">
               <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                 <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                 </svg>
               </div>
             </div>
             <div class="ml-2 sm:ml-4">
               <h3 class="text-sm sm:text-lg font-medium text-gray-900">Alumni Management</h3>
               <p class="text-xs sm:text-sm text-gray-600">Manage alumni network</p>
             </div>
           </div>
         </router-link>
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
const courseLoading = ref(false)
const stats = ref({
  totalUsers: 0,
  totalCourses: 0,
  pendingCourses: 0,
  totalRevenue: 0
})
const recentUsers = ref([])
const pendingCourses = ref([])

// Check if user has admin access
const hasAdminAccess = computed(() => {
  return authStore.isAuthenticated && authStore.isAdmin
})

// Fetch dashboard statistics
const fetchDashboardStats = async () => {
  if (!hasAdminAccess.value) {
    console.log('❌ User does not have admin access')
    toast.error('Access denied. Admin privileges required.')
    return
  }
  
  loading.value = true
  try {
    console.log('🚀 Fetching dashboard stats...')
    
    // Fetch users
    const usersResponse = await adminAPI.getAllUsers()
    const users = usersResponse.data.data || []
    console.log('✅ Users fetched:', users.length)
    
    // Try to get total courses from categories first (same as Categories page)
    let totalCourses = 0
    try {
      const categoriesResponse = await fetch('/api/categories', {
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
          'Content-Type': 'application/json'
        }
      })
      
      if (categoriesResponse.ok) {
        const categoriesData = await categoriesResponse.json()
        const categories = categoriesData.data || []
        
        // Calculate total courses from categories (same logic as Categories page)
        totalCourses = categories.reduce((sum, cat) => sum + (cat.courses_count || 0), 0)
        console.log('✅ Categories fetched. Total courses from categories:', totalCourses)
      } else {
        throw new Error('Categories API failed')
      }
    } catch (categoriesError) {
      console.log('⚠️ Categories API failed, trying admin courses API as fallback...')
      
      // Fallback: try admin courses API
      try {
    const coursesResponse = await adminAPI.getAllCourses()
        totalCourses = coursesResponse.data?.data?.total || 0
        console.log('✅ Fallback: Courses fetched from admin API. Total:', totalCourses)
      } catch (adminError) {
        console.error('❌ Both categories and admin courses API failed')
        totalCourses = 0
      }
    }
    
    // Fetch pending courses - use total count from pagination
    const pendingResponse = await adminAPI.getPendingCourses()
    const totalPending = pendingResponse.data?.data?.total || 0
    console.log('✅ Pending courses fetched. Total:', totalPending)
    
    // Calculate stats
      stats.value = {
      totalUsers: users.length,
      totalCourses: totalCourses, // Use count from categories or fallback
      pendingCourses: totalPending,
      totalRevenue: 0 // TODO: Implement revenue calculation
    }
    
    // Set recent users (last 5)
    recentUsers.value = users.slice(0, 5)
    
    // Set pending courses for display
    pendingCourses.value = pendingResponse.data?.data?.data || []
    
    console.log('✅ Dashboard stats loaded successfully:', stats.value)
    
  } catch (error) {
    console.error('❌ Error fetching dashboard stats:', error)
    
    // Handle specific error cases
    if (error.response?.status === 403) {
      toast.error('Access denied. Please check your admin privileges.')
    } else if (error.response?.status === 401) {
      toast.error('Authentication required. Please log in again.')
    } else {
      toast.error('Failed to load dashboard statistics: ' + (error.message || 'Unknown error'))
    }
  } finally {
    loading.value = false
  }
}

// Fetch pending courses separately
const fetchPendingCourses = async () => {
  if (!hasAdminAccess.value) return
  
  courseLoading.value = true
  try {
    const response = await adminAPI.getPendingCourses()
    pendingCourses.value = response.data?.data?.data || []
    console.log('✅ Pending courses loaded:', pendingCourses.value.length)
  } catch (error) {
    console.error('❌ Error fetching pending courses:', error)
    toast.error('Failed to load pending courses')
  } finally {
    courseLoading.value = false
  }
}

// Approve course
const approveCourse = async (courseId) => {
  try {
    const course = pendingCourses.value.find(c => c.id === courseId)
    if (course) course.approving = true
    
    await adminAPI.approveCourse(courseId)
    
    toast.success('Course approved successfully!')
    
    // Refresh data
    await Promise.all([
      fetchDashboardStats(),
      fetchPendingCourses()
    ])
    
  } catch (error) {
    console.error('❌ Error approving course:', error)
    toast.error('Failed to approve course')
  } finally {
    const course = pendingCourses.value.find(c => c.id === courseId)
    if (course) course.approving = false
  }
}

// Reject course
const rejectCourse = async (courseId) => {
  try {
    const course = pendingCourses.value.find(c => c.id === courseId)
    if (course) course.rejecting = true
    
    await adminAPI.rejectCourse(courseId, 'Rejected by admin')
    
    toast.success('Course rejected successfully!')
    
    // Refresh data
    await Promise.all([
      fetchDashboardStats(),
      fetchPendingCourses()
    ])
    
  } catch (error) {
    console.error('❌ Error rejecting course:', error)
    toast.error('Failed to reject course')
  } finally {
    const course = pendingCourses.value.find(c => c.id === courseId)
    if (course) course.rejecting = false
  }
}

// Refresh all data
const refreshData = async () => {
  await Promise.all([
    fetchDashboardStats(),
    fetchPendingCourses()
  ])
}

// Component lifecycle
onMounted(async () => {
  console.log('🚀 AdminDashboard component mounted')
  console.log('🔐 Auth state:', {
    isAuthenticated: authStore.isAuthenticated,
    isAdmin: authStore.isAdmin,
    userRole: authStore.userRole
  })
  
  if (hasAdminAccess.value) {
    await fetchDashboardStats()
  } else {
    console.log('❌ User does not have admin access')
    toast.error('Access denied. Admin privileges required.')
    // Redirect to home or show access denied message
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

.btn-danger {
  @apply bg-red-600 text-white hover:bg-red-700 focus:ring-blue-500;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}
</style>
