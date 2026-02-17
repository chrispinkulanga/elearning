<template>
  <div>
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <p class="text-gray-600 text-xs sm:text-sm mb-2">Total Enrollments</p>
        <p class="text-xl sm:text-3xl font-bold text-gray-900">
          {{ totalEnrollments }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <p class="text-gray-600 text-xs sm:text-sm mb-2">Active Enrollments</p>
        <p class="text-xl sm:text-3xl font-bold text-green-600">
          {{ activeEnrollments }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <p class="text-gray-600 text-xs sm:text-sm mb-2">Pending Approvals</p>
        <p class="text-xl sm:text-3xl font-bold text-yellow-600">
          {{ pendingEnrollments }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <p class="text-gray-600 text-xs sm:text-sm mb-2">Completed</p>
        <p class="text-xl sm:text-3xl font-bold text-blue-600">
          {{ completedEnrollments }}
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex gap-4">
        <div class="flex-1">
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Search enrollments..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div class="min-w-[200px]">
          <select
            v-model="statusFilter"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Enrollments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ID
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Student
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Course
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Instructor
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Progress
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Grade
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Enrolled
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" class="animate-pulse">
              <td colspan="10" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            <tr v-else-if="filteredEnrollments.length === 0" class="text-center">
              <td colspan="10" class="px-6 py-4 text-gray-500">
                No enrollments found
              </td>
            </tr>
            <tr v-else v-for="enrollment in paginatedEnrollments" :key="enrollment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ enrollment.id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ enrollment.student?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ enrollment.student?.email || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ enrollment.course?.title || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ enrollment.course?.instructor?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-yellow-100 text-yellow-800': enrollment.status === 'pending',
                    'bg-green-100 text-green-800': enrollment.status === 'approved',
                    'bg-blue-100 text-blue-800': enrollment.status === 'completed',
                    'bg-red-100 text-red-800': enrollment.status === 'rejected'
                  }"
                >
                  {{ enrollment.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ enrollment.progress_percentage || 0 }}%
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ enrollment.grade || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(enrollment.enrolled_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                  <button
                    @click="viewEnrollment(enrollment)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  
                  <button
                    v-if="enrollment.status === 'pending'"
                    @click="approveEnrollment(enrollment.id)"
                    class="text-green-600 hover:text-green-900"
                    title="Approve"
                  >
                    <CheckCircleIcon class="w-4 h-4" />
                  </button>
                  
                  <button
                    v-if="enrollment.status === 'pending'"
                    @click="rejectEnrollment(enrollment.id)"
                    class="text-red-600 hover:text-red-900"
                    title="Reject"
                  >
                    <XCircleIcon class="w-4 h-4" />
                  </button>
                  
                  <button
                    @click="deleteEnrollment(enrollment)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredEnrollments.length) }}</span>
              of
              <span class="font-medium">{{ filteredEnrollments.length }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                @click="currentPage = Math.max(1, currentPage - 1)"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ChevronLeftIcon class="h-5 w-5" />
              </button>
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="currentPage = page"
                :class="[
                  page === currentPage
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                ]"
              >
                {{ page }}
              </button>
              <button
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ChevronRightIcon class="h-5 w-5" />
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Enrollment Details Modal -->
    <div
      v-if="showDetailsModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Enrollment Details</h3>
            <button
              @click="showDetailsModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          
          <div v-if="selectedEnrollment" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-3">Student Information</h4>
              <div class="space-y-2">
                <p><strong>Name:</strong> {{ selectedEnrollment.student?.name }}</p>
                <p><strong>Email:</strong> {{ selectedEnrollment.student?.email }}</p>
              </div>
            </div>
            
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-3">Course Information</h4>
              <div class="space-y-2">
                <p><strong>Course:</strong> {{ selectedEnrollment.course?.title }}</p>
                <p><strong>Instructor:</strong> {{ selectedEnrollment.course?.instructor?.name }}</p>
              </div>
            </div>
            
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-3">Enrollment Details</h4>
              <div class="space-y-2">
                <p><strong>Status:</strong> 
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2"
                    :class="{
                      'bg-yellow-100 text-yellow-800': selectedEnrollment.status === 'pending',
                      'bg-green-100 text-green-800': selectedEnrollment.status === 'approved',
                      'bg-blue-100 text-blue-800': selectedEnrollment.status === 'completed',
                      'bg-red-100 text-red-800': selectedEnrollment.status === 'rejected'
                    }"
                  >
                    {{ selectedEnrollment.status }}
                  </span>
                </p>
                <p><strong>Enrollment Date:</strong> {{ formatDate(selectedEnrollment.enrolled_at) }}</p>
                <p v-if="selectedEnrollment.completed_at">
                  <strong>Completion Date:</strong> {{ formatDate(selectedEnrollment.completed_at) }}
                </p>
              </div>
            </div>
            
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-3">Progress</h4>
              <div class="space-y-2">
                <p><strong>Progress:</strong> {{ selectedEnrollment.progress_percentage || 0 }}%</p>
                <p v-if="selectedEnrollment.grade">
                  <strong>Grade:</strong> {{ selectedEnrollment.grade }}
                </p>
              </div>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end">
            <button
              @click="showDetailsModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
          <p class="text-sm text-gray-500 mb-6">
            Are you sure you want to delete enrollment "{{ enrollmentToDelete?.student?.name }} - {{ enrollmentToDelete?.course?.title }}"?
            This action cannot be undone.
          </p>
          <div class="flex gap-3 justify-center">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import {
  PlusIcon,
  EyeIcon,
  CheckCircleIcon,
  XCircleIcon,
  TrashIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const toast = useToast()

// State
const enrollments = ref([])
const loading = ref(false)
const searchTerm = ref('')
const statusFilter = ref('all')
const showDetailsModal = ref(false)
const showDeleteModal = ref(false)
const selectedEnrollment = ref(null)
const enrollmentToDelete = ref(null)
const currentPage = ref(1)
const itemsPerPage = 10

// Computed
const filteredEnrollments = computed(() => {
  let filtered = enrollments.value
  
  if (searchTerm.value) {
    filtered = filtered.filter(enrollment =>
      enrollment.student?.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      enrollment.student?.email?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      enrollment.course?.title?.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  }
  
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(enrollment => enrollment.status === statusFilter.value)
  }
  
  return filtered
})

const totalPages = computed(() => Math.ceil(filteredEnrollments.value.length / itemsPerPage))

const paginatedEnrollments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredEnrollments.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const totalEnrollments = computed(() => enrollments.value.length)
const activeEnrollments = computed(() => enrollments.value.filter(e => e.status === 'active').length)
const pendingEnrollments = computed(() => enrollments.value.filter(e => e.status === 'pending').length)
const completedEnrollments = computed(() => enrollments.value.filter(e => e.status === 'completed').length)

// Methods
const fetchEnrollments = async () => {
  try {
    loading.value = true
    const response = await fetch(`/api/admin/enrollments`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to fetch enrollments')
    
    const data = await response.json()
    enrollments.value = data.data?.data || []
  } catch (error) {
    console.error('Error fetching enrollments:', error)
    toast.error('Failed to fetch enrollments')
  } finally {
    loading.value = false
  }
}

const viewEnrollment = (enrollment) => {
  selectedEnrollment.value = enrollment
  showDetailsModal.value = true
}

const approveEnrollment = async (id) => {
  try {
    const response = await fetch(`/api/admin/enrollments/${id}/approve`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to approve enrollment')
    
    toast.success('Enrollment approved successfully!')
    await fetchEnrollments()
  } catch (error) {
    console.error('Error approving enrollment:', error)
    toast.error('Failed to approve enrollment')
  }
}

const rejectEnrollment = async (id) => {
  try {
    const response = await fetch(`/api/admin/enrollments/${id}/reject`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to reject enrollment')
    
    toast.success('Enrollment rejected successfully!')
    await fetchEnrollments()
  } catch (error) {
    console.error('Error rejecting enrollment:', error)
    toast.error('Failed to reject enrollment')
  }
}

const deleteEnrollment = (enrollment) => {
  enrollmentToDelete.value = enrollment
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!enrollmentToDelete.value) return
  
  try {
    const response = await fetch(`/api/admin/enrollments/${enrollmentToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to delete enrollment')
    
    toast.success('Enrollment deleted successfully!')
    await fetchEnrollments()
    showDeleteModal.value = false
    enrollmentToDelete.value = null
  } catch (error) {
    console.error('Error deleting enrollment:', error)
    toast.error('Failed to delete enrollment')
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

// Watchers
watch([searchTerm, statusFilter], () => {
  currentPage.value = 1
})

// Lifecycle
onMounted(() => {
  fetchEnrollments()
})
</script>
