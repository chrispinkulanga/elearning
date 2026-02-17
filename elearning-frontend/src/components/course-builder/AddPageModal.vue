<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">Add New Page</h3>
        <button @click="closeModal" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <form @submit.prevent="createPage" class="modal-body">
        <div class="form-group">
          <label for="page-title" class="form-label">Page Title *</label>
          <input
            id="page-title"
            v-model="form.title"
            type="text"
            class="form-input"
            placeholder="Enter page title"
            required
            maxlength="255"
          />
          <p class="form-help">{{ form.title.length }}/255 characters</p>
        </div>
        
        <div class="form-group">
          <label for="page-description" class="form-label">Description</label>
          <textarea
            id="page-description"
            v-model="form.description"
            class="form-textarea"
            placeholder="Enter page description (optional)"
            rows="3"
          ></textarea>
        </div>
        
        <div class="form-group">
          <label for="page-type" class="form-label">Page Type *</label>
          <select
            id="page-type"
            v-model="form.content_type"
            class="form-select"
            required
          >
            <option value="">Select page type</option>
            <option value="lesson">Lesson</option>
            <option value="quiz">Quiz</option>
            <option value="assignment">Assignment</option>
            <option value="overview">Overview</option>
          </select>
          <p class="form-help">Choose the type of content this page will contain</p>
        </div>
        
        <div class="form-group">
          <label class="form-label">Page Settings</label>
          <div class="checkbox-group">
            <label class="checkbox-item">
              <input
                v-model="form.is_preview"
                type="checkbox"
                class="form-checkbox"
              />
              <span class="checkbox-label">Allow preview (students can see this page before enrolling)</span>
            </label>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" @click="closeModal" class="btn-secondary">
            Cancel
          </button>
          <button type="submit" class="btn-primary" :disabled="loading">
            <span v-if="loading" class="loading-spinner"></span>
            {{ loading ? 'Creating...' : 'Create Page' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import { courseBuilderAPI } from '@/services/api'

const emit = defineEmits(['close', 'page-created'])
const toast = useToast()

// Form data
const form = reactive({
  title: '',
  description: '',
  content_type: '',
  is_preview: false
})

const loading = ref(false)

// Methods
const closeModal = () => {
  emit('close')
}

const route = useRoute()

const createPage = async () => {
  if (!form.title.trim() || !form.content_type) {
    toast.warning('Please fill in all required fields')
    return
  }
  
  try {
    loading.value = true
    
    // Get course ID from route params
    const courseId = route.params.courseId
    
    const response = await courseBuilderAPI.addPage(courseId, {
      title: form.title.trim(),
      description: form.description.trim(),
      content_type: form.content_type,
      is_preview: form.is_preview
    })
    
    if (response.status === 'success') {
      toast.success('Page created successfully')
      emit('page-created', response.data)
      resetForm()
    } else {
      toast.error(response.message || 'Failed to create page')
    }
  } catch (error) {
    console.error('Create page error:', error)
    const msg = error.response?.data?.message || error.message || 'Failed to create page. Please try again.'
    toast.error(msg)
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.title = ''
  form.description = ''
  form.content_type = ''
  form.is_preview = false
}
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50;
}

.modal-content {
  @apply bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto;
}

.modal-header {
  @apply flex items-center justify-between p-6 border-b border-gray-200;
}

.modal-title {
  @apply text-lg font-medium text-gray-900;
}

.modal-close {
  @apply p-2 text-gray-400 hover:text-gray-600 transition-colors;
}

.modal-body {
  @apply p-6;
}

.form-group {
  @apply mb-4;
}

.form-label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}

.form-input,
.form-select,
.form-textarea {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500;
}

.form-textarea {
  resize: vertical;
}

.form-help {
  @apply mt-1 text-xs text-gray-500;
}

.checkbox-group {
  @apply space-y-2;
}

.checkbox-item {
  @apply flex items-start space-x-2;
}

.form-checkbox {
  @apply mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded;
}

.checkbox-label {
  @apply text-sm text-gray-700;
}

.modal-footer {
  @apply flex items-center justify-end space-x-3 pt-4 border-t border-gray-200;
}

.btn-primary {
  @apply px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center space-x-2;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors;
}

.loading-spinner {
  @apply animate-spin rounded-full h-4 w-4 border-b-2 border-white;
}
</style>
