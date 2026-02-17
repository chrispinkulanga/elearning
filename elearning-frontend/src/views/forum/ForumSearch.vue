<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Search Results</h1>
            <p class="mt-2 text-gray-600">
              {{ searchQuery ? `Results for "${searchQuery}"` : 'Search forum topics' }}
            </p>
            <p v-if="topics.length > 0" class="mt-1 text-sm text-gray-500">
              Found {{ totalResults }} result{{ totalResults !== 1 ? 's' : '' }}
            </p>
          </div>
          <router-link
            to="/forum"
            class="btn btn-secondary"
          >
            Back to Forum
          </router-link>
        </div>
      </div>

      <!-- Search Form -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <form @submit.prevent="performSearch" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Search Topics</label>
              <div class="relative">
                <input
                  v-model="searchForm.query"
                  type="text"
                  placeholder="Search topics, content, or tags..."
                  class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  required
                />
                <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
            <div class="col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select
                v-model="searchForm.category"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Categories</option>
                <option 
                  v-for="category in categories" 
                  :key="category.slug" 
                  :value="category.slug"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div class="col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
              <select
                v-model="searchForm.sort"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="latest">Latest</option>
                <option value="oldest">Oldest</option>
                <option value="most_replied">Most Replied</option>
                <option value="most_viewed">Most Viewed</option>
              </select>
            </div>
            <div class="col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
              <div class="flex gap-2">
                <button
                  type="submit"
                  :disabled="searching"
                  class="btn btn-primary w-full px-8"
                >
                  <span v-if="searching" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                  {{ searching ? 'Searching...' : 'Search' }}
                </button>
                <button
                  v-if="hasSearched"
                  type="button"
                  @click="clearSearch"
                  class="btn btn-secondary px-4"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Loading State -->
      <div v-if="searching && !hasSearched" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Search Results -->
      <div v-else-if="hasSearched">
        <div v-if="topics.length > 0" class="space-y-4">
          <div
            v-for="topic in topics"
            :key="topic.id"
            class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                  <router-link
                    :to="`/forum/topics/${topic.id}`"
                    class="text-lg font-semibold text-gray-900 hover:text-primary-600 transition-colors"
                  >
                    {{ topic.title }}
                  </router-link>
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
                
                <div class="text-gray-600 mb-3" v-html="highlightSearchTerms(topic.content)"></div>
                
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <span>By {{ topic.user?.name }}</span>
                    <span>{{ formatDate(topic.created_at) }}</span>
                    <div class="flex items-center space-x-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                      <span>{{ topic.replies_count || 0 }} replies</span>
                    </div>
                    <div class="flex items-center space-x-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      <span>{{ topic.views || 0 }} views</span>
                    </div>
                  </div>
                  <router-link
                    :to="`/forum/topics/${topic.id}`"
                    class="btn btn-sm btn-primary"
                  >
                    View Discussion
                  </router-link>
                </div>
                
                <div v-if="topic.tags && topic.tags.length > 0" class="mt-3">
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="tag in topic.tags"
                      :key="tag"
                      class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
          <p class="mt-1 text-sm text-gray-500">
            Try adjusting your search terms or browse all topics instead.
          </p>
          <div class="mt-6">
            <router-link to="/forum" class="btn btn-primary">
              Browse All Topics
            </router-link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-8 flex justify-center">
          <nav class="flex items-center space-x-2">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="btn btn-secondary px-3 py-2"
            >
              Previous
            </button>
            
            <div class="flex space-x-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-2 rounded-md text-sm font-medium',
                  page === currentPage
                    ? 'bg-primary-600 text-white'
                    : 'text-gray-700 hover:bg-gray-100'
                ]"
              >
                {{ page }}
              </button>
            </div>
            
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="btn btn-secondary px-3 py-2"
            >
              Next
            </button>
          </nav>
        </div>
      </div>

      <!-- Initial State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Search Forum Topics</h3>
        <p class="mt-1 text-sm text-gray-500">
          Enter your search terms above to find relevant discussions.
        </p>
        <div class="mt-6">
          <router-link to="/forum" class="btn btn-primary">
            Browse All Topics
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { forumAPI, forumCategoryAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const searchForm = reactive({
  query: '',
  category: '',
  sort: 'latest'
})

const topics = ref([])
const searching = ref(false)
const hasSearched = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const totalResults = ref(0)
const searchQuery = ref('')
const categories = ref([])

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
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

const highlightSearchTerms = (content) => {
  if (!searchQuery.value) return content.substring(0, 200) + '...'
  
  const query = searchQuery.value.toLowerCase()
  const index = content.toLowerCase().indexOf(query)
  
  if (index === -1) return content.substring(0, 200) + '...'
  
  const start = Math.max(0, index - 50)
  const end = Math.min(content.length, index + 150)
  let excerpt = content.substring(start, end)
  
  if (start > 0) excerpt = '...' + excerpt
  if (end < content.length) excerpt = excerpt + '...'
  
  // Highlight search terms
  const regex = new RegExp(`(${searchQuery.value})`, 'gi')
  excerpt = excerpt.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>')
  
  return excerpt
}

const performSearch = async (page = 1) => {
  if (!searchForm.query.trim()) {
    toast.error('Please enter a search query')
    return
  }
  
  if (searchForm.query.trim().length < 2) {
    toast.error('Search query must be at least 2 characters long')
    return
  }
  
  searching.value = true
  searchQuery.value = searchForm.query.trim()
  
  try {
    const params = {
      page: page,
      per_page: 15
    }
    
    if (searchForm.category) {
      params.category = searchForm.category
    }
    
    if (searchForm.sort) {
      params.sort = searchForm.sort
    }
    
    const response = await forumAPI.searchAllTopics(searchForm.query.trim(), params)
    
    if (response.data.status === 'success') {
      const data = response.data.data
      
      if (data.data) {
        // Paginated response
        topics.value = data.data
        totalResults.value = data.total
        totalPages.value = Math.ceil(data.total / data.per_page)
        currentPage.value = data.current_page
      } else {
        // Non-paginated response
        topics.value = data
        totalResults.value = data.length
        totalPages.value = 1
        currentPage.value = 1
      }
      
      hasSearched.value = true
      
      // Update URL with search parameters
      router.push({
        path: '/forum/search',
        query: {
          q: searchForm.query.trim(),
          category: searchForm.category || undefined,
          sort: searchForm.sort !== 'latest' ? searchForm.sort : undefined,
          page: page > 1 ? page : undefined
        }
      })
    } else {
      toast.error('Search failed')
    }
  } catch (error) {
    console.error('Search error:', error)
    console.error('Error response:', error.response?.data)
    
    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorMessage = Object.values(errors).flat().join(', ')
      toast.error('Validation error: ' + errorMessage)
    } else {
      toast.error('Search failed. Please try again.')
    }
  } finally {
    searching.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    performSearch(page)
  }
}

const clearSearch = () => {
  searchForm.query = ''
  searchForm.category = ''
  searchForm.sort = 'latest'
  searchQuery.value = ''
  topics.value = []
  hasSearched.value = false
  totalResults.value = 0
  currentPage.value = 1
  totalPages.value = 1
  
  // Clear URL parameters
  router.push({ path: '/forum/search' })
}

const fetchCategories = async () => {
  try {
    const response = await forumCategoryAPI.getAll()
    if (response.data.status === 'success') {
      categories.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching categories:', error)
    toast.error('Failed to load categories')
  }
}

const initializeFromRoute = () => {
  const query = route.query.q
  const category = route.query.category
  const sort = route.query.sort
  const page = parseInt(route.query.page) || 1
  
  if (query) {
    searchForm.query = query
    searchForm.category = category || ''
    searchForm.sort = sort || 'latest'
    searchQuery.value = query
    performSearch(page)
  }
}

onMounted(() => {
  fetchCategories()
  initializeFromRoute()
})

watch(() => route.query, () => {
  initializeFromRoute()
}, { immediate: true })
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
</style>
