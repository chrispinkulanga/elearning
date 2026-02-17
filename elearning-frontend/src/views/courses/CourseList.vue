<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Browse Courses</h1>
            <p class="mt-2 text-gray-600">Discover amazing courses from expert instructors</p>
            <p class="mt-1 text-sm text-gray-500">{{ totalCourses }} courses available</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="showFilters = !showFilters"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
              </svg>
              Filters
              <span v-if="activeFiltersCount > 0" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                {{ activeFiltersCount }}
              </span>
            </button>
            <!-- Enhanced sort dropdown -->
            <div class="relative">
              <select
                v-model="sortBy"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="popular">Most Popular</option>
                <option value="rating">Highest Rated</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div v-if="showFilters" class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search with debouncing -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search courses..."
                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          
          <!-- Enhanced Category with count -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="filters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }} ({{ category.courses_count || 0 }})
              </option>
            </select>
          </div>
          
          <!-- Level with icons -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
            <select
              v-model="filters.level"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">All Levels</option>
              <option value="beginner">🟢 Beginner</option>
              <option value="intermediate">🟡 Intermediate</option>
              <option value="advanced">🔴 Advanced</option>
            </select>
          </div>
          
          <!-- Enhanced Price with icons -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
            <select
              v-model="filters.price"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">All Prices</option>
              <option value="free">🆓 Free</option>
              <option value="paid">💰 Paid</option>
              <option value="under_50">💵 Under $50</option>
              <option value="under_100">💵 Under $100</option>
            </select>
          </div>
        </div>
        
        <!-- Enhanced Clear Filters -->
        <div class="mt-4 flex justify-between items-center">
          <div class="text-sm text-gray-500">
            {{ sortedCourses.length }} of {{ totalCourses }} courses
          </div>
          <button
            @click="clearFilters"
            class="text-sm text-gray-500 hover:text-gray-700 underline"
          >
            Clear all filters
          </button>
        </div>
      </div>
    </div>

    <!-- Course Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <CourseSkeleton v-for="n in 6" :key="n" />
      </div>
      
      <!-- No Results -->
      <div v-else-if="filteredCourses.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No courses found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
      </div>
      
      <!-- Course Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="course in sortedCourses"
          :key="course.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer"
          @click="viewCourse(course)"
        >
          <!-- Course Image -->
          <div class="aspect-w-16 aspect-h-9 bg-gray-200">
            <img
              v-if="course.thumbnail"
              :src="getImageUrl(course.thumbnail)"
              :alt="course.title"
              class="w-full h-48 object-cover"
            />
            <div v-else class="w-full h-48 bg-gray-200 flex items-center justify-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
          </div>
          
          <!-- Course Info -->
          <div class="p-4">
            <div class="flex items-center justify-between mb-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="course.level === 'beginner' ? 'bg-green-100 text-green-800' : 
                             course.level === 'intermediate' ? 'bg-yellow-100 text-yellow-800' : 
                             'bg-red-100 text-red-800'">
                {{ course.level }}
              </span>
              <span class="text-sm text-gray-500">{{ course.category?.name }}</span>
            </div>
            
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ course.title }}</h3>
            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ course.short_description }}</p>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <img
                  v-if="course.instructor?.avatar"
                  :src="course.instructor.avatar"
                  :alt="course.instructor.name"
                  class="w-6 h-6 rounded-full"
                />
                <span class="text-sm text-gray-600">{{ course.instructor?.name }}</span>
              </div>
              <div class="text-right">
                <div v-if="course.is_free" class="text-green-600 font-semibold">Free</div>
                <div v-else class="text-gray-900 font-semibold">${{ course.price || 0 }}</div>
                <div class="text-xs text-gray-500">{{ course.enrollments_count || 0 }} students</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="currentPage = page"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md',
              currentPage === page
                ? 'bg-primary-600 text-white'
                : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import appConfig from '@/config/app.config'
import { useToast } from '@/composables/useToast'
import CourseSkeleton from '@/components/CourseSkeleton.vue'

const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

