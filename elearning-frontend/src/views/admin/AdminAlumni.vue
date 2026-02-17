<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Alumni Management</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Manage alumni network and invitations</p>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <button 
              @click="openInviteModal"
              class="btn btn-primary flex items-center justify-center text-sm"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Invite to Alumni
            </button>
            <button 
              @click="openCreateModal"
              class="btn btn-secondary flex items-center justify-center text-sm"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
              Add Alumni
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Alumni</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalAlumni }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Active Alumni</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.activeAlumni }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Pending Invitations</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.pendingAlumni }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Eligible Users</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.eligibleUsers }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 mb-6">
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search by name or email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="filters.status"
                @change="fetchAlumni"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="btn btn-outline w-full"
              >
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Alumni List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Alumni Directory</h3>
        </div>
        <div class="p-6">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          <div v-else-if="alumni.length > 0" class="space-y-4">
            <div
              v-for="alumnus in alumni"
              :key="alumnus.id"
              class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                      <span class="text-lg font-medium text-blue-600">
                        {{ alumnus.user?.name?.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2">
                      <h4 class="text-sm font-medium text-gray-900">{{ alumnus.user?.name }}</h4>
                      <span :class="getStatusBadgeClass(alumnus.status)">
                        {{ alumnus.status }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ alumnus.user?.email }}</p>
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600">
                      <div v-if="alumnus.graduation_year">
                        <span class="font-medium">Graduation Year:</span> {{ alumnus.graduation_year }}
                      </div>
                      <div v-if="alumnus.degree">
                        <span class="font-medium">Degree:</span> {{ alumnus.degree }}
                      </div>
                      <div v-if="alumnus.current_position">
                        <span class="font-medium">Position:</span> {{ alumnus.current_position }}
                      </div>
                      <div v-if="alumnus.company">
                        <span class="font-medium">Company:</span> {{ alumnus.company }}
                      </div>
                    </div>
                    <div v-if="alumnus.achievements" class="mt-2">
                      <span class="font-medium text-sm text-gray-600">Achievements:</span>
                      <p class="text-sm text-gray-500 mt-1">{{ alumnus.achievements }}</p>
                    </div>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <button
                    @click="openEditModal(alumnus)"
                    class="btn btn-outline text-xs px-3 py-1"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteAlumnus(alumnus.id)"
                    class="btn btn-danger text-xs px-3 py-1"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No alumni found
          </div>
        </div>
      </div>

      <!-- Create/Edit Alumni Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">
                {{ isEditing ? 'Edit Alumni' : 'Add Alumni' }}
              </h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <form @submit.prevent="isEditing ? updateAlumnus() : createAlumnus()">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                  <select
                    v-model="form.user_id"
                    :disabled="isEditing"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select User</option>
                    <option
                      v-for="user in eligibleUsers"
                      :key="user.id"
                      :value="user.id"
                    >
                      {{ user.name }} ({{ user.email }})
                    </option>
                  </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year</label>
                    <input
                      v-model="form.graduation_year"
                      type="text"
                      placeholder="2024"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Degree</label>
                    <input
                      v-model="form.degree"
                      type="text"
                      placeholder="Bachelor's"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Major</label>
                  <input
                    v-model="form.major"
                    type="text"
                    placeholder="Computer Science"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Current Position</label>
                  <input
                    v-model="form.current_position"
                    type="text"
                    placeholder="Software Engineer"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                  <input
                    v-model="form.company"
                    type="text"
                    placeholder="Tech Corp"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">LinkedIn URL</label>
                  <input
                    v-model="form.linkedin_url"
                    type="url"
                    placeholder="https://linkedin.com/in/username"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Achievements</label>
                  <textarea
                    v-model="form.achievements"
                    rows="3"
                    placeholder="Notable achievements and accomplishments..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                </div>
                <div v-if="isEditing">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <select
                    v-model="form.status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                  </select>
                </div>
              </div>
              <div class="flex justify-end space-x-3 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="btn btn-primary"
                >
                  <span v-if="submitting">Saving...</span>
                  <span v-else>{{ isEditing ? 'Update' : 'Create' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Invite Modal -->
      <div v-if="showInviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Invite to Alumni Network</h3>
              <button @click="closeInviteModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <form @submit.prevent="sendInvitation">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                  <select
                    v-model="inviteForm.user_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select User</option>
                    <option
                      v-for="user in eligibleUsers"
                      :key="user.id"
                      :value="user.id"
                    >
                      {{ user.name }} ({{ user.email }})
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Personal Message (Optional)</label>
                  <textarea
                    v-model="inviteForm.message"
                    rows="4"
                    placeholder="Add a personal message to the invitation..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                </div>
              </div>
              <div class="flex justify-end space-x-3 mt-6">
                <button
                  type="button"
                  @click="closeInviteModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="btn btn-primary"
                >
                  <span v-if="submitting">Sending...</span>
                  <span v-else>Send Invitation</span>
                </button>
              </div>
            </form>
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
const submitting = ref(false)
const alumni = ref([])
const eligibleUsers = ref([])
const stats = ref({
  totalAlumni: 0,
  activeAlumni: 0,
  pendingAlumni: 0,
  eligibleUsers: 0
})

const filters = ref({
  search: '',
  status: 'all'
})

const showModal = ref(false)
const showInviteModal = ref(false)
const isEditing = ref(false)
const editingAlumnus = ref(null)

const form = ref({
  user_id: '',
  graduation_year: '',
  degree: '',
  major: '',
  achievements: '',
  current_position: '',
  company: '',
  linkedin_url: '',
  status: 'active'
})

const inviteForm = ref({
  user_id: '',
  message: ''
})

// Check if user has admin access
const hasAdminAccess = computed(() => {
  return authStore.isAuthenticated && authStore.isAdmin
})

// Debounced search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchAlumni()
  }, 500)
}

