<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <div v-else-if="course" class="space-y-8">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="relative h-64 bg-gradient-to-r from-primary-600 to-primary-800">
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>
            <div class="relative h-full flex items-center justify-center">
              <div class="text-center text-white">
                <h1 class="text-3xl font-bold mb-2">{{ course.title }}</h1>
                <p class="text-lg opacity-90">{{ course.short_description }}</p>
              </div>
            </div>
          </div>

          <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-4 mb-4">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span class="ml-1 text-sm text-gray-600">{{ (course.average_rating ?? 0).toFixed(1) }} ({{ course.reviews_count || 0 }} reviews)</span>
                  </div>
                  <span class="text-sm text-gray-500">{{ course.enrollments_count || 0 }} students enrolled</span>
                  <span class="text-sm text-gray-500">{{ course.level }}</span>
                </div>

                <div class="flex items-center space-x-4 mb-6">
                  <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                      <span class="text-primary-600 font-medium">
                        {{ course.instructor?.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">{{ course.instructor?.name }}</p>
                      <p class="text-xs text-gray-500">Instructor</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="lg:ml-8 lg:w-80">
                <div class="bg-gray-50 rounded-lg p-6">
                  <div class="text-center mb-4">
                    <div class="text-3xl font-bold text-gray-900">
                      {{ course?.is_free || Number(course?.price || 0) === 0 ? 'Free' : (`$${Number(course.price).toFixed(2)}`) }}
                    </div>
                  </div>

                  <div v-if="isEnrolled" class="space-y-3">
                    <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                      <div class="flex items-center">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="ml-2 text-sm font-medium text-success-800">Enrolled</span>
                      </div>
                    </div>
                    <router-link :to="`/courses/${course.slug}/learn`" class="btn btn-primary w-full">
                      Continue Learning
                    </router-link>
                  </div>
                  <div v-else class="space-y-3">
                    <button 
                      v-if="course?.is_free || Number(course?.price || 0) === 0"
                      @click="enrollInCourse"
                      :disabled="enrolling"
                      class="btn btn-primary w-full"
                    >
                      <span v-if="enrolling" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                      {{ enrolling ? 'Enrolling...' : 'Enroll Now' }}
                    </button>
                    <router-link 
                      v-else
                      :to="`/payment/${course.slug}`" 
                      class="btn btn-primary w-full"
                    >
                      Pay & Enroll
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">What you'll learn</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div v-for="(item, index) in (course.outcomes || [])" :key="index" class="flex items-start">
                  <svg class="w-5 h-5 text-success-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-sm text-gray-700">{{ item }}</span>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
              <div class="prose prose-sm max-w-none" v-html="course.description"></div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Course Content</h3>
              <div class="space-y-2">
                <template v-if="(course.sections && course.sections.length)">
                  <div v-for="section in course.sections" :key="section.id" class="border border-gray-200 rounded-lg">
                    <div class="p-4 bg-gray-50">
                      <h4 class="font-medium text-gray-900">{{ section.title }}</h4>
                      <p class="text-sm text-gray-500">{{ (section.lessons || []).length }} lessons</p>
                    </div>
                    <div class="divide-y divide-gray-200">
                      <div v-for="lesson in (section.lessons || [])" :key="lesson.id" class="p-4 flex items-center justify-between">
                        <div class="flex items-center">
                          <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                          <span class="text-sm text-gray-700">{{ lesson.title }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ lesson.video_duration ? `${lesson.video_duration} min` : '' }}</span>
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else-if="(course.lessons && course.lessons.length)">
                  <div class="border border-gray-200 rounded-lg">
                    <div class="p-4 bg-gray-50">
                      <h4 class="font-medium text-gray-900">All lessons</h4>
                      <p class="text-sm text-gray-500">{{ course.lessons.length }} lessons</p>
                    </div>
                    <div class="divide-y divide-gray-200">
                      <div v-for="lesson in course.lessons" :key="lesson.id" class="p-4 flex items-center justify-between">
                        <div class="flex items-center">
                          <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                          <span class="text-sm text-gray-700">{{ lesson.title }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ lesson.video_duration ? `${lesson.video_duration} min` : '' }}</span>
                      </div>
                    </div>
                  </div>
                </template>
                <p v-else class="text-sm text-gray-500">No content available.</p>
              </div>
            </div>

            <!-- Course Comments Section -->
            <CourseComments :course-id="course.id" />

            <!-- Course Reviews Section -->
            <CourseReviews :course-id="course.id" :is-enrolled="isEnrolled" />
          </div>

          <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Instructor</h3>
              <div class="flex items-start space-x-4">
                <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center">
                  <span class="text-primary-600 font-medium text-xl">
                    {{ course.instructor?.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900">{{ course.instructor?.name }}</h4>
                  <p class="text-sm text-gray-700">{{ course.instructor?.bio }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Course not found</h3>
        <p class="mt-1 text-sm text-gray-500">The course you're looking for doesn't exist.</p>
        <div class="mt-6">
          <router-link to="/courses" class="btn btn-primary">Browse Courses</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import CourseComments from '@/components/CourseComments.vue'
import CourseReviews from '@/components/CourseReviews.vue'

const route = useRoute()
const router = useRouter()
const courseStore = useCourseStore()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const enrolling = ref(false)
const course = ref(null)

const isEnrolled = computed(() => {
  if (!course.value) return false
  // Check if user is enrolled from the course data
  const enrolled = course.value.is_enrolled || false
  console.log('CourseDetail: isEnrolled computed:', {
    courseId: course.value.id,
    isEnrolled: enrolled,
    courseData: course.value
  })
  return enrolled
})

const fetchCourseData = async () => {
  loading.value = true
  try {
    const courseResponse = await courseStore.fetchCourseBySlug(route.params.slug)
    course.value = courseResponse
  } catch (error) {
    console.error('Error fetching course data:', error)
    toast.error('Failed to load course details')
  } finally {
    loading.value = false
  }
}

const enrollInCourse = async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  enrolling.value = true
  try {
    console.log('Attempting to enroll in course:', course.value.id)
    console.log('Course data:', course.value)
    await courseStore.enrollInCourse(course.value.id)
    toast.success('Successfully enrolled in course!')
    
    // Refresh course data to update enrollment status
    await fetchCourseData()
    
    // Redirect to learning page
    router.push(`/courses/${course.value.slug}/learn`)
  } catch (error) {
    console.error('Error enrolling in course:', error)
    console.error('Error response:', error.response)
    console.error('Error data:', error.response?.data)
    
    // Handle specific error cases
    if (error.response?.data?.message === 'Already enrolled in this course') {
      toast.info('You are already enrolled in this course!')
      // Refresh course data to update enrollment status
      await fetchCourseData()
    } else {
      toast.error('Failed to enroll in course')
    }
  } finally {
    enrolling.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchCourseData()
})
</script>