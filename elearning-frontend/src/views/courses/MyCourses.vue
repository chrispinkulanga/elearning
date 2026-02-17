<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Learning</h1>
            <p class="mt-2 text-gray-600">Continue your learning journey</p>
          </div>
          <router-link to="/courses" class="btn btn-primary">
            Browse More Courses
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
          <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">Filter:</label>
            <select
              v-model="filter"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
              <option value="all">All Courses</option>
              <option value="in-progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="not-started">Not Started</option>
            </select>
          </div>
          
          <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">Sort:</label>
            <select
              v-model="sortBy"
              class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
              <option value="recent">Recently Enrolled</option>
              <option value="progress">Progress</option>
              <option value="name">Course Name</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredCourses.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No courses found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ filter === 'all' ? 'You haven\'t enrolled in any courses yet.' : `No ${filter.replace('-', ' ')} courses found.` }}
        </p>
        <div class="mt-6">
          <router-link to="/courses" class="btn btn-primary">
            Browse Courses
          </router-link>
        </div>
      </div>

      <!-- Courses Grid -->
      <div v-else class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div
          v-for="enrollment in filteredCourses"
          :key="enrollment.id"
          class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200"
        >
          <!-- Course Image -->
          <div class="relative h-48 bg-gray-200">
            <img
              v-if="enrollment.course?.image"
              :src="enrollment.course.image"
              :alt="enrollment.course.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
            
            <!-- Progress Badge -->
            <div class="absolute top-2 right-2">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  (enrollment.progress || 0) === 100 ? 'bg-success-100 text-success-800' : 'bg-primary-100 text-primary-800'
                ]"
              >
                {{ enrollment.progress || 0 }}% Complete
              </span>
            </div>
          </div>

          <!-- Course Content -->
          <div class="p-6">
            <div class="flex items-start justify-between mb-2">
              <h3 class="text-lg font-medium text-gray-900 truncate">
                {{ enrollment.course?.title }}
              </h3>
            </div>
            
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
              {{ enrollment.course?.subtitle || enrollment.course?.description }}
            </p>

            <!-- Course Meta -->
            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
              <div class="flex items-center space-x-4">
                <span>{{ enrollment.course?.instructor?.name }}</span>
                <span>{{ enrollment.course?.level }}</span>
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                <span>{{ enrollment.course?.average_rating || 0 }}</span>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
              <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Progress</span>
                <span>{{ enrollment.progress || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: (enrollment.progress || 0) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Course Stats -->
            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
              <span>{{ enrollment.completed_lessons || 0 }} of {{ enrollment.course?.total_lessons || 0 }} lessons</span>
              <span>{{ enrollment.course?.total_duration || 0 }}h</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-2">
              <router-link
                :to="`/courses/${enrollment.course?.slug}`"
                class="btn btn-primary flex-1"
              >
                {{ (enrollment.progress || 0) === 100 ? 'Review Course' : 'Continue Learning' }}
              </router-link>
              
              <button
                v-if="(enrollment.progress || 0) === 100"
                @click="downloadCertificate(enrollment)"
                class="btn btn-secondary"
              >
                Certificate
              </button>
              <router-link
                v-if="enrollment.course?.id"
                :to="`/courses/${enrollment.course.id}/builder`"
                class="btn btn-secondary"
              >
                Builder
              </router-link>
            </div>

            <!-- Enrollment Date -->
            <div class="mt-3 text-xs text-gray-400">
              Enrolled {{ formatDate(enrollment.enrolled_at || enrollment.created_at) }}
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
              page === currentPage
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
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'

const courseStore = useCourseStore()
const toast = useToast()

const loading = ref(false)
const filter = ref('all')
const sortBy = ref('recent')
const currentPage = ref(1)
const perPage = 12

const filteredCourses = computed(() => {
  let enrollments = courseStore.myCourses

  // Apply filter
  if (filter.value === 'in-progress') {
    enrollments = enrollments.filter(e => e.progress > 0 && e.progress < 100)
  } else if (filter.value === 'completed') {
    enrollments = enrollments.filter(e => e.progress === 100)
  } else if (filter.value === 'not-started') {
    enrollments = enrollments.filter(e => e.progress === 0)
  }

  // Apply sorting
  enrollments.sort((a, b) => {
    switch (sortBy.value) {
      case 'progress':
        return b.progress - a.progress
      case 'name':
        return (a.course?.title || '').localeCompare(b.course?.title || '')
      case 'recent':
      default:
        return new Date(b.enrolled_at || b.created_at) - new Date(a.enrolled_at || a.created_at)
    }
  })

  return enrollments
})

const totalPages = computed(() => {
  return Math.ceil(filteredCourses.value.length / perPage)
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

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const downloadCertificate = async (enrollment) => {
  try {
    // This would call the certificate download API
    toast.success('Certificate download started')
  } catch (error) {
    toast.error('Failed to download certificate')
  }
}

const fetchMyCourses = async () => {
  loading.value = true
  try {
    await courseStore.fetchMyCourses()
  } catch (error) {
    console.error('Error fetching my courses:', error)
    toast.error('Failed to load your courses')
  } finally {
    loading.value = false
  }
}

// Watch for filter/sort changes
watch([filter, sortBy], () => {
  currentPage.value = 1
})

onMounted(() => {
  fetchMyCourses()
})
</script> 