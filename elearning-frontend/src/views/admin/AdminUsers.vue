<template>
  <div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <div class="flex items-center">
          <div class="p-1 sm:p-2 bg-blue-100 rounded-lg">
            <UsersIcon class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
          </div>
          <div class="ml-2 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-600">Total Users</p>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalUsers }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <div class="flex items-center">
          <div class="p-1 sm:p-2 bg-green-100 rounded-lg">
            <AcademicCapIcon class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
          </div>
          <div class="ml-2 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-600">Instructors</p>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.instructors }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <div class="flex items-center">
          <div class="p-1 sm:p-2 bg-yellow-100 rounded-lg">
            <UserIcon class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" />
          </div>
          <div class="ml-2 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-600">Students</p>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.students }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-3 sm:p-6">
        <div class="flex items-center">
          <div class="p-1 sm:p-2 bg-red-100 rounded-lg">
            <ShieldCheckIcon class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" />
          </div>
          <div class="ml-2 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-600">Admins</p>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.admins }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-medium text-gray-900">Users</h3>
          </div>
          <div class="mt-4 sm:mt-0 sm:ml-4 flex space-x-3">
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Add User
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search users..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <select
            v-model="filters.role"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Roles</option>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Admin</option>
          </select>
          <select
            v-model="filters.status"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                User
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Role
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Joined
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center overflow-hidden">
                      <img
                        v-if="user.avatar"
                        :src="`${apiUrl}/storage/${user.avatar}`"
                        :alt="user.name"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-sm font-medium text-primary-600">
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    user.roles?.[0]?.name === 'admin' ? 'bg-red-100 text-red-800' :
                    user.roles?.[0]?.name === 'instructor' ? 'bg-blue-100 text-blue-800' :
                    'bg-green-100 text-green-800'
                  ]"
                >
                  {{ user.roles?.[0]?.name || 'No Role' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ user.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="editUser(user)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="toggleUserStatus(user)"
                    :class="user.status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                  >
                    <component :is="user.status === 'active' ? XMarkIcon : CheckIcon" class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteUser(user)"
                    class="text-red-600 hover:text-red-900"
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
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ paginationStart }}</span> to <span class="font-medium">{{ paginationEnd }}</span> of <span class="font-medium">{{ totalUsers }}</span> results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="currentPage = page"
                  :class="[
                    page === currentPage
                      ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary-100">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">Loading...</h3>
        </div>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <UserModal
      v-if="showCreateModal || showEditModal"
      :user="editingUser"
      :is-edit="showEditModal"
      @close="closeModal"
      @saved="onUserSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import UserModal from './components/UserModal.vue'
import appConfig from '@/config/app.config'
import {
  UsersIcon,
  AcademicCapIcon,
  UserIcon,
  ShieldCheckIcon,
  PlusIcon,
  PencilIcon,
  XMarkIcon,
  CheckIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const toast = useToast()

// Computed API URL
const apiUrl = computed(() => appConfig.apiUrl)

// State
const users = ref([])
const loading = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingUser = ref(null)

// Filters
const filters = ref({
  search: '',
  role: '',
  status: ''
})

// Stats
const stats = ref({
  totalUsers: 0,
  instructors: 0,
  students: 0,
  admins: 0
})

// Computed
const filteredUsers = computed(() => {
  let filtered = users.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(user =>
      user.name?.toLowerCase().includes(search) ||
      user.email?.toLowerCase().includes(search)
    )
  }

  if (filters.value.role) {
    filtered = filtered.filter(user =>
      user.roles?.[0]?.name === filters.value.role
    )
  }

  if (filters.value.status) {
    filtered = filtered.filter(user => user.status === filters.value.status)
  }

  return filtered
})

const totalUsers = computed(() => filteredUsers.value.length)
const totalPages = computed(() => Math.ceil(totalUsers.value / itemsPerPage))
const paginationStart = computed(() => (currentPage.value - 1) * itemsPerPage + 1)
const paginationEnd = computed(() => Math.min(currentPage.value * itemsPerPage, totalUsers.value))

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})


// Methods
const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/admin/users`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to fetch users')
    
    const data = await response.json()
    users.value = data.data || []
    calculateStats()
  } catch (error) {
    console.error('Error fetching users:', error)
    toast.error('Failed to fetch users')
  } finally {
    loading.value = false
  }
}

const calculateStats = () => {
  stats.value = {
    totalUsers: users.value.length,
    instructors: users.value.filter(u => u.roles?.[0]?.name === 'instructor').length,
    students: users.value.filter(u => u.roles?.[0]?.name === 'student').length,
    admins: users.value.filter(u => u.roles?.[0]?.name === 'admin').length
  }
}

const editUser = (user) => {
  editingUser.value = { ...user }
  showEditModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingUser.value = null
}

const onUserSaved = () => {
  closeModal()
  fetchUsers()
  toast.success('User saved successfully')
}

const toggleUserStatus = async (user) => {
  try {
    const newStatus = user.status === 'active' ? 'inactive' : 'active'
    // API call to update status
    const response = await fetch(`/api/admin/users/${user.id}/status`, {
      method: 'PUT',
      headers: { 
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify({ status: newStatus })
    })
    
    if (!response.ok) throw new Error('Failed to update user status')
    
    user.status = newStatus
    calculateStats()
    toast.success(`User ${newStatus} successfully`)
  } catch (error) {
    console.error('Error updating user status:', error)
    toast.error('Failed to update user status')
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) return
  
  try {
    // API call to delete user
    const response = await fetch(`/api/admin/users/${user.id}`, {
      method: 'DELETE',
      headers: { 
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json' 
      }
    })
    
    if (!response.ok) throw new Error('Failed to delete user')
    
    users.value = users.value.filter(u => u.id !== user.id)
    calculateStats()
    toast.success('User deleted successfully')
  } catch (error) {
    console.error('Error deleting user:', error)
    toast.error('Failed to delete user')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

// Watchers
watch(filters, () => {
  currentPage.value = 1
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchUsers()
})
</script>
