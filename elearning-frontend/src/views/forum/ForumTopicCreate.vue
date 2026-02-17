<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Create New Topic</h1>
            <p class="mt-2 text-gray-600">Start a new discussion in the community forum</p>
          </div>
          <router-link
            to="/forum"
            class="btn btn-secondary"
          >
            Back to Forum
          </router-link>
        </div>
      </div>

      <!-- Create Form -->
      <div class="bg-white rounded-lg shadow-sm p-6">


        <form @submit.prevent="createTopic" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
            <input
              v-model="topicForm.title"
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
              v-model="topicForm.category"
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
               v-model="topicForm.content"
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
              :auto-enhance="true"
              @files-selected="onFilesSelected"
              @files-removed="onFilesRemoved"
            />
          </div>

          <!-- Poll Creator -->
          <PollCreator
            v-model="topicForm.poll"
            @poll-validated="onPollValidated"
          />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tags (Optional)</label>
            <input
              v-model="topicForm.tagsInput"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Enter tags separated by commas (e.g., javascript, vue, laravel)"
            />
            <p class="mt-1 text-sm text-gray-500">Tags help others find your topic</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-6">
            <router-link
              to="/forum"
              class="btn btn-secondary"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="creating"
              class="btn btn-primary"
            >
              <span v-if="creating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ creating ? 'Creating...' : 'Create Topic' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { forumAPI, forumCategoryAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import RichTextEditor from '@/components/RichTextEditor.vue'
import FileUpload from '@/components/FileUpload.vue'
import PollCreator from '@/components/PollCreator.vue'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const creating = ref(false)
const errors = reactive({})
const categories = ref([])

const topicForm = reactive({
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



const createTopic = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to create topics')
    router.push('/auth/login')
    return
  }

  creating.value = true
  errors.value = {}

  try {
    // Convert tags input to array
    const tags = topicForm.tagsInput
      .split(',')
      .map(tag => tag.trim())
      .filter(tag => tag.length > 0)

    // Prepare topic data with poll and attachments
    const topicData = {
      title: topicForm.title,
      category: topicForm.category,
      content: topicForm.content,
      tags: tags,
      is_draft: false // Mark as final topic, not draft
    }

    // Add poll data if enabled and valid
    if (pollValid.value && pollData.value) {
      topicData.poll_question = pollData.value.question
      topicData.poll_options = pollData.value.options.filter(option => option.trim().length > 0)
      topicData.poll_is_multiple_choice = pollData.value.is_multiple_choice
      topicData.poll_expires_at = pollData.value.expires_at || null
    }

    // Don't include attachment data in initial topic creation
    // We'll upload files after topic is created and update the topic

    // Create new topic with all data
    const response = await forumAPI.createStandaloneTopic(topicData)
    
    if (response.data.status === 'success') {
      const newTopic = response.data.data
      
      // If we have files, upload them
      if (fileUpload.value && fileUpload.value.files.length > 0) {
        try {
          const uploadedAttachments = await uploadAttachments(newTopic.id)
          console.log('Uploaded attachments:', uploadedAttachments)
          console.log('All attachments uploaded successfully!')
        } catch (error) {
          console.error('Failed to upload attachments:', error)
          console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
            statusText: error.response?.statusText
          })
          toast.warning('Topic created but some attachments failed to upload: ' + (error.message || 'Unknown error'))
        }
      }
      

      
      toast.success('Topic created successfully!')
      router.push(`/forum/topics/${newTopic.id}`)
    } else {
      toast.error('Failed to create topic')
    }
  } catch (error) {
    console.error('Error creating topic:', error)
    console.error('Error response:', error.response?.data)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      toast.error('Validation errors: ' + Object.values(error.response.data.errors).flat().join(', '))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to create topic: ' + (error.message || 'Unknown error'))
    }
  } finally {
    creating.value = false
  }
}

const uploadAttachments = async (topicId) => {
  try {
    const files = fileUpload.value.files
    console.log('Uploading attachments for topic:', topicId, 'Files:', files)
    const uploadedAttachments = []
    
    for (const fileObj of files) {
      console.log('Uploading file:', fileObj.file.name, 'Size:', fileObj.file.size, 'Type:', fileObj.file.type)
      
      const formData = new FormData()
      formData.append('file', fileObj.file)
      
      console.log('Sending upload request to:', `/forums/topics/${topicId}/attachments`)
      const response = await forumAPI.uploadAttachment(topicId, formData)
      console.log('Upload response:', response)
      
      if (response.data.status === 'success') {
        const attachmentData = response.data.data
        console.log('Attachment data received:', attachmentData)
        
        // Ensure the attachment data has the correct structure
        const attachment = {
          id: attachmentData.id,
          filename: attachmentData.filename || fileObj.file.name,
          original_filename: attachmentData.original_filename || fileObj.file.name,
          file_path: attachmentData.file_path || attachmentData.filename,
          file_size: attachmentData.file_size || fileObj.file.size,
          mime_type: attachmentData.mime_type || fileObj.file.type,
          file_type: attachmentData.file_type || fileObj.file.name.split('.').pop(),
          is_image: attachmentData.is_image !== undefined ? attachmentData.is_image : fileObj.file.type.startsWith('image/'),
          is_video: attachmentData.is_video !== undefined ? attachmentData.is_video : fileObj.file.type.startsWith('video/'),
          thumbnail_path: attachmentData.thumbnail_path || null,
          uploaded_at: attachmentData.uploaded_at || new Date().toISOString(),
          uploaded_by: attachmentData.uploaded_by || authStore.user?.id
        }
        uploadedAttachments.push(attachment)
        console.log('Added attachment to list:', attachment)
      } else {
        console.error('Upload failed for file:', fileObj.file.name, 'Response:', response.data)
      }
    }
    
    console.log('All attachments uploaded:', uploadedAttachments)
    return uploadedAttachments
  } catch (error) {
    console.error('Failed to upload attachments:', error)
    console.error('Error details:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
      statusText: error.response?.statusText
    })
    throw error
  }
}


const createPoll = async (topicId) => {
  try {
    await forumAPI.createPoll(topicId, pollData.value)
  } catch (error) {
    console.error('Failed to create poll:', error)
    toast.warning('Topic created but poll failed to create')
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

const pollSavedHandler = (event) => {
  toast.success(event.detail.message)
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to create topics')
    router.push('/auth/login')
    return
  }
  
  // Fetch categories
  fetchCategories()
  
  // Listen for poll saved event
  window.addEventListener('poll-saved', pollSavedHandler)
})

onBeforeUnmount(() => {
  window.removeEventListener('poll-saved', pollSavedHandler)
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
