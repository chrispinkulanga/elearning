<template>
  <div class="space-y-3">
    <!-- Debug Panel -->
    <div class="bg-gray-100 p-2 text-xs border rounded" :class="'bg-' + (level === 1 ? 'gray' : level === 2 ? 'blue' : level === 3 ? 'green' : 'yellow') + '-100'">
      <div><strong>Level: {{ level }}</strong> ({{ level === 1 ? 'Gray' : level === 2 ? 'Blue' : level === 3 ? 'Green' : 'Yellow' }} messages)</div>
      <div>Show Reply Form: {{ showReplyForm }}</div>
      <div>Active Reply ID: {{ activeReplyId }}</div>
      <div>Total Replies: {{ replies.length }}</div>
      <div>Component Instance ID: {{ Math.random().toString(36).substr(2, 9) }}</div>
    </div>
    <div
      v-for="reply in replies"
      :key="reply.id"
      :id="`reply-${reply.id}`"
      class="relative"
    >
      <!-- Visual connector for nested replies -->
      <div v-if="level > 1" class="absolute -left-4 top-2 w-4 h-4 border-l-2 border-t-2 border-gray-400 rounded-tl-md"></div>
      <div v-if="level > 1" class="absolute -left-4 top-2 bottom-2 border-l-2 border-gray-200"></div>
      
      <!-- Reply content with dynamic styling based on nesting level -->
      <div 
        class="relative p-3 border rounded-lg"
        :class="[getReplyBackgroundClass(), level > 1 ? 'ml-2' : '']"
      >
        <div class="flex items-start space-x-3">
          <div 
            class="rounded-full flex items-center justify-center overflow-hidden"
            :class="getAvatarSizeClass()"
          >
            <img
              v-if="reply.user?.avatar"
              :src="`${storageUrl}/storage/${reply.user.avatar}`"
              :alt="reply.user.name"
              class="w-full h-full object-cover"
            />
            <span v-else class="font-medium" :class="getAvatarTextClass()">
              {{ reply.user?.name?.charAt(0)?.toUpperCase() }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-2">
              <div>
                <p class="font-medium text-gray-900" :class="getUsernameSizeClass()">{{ reply.user?.name }}</p>
                <p class="text-gray-500" :class="getDateSizeClass()">{{ formatDate(reply.created_at) }}</p>
              </div>
              <div v-if="canEditReply(reply)" class="flex items-center space-x-1">
                <button
                  @click="editReply(reply)"
                  class="text-gray-400 hover:text-gray-600 p-1"
                  title="Edit reply"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  @click="deleteReply(reply.id)"
                  class="text-gray-400 hover:text-red-600 p-1"
                  title="Delete reply"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
            <div class="prose max-w-none">
              <div v-html="formatContent(reply.content)" :class="getContentSizeClass()"></div>
            </div>
            <div class="flex items-center justify-between mt-3">
              <div class="flex items-center space-x-3 text-gray-500" :class="getActionSizeClass()">
                <button
                  @click="likeReply(reply)"
                  :class="[
                    'flex items-center space-x-1 transition-colors duration-200',
                    reply.is_liked ? 'text-red-600' : 'text-gray-500 hover:text-red-600'
                  ]"
                >
                  <svg :class="getHeartSizeClass()" :fill="reply.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 20 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                  </svg>
                  <span>{{ reply.upvotes || 0 }}</span>
                </button>
                <button
                  @click="toggleReplyForm(reply.id)"
                  :disabled="isTopicLocked"
                  :class="[
                    'flex items-center space-x-1 transition-colors duration-200 px-4 py-3 rounded hover:bg-blue-100 min-h-[40px] min-w-[100px] border-2 border-blue-500 hover:border-blue-600',
                    isTopicLocked 
                      ? 'text-gray-400 cursor-not-allowed bg-gray-50 border-gray-300' 
                      : 'text-blue-600 bg-blue-50 hover:bg-blue-100'
                  ]"
                  :title="isTopicLocked ? 'Cannot reply to locked discussion' : 'Reply to this message (Level: ' + level + ')'"
                >
                  <svg :class="getReplyIconSizeClass()" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                  </svg>
                  <span>Reply (L{{ level }})</span>
                </button>
                <!-- Test button to force show reply form -->
                <button
                  @click="forceShowReplyForm(reply.id)"
                  class="flex items-center space-x-1 px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600"
                  title="Force show reply form (Debug)"
                >
                  <span>TEST</span>
                </button>
                <button
                  v-if="getDirectChildren(reply.id).length > 0"
                  @click="toggleReplies(reply.id)"
                  class="text-blue-600 hover:text-blue-800 font-medium flex items-center space-x-1 transition-colors duration-200 hover:bg-blue-50 px-2 py-1 rounded"
                >
                  <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': isRepliesExpanded(reply.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                  <span>
                    {{ isRepliesExpanded(reply.id) ? 'Hide' : 'View' }} {{ getRepliesCount(reply.id) }} {{ getRepliesCount(reply.id) === 1 ? 'reply' : 'replies' }}
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Debug Info -->
      <div v-if="showReplyForm && activeReplyId === reply.id" class="text-xs text-red-600 bg-red-100 p-1 rounded">
        DEBUG: Form is active for reply {{ reply.id }} at level {{ level }}
      </div>

      <!-- Inline Reply Form for this specific reply -->
      <div v-if="showReplyForm && activeReplyId === reply.id" class="mt-4 p-4 border-2 border-red-500 rounded-lg bg-yellow-100 shadow-xl relative z-50" :class="level > 1 ? 'ml-2' : ''" style="background-color: #fef3c7 !important; border: 3px solid #ef4444 !important; min-height: 200px;">
        <h5 class="text-sm font-medium text-gray-900 mb-2">Reply to {{ reply.user?.name }} (Level: {{ level }}, ID: {{ reply.id }})</h5>
        <form @submit.prevent="submitReply">
          <textarea
            v-model="replyForm.content"
            rows="4"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm resize-none"
            :class="{ 'border-red-500': replyFormErrors.content }"
            placeholder="Write your reply to {{ reply.user?.name }}..."
          ></textarea>
          <p v-if="replyFormErrors.content" class="mt-1 text-xs text-red-600">{{ replyFormErrors.content }}</p>
          <div class="flex justify-end space-x-2 mt-2">
            <button
              type="button"
              @click="cancelReply"
              class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submittingReply"
              class="px-4 py-1 text-sm bg-primary-600 text-white rounded hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ submittingReply ? 'Posting...' : 'Post Reply' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Recursively render nested replies -->
      <div v-if="isRepliesExpanded(reply.id)" class="mt-3" :class="level > 1 ? 'ml-6' : 'ml-11'">
        <ForumReplyThread
          :replies="getDirectChildren(reply.id)"
          :all-replies="allReplies"
          :level="level + 1"
          :expanded-nested="expandedNested"
          :is-topic-locked="isTopicLocked"
          :storage-url="storageUrl"
          :topic-id="topicId"
          @toggle-replies="toggleReplies"
          @like-reply="likeReply"
          @open-reply-modal="openReplyModal"
          @edit-reply="editReply"
          @delete-reply="deleteReply"
          @reply-added="handleReplyAdded"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import { forumAPI } from '@/services/api'

