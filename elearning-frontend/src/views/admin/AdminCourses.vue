<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Course Management</h1>
            <p class="mt-2 text-gray-600">Manage all platform courses</p>
          </div>
          <div class="flex space-x-3">
            <button 
              @click="refreshData" 
              :disabled="loading"
              class="btn btn-secondary flex items-center space-x-2"
            >
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Refreshing...' : 'Refresh' }}</span>
            </button>
            <router-link to="/admin" class="btn btn-primary">
              Back to Dashboard
            </router-link>
          </div>
        </div>
      </div>

      <!-- Access Control -->
      <div v-if="!authStore.isAuthenticated" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8 text-center">
        <h3 class="text-lg font-medium text-red-800 mb-2">Access Denied</h3>
        <p class="text-red-600">Please log in to access course management.</p>
        <router-link to="/login" class="inline-block mt-3 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
          Go to Login
        </router-link>
      </div>

      <div v-else-if="!authStore.isAdmin" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8 text-center">
        <h3 class="text-lg font-medium text-red-800 mb-2">Access Denied</h3>
        <p class="text-red-600">You do not have admin privileges to access course management.</p>
        <router-link to="/" class="inline-block mt-3 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
          Go Home
        </router-link>
      </div>

      <!-- Course Management Content -->
      <div v-else>
        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
              <select 
                v-model="filters.status" 
                @change="applyFilters"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="draft">Draft</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category Filter</label>
              <select 
                v-model="filters.category" 
                @change="applyFilters"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
              <input 
                v-model="filters.search" 
                @input="debounceSearch"
                type="text" 
                placeholder="Search courses..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div class="flex items-end">
              <button 
                @click="clearFilters" 
                class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedCourses.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <div class="flex items-center justify-between">
            <span class="text-blue-800">
              {{ selectedCourses.length }} course(s) selected
            </span>
            <div class="flex space-x-2">
              <button 
                @click="bulkApprove"
                :disabled="bulkLoading"
                class="btn btn-success"
              >
                {{ bulkLoading ? 'Approving...' : 'Approve Selected' }}
              </button>
              <button 
                @click="bulkReject"
                :disabled="bulkLoading"
                class="btn btn-danger"
              >
                {{ bulkLoading ? 'Rejecting...' : 'Reject Selected' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Courses Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">
                Courses ({{ pagination.total }})
              </h3>
              <div class="flex items-center space-x-2">
                <label class="flex items-center space-x-2">
                  <input 
                    type="checkbox" 
                    @change="toggleSelectAll"
                    :checked="selectAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  <span class="text-sm text-gray-700">Select All</span>
                </label>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Course
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Instructor
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Category
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Price
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading" class="bg-white">
                  <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex justify-center">
                      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>
                  </td>
                </tr>
                
                <tr v-else-if="courses.length === 0" class="bg-white">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    No courses found
                  </td>
                </tr>
                
                <tr 
                  v-else
                  v-for="course in courses" 
                  :key="course.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <input 
                        type="checkbox" 
                        :value="course.id"
                        v-model="selectedCourses"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                      />
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ course.title }}</div>
                        <div class="text-sm text-gray-500">{{ course.slug }}</div>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ course.instructor?.name || 'Unknown' }}</div>
                    <div class="text-sm text-gray-500">{{ course.instructor?.email || 'No email' }}</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ course.category?.name || 'No Category' }}
                    </span>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="{
                        'bg-yellow-100 text-yellow-800': course.status === 'pending',
                        'bg-green-100 text-green-800': course.status === 'approved',
                        'bg-red-100 text-red-800': course.status === 'rejected',
                        'bg-gray-100 text-gray-800': course.status === 'draft'
                      }">
                      {{ course.status }}
                    </span>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${{ course.price || 0 }}
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button
                        v-if="course.status === 'pending'"
                        @click="approveCourse(course.id)"
                        :disabled="course.approving"
                        class="text-green-600 hover:text-green-900 disabled:opacity-50"
                      >
                        {{ course.approving ? 'Approving...' : 'Approve' }}
                      </button>
                      
                      <button
                        v-if="course.status === 'pending'"
                        @click="rejectCourse(course.id)"
                        :disabled="course.rejecting"
                        class="text-red-600 hover:text-red-900 disabled:opacity-50"
                      >
                        {{ course.rejecting ? 'Rejecting...' : 'Reject' }}
                      </button>
                      
                      <button
                        @click="viewCourseDetail(course.id)"
                        class="text-blue-600 hover:text-blue-900 mr-2"
                        title="View Course Details"
                      >
                        <i class="fas fa-eye"></i> View
                      </button>
                      
                      <button
                        @click="viewCourse(course.id)"
                        class="text-green-600 hover:text-green-900"
                        title="Preview Course Content"
                      >
                        <i class="fas fa-eye"></i> Content
                      </button>
                      
                      <button
                        @click="deleteCourse(course.id)"
                        :disabled="course.deleting"
                        class="text-red-600 hover:text-red-900 disabled:opacity-50"
                      >
                        {{ course.deleting ? 'Deleting...' : 'Delete' }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
              </div>
              <div class="flex space-x-2">
                <button 
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button 
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page >= pagination.last_page"
                  class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
            </div>
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
import { adminAPI, categoryAPI } from '@/services/api'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const bulkLoading = ref(false)
const courses = ref([])
const categories = ref([])
const selectedCourses = ref([])
const selectAll = ref(false)

// Filters
const filters = ref({
  status: '',
  category: '',
  search: ''
})

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
  last_page: 1
})

// Debounce search
let searchTimeout = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

