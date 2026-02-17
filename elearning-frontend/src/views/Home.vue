<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-primary-600 to-primary-800 overflow-hidden">
      <div class="absolute inset-0 bg-black opacity-10"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Learn Without Limits
          </h1>
          <p class="text-xl md:text-2xl text-primary-100 mb-8 max-w-3xl mx-auto">
            Start, switch, or advance your career with thousands of courses from world-class universities and companies.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link
              to="/courses"
              class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition-colors duration-200"
            >
              Browse Courses
            </router-link>
            <router-link
              to="/register"
              class="inline-flex items-center justify-center px-8 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-primary-700 transition-colors duration-200"
            >
              Join for Free
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-3xl font-bold text-primary-600 mb-2">{{ stats.courses }}+</div>
            <div class="text-gray-600">Online Courses</div>
          </div>
          <div>
            <div class="text-3xl font-bold text-primary-600 mb-2">{{ stats.students }}+</div>
            <div class="text-gray-600">Active Students</div>
          </div>
          <div>
            <div class="text-3xl font-bold text-primary-600 mb-2">{{ stats.instructors }}+</div>
            <div class="text-gray-600">Expert Instructors</div>
          </div>
          <div>
            <div class="text-3xl font-bold text-primary-600 mb-2">{{ stats.certificates }}+</div>
            <div class="text-gray-600">Certificates Issued</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Courses Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Courses</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Discover the most popular courses chosen by our community
          </p>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="featuredCourses.length > 0" class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="course in featuredCourses"
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
            </div>

            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-2">{{ course.title }}</h3>
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ course.short_description || course.description }}</p>
              
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <span class="text-sm text-gray-600 ml-1">{{ course.average_rating || 0 }}</span>
                </div>
                  <span class="text-sm text-gray-500">({{ course.reviews_count || 0 }})</span>
                </div>
                <span class="text-sm text-gray-500">{{ course.lessons_count || 0 }} lessons</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-600">
                      {{ course.instructor?.name?.charAt(0)?.toUpperCase() || 'I' }}
                    </span>
                  </div>
                  <span class="text-sm text-gray-600">{{ course.instructor?.name || 'Instructor' }}</span>
              </div>
              <router-link
                :to="`/courses/${course.slug}`"
                :class="[
                  'inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md transition-colors duration-200',
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

        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No featured courses</h3>
          <p class="mt-1 text-sm text-gray-500">Check back later for featured courses.</p>
          <router-link
            to="/courses"
            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition-colors duration-200"
          >
            View All Courses
          </router-link>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Explore Categories</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Find courses in your area of interest
          </p>
        </div>

        <div v-if="categories.length > 0" class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="category in categories"
            :key="category.id"
            class="group cursor-pointer"
            @click="navigateToCategory(category)"
          >
            <div class="bg-gray-50 rounded-lg p-6 text-center hover:bg-primary-50 transition-colors duration-200">
              <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-200 transition-colors duration-200">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">{{ category.name }}</h3>
              <p class="text-sm text-gray-600">{{ category.courses_count || 0 }} courses</p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No categories available</h3>
          <p class="mt-1 text-sm text-gray-500">Categories will appear here once added.</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-primary-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Start Learning?</h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
          Join thousands of learners and start your journey today
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/register"
            class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition-colors duration-200"
          >
            Get Started
          </router-link>
          <router-link
            to="/courses"
            class="inline-flex items-center justify-center px-8 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-primary-700 transition-colors duration-200"
          >
            Browse Courses
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'
import { courseAPI } from '@/services/api'

const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

const loading = ref(false)
const featuredCourses = ref([])
const categories = ref([])

const stats = ref({
  courses: 1000,
  students: 50000,
  instructors: 500,
  certificates: 25000
})

