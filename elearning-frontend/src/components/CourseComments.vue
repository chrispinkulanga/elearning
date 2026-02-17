<template>
  <div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-medium text-gray-900">Questions & Discussion</h3>
      <div class="flex items-center space-x-4 text-sm text-gray-500">
        <span>{{ commentStats.total_questions || 0 }} questions</span>
        <span>{{ commentStats.total_replies || 0 }} replies</span>
      </div>
    </div>

    <!-- Comment Form -->
    <div v-if="authStore.isAuthenticated" class="mb-6">
      <form @submit.prevent="submitComment" class="space-y-4">
        <div>
          <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
            {{ replyingTo ? 'Reply to comment' : 'Ask a question or share your thoughts' }}
          </label>
          <textarea
            id="comment"
            v-model="newComment.content"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            :placeholder="replyingTo ? 'Write your reply...' : 'Ask a question about this course...'"
            required
          ></textarea>
        </div>
        <div class="flex items-center justify-between">
          <div v-if="replyingTo" class="flex items-center text-sm text-gray-500">
            <span>Replying to {{ replyingTo.user.name }}</span>
            <button
              type="button"
              @click="cancelReply"
              class="ml-2 text-primary-600 hover:text-primary-500"
            >
              Cancel
            </button>
          </div>
          <div class="flex items-center space-x-3">
            <button
              type="button"
              @click="cancelReply"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ replyingTo ? 'Reply' : 'Post Question' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Login Prompt -->
    <div v-else class="mb-6 p-4 bg-gray-50 rounded-lg text-center">
      <p class="text-gray-600 mb-3">Please log in to ask questions or join the discussion.</p>
      <router-link to="/login" class="btn btn-primary">
        Log In
      </router-link>
    </div>

    <!-- Comments List -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="comments.length === 0" class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No questions yet</h3>
      <p class="mt-1 text-sm text-gray-500">Be the first to ask a question about this course.</p>
    </div>

    <div v-else class="space-y-6">
      <div v-for="comment in comments" :key="comment.id" class="border-b border-gray-200 pb-6 last:border-b-0">
        <!-- Main Comment -->
        <div class="flex space-x-3">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
              <span class="text-primary-600 font-medium text-sm">
                {{ comment.user.name.charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 mb-2">
              <h4 class="text-sm font-medium text-gray-900">{{ comment.user.name }}</h4>
              <span v-if="comment.is_instructor_reply" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Instructor
              </span>
              <span class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</span>
            </div>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
            
            <!-- Comment Actions -->
            <div class="flex items-center space-x-4 mt-3">
              <button
                v-if="authStore.isAuthenticated && !replyingTo"
                @click="startReply(comment)"
                class="text-sm text-primary-600 hover:text-primary-500"
              >
                Reply
              </button>
              <button
                v-if="canEditComment(comment)"
                @click="editComment(comment)"
                class="text-sm text-gray-600 hover:text-gray-500"
              >
                Edit
              </button>
              <button
                v-if="canDeleteComment(comment)"
                @click="deleteComment(comment)"
                class="text-sm text-red-600 hover:text-red-500"
              >
                Delete
              </button>
            </div>

            <!-- Replies -->
            <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 space-y-4">
              <div v-for="reply in comment.replies" :key="reply.id" class="flex space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                    <span class="text-gray-600 font-medium text-xs">
                      {{ reply.user.name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2 mb-1">
                    <h5 class="text-xs font-medium text-gray-900">{{ reply.user.name }}</h5>
                    <span v-if="reply.is_instructor_reply" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      Instructor
                    </span>
                    <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
                  </div>
                  <p class="text-xs text-gray-700 whitespace-pre-wrap">{{ reply.content }}</p>
                  
                  <!-- Reply Actions -->
                  <div class="flex items-center space-x-3 mt-2">
                    <button
                      v-if="canEditComment(reply)"
                      @click="editComment(reply)"
                      class="text-xs text-gray-600 hover:text-gray-500"
                    >
                      Edit
                    </button>
                    <button
                      v-if="canDeleteComment(reply)"
                      @click="deleteComment(reply)"
                      class="text-xs text-red-600 hover:text-red-500"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button -->
    <div v-if="hasMoreComments" class="mt-6 text-center">
      <button
        @click="loadMoreComments"
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
  }
})

const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const loadingMore = ref(false)
const submitting = ref(false)
const comments = ref([])
const commentStats = ref({})
const currentPage = ref(1)
const hasMoreComments = ref(false)
const replyingTo = ref(null)
const editingComment = ref(null)

const newComment = ref({
  content: '',
  parent_id: null
})

const canEditComment = (comment) => {
  return authStore.isAuthenticated && comment.user_id === authStore.user?.id
}

const canDeleteComment = (comment) => {
  return authStore.isAuthenticated && comment.user_id === authStore.user?.id
}

const fetchComments = async (page = 1) => {
  try {
    if (page === 1) {
      loading.value = true
    } else {
      loadingMore.value = true
    }

    const response = await courseAPI.getComments(props.courseId, { page })
    const data = response.data.data

    if (page === 1) {
      comments.value = data.data
    } else {
      comments.value.push(...data.data)
    }

    currentPage.value = data.current_page
    hasMoreComments.value = data.current_page < data.last_page
  } catch (error) {
    console.error('Error fetching comments:', error)
    toast.error('Failed to load comments')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const fetchCommentStats = async () => {
  try {
    const response = await courseAPI.getCommentStats(props.courseId)
    commentStats.value = response.data.data
  } catch (error) {
    console.error('Error fetching comment stats:', error)
  }
}

const loadMoreComments = () => {
  fetchComments(currentPage.value + 1)
}

const submitComment = async () => {
  if (!newComment.value.content.trim()) return

  submitting.value = true
  try {
    const data = {
      content: newComment.value.content.trim(),
      parent_id: replyingTo.value?.id || null
    }

    const response = await courseAPI.createComment(props.courseId, data)
    const comment = response.data.data

    if (replyingTo.value) {
      // Add reply to the parent comment
      const parentComment = comments.value.find(c => c.id === replyingTo.value.id)
      if (parentComment) {
        if (!parentComment.replies) {
          parentComment.replies = []
        }
        parentComment.replies.push(comment)
      }
    } else {
      // Add new top-level comment
      comments.value.unshift(comment)
    }

    // Update stats
    commentStats.value.total_questions = (commentStats.value.total_questions || 0) + (replyingTo.value ? 0 : 1)
    commentStats.value.total_replies = (commentStats.value.total_replies || 0) + (replyingTo.value ? 1 : 0)

    newComment.value = { content: '', parent_id: null }
    replyingTo.value = null

    toast.success(replyingTo.value ? 'Reply posted successfully' : 'Question posted successfully')
  } catch (error) {
    console.error('Error creating comment:', error)
    toast.error('Failed to post comment')
  } finally {
    submitting.value = false
  }
}

const startReply = (comment) => {
  replyingTo.value = comment
  newComment.value.parent_id = comment.id
}

const cancelReply = () => {
  replyingTo.value = null
  newComment.value = { content: '', parent_id: null }
}

const editComment = (comment) => {
  editingComment.value = comment
  newComment.value.content = comment.content
}

const deleteComment = async (comment) => {
  if (!confirm('Are you sure you want to delete this comment?')) return

  try {
    await courseAPI.deleteComment(props.courseId, comment.id)
    
    if (comment.parent_id) {
      // Remove reply from parent comment
      const parentComment = comments.value.find(c => c.id === comment.parent_id)
      if (parentComment && parentComment.replies) {
        parentComment.replies = parentComment.replies.filter(r => r.id !== comment.id)
      }
    } else {
      // Remove top-level comment
      comments.value = comments.value.filter(c => c.id !== comment.id)
    }

    // Update stats
    if (comment.parent_id) {
      commentStats.value.total_replies = Math.max(0, (commentStats.value.total_replies || 0) - 1)
    } else {
      commentStats.value.total_questions = Math.max(0, (commentStats.value.total_questions || 0) - 1)
    }

    toast.success('Comment deleted successfully')
  } catch (error) {
    console.error('Error deleting comment:', error)
    toast.error('Failed to delete comment')
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
  fetchComments()
  fetchCommentStats()
})

watch(() => props.courseId, () => {
  comments.value = []
  commentStats.value = {}
  currentPage.value = 1
  hasMoreComments.value = false
  fetchComments()
  fetchCommentStats()
})
</script>
