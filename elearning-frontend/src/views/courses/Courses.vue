<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">All Courses</h1>
        <p class="text-gray-600">Discover courses from expert instructors</p>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6">
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="col-span-1 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Search Courses</label>
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search courses..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                />
                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>

            <!-- Category Filter -->
            <div class="col-span-1 md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select
                v-model="selectedCategory"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Level Filter -->
            <div class="col-span-1 md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
              <select
                v-model="selectedLevel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">All Levels</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>

            <!-- Sort -->
            <div class="col-span-1 md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
              <select
                v-model="sortBy"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="latest">Latest</option>
                <option value="popular">Most Popular</option>
                <option value="rating">Highest Rated</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
              </select>
            </div>
          </div>

          <!-- Quick Filters -->
          <div class="mt-4 flex flex-wrap gap-2">
            <button
              @click="toggleFilter('free')"
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium transition-colors duration-200',
                filters.free ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              Free Courses
            </button>
            <button
              @click="toggleFilter('featured')"
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium transition-colors duration-200',
                filters.featured ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              Featured
            </button>
            <button
              @click="toggleFilter('new')"
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium transition-colors duration-200',
                filters.new ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              New Courses
            </button>
            <button
              @click="clearFilters"
              class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Results Info -->
      <div v-else class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-gray-600">
            Showing {{ paginatedCourses.length }} of {{ totalCourses }} courses
          </p>
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">View:</span>
            <button
              @click="viewMode = 'grid'"
              :class="[
                'p-2 rounded-md transition-colors duration-200',
                viewMode === 'grid' ? 'bg-primary-100 text-primary-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
            </button>
            <button
              @click="viewMode = 'list'"
              :class="[
                'p-2 rounded-md transition-colors duration-200',
                viewMode === 'list' ? 'bg-primary-100 text-primary-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Courses Grid -->
      <div v-if="!loading && paginatedCourses.length > 0" class="space-y-6">
        <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6' : 'space-y-4'">
          <div
            v-for="course in paginatedCourses"
            :key="course.id"
            class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200"
          >
            <div class="relative h-48 bg-gray-200">
              <img
                v-if="course.image"
                :src="course.image"
                :alt="course.title"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <div class="absolute top-2 right-2">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    course.price === 0 ? 'bg-green-100 text-green-800' : 'bg-primary-100 text-primary-800'
                  ]"
                >
                  {{ course.price === 0 || course.price === null || course.price === undefined ? 'Free' : `$${course.price}` }}
                </span>
              </div>
              <div v-if="course.is_featured" class="absolute top-2 left-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Featured
                </span>
              </div>
            </div>

            <div class="p-6">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500">{{ course.category?.name }}</span>
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <span class="text-sm text-gray-600 ml-1">{{ course.average_rating || 0 }}</span>
                </div>
              </div>

              <h3 class="text-lg font-medium text-gray-900 mb-2">{{ course.title }}</h3>
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ course.subtitle || course.description }}</p>

              <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                <span>{{ course.instructor?.name }}</span>
                <span>{{ course.total_duration || 0 }}h</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-500">{{ course.total_enrollments || 0 }} students</span>
                  <span class="text-sm text-gray-500">{{ course.total_lessons || 0 }} lessons</span>
                </div>
                <router-link
                  :to="`/courses/${course.slug}`"
                  :class="[
                    'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md transition-colors duration-200',
                    course.is_enrolled 
                      ? 'bg-green-600 hover:bg-green-700 text-white' 
                      : 'bg-primary-600 hover:bg-primary-700 text-white'
                  ]"
                >
                  {{ course.is_enrolled ? 'Enrolled' : 'View Course' }}
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center mt-8">
          <nav class="flex items-center space-x-2">
            <button
              @click="goToPreviousPage"
              :disabled="currentPage === 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                page === currentPage
                  ? 'bg-primary-600 text-white'
                  : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            
            <button
              @click="goToNextPage"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </nav>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && paginatedCourses.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No courses found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ searchQuery ? 'No courses match your search criteria.' : 'No courses available at the moment.' }}
        </p>
        <div class="mt-6">
          <button
            @click="clearFilters"
            class="btn btn-primary"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

const loading = ref(false)
const courses = ref([])
const categories = ref([])
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedLevel = ref('')
const sortBy = ref('latest')
const viewMode = ref('grid')
const currentPage = ref(1)
const perPage = 12

const filters = reactive({
  free: false,
  featured: false,
  new: false
})

const filteredCourses = computed(() => {
  // Use store's filtered courses as base
  let filtered = courseStore.filteredCourses || courses.value

  // Apply local quick filters that aren't handled by the store
  if (filters.free) {
    filtered = filtered.filter(course => course.price === 0)
  }
  if (filters.featured) {
    filtered = filtered.filter(course => course.is_featured)
  }
  if (filters.new) {
    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)
    filtered = filtered.filter(course => new Date(course.created_at) > thirtyDaysAgo)
  }

  // Sort courses
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'popular':
        return (b.total_enrollments || 0) - (a.total_enrollments || 0)
      case 'rating':
        return (b.average_rating || 0) - (a.average_rating || 0)
      case 'price_low':
        return (a.price || 0) - (b.price || 0)
      case 'price_high':
        return (b.price || 0) - (a.price || 0)
      case 'latest':
      default:
        return new Date(b.created_at) - new Date(a.created_at)
    }
  })

  return filtered
})