// Fetch alumni data
const fetchAlumni = async () => {
  if (!hasAdminAccess.value) return

  loading.value = true
  try {
    const response = await adminAPI.getAlumni(filters.value)
    alumni.value = response.data.data || []
    updateStats()
  } catch (error) {
    console.error('Error fetching alumni:', error)
    if (error.response?.status >= 400) {
      toast.error('Failed to load alumni data')
    }
  } finally {
    loading.value = false
  }
}

// Fetch eligible users
const fetchEligibleUsers = async () => {
  try {
    console.log('Fetching eligible users...')
    const response = await adminAPI.getEligibleUsers()
    console.log('Eligible users response:', response)
    eligibleUsers.value = response.data.data || []
    console.log('Eligible users set:', eligibleUsers.value.length)
  } catch (error) {
    console.error('Error fetching eligible users:', error)
    toast.error('Failed to load eligible users')
  }
}

// Update statistics
const updateStats = () => {
  stats.value.totalAlumni = alumni.value.length
  stats.value.activeAlumni = alumni.value.filter(a => a.status === 'active').length
  stats.value.pendingAlumni = alumni.value.filter(a => a.status === 'pending').length
  stats.value.eligibleUsers = eligibleUsers.value.length
}

// Reset filters
const resetFilters = () => {
  filters.value = {
    search: '',
    status: 'all'
  }
  fetchAlumni()
}

// Modal functions
const openCreateModal = () => {
  isEditing.value = false
  editingAlumnus.value = null
  form.value = {
    user_id: '',
    graduation_year: '',
    degree: '',
    major: '',
    achievements: '',
    current_position: '',
    company: '',
    linkedin_url: '',
    status: 'active'
  }
  showModal.value = true
}

const openEditModal = (alumnus) => {
  isEditing.value = true
  editingAlumnus.value = alumnus
  form.value = {
    user_id: alumnus.user_id,
    graduation_year: alumnus.graduation_year || '',
    degree: alumnus.degree || '',
    major: alumnus.major || '',
    achievements: alumnus.achievements || '',
    current_position: alumnus.current_position || '',
    company: alumnus.company || '',
    linkedin_url: alumnus.linkedin_url || '',
    status: alumnus.status
  }
  showModal.value = true
}

const openInviteModal = () => {
  inviteForm.value = {
    user_id: '',
    message: ''
  }
  showInviteModal.value = true
}

const closeModal = () => {
  showModal.value = false
  isEditing.value = false
  editingAlumnus.value = null
}

const closeInviteModal = () => {
  showInviteModal.value = false
}

// CRUD operations
const createAlumnus = async () => {
  submitting.value = true
  try {
    await adminAPI.createAlumni(form.value)
    toast.success('Alumni record created successfully!')
    closeModal()
    await fetchAlumni()
  } catch (error) {
    console.error('Error creating alumni:', error)
    toast.error('Failed to create alumni record')
  } finally {
    submitting.value = false
  }
}

const updateAlumnus = async () => {
  submitting.value = true
  try {
    await adminAPI.updateAlumni(editingAlumnus.value.id, form.value)
    toast.success('Alumni record updated successfully!')
    closeModal()
    await fetchAlumni()
  } catch (error) {
    console.error('Error updating alumni:', error)
    toast.error('Failed to update alumni record')
  } finally {
    submitting.value = false
  }
}

const deleteAlumnus = async (alumnusId) => {
  if (!confirm('Are you sure you want to delete this alumni record?')) return

  try {
    await adminAPI.deleteAlumni(alumnusId)
    toast.success('Alumni record deleted successfully!')
    await fetchAlumni()
  } catch (error) {
    console.error('Error deleting alumni:', error)
    toast.error('Failed to delete alumni record')
  }
}

const sendInvitation = async () => {
  submitting.value = true
  try {
    const response = await adminAPI.inviteToAlumni(inviteForm.value)
    console.log('Invitation response:', response)
    
    if (response.data.status === 'success') {
      toast.success('Alumni invitation sent successfully!')
      closeInviteModal()
      await fetchAlumni()
    } else {
      toast.error(response.data.message || 'Failed to send invitation')
    }
  } catch (error) {
    console.error('Error sending invitation:', error)
    
    // Provide more specific error messages
    if (error.response?.status === 409) {
      toast.error('User already has an alumni record')
    } else if (error.response?.status === 422) {
      toast.error('Please select a user to invite')
    } else if (error.response?.status === 500) {
      toast.error('Server error. Please try again later.')
    } else {
      toast.error('Failed to send invitation: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    submitting.value = false
  }
}

// Helper functions
const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    pending: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    inactive: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.inactive
}

// Component lifecycle
onMounted(async () => {
  if (!hasAdminAccess.value) {
    toast.error('Access denied. Admin privileges required.')
    router.push('/')
    return
  }

  await Promise.all([
    fetchAlumni(),
    fetchEligibleUsers()
  ])
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

.btn-outline {
  @apply bg-white text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-blue-500;
}

.btn-danger {
  @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}
</style>
