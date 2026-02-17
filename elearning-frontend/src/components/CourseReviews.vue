<template>
  <div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-medium text-gray-900">Student Reviews</h3>
      <div class="flex items-center space-x-4 text-sm text-gray-500">
        <span>{{ reviews.length }} reviews</span>
        <div class="flex items-center">
          <div class="flex items-center">
            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            <span class="ml-1">{{ averageRating.toFixed(1) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Form for Enrolled Students -->
    <div v-if="canSubmitReview" class="mb-8 border-b border-gray-200 pb-6">
      <h4 class="text-md font-medium text-gray-900 mb-4">Write a Review</h4>
      <form @submit.prevent="submitReview" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
          <div class="flex items-center space-x-1">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="newReview.rating = star"
              class="w-8 h-8 focus:outline-none"
            >
              <svg 
                :class="star <= newReview.rating ? 'text-yellow-400' : 'text-gray-300'" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
            </button>
            <span class="ml-2 text-sm text-gray-600">{{ newReview.rating }}/5 stars</span>
          </div>
        </div>
        
        <div>
          <label for="review-comment" class="block text-sm font-medium text-gray-700 mb-2">
            Your Review (Optional)
          </label>
          <textarea
            id="review-comment"
            v-model="newReview.comment"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            placeholder="Share your thoughts about this course..."
          ></textarea>
        </div>
        
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Reviews are moderated and will appear after approval.
          </p>
          <button
            type="submit"
            :disabled="submitting || newReview.rating === 0"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
            {{ submitting ? 'Submitting...' : 'Submit Review' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Login Prompt for Non-Enrolled Users -->
    <div v-else-if="!authStore.isAuthenticated" class="mb-8 border-b border-gray-200 pb-6">
      <div class="text-center py-4">
        <p class="text-gray-600 mb-3">Please log in to write a review.</p>
        <router-link to="/login" class="btn btn-primary">
          Log In
        </router-link>
      </div>
    </div>

    <!-- Enrolled but Already Reviewed Message -->
    <div v-else-if="hasReviewed" class="mb-8 border-b border-gray-200 pb-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="text-sm text-blue-800">You have already reviewed this course.</p>
        </div>
      </div>
    </div>

    <!-- Not Enrolled Message -->
    <div v-else-if="!isEnrolled" class="mb-8 border-b border-gray-200 pb-6">
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <p class="text-sm text-yellow-800">You must be enrolled in this course to write a review.</p>
        </div>
      </div>
    </div>

    <!-- Reviews List -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="reviews.length === 0" class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No reviews yet</h3>
      <p class="mt-1 text-sm text-gray-500">Be the first to review this course.</p>
    </div>

    <div v-else class="space-y-6">
      <div v-for="review in reviews" :key="review.id" class="border-b border-gray-200 pb-6 last:border-b-0">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
              <span class="text-primary-600 font-medium text-sm">
                {{ review.user?.name?.charAt(0)?.toUpperCase() }}
              </span>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 mb-2">
              <h4 class="text-sm font-medium text-gray-900">{{ review.user?.name }}</h4>
              <div class="flex items-center">
                <div v-for="i in 5" :key="i" class="w-4 h-4">
                  <svg :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                </div>
              </div>
              <span class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</span>
            </div>
            <p v-if="review.comment" class="text-sm text-gray-700 whitespace-pre-wrap">{{ review.comment }}</p>
            
            <!-- Review Actions -->
            <div class="flex items-center space-x-4 mt-3">
              <button
                v-if="canEditReview(review)"
                @click="editReview(review)"
                class="text-sm text-gray-600 hover:text-gray-500"
              >
                Edit
              </button>
              <button
                v-if="canDeleteReview(review)"
                @click="deleteReview(review)"
                class="text-sm text-red-600 hover:text-red-500"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button -->
    <div v-if="hasMoreReviews" class="mt-6 text-center">
      <button
        @click="loadMoreReviews"
        :disabled="loadingMore"
        class="px-4 py-2 text-sm font-medium text-primary-600 bg-white border border-primary-300 rounded-md hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="loadingMore" class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-600 mr-2"></span>
        Load More
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { courseAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const props = defineProps({
  courseId: {
    type: [String, Number],
    required: true
  },
  isEnrolled: {
    type: Boolean,
    default: false
  }
})

const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const loadingMore = ref(false)
const submitting = ref(false)
const reviews = ref([])
const currentPage = ref(1)
const hasMoreReviews = ref(false)
const hasReviewed = ref(false)
const editingReview = ref(null)

const newReview = ref({
  rating: 0,
  comment: ''
})

const canSubmitReview = computed(() => {
  return authStore.isAuthenticated && props.isEnrolled && !hasReviewed.value
})

const canEditReview = (review) => {
  return authStore.isAuthenticated && review.user_id === authStore.user?.id
}

const canDeleteReview = (review) => {
  return authStore.isAuthenticated && review.user_id === authStore.user?.id
}

const averageRating = computed(() => {
  if (reviews.value.length === 0) return 0
  const total = reviews.value.reduce((sum, review) => sum + review.rating, 0)
  return total / reviews.value.length
})

const fetchReviews = async (page = 1) => {
  try {
    if (page === 1) {
      loading.value = true
    } else {
      loadingMore.value = true
    }

    const response = await courseAPI.getReviews(props.courseId, { page })
    const data = response.data.data

    if (page === 1) {
      reviews.value = data.data
    } else {
      reviews.value.push(...data.data)
    }

    currentPage.value = data.current_page
    hasMoreReviews.value = data.current_page < data.last_page

    // Check if current user has reviewed
    if (authStore.isAuthenticated) {
      hasReviewed.value = reviews.value.some(review => review.user_id === authStore.user.id)
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
    toast.error('Failed to load reviews')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMoreReviews = () => {
  fetchReviews(currentPage.value + 1)
}

const submitReview = async () => {
  if (newReview.value.rating === 0) return

  submitting.value = true
  try {
    const data = {
      rating: newReview.value.rating,
      comment: newReview.value.comment.trim()
    }

    const response = await courseAPI.createReview(props.courseId, data)
    const review = response.data.data

    // Add review to the list
    reviews.value.unshift(review)
    hasReviewed.value = true

    newReview.value = { rating: 0, comment: '' }

    toast.success('Review submitted successfully and is pending approval')
  } catch (error) {
    console.error('Error creating review:', error)
    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to submit review')
    }
  } finally {
    submitting.value = false
  }
}

const editReview = (review) => {
  editingReview.value = review
  newReview.value = {
    rating: review.rating,
    comment: review.comment || ''
  }
}

const deleteReview = async (review) => {
  if (!confirm('Are you sure you want to delete this review?')) return

  try {
    await courseAPI.deleteReview(props.courseId, review.id)
    
    // Remove review from list
    reviews.value = reviews.value.filter(r => r.id !== review.id)
    hasReviewed.value = false

    toast.success('Review deleted successfully')
  } catch (error) {
    console.error('Error deleting review:', error)
    toast.error('Failed to delete review')
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

  if (diffInHours < 1) {
    return 'Just now'
  } else if (diffInHours < 24) {
    return `${diffInHours}h ago`
  } else if (diffInHours < 168) { // 7 days
    return `${Math.floor(diffInHours / 24)}d ago`
  } else {
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }
}

onMounted(() => {
  fetchReviews()
})

watch(() => props.courseId, () => {
  reviews.value = []
  hasReviewed.value = false
  currentPage.value = 1
  hasMoreReviews.value = false
  fetchReviews()
})
</script>