// Reactive data
const loading = ref(false)
const courses = ref([])
const categories = ref([])
const showFilters = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const itemsPerPage = 12
const searchTimeout = ref(null)
const sortBy = ref('newest')

// Filters
const filters = ref({
  search: '',
  category: '',
  level: '',
  price: ''
})

// Computed properties
const filteredCourses = computed(() => {
  let filtered = courses.value

  // Search filter
  if (filters.value.search) {
    const searchTerm = filters.value.search.toLowerCase()
    filtered = filtered.filter(course =>
      course.title.toLowerCase().includes(searchTerm) ||
      course.description.toLowerCase().includes(searchTerm) ||
      course.instructor?.name.toLowerCase().includes(searchTerm)
    )
  }

  // Category filter
  if (filters.value.category) {
    filtered = filtered.filter(course => course.category_id == filters.value.category)
  }

  // Level filter
  if (filters.value.level) {
    filtered = filtered.filter(course => course.level === filters.value.level)
  }

  // Price filter
  if (filters.value.price) {
    switch (filters.value.price) {
      case 'free':
        filtered = filtered.filter(course => course.is_free)
        break
      case 'paid':
        filtered = filtered.filter(course => !course.is_free)
        break
      case 'under_50':
        filtered = filtered.filter(course => !course.is_free && course.price < 50)
        break
      case 'under_100':
        filtered = filtered.filter(course => !course.is_free && course.price < 100)
        break
    }
  }

  return filtered
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

const activeFiltersCount = computed(() => {
  return Object.values(filters.value).filter(value => value !== '').length
})

const totalCourses = computed(() => courses.value.length)

// Computed property for sorting
const sortedCourses = computed(() => {
  let sorted = filteredCourses.value;

  if (sortBy.value === 'newest') {
    sorted = [...sorted].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } else if (sortBy.value === 'oldest') {
    sorted = [...sorted].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
  } else if (sortBy.value === 'popular') {
    sorted = [...sorted].sort((a, b) => (b.enrollments_count || 0) - (a.enrollments_count || 0));
  } else if (sortBy.value === 'rating') {
    sorted = [...sorted].sort((a, b) => (b.average_rating || 0) - (a.average_rating || 0));
  } else if (sortBy.value === 'price_low') {
    sorted = [...sorted].sort((a, b) => (a.price || 0) - (b.price || 0));
  } else if (sortBy.value === 'price_high') {
    sorted = [...sorted].sort((a, b) => (b.price || 0) - (a.price || 0));
  }

  return sorted;
});

// Methods
const fetchCourses = async () => {
  loading.value = true
  try {
    const response = await courseStore.fetchCourses({
      page: currentPage.value,
      per_page: itemsPerPage,
      ...filters.value,
      sort_by: sortBy.value
    })
    courses.value = response.data || []
    totalPages.value = Math.ceil((response.total || 0) / itemsPerPage)
  } catch (error) {
    console.error('Error fetching courses:', error)
    // Silently fail - don't show error toast for empty data
    console.log('ℹ️ No courses available')
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await courseStore.fetchCategories()
    categories.value = response || []
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const clearFilters = () => {
  filters.value = {
    search: '',
    category: '',
    level: '',
    price: ''
  }
  sortBy.value = 'newest'
  currentPage.value = 1
}

const viewCourse = (course) => {
  router.push(`/courses/${course.slug}`)
}

const getImageUrl = (thumbnail) => {
  if (thumbnail.startsWith('http')) {
    return thumbnail
  }
  return `${appConfig.apiUrl}/storage/${thumbnail}`
}

// Watchers with debouncing for search
watch(filters.value.search, () => {
  // Debounce search to avoid too many API calls
  clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    fetchCourses()
  }, 300)
})

watch([filters.value.category, filters.value.level, filters.value.price], () => {
  fetchCourses()
}, { deep: true })

watch(currentPage, () => {
  fetchCourses()
})

watch(sortBy, () => {
  fetchCourses()
})

// Lifecycle
onMounted(() => {
  fetchCourses()
  fetchCategories()
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