const fetchFeaturedCourses = async () => {
  loading.value = true
  try {
    console.log('🚀 Fetching featured courses...')
    
    // Use the dedicated featured courses endpoint
    const response = await courseAPI.getFeatured()
    console.log('✅ Featured courses response:', response)
    
    if (response.data && response.data.status === 'success' && response.data.data) {
      featuredCourses.value = response.data.data
      console.log('✅ Featured courses loaded:', featuredCourses.value.length)
    } else {
      console.warn('⚠️ No featured courses found, trying to fetch recent approved courses...')
      
      // Fallback: fetch recent courses if no featured courses
      try {
        const fallbackResponse = await courseAPI.getAll({ 
          per_page: 6,
          sort_by: 'created_at',
          sort_order: 'desc'
        })
        
        if (fallbackResponse.data && (fallbackResponse.data.data || fallbackResponse.data)) {
          const list = fallbackResponse.data.data || fallbackResponse.data
          featuredCourses.value = Array.isArray(list) ? list.slice(0, 6) : []
          console.log('✅ Fallback courses loaded:', featuredCourses.value.length)
        } else {
          featuredCourses.value = []
          console.warn('⚠️ No fallback courses found either')
        }
      } catch (fallbackError) {
        console.error('❌ Error fetching fallback courses:', fallbackError)
        featuredCourses.value = []
      }
    }
  } catch (error) {
    console.error('❌ Error fetching featured courses:', error)
    
    // Try fallback on error too
    try {
      console.log('🔄 Trying fallback courses due to error...')
      const fallbackResponse = await courseAPI.getAll({ 
        per_page: 6,
        sort_by: 'created_at',
        sort_order: 'desc'
      })
      
      if (fallbackResponse.data && (fallbackResponse.data.data || fallbackResponse.data)) {
        const list = fallbackResponse.data.data || fallbackResponse.data
        featuredCourses.value = Array.isArray(list) ? list.slice(0, 6) : []
        console.log('✅ Fallback courses loaded after error:', featuredCourses.value.length)
        // Don't show error toast if fallback succeeded
        return
      } else {
        featuredCourses.value = []
      }
    } catch (fallbackError) {
      console.error('❌ Error fetching fallback courses:', fallbackError)
      featuredCourses.value = []
    }
    
    // Silently fail - don't show error toast to user
    console.log('ℹ️ No featured courses available')
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    console.log('🚀 Fetching categories...')
    
    // Use the categories endpoint directly
    const response = await courseAPI.categories()
    console.log('✅ Categories response:', response)
    console.log('✅ Categories response.data:', response.data)
    console.log('✅ Categories response.data.status:', response.data?.status)
    console.log('✅ Categories response.data.data:', response.data?.data)
    
    if (response.data && response.data.status === 'success' && response.data.data) {
      categories.value = response.data.data
      console.log('✅ Categories loaded successfully:', categories.value.length)
      console.log('✅ Categories data:', categories.value)
    } else {
      console.warn('⚠️ No categories found or invalid response format')
      console.warn('⚠️ Response structure:', {
        hasData: !!response.data,
        status: response.data?.status,
        dataExists: !!response.data?.data,
        dataLength: response.data?.data?.length
      })
      categories.value = []
    }
  } catch (error) {
    console.error('❌ Error fetching categories:', error)
    console.error('❌ Error response:', error.response)
    console.error('❌ Error status:', error.response?.status)
    console.error('❌ Error data:', error.response?.data)
    
    // Try alternative approach - direct fetch to see what's happening
    try {
      console.log('🔄 Trying direct fetch to /api/categories...')
      const directResponse = await fetch('/api/categories')
      const directData = await directResponse.json()
      console.log('🔄 Direct fetch response:', directResponse.status, directData)
      
      if (directResponse.ok && directData.status === 'success' && directData.data) {
        categories.value = directData.data
        console.log('✅ Categories loaded via direct fetch:', categories.value.length)
      } else {
        console.warn('⚠️ Direct fetch also failed')
        categories.value = []
      }
    } catch (directError) {
      console.error('❌ Direct fetch also failed:', directError)
      categories.value = []
    }
    
    // Silently fail - don't show error toast to user
    console.log('ℹ️ No categories available')
  }
}

const navigateToCategory = (category) => {
  router.push({
    path: '/courses',
    query: { category: category.id }
  })
}

onMounted(async () => {
  console.log('🚀 Home component mounted, fetching data...')
  
  // Fetch both featured courses and categories
  await Promise.all([
    fetchFeaturedCourses(),
    fetchCategories()
  ])
  
  console.log('✅ Home component data loaded:', {
    featuredCourses: featuredCourses.value.length,
    categories: categories.value.length
  })
})
</script>