// Fetch courses
const fetchCourses = async () => {
  if (!authStore.isAuthenticated || !authStore.isAdmin) {
    return
  }

  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      ...filters.value
    }
    
    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })
    
    const response = await adminAPI.getAllCourses(params)
    const data = response.data.data
    
    courses.value = data.data || []
    pagination.value = {
      current_page: data.current_page || 1,
      per_page: data.per_page || 15,
      total: data.total || 0,
      from: data.from || 0,
      to: data.to || 0,
      last_page: data.last_page || 1
    }
    
    console.log('✅ Courses loaded successfully:', courses.value.length)
    
  } catch (error) {
    console.error('❌ Error fetching courses:', error)
    toast.error('Failed to load courses')
  } finally {
    loading.value = false
  }
}

// Fetch categories
const fetchCategories = async () => {
  try {
    const response = await categoryAPI.getAll()
    categories.value = response.data.data || []
  } catch (error) {
    console.error('❌ Error fetching categories:', error)
  }
}

// Apply filters
const applyFilters = () => {
  pagination.value.current_page = 1
  fetchCourses()
}

// Clear filters
const clearFilters = () => {
  filters.value = {
    status: '',
    category: '',
    search: ''
  }
  pagination.value.current_page = 1
  fetchCourses()
}

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page
    fetchCourses()
  }
}

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedCourses.value = courses.value.map(course => course.id)
  } else {
    selectedCourses.value = []
  }
}

// Watch selected courses
const updateSelectAll = computed(() => {
  if (courses.value.length === 0) return false
  return selectedCourses.value.length === courses.value.length
})

// Approve course
const approveCourse = async (courseId) => {
  try {
    const course = courses.value.find(c => c.id === courseId)
    if (course) course.approving = true
    
    await adminAPI.approveCourse(courseId)
    
    toast.success('Course approved successfully!')
    await fetchCourses()
    
  } catch (error) {
    console.error('❌ Error approving course:', error)
    toast.error('Failed to approve course')
  } finally {
    const course = courses.value.find(c => c.id === courseId)
    if (course) course.approving = false
  }
}

// Reject course
const rejectCourse = async (courseId) => {
  try {
    const course = courses.value.find(c => c.id === courseId)
    if (course) course.rejecting = true
    
    await adminAPI.rejectCourse(courseId, 'Rejected by admin')
    
    toast.success('Course rejected successfully!')
    await fetchCourses()
    
  } catch (error) {
    console.error('❌ Error rejecting course:', error)
    toast.error('Failed to reject course')
  } finally {
    const course = courses.value.find(c => c.id === courseId)
    if (course) course.rejecting = false
  }
}

// Bulk approve
const bulkApprove = async () => {
  if (selectedCourses.value.length === 0) return
  
  bulkLoading.value = true
  try {
    await adminAPI.bulkApproveCourses({ course_ids: selectedCourses.value })
    
    toast.success(`${selectedCourses.value.length} course(s) approved successfully!`)
    selectedCourses.value = []
    selectAll.value = false
    await fetchCourses()
    
  } catch (error) {
    console.error('❌ Error bulk approving courses:', error)
    toast.error('Failed to approve courses')
  } finally {
    bulkLoading.value = false
  }
}

// Bulk reject
const bulkReject = async () => {
  if (selectedCourses.value.length === 0) return
  
  bulkLoading.value = true
  try {
    // Note: You might need to implement bulk reject in your backend
    for (const courseId of selectedCourses.value) {
      await adminAPI.rejectCourse(courseId, 'Bulk rejected by admin')
    }
    
    toast.success(`${selectedCourses.value.length} course(s) rejected successfully!`)
    selectedCourses.value = []
    selectAll.value = false
    await fetchCourses()
    
  } catch (error) {
    console.error('❌ Error bulk rejecting courses:', error)
    toast.error('Failed to reject courses')
  } finally {
    bulkLoading.value = false
  }
}

// View course detail
const viewCourseDetail = (courseId) => {
  // Find the course to get its slug
  const course = courses.value.find(c => c.id === courseId)
  if (course && course.slug) {
    // Navigate to the course detail page
    router.push(`/courses/${course.slug}`)
  } else {
    toast.error('Course not found or missing slug')
  }
}

// View course content
const viewCourse = (courseId) => {
  // Find the course to get its slug
  const course = courses.value.find(c => c.id === courseId)
  if (course && course.slug) {
    // Navigate to the course lessons page for admin preview
    router.push(`/courses/${course.slug}/lessons?admin=true`)
  } else {
    toast.error('Course not found or missing slug')
  }
}

// Delete course
const deleteCourse = async (courseId) => {
  if (!confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
    return
  }
  
  try {
    const course = courses.value.find(c => c.id === courseId)
    if (course) course.deleting = true
    
    // Note: You might need to implement delete course in your backend
    // await adminAPI.deleteCourse(courseId)
    
    toast.success('Course deleted successfully!')
    await fetchCourses()
    
  } catch (error) {
    console.error('❌ Error deleting course:', error)
    toast.error('Failed to delete course')
  } finally {
    const course = courses.value.find(c => c.id === courseId)
    if (course) course.deleting = false
  }
}

// Refresh data
const refreshData = async () => {
  await Promise.all([
    fetchCourses(),
    fetchCategories()
  ])
}

// Component lifecycle
onMounted(async () => {
  console.log('🚀 AdminCourses component mounted')
  
  if (authStore.isAuthenticated && authStore.isAdmin) {
    await Promise.all([
      fetchCourses(),
      fetchCategories()
    ])
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

.btn-secondary {
  @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
}

.btn-success {
  @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
}

.btn-danger {
  @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
}
</style>
