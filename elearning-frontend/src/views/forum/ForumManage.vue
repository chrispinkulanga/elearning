<template>
  <div>
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Forum Management</h1>
            <p class="mt-2 text-gray-600">Manage forum topics, categories, and moderation</p>
          </div>
          <router-link
            to="/forum"
            class="btn btn-secondary"
          >
            Back to Forum
          </router-link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Topics</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.totalTopics }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Active Topics</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.activeTopics }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Pinned Topics</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.pinnedTopics }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Locked Topics</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.lockedTopics }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Management Tabs -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
                activeTab === tab.id
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Topics Management Tab -->
          <div v-if="activeTab === 'topics'" class="space-y-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Manage Topics</h3>
              <div class="flex space-x-2">
                <!-- Duration Filter -->
                <select
                  v-model="durationFilter"
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">All Time</option>
                  <option value="today">Today</option>
                  <option value="yesterday">Yesterday</option>
                  <option value="this_week">This Week</option>
                  <option value="last_week">Last Week</option>
                  <option value="this_month">This Month</option>
                  <option value="last_month">Last Month</option>
                  <option value="last_3_months">Last 3 Months</option>
                  <option value="last_6_months">Last 6 Months</option>
                  <option value="this_year">This Year</option>
                  <option value="last_year">Last Year</option>
                  <option value="custom">Custom Range</option>
                </select>
                
                <!-- Status Filter -->
                <select
                  v-model="topicFilter"
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">All Topics</option>
                  <option value="pinned">Pinned</option>
                  <option value="locked">Locked</option>
                  <option value="reported">Reported</option>
                  <option value="active">Active (Unlocked)</option>
                </select>
                
                <!-- Sort Filter -->
                <select
                  v-model="sortFilter"
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="newest">Newest First</option>
                  <option value="oldest">Oldest First</option>
                  <option value="most_replied">Most Replied</option>
                  <option value="most_viewed">Most Viewed</option>
                  <option value="alphabetical">Alphabetical</option>
                </select>
              </div>
            </div>

            <!-- Custom Date Range (shown when "Custom Range" is selected) -->
            <div v-if="durationFilter === 'custom'" class="mb-4 p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center space-x-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                  <input
                    v-model="customStartDate"
                    type="date"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                  <input
                    v-model="customEndDate"
                    type="date"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  />
                </div>
                <div class="flex items-end">
                  <button
                    @click="applyCustomDateRange"
                    class="btn btn-primary"
                  >
                    Apply
                  </button>
                </div>
              </div>
            </div>

            <!-- Filter Summary -->
            <div v-if="hasActiveFilters" class="mb-4 p-3 bg-blue-50 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                  </svg>
                  <span class="text-sm font-medium text-blue-800">Active Filters:</span>
                  <span class="text-sm text-blue-700">{{ getFilterSummary() }}</span>
                </div>
                <button
                  @click="clearAllFilters"
                  class="text-sm text-blue-600 hover:text-blue-800 underline"
                >
                  Clear All
                </button>
              </div>
            </div>

            <!-- Results Counter -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                  <span class="text-sm font-medium text-gray-700">
                    Showing {{ filteredTopics.length }} of {{ topics.length }} topics
                  </span>
                </div>
                <div v-if="filteredTopics.length !== topics.length" class="text-sm text-gray-500">
                  {{ topics.length - filteredTopics.length }} hidden by filters
                </div>
              </div>
            </div>

            <div v-if="loading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            </div>

            <div v-else-if="filteredTopics.length > 0" class="space-y-3">
              <div
                v-for="topic in filteredTopics"
                :key="topic.id"
                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                      <h4 class="font-medium text-gray-900">{{ topic.title }}</h4>
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          getCategoryColor(topic.category)
                        ]"
                      >
                        {{ topic.category }}
                      </span>
                      <span v-if="topic.is_pinned" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Pinned
                      </span>
                      <span v-if="topic.is_locked" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Locked
                      </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ topic.content.substring(0, 100) }}...</p>
                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                      <span>By {{ topic.user?.name }}</span>
                      <span>{{ formatDate(topic.created_at) }}</span>
                      <span>{{ topic.replies_count || 0 }} replies</span>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="togglePin(topic)"
                      :class="[
                        'btn btn-sm',
                        topic.is_pinned ? 'btn-warning' : 'btn-secondary'
                      ]"
                    >
                      {{ topic.is_pinned ? 'Unpin' : 'Pin' }}
                    </button>
                    <button
                      @click="toggleLock(topic)"
                      :class="[
                        'btn btn-sm',
                        topic.is_locked ? 'btn-danger' : 'btn-secondary'
                      ]"
                    >
                      {{ topic.is_locked ? 'Unlock' : 'Lock' }}
                    </button>
                    <button
                      @click="deleteTopic(topic)"
                      class="btn btn-sm btn-danger"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="topics.length === 0" class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No topics found</h3>
              <p class="mt-1 text-sm text-gray-500">There are no forum topics in the system yet.</p>
          </div>

            <div v-else class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No topics match your filters</h3>
              <p class="mt-1 text-sm text-gray-500">Try adjusting your filter criteria to see more results.</p>
              <div class="mt-4">
              <button
                  @click="clearAllFilters"
                class="btn btn-primary"
              >
                  Clear All Filters
                    </button>
                  </div>
                </div>
              </div>



          <!-- Reports Management Tab -->
          <div v-if="activeTab === 'reports'" class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Reported Content</h3>
            <div class="text-center py-8">
              <p class="text-gray-500">No reported content found</p>
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">{{ deleteModalTitle }}</h3>
          </div>
          </div>
        
        <div class="mb-6">
          <p class="text-sm text-gray-600">{{ deleteModalMessage }}</p>
          </div>
        
        <div class="flex space-x-3">
            <button
            @click="closeDeleteModal"
            class="btn btn-secondary flex-1"
            >
              Cancel
            </button>
            <button
            @click="confirmDelete"
            class="btn btn-danger flex-1"
            >
            Delete
            </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { forumAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const activeTab = ref('topics')
const topics = ref([])
const topicFilter = ref('')
const durationFilter = ref('')
const sortFilter = ref('newest')
const customStartDate = ref('')
const customEndDate = ref('')

const stats = reactive({
  totalTopics: 0,
  activeTopics: 0,
  pinnedTopics: 0,
  lockedTopics: 0
})



// Modal state
const showDeleteModal = ref(false)
const deleteModalTitle = ref('')
const deleteModalMessage = ref('')
const deleteModalType = ref('') // 'topic' or 'category'
const itemToDelete = ref(null)

const tabs = [
  { id: 'topics', name: 'Topics' },
  { id: 'reports', name: 'Reports' }
]

const filteredTopics = computed(() => {
  let filtered = [...topics.value]
  
  // Apply status filter
  if (topicFilter.value) {
    filtered = filtered.filter(topic => {
    switch (topicFilter.value) {
      case 'pinned':
        return topic.is_pinned
      case 'locked':
        return topic.is_locked
      case 'reported':
        return topic.reports_count > 0
        case 'active':
          return !topic.is_locked
      default:
        return true
    }
  })
  }
  
  // Apply duration filter
  if (durationFilter.value && durationFilter.value !== 'custom') {
    const dateRange = getDateRange(durationFilter.value)
    filtered = filtered.filter(topic => {
      const topicDate = new Date(topic.created_at)
      return topicDate >= dateRange.start && topicDate <= dateRange.end
    })
  }
  
  // Apply custom date range
  if (durationFilter.value === 'custom' && customStartDate.value && customEndDate.value) {
    const startDate = new Date(customStartDate.value)
    const endDate = new Date(customEndDate.value)
    endDate.setHours(23, 59, 59, 999) // Include the entire end date
    
    filtered = filtered.filter(topic => {
      const topicDate = new Date(topic.created_at)
      return topicDate >= startDate && topicDate <= endDate
    })
  }
  
  // Apply sorting - Pinned topics always come first
  filtered.sort((a, b) => {
    // First, prioritize pinned topics
    if (a.is_pinned && !b.is_pinned) return -1
    if (!a.is_pinned && b.is_pinned) return 1
    
    // If both are pinned or both are not pinned, sort by selected criteria
    switch (sortFilter.value) {
      case 'newest':
        return new Date(b.created_at) - new Date(a.created_at)
      case 'oldest':
        return new Date(a.created_at) - new Date(b.created_at)
      case 'most_replied':
        return (b.replies_count || 0) - (a.replies_count || 0)
      case 'most_viewed':
        return (b.views || 0) - (a.views || 0)
      case 'alphabetical':
        return a.title.localeCompare(b.title)
      default:
        return new Date(b.created_at) - new Date(a.created_at) // Default to newest
    }
  })
  
  return filtered
})

const getCategoryColor = (category) => {
  const colors = {
    'general': 'bg-blue-100 text-blue-800',
    'technical': 'bg-green-100 text-green-800',
    'course-specific': 'bg-purple-100 text-purple-800',
    'feedback': 'bg-orange-100 text-orange-800'
  }
  return colors[category] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getDateRange = (duration) => {
  const now = new Date()
  const start = new Date()
  const end = new Date()
  
  switch (duration) {
    case 'today':
      start.setHours(0, 0, 0, 0)
      end.setHours(23, 59, 59, 999)
      break
    case 'yesterday':
      start.setDate(now.getDate() - 1)
      start.setHours(0, 0, 0, 0)
      end.setDate(now.getDate() - 1)
      end.setHours(23, 59, 59, 999)
      break
    case 'this_week':
      start.setDate(now.getDate() - now.getDay())
      start.setHours(0, 0, 0, 0)
      end.setHours(23, 59, 59, 999)
      break
    case 'last_week':
      start.setDate(now.getDate() - now.getDay() - 7)
      start.setHours(0, 0, 0, 0)
      end.setDate(now.getDate() - now.getDay() - 1)
      end.setHours(23, 59, 59, 999)
      break
    case 'this_month':
      start.setDate(1)
      start.setHours(0, 0, 0, 0)
      end.setHours(23, 59, 59, 999)
      break
    case 'last_month':
      start.setMonth(now.getMonth() - 1, 1)
      start.setHours(0, 0, 0, 0)
      end.setDate(0)
      end.setHours(23, 59, 59, 999)
      break
    case 'last_3_months':
      start.setMonth(now.getMonth() - 3)
      start.setHours(0, 0, 0, 0)
      end.setHours(23, 59, 59, 999)
      break
    case 'last_6_months':
      start.setMonth(now.getMonth() - 6)
      start.setHours(0, 0, 0, 0)
      end.setHours(23, 59, 59, 999)
      break
    case 'this_year':
      start.setMonth(0, 1)
      start.setHours(0, 0, 0, 0)
      end.setHours(23, 59, 59, 999)
      break
    case 'last_year':
      start.setFullYear(now.getFullYear() - 1, 0, 1)
      start.setHours(0, 0, 0, 0)
      end.setFullYear(now.getFullYear() - 1, 11, 31)
      end.setHours(23, 59, 59, 999)
      break
    default:
      start.setFullYear(2000) // Very old date
      end.setHours(23, 59, 59, 999)
  }
  
  return { start, end }
}

const hasActiveFilters = computed(() => {
  return topicFilter.value || durationFilter.value || sortFilter.value !== 'newest'
})

const getFilterSummary = () => {
  const filters = []
  
  if (durationFilter.value) {
    const durationLabels = {
      'today': 'Today',
      'yesterday': 'Yesterday',
      'this_week': 'This Week',
      'last_week': 'Last Week',
      'this_month': 'This Month',
      'last_month': 'Last Month',
      'last_3_months': 'Last 3 Months',
      'last_6_months': 'Last 6 Months',
      'this_year': 'This Year',
      'last_year': 'Last Year',
      'custom': 'Custom Range'
    }
    filters.push(durationLabels[durationFilter.value] || durationFilter.value)
  }
  
  if (topicFilter.value) {
    const statusLabels = {
      'pinned': 'Pinned',
      'locked': 'Locked',
      'reported': 'Reported',
      'active': 'Active'
    }
    filters.push(statusLabels[topicFilter.value] || topicFilter.value)
  }
  
  if (sortFilter.value !== 'newest') {
    const sortLabels = {
      'oldest': 'Oldest First',
      'most_replied': 'Most Replied',
      'most_viewed': 'Most Viewed',
      'alphabetical': 'Alphabetical'
    }
    filters.push(sortLabels[sortFilter.value] || sortFilter.value)
  }
  
  return filters.join(', ')
}

const applyCustomDateRange = () => {
  if (!customStartDate.value || !customEndDate.value) {
    toast.error('Please select both start and end dates')
    return
  }
  
  if (new Date(customStartDate.value) > new Date(customEndDate.value)) {
    toast.error('Start date cannot be after end date')
    return
  }
  
  toast.success('Custom date range applied')
}

const clearAllFilters = () => {
  topicFilter.value = ''
  durationFilter.value = ''
  sortFilter.value = 'newest'
  customStartDate.value = ''
  customEndDate.value = ''
  toast.success('All filters cleared')
}

const fetchTopics = async () => {
  loading.value = true
  try {
    const response = await forumAPI.getAllTopics({ per_page: 100 })
    if (response.data.status === 'success') {
      topics.value = response.data.data.data || response.data.data
      updateStats()
    }
  } catch (error) {
    console.error('Error fetching topics:', error)
    toast.error('Failed to load topics')
  } finally {
    loading.value = false
  }
}

const updateStats = () => {
  stats.totalTopics = topics.value.length
  stats.activeTopics = topics.value.filter(t => !t.is_locked).length
  stats.pinnedTopics = topics.value.filter(t => t.is_pinned).length
  stats.lockedTopics = topics.value.filter(t => t.is_locked).length
}

const togglePin = async (topic) => {
  try {
    const response = await forumAPI.pinStandaloneTopic(topic.id)
    if (response.data.status === 'success') {
      // Use the actual data returned from the API instead of manually toggling
      topic.is_pinned = response.data.data.is_pinned
      updateStats()
      toast.success(response.data.message)
    }
  } catch (error) {
    console.error('Error pinning topic:', error)
    toast.error('Failed to update topic')
  }
}

const toggleLock = async (topic) => {
  try {
    const response = await forumAPI.lockStandaloneTopic(topic.id)
    if (response.data.status === 'success') {
      // Use the actual data returned from the API instead of manually toggling
      topic.is_locked = response.data.data.is_locked
      updateStats()
      toast.success(response.data.message)
    }
  } catch (error) {
    console.error('Error locking topic:', error)
    toast.error('Failed to update topic')
  }
}

const showDeleteConfirmation = (type, item) => {
  deleteModalType.value = type
  itemToDelete.value = item
  
  if (type === 'topic') {
    deleteModalTitle.value = 'Delete Topic'
    deleteModalMessage.value = `Are you sure you want to delete the topic "${item.title}"? This action cannot be undone.`
  }
  
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!itemToDelete.value) return
  
  try {
    if (deleteModalType.value === 'topic') {
      await forumAPI.deleteStandaloneTopic(itemToDelete.value.id)
      topics.value = topics.value.filter(t => t.id !== itemToDelete.value.id)
    updateStats()
    toast.success('Topic deleted successfully')
    }
  } catch (error) {
    console.error('Error deleting item:', error)
    toast.error('Failed to delete item')
  } finally {
    closeDeleteModal()
  }
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteModalTitle.value = ''
  deleteModalMessage.value = ''
  deleteModalType.value = ''
  itemToDelete.value = null
}

const deleteTopic = (topic) => {
  showDeleteConfirmation('topic', topic)
}



onMounted(() => {
  fetchTopics()
})
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
}

.btn-primary {
  @apply text-white bg-primary-600 hover:bg-primary-700 focus:ring-primary-500;
}

.btn-secondary {
  @apply text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-500;
}

.btn-danger {
  @apply text-white bg-red-600 hover:bg-red-700 focus:ring-red-500;
}

.btn-warning {
  @apply text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500;
}

.btn-sm {
  @apply px-3 py-1.5 text-xs;
}
</style>
