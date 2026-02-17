<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Forum Topic</h1>
            <p class="mt-2 text-gray-600">Update your forum topic</p>
          </div>
          <router-link
            :to="`/forum/topics/${topicId}`"
            class="btn btn-secondary"
          >
            Back to Topic
          </router-link>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Edit Form -->
      <div v-else-if="topic" class="bg-white rounded-lg shadow-sm p-6">
        <form @submit.prevent="updateTopic" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
            <input
              v-model="editForm.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': errors.title }"
              placeholder="Enter topic title"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
            <select
              v-model="editForm.category"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': errors.category }"
            >
              <option value="">Select Category</option>
              <option 
                v-for="category in categories" 
                :key="category.slug" 
                :value="category.slug"
              >
                {{ category.name }}
              </option>
            </select>
            <p v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
            <RichTextEditor
              ref="richTextEditor"
              v-model="editForm.content"
              placeholder="Share your thoughts, questions, or start a discussion..."
            />
            <p v-if="errors.content" class="mt-1 text-sm text-red-600">{{ errors.content }}</p>
          </div>

          <!-- File Attachments -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional)</label>
            <p class="text-xs text-gray-500 mb-2">Support for images, videos, PDFs, and documents up to 10MB</p>
            <FileUpload
              ref="fileUpload"
              :max-files="5"
              :max-file-size="10 * 1024 * 1024"
              @files-selected="onFilesSelected"
              @files-removed="onFilesRemoved"
            />
          </div>

          <!-- Poll Creator -->
          <PollCreator
            v-model="editForm.poll"
            @poll-validated="onPollValidated"
          />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tags (Optional)</label>
            <input
              v-model="editForm.tagsInput"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Enter tags separated by commas (e.g., javascript, vue, laravel)"
            />
            <p class="mt-1 text-sm text-gray-500">Tags help others find your topic</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-6">
            <router-link
              :to="`/forum/topics/${topicId}`"
              class="btn btn-secondary"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="updating"
              class="btn btn-primary"
            >
              <span v-if="updating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ updating ? 'Updating...' : 'Update Topic' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Error State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Topic not found</h3>
        <p class="mt-1 text-sm text-gray-500">The topic you're looking for doesn't exist or you don't have permission to edit it.</p>
        <div class="mt-6">
          <router-link to="/forum" class="btn btn-primary">
            Back to Forum
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { forumAPI, forumCategoryAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import RichTextEditor from '@/components/RichTextEditor.vue'
import FileUpload from '@/components/FileUpload.vue'
import PollCreator from '@/components/PollCreator.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const topicId = route.params.id
const loading = ref(true)
const updating = ref(false)
const topic = ref(null)
const errors = reactive({})
const categories = ref([])

const editForm = reactive({
  title: '',
  category: '',
  content: '',
  tagsInput: '',
  poll: {
    question: '',
    options: ['', ''],
    is_multiple_choice: false,
    expires_at: ''
  }
})

const fileUpload = ref(null)
const pollValid = ref(false)
const pollData = ref(null)
const richTextEditor = ref(null)

const fetchTopic = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to edit topics')
    router.push('/auth/login')
    return
  }

  loading.value = true
  try {
    const response = await forumAPI.getStandaloneTopic(topicId)
    
    if (response.data.status === 'success') {
      const topicData = response.data.data
      
      // Check if user can edit this topic
      if (topicData.user_id !== authStore.user?.id && !authStore.isInstructor && !authStore.isAdmin) {
        toast.error('You do not have permission to edit this topic')
        router.push(`/forum/topics/${topicId}`)
        return
      }
      
      topic.value = topicData
      
      // Populate form
      editForm.title = topicData.title
      editForm.category = topicData.category
      editForm.content = topicData.content
      editForm.tagsInput = Array.isArray(topicData.tags) ? topicData.tags.join(', ') : ''
      
      // Load poll data if it exists
      if (topicData.poll_question) {
        editForm.poll.question = topicData.poll_question
        editForm.poll.options = topicData.poll_options || ['', '']
        editForm.poll.is_multiple_choice = topicData.poll_is_multiple_choice || false
        editForm.poll.expires_at = topicData.poll_expires_at || ''
        pollValid.value = true
        pollData.value = editForm.poll
      }
    } else {
      toast.error('Failed to load topic')
      router.push('/forum')
    }
  } catch (error) {
    console.error('Error fetching topic:', error)
    toast.error('Failed to load topic')
    router.push('/forum')
  } finally {
    loading.value = false
  }
 }

const updateTopic = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to edit topics')
    router.push('/auth/login')
    return
  }

  updating.value = true
  errors.value = {}

  try {
    // Convert tags input to array
    const tags = editForm.tagsInput
      .split(',')
      .map(tag => tag.trim())
      .filter(tag => tag.length > 0)

    const updateData = {
      title: editForm.title,
      category: editForm.category,
      content: editForm.content,
      tags: tags
    }

    // Add poll data if enabled and valid
    if (pollValid.value && pollData.value) {
      updateData.poll_question = pollData.value.question
      updateData.poll_options = pollData.value.options.filter(option => option.trim().length > 0)
      updateData.poll_is_multiple_choice = pollData.value.is_multiple_choice
      updateData.poll_expires_at = pollData.value.expires_at || null
    }

    // Add attachment data if files are selected
    if (fileUpload.value && fileUpload.value.files.length > 0) {
      const attachments = fileUpload.value.files.map(fileObj => ({
        filename: fileObj.file.name,
        original_filename: fileObj.file.name,
        file_path: `temp/${fileObj.file.name}`,
        file_size: fileObj.file.size,
        mime_type: fileObj.file.type,
        file_type: fileObj.file.name.split('.').pop(),
        is_image: fileObj.file.type.startsWith('image/'),
        is_video: fileObj.file.type.startsWith('video/'),
        thumbnail_path: null,
        uploaded_at: new Date().toISOString(),
        uploaded_by: authStore.user?.id
      }))
      
      updateData.attachments = attachments
    }

    const response = await forumAPI.updateStandaloneTopic(topicId, updateData)
    
    if (response.data.status === 'success') {
      toast.success('Topic updated successfully!')
      router.push(`/forum/topics/${topicId}`)
    } else {
      toast.error('Failed to update topic')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error('Failed to update topic')
    }
  } finally {
    updating.value = false
  }
}

const onFilesSelected = (files) => {
  console.log('Files selected:', files)
}

const onFilesRemoved = (files, removedFile) => {
  console.log('File removed:', removedFile)
}

const onPollValidated = ({ isValid, data }) => {
  pollValid.value = isValid
  pollData.value = data
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

onMounted(() => {
  fetchCategories()
  fetchTopic()
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
</style>