const totalCourses = computed(() => courseStore.pagination.total || filteredCourses.value.length)
const totalPages = computed(() => courseStore.pagination.last_page || Math.ceil(filteredCourses.value.length / perPage))
const paginatedCourses = computed(() => {
  // Use store's courses if available, otherwise use local filtered courses
  return courseStore.courses || filteredCourses.value
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const toggleFilter = (filterName) => {
  filters[filterName] = !filters[filterName]
  currentPage.value = 1
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedLevel.value = ''
  sortBy.value = 'latest'
  filters.free = false
  filters.featured = false
  filters.new = false
  currentPage.value = 1
  
  // Clear store filters and refetch
  courseStore.filters.category = ''
  courseStore.filters.level = ''
  courseStore.searchQuery = ''
  fetchCourses()
}

const goToPage = async (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    courseStore.pagination.current_page = page
    await fetchCourses()
  }
}

const goToPreviousPage = async () => {
  if (currentPage.value > 1) {
    await goToPage(currentPage.value - 1)
  }
}

const goToNextPage = async () => {
  if (currentPage.value < totalPages.value) {
    await goToPage(currentPage.value + 1)
  }
}

const fetchCourses = async () => {
  loading.value = true
  try {
    console.log('🚀 Fetching courses and categories...')
    
    // Update store filters with current local filter values
    courseStore.filters.category = selectedCategory.value
    courseStore.filters.level = selectedLevel.value
    courseStore.searchQuery = searchQuery.value
    
    console.log('🔍 Current filters:', {
      category: selectedCategory.value,
      level: selectedLevel.value,
      search: searchQuery.value,
      storeFilters: courseStore.filters
    })
    
    // Fetch both courses and categories in parallel
    const [coursesResponse, categoriesResponse] = await Promise.all([
      courseStore.fetchCourses(),
      courseStore.fetchCategories()
    ])
    
    console.log('✅ Courses response:', coursesResponse)
    console.log('✅ Categories response:', categoriesResponse)
    
    // Handle courses response (store returns response.data)
    const coursesData = coursesResponse?.data ? coursesResponse.data : coursesResponse
    if (coursesData) {
      if (coursesData.status === 'success') {
        courses.value = coursesData.data || []
      } else if (Array.isArray(coursesData)) {
        courses.value = coursesData
      } else {
        // Fallback to store state if shape is unexpected
        courses.value = courseStore.courses || []
      }
      console.log('✅ Courses loaded:', courses.value.length)
    } else {
      console.warn('⚠️ No courses data in response; using store state')
      courses.value = courseStore.courses || []
    }
    
    // Handle categories response (store returns response.data)
    const categoriesData = categoriesResponse?.data ? categoriesResponse.data : categoriesResponse
    if (categoriesData) {
      if (categoriesData.status === 'success') {
        categories.value = categoriesData.data || []
      } else if (Array.isArray(categoriesData)) {
        categories.value = categoriesData
      } else {
        categories.value = courseStore.categories || []
      }
      console.log('✅ Categories loaded:', categories.value.length)
    } else {
      console.warn('⚠️ No categories data; using store state')
      categories.value = courseStore.categories || []
    }
    
    console.log('✅ Data loading completed:', {
      courses: courses.value.length,
      categories: categories.value.length
    })
    
  } catch (error) {
    console.error('❌ Error fetching data:', error)
    console.error('❌ Error response:', error.response)
    console.error('❌ Error status:', error.response?.status)
    console.error('❌ Error data:', error.response?.data)
    
    // Try alternative approach for categories if main call fails
    try {
      console.log('🔄 Trying direct categories fetch...')
      const directCategoriesResponse = await fetch('/api/categories')
      const directCategoriesData = await directCategoriesResponse.json()
      
      if (directCategoriesResponse.ok && directCategoriesData.status === 'success') {
        categories.value = directCategoriesData.data || []
        console.log('✅ Categories loaded via direct fetch:', categories.value.length)
      }
    } catch (directError) {
      console.error('❌ Direct categories fetch also failed:', directError)
    }
    
    // Silently fail - don't show error toast to user for empty data
    console.log('ℹ️ No courses or categories available')
  } finally {
    loading.value = false
  }
}

// Watch for filter changes
watch([searchQuery, selectedCategory, selectedLevel, sortBy, filters], () => {
  currentPage.value = 1
  // Update store filters and refetch courses
  courseStore.filters.category = selectedCategory.value
  courseStore.filters.level = selectedLevel.value
  courseStore.searchQuery = searchQuery.value
  fetchCourses()
})

// Watch for route query changes
watch(() => route.query, (query) => {
  if (query.category) {
    selectedCategory.value = parseInt(query.category)
    // Update store filters and refetch courses
    courseStore.filters.category = selectedCategory.value
    fetchCourses()
  }
}, { immediate: true })

onMounted(() => {
  fetchCourses()
})
</script> 