const authStore = useAuthStore()
const toast = useToast()

const props = defineProps({
  replies: {
    type: Array,
    required: true
  },
  allReplies: {
    type: Array,
    required: true
  },
  level: {
    type: Number,
    default: 1
  },
  expandedNested: {
    type: Set,
    required: true
  },
  isTopicLocked: {
    type: Boolean,
    default: false
  },
  storageUrl: {
    type: String,
    required: true
  },
  topicId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits([
  'toggle-replies',
  'like-reply',
  'open-reply-modal',
  'edit-reply',
  'delete-reply',
  'reply-added'
])

// Reply form state
const showReplyForm = ref(false)
const activeReplyId = ref(null)
const submittingReply = ref(false)
const replyForm = ref({
  content: '',
  parent_id: null
})
const replyFormErrors = ref({})

// Get direct children only (not all descendants)
const getDirectChildren = (parentId) => {
  return props.allReplies.filter(reply => reply.parent_id === parentId)
}

// Get background class based on nesting level
const getReplyBackgroundClass = () => {
  const classes = [
    'bg-gray-50 border-gray-200', // Level 1
    'bg-blue-50 border-blue-200', // Level 2
    'bg-green-50 border-green-200', // Level 3
    'bg-yellow-50 border-yellow-200', // Level 4
    'bg-purple-50 border-purple-200', // Level 5+
    'bg-pink-50 border-pink-200', // Level 6+
    'bg-indigo-50 border-indigo-200', // Level 7+
    'bg-orange-50 border-orange-200', // Level 8+
  ]
  
  return classes[Math.min(props.level - 1, classes.length - 1)]
}

// Get avatar size based on nesting level
const getAvatarSizeClass = () => {
  const sizes = [
    'w-8 h-8 bg-primary-100', // Level 1
    'w-6 h-6 bg-gray-100', // Level 2
    'w-5 h-5 bg-gray-100', // Level 3
    'w-4 h-4 bg-gray-100', // Level 4+
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get avatar text size based on nesting level
const getAvatarTextClass = () => {
  const sizes = [
    'text-primary-600 text-sm', // Level 1
    'text-gray-600 text-xs', // Level 2
    'text-gray-600 text-[10px]', // Level 3
    'text-gray-600 text-[8px]', // Level 4+
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get username size based on nesting level
const getUsernameSizeClass = () => {
  const sizes = [
    'text-sm', // Level 1
    'text-sm', // Level 2
    'text-xs', // Level 3
    'text-[10px]', // Level 4+
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get date size based on nesting level
const getDateSizeClass = () => {
  const sizes = [
    'text-xs', // Level 1
    'text-xs', // Level 2
    'text-[10px]', // Level 3
    'text-[8px]', // Level 4+
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get content size based on nesting level
const getContentSizeClass = () => {
  const sizes = [
    'text-sm text-gray-700', // Level 1
    'text-sm text-gray-700', // Level 2
    'text-xs text-gray-700', // Level 3
    'text-[10px] text-gray-700', // Level 4+
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get action size based on nesting level
const getActionSizeClass = () => {
  const sizes = [
    'text-xs', // Level 1
    'text-xs', // Level 2
    'text-[11px]', // Level 3
    'text-[10px]', // Level 4+ - Keep readable
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get heart icon size based on nesting level
const getHeartSizeClass = () => {
  const sizes = [
    'w-4 h-4', // Level 1
    'w-3 h-3', // Level 2
    'w-3 h-3', // Level 3
    'w-3 h-3', // Level 4+ - Keep visible
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Get reply icon size based on nesting level
const getReplyIconSizeClass = () => {
  const sizes = [
    'w-4 h-4', // Level 1
    'w-3 h-3', // Level 2
    'w-3 h-3', // Level 3
    'w-3 h-3', // Level 4+ - Keep visible
  ]
  
  return sizes[Math.min(props.level - 1, sizes.length - 1)]
}

// Check if replies are expanded
const isRepliesExpanded = (replyId) => {
  return props.expandedNested.has(replyId)
}

// Get replies count
const getRepliesCount = (parentId) => {
  return getDirectChildren(parentId).length
}

// Check if user can edit reply
const canEditReply = (reply) => {
  // For now, allow all users to edit their own replies
  // In a real implementation, this would check against the auth store
  return true
}

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Format content
const formatContent = (content) => {
  return content.replace(/\n/g, '<br>')
}

// Reply form methods
const toggleReplyForm = (replyId) => {
  console.log('Toggle reply form called for reply:', replyId, 'at level:', props.level)
  if (showReplyForm.value && activeReplyId.value === replyId) {
    // Close the form if clicking the same reply
    console.log('Closing reply form for:', replyId)
    cancelReply()
  } else {
    // Open form for this reply
    console.log('Opening reply form for:', replyId)
    showReplyForm.value = true
    activeReplyId.value = replyId
    replyForm.value = {
      content: '',
      parent_id: replyId
    }
    replyFormErrors.value = {}
  }
}

const cancelReply = () => {
  showReplyForm.value = false
  activeReplyId.value = null
  replyForm.value = {
    content: '',
    parent_id: null
  }
  replyFormErrors.value = {}
}

const forceShowReplyForm = (replyId) => {
  console.log('Force showing reply form for:', replyId, 'at level:', props.level)
  showReplyForm.value = true
  activeReplyId.value = replyId
  replyForm.value = {
    content: '',
    parent_id: replyId
  }
  replyFormErrors.value = {}
}

const submitReply = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    return
  }

  // Validate content is not empty
  if (!replyForm.value.content || replyForm.value.content.trim().length === 0) {
    replyFormErrors.value = { content: 'Reply cannot be empty' }
    toast.error('Reply cannot be empty')
    return
  }

  submittingReply.value = true
  replyFormErrors.value = {}

  try {
    const response = await forumAPI.createStandaloneReply(props.topicId, replyForm.value)
    if (response.data.status === 'success') {
      // Emit the new reply to parent component
      emit('reply-added', response.data.data)
      
      // Close the form
      cancelReply()
      
      toast.success('Reply posted successfully!')
    } else {
      toast.error('Failed to post reply')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      replyFormErrors.value = error.response.data.errors
      toast.error('Validation error: ' + Object.values(error.response.data.errors).flat().join(', '))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to post reply: ' + (error.message || 'Unknown error'))
    }
  } finally {
    submittingReply.value = false
  }
}

// Event handlers
const toggleReplies = (replyId) => {
  emit('toggle-replies', replyId)
}

const likeReply = (reply) => {
  emit('like-reply', reply)
}

const openReplyModal = (reply) => {
  console.log('Reply button clicked for reply:', reply.id, 'at level:', props.level)
  emit('open-reply-modal', reply)
}

const editReply = (reply) => {
  emit('edit-reply', reply)
}

const deleteReply = (replyId) => {
  emit('delete-reply', replyId)
}

const handleReplyAdded = (newReply) => {
  emit('reply-added', newReply)
}
</script>
