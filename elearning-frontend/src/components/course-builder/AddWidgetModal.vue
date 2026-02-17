<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">Add Content Widget</h3>
        <button @click="closeModal" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="widget-grid">
          <div
            v-for="(widgetType, key) in availableWidgetTypes"
            :key="key"
            class="widget-option"
            @click="selectWidgetType(key)"
          >
            <div class="widget-icon">
              <i :class="getWidgetIcon(key)"></i>
            </div>
            <div class="widget-info">
              <h4 class="widget-name">{{ widgetType }}</h4>
              <p class="widget-description">{{ getWidgetDescription(key) }}</p>
            </div>
          </div>
        </div>
        
        <!-- Widget Configuration Form -->
        <div v-if="selectedType" class="widget-config">
          <h4 class="config-title">Configure {{ getWidgetTypeName(selectedType) }}</h4>
          
          <!-- Text Widget -->
          <div v-if="selectedType === 'text'" class="widget-form">
            <div class="form-group">
              <label class="form-label">Content</label>
              <textarea
                v-model="widgetData.content"
                class="form-textarea"
                placeholder="Enter your text content..."
                rows="6"
                required
              ></textarea>
            </div>
          </div>
          
          <!-- Image Widget -->
          <div v-else-if="selectedType === 'image'" class="widget-form">
            <!-- File Upload Section -->
            <div class="form-group">
              <label class="form-label">Upload Image *</label>
              <div class="file-upload-area" 
                   :class="{ 'has-file': uploadedImage }"
                   @dragover.prevent="isDragOver = true"
                   @dragenter.prevent="isDragOver = true"
                   @dragleave.prevent="isDragOver = false"
                   @drop.prevent="handleImageDrop"
                   @click="triggerImageSelect">
                <div v-if="!uploadedImage" class="upload-placeholder">
                  <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                  </svg>
                  <p class="upload-text">Drag and drop an image here, or click to select</p>
                  <p class="upload-hint">PNG, JPG, GIF up to 10MB</p>
                  <input
                    ref="imageInput"
                    type="file"
                    accept="image/*"
                    @change="handleImageSelect"
                    class="file-input"
                    style="display: none;"
                  />
                </div>
                <div v-else class="uploaded-file">
                  <img :src="uploadedImagePreview" :alt="uploadedImage.name" class="file-preview" />
                  <div class="file-info">
                    <p class="file-name">{{ uploadedImage.name }}</p>
                    <p class="file-size">{{ formatFileSize(uploadedImage.size) }}</p>
                    <button type="button" @click="removeImage" class="remove-file-btn">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <p v-if="imageUploadError" class="error-text">{{ imageUploadError }}</p>
            </div>
            
            <!-- URL Input (Alternative) -->
            <div class="form-group">
              <label class="form-label">Or use Image URL</label>
              <input
                v-model="widgetData.url"
                type="url"
                class="form-input"
                placeholder="https://example.com/image.jpg"
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Alt Text</label>
              <input
                v-model="widgetData.alt"
                type="text"
                class="form-input"
                placeholder="Description of the image"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Caption</label>
              <input
                v-model="widgetData.caption"
                type="text"
                class="form-input"
                placeholder="Image caption (optional)"
              />
            </div>
          </div>
          
          <!-- Video Widget -->
          <div v-else-if="selectedType === 'video'" class="widget-form">
            <div class="form-group">
              <label class="form-label">Video URL *</label>
              <input
                v-model="widgetData.url"
                type="url"
                class="form-input"
                placeholder="https://youtube.com/watch?v=..."
                required
              />
            </div>
            <div class="form-group">
              <label class="form-label">Title</label>
              <input
                v-model="widgetData.title"
                type="text"
                class="form-input"
                placeholder="Video title"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea
                v-model="widgetData.description"
                class="form-textarea"
                placeholder="Video description"
                rows="3"
              ></textarea>
            </div>
          </div>
          
          <!-- MCQ Widget -->
          <div v-else-if="selectedType === 'mcq'" class="widget-form">
            <div class="form-group">
              <label class="form-label">Question *</label>
              <input
                v-model="widgetData.question"
                type="text"
                class="form-input"
                placeholder="Enter your question..."
                required
              />
            </div>
            <div class="form-group">
              <label class="form-label">Options *</label>
              <div class="options-list">
                <div
                  v-for="(option, index) in widgetData.options"
                  :key="index"
                  class="option-item"
                >
                  <input
                    v-model="widgetData.options[index]"
                    type="text"
                    class="form-input"
                    :placeholder="`Option ${index + 1}`"
                    required
                  />
                  <button
                    v-if="widgetData.options.length > 2"
                    @click="removeOption(index)"
                    type="button"
                    class="remove-option-btn"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <button
                v-if="widgetData.options.length < 6"
                @click="addOption"
                type="button"
                class="btn-secondary btn-small"
              >
                + Add Option
              </button>
            </div>
            <div class="form-group">
              <label class="checkbox-item">
                <input
                  v-model="widgetData.is_multiple_choice"
                  type="checkbox"
                  class="form-checkbox"
                />
                <span class="checkbox-label">Allow multiple choice answers</span>
              </label>
            </div>
          </div>
          
          <!-- Poll Widget -->
          <div v-else-if="selectedType === 'poll'" class="widget-form">
            <div class="form-group">
              <label class="form-label">Poll Question *</label>
              <input
                v-model="widgetData.question"
                type="text"
                class="form-input"
                placeholder="What would you like to ask?"
                required
              />
            </div>
            <div class="form-group">
              <label class="form-label">Poll Options *</label>
              <div class="options-list">
                <div
                  v-for="(option, index) in widgetData.options"
                  :key="index"
                  class="option-item"
                >
                  <input
                    v-model="widgetData.options[index]"
                    type="text"
                    class="form-input"
                    :placeholder="`Option ${index + 1}`"
                    required
                  />
                  <button
                    v-if="widgetData.options.length > 2"
                    @click="removeOption(index)"
                    type="button"
                    class="remove-option-btn"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <button
                v-if="widgetData.options.length < 6"
                @click="addOption"
                type="button"
                class="btn-secondary btn-small"
              >
                + Add Option
              </button>
            </div>
            <div class="form-group">
              <label class="checkbox-item">
                <input
                  v-model="widgetData.is_multiple_choice"
                  type="checkbox"
                  class="form-checkbox"
                />
                <span class="checkbox-label">Allow multiple choice voting</span>
              </label>
            </div>
          </div>
          
          <!-- File Widget -->
          <div v-else-if="selectedType === 'file'" class="widget-form">
            <!-- File Upload Section -->
            <div class="form-group">
              <label class="form-label">Upload File *</label>
              <div class="file-upload-area" 
                   :class="{ 'has-file': uploadedFile }"
                   @dragover.prevent="isDragOver = true"
                   @dragenter.prevent="isDragOver = true"
                   @dragleave.prevent="isDragOver = false"
                   @drop.prevent="handleFileDrop"
                   @click="triggerFileSelect">
                <div v-if="!uploadedFile" class="upload-placeholder">
                  <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                  </svg>
                  <p class="upload-text">Drag and drop a file here, or click to select</p>
                  <p class="upload-hint">PDF, DOC, XLS, PPT, TXT up to 10MB</p>
                  <input
                    ref="fileInput"
                    type="file"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv"
                    @change="handleFileSelect"
                    class="file-input"
                    style="display: none;"
                  />
                </div>
                <div v-else class="uploaded-file">
                  <div class="file-icon">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                  <div class="file-info">
                    <p class="file-name">{{ uploadedFile.name }}</p>
                    <p class="file-size">{{ formatFileSize(uploadedFile.size) }}</p>
                    <button type="button" @click="removeFile" class="remove-file-btn">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <p v-if="fileUploadError" class="error-text">{{ fileUploadError }}</p>
            </div>
            
            <!-- URL Input (Alternative) -->
            <div class="form-group">
              <label class="form-label">Or use File URL</label>
              <input
                v-model="widgetData.url"
                type="url"
                class="form-input"
                placeholder="https://example.com/file.pdf"
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Filename</label>
              <input
                v-model="widgetData.filename"
                type="text"
                class="form-input"
                placeholder="document.pdf"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea
                v-model="widgetData.description"
                class="form-textarea"
                placeholder="File description"
                rows="3"
              ></textarea>
            </div>
          </div>
          
          <!-- Code Widget -->
          <div v-else-if="selectedType === 'code'" class="widget-form">
            <div class="form-group">
              <label class="form-label">Code *</label>
              <textarea
                v-model="widgetData.code"
                class="form-textarea code-textarea"
                placeholder="Enter your code here..."
                rows="8"
                required
              ></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Language</label>
              <select v-model="widgetData.language" class="form-select">
                <option value="text">Plain Text</option>
                <option value="javascript">JavaScript</option>
                <option value="python">Python</option>
                <option value="java">Java</option>
                <option value="cpp">C++</option>
                <option value="csharp">C#</option>
                <option value="php">PHP</option>
                <option value="html">HTML</option>
                <option value="css">CSS</option>
                <option value="sql">SQL</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Title</label>
              <input
                v-model="widgetData.title"
                type="text"
                class="form-input"
                placeholder="Code block title"
              />
            </div>
          </div>
          
          <!-- Embed Widget -->
          <div v-else-if="selectedType === 'embed'" class="widget-form">
            <div class="form-group">
              <label class="form-label">Embed Code *</label>
              <textarea
                v-model="widgetData.embed_code"
                class="form-textarea"
                placeholder="<iframe src='...'></iframe>"
                rows="6"
                required
              ></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Title</label>
              <input
                v-model="widgetData.title"
                type="text"
                class="form-input"
                placeholder="Embed title"
              />
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" @click="goBack" class="btn-secondary">
              Back to Widgets
            </button>
            <button @click="createWidget" class="btn-primary" :disabled="loading">
              <span v-if="loading" class="loading-spinner"></span>
              {{ loading ? 'Creating...' : 'Add Widget' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useToast } from 'vue-toastification'
import { courseBuilderAPI } from '@/services/api'

const props = defineProps({
  pageId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['close', 'widget-created'])
const toast = useToast()

// Available widget types
const availableWidgetTypes = {
  text: 'Text Content',
  image: 'Image',
  video: 'Video',
  mcq: 'Multiple Choice Question',
  poll: 'Poll',
  file: 'File Download',
  code: 'Code Block',
  embed: 'Embedded Content'
}

// Widget data
const selectedType = ref('')
const widgetData = reactive({
  content: '',
  url: '',
  alt: '',
  caption: '',
  title: '',
  description: '',
  question: '',
  options: ['', ''],
  is_multiple_choice: false,
  filename: '',
  code: '',
  language: 'text',
  embed_code: ''
})

// File upload state
const uploadedImage = ref(null)
const uploadedImagePreview = ref('')
const uploadedFile = ref(null)
const isDragOver = ref(false)
const imageUploadError = ref('')
const fileUploadError = ref('')

// Refs for file inputs
const imageInput = ref(null)
const fileInput = ref(null)

const loading = ref(false)

// Methods
const closeModal = () => {
  emit('close')
}

const selectWidgetType = (type) => {
  selectedType.value = type
  resetWidgetData()
}

const goBack = () => {
  selectedType.value = ''
  resetWidgetData()
}

const resetWidgetData = () => {
  Object.keys(widgetData).forEach(key => {
    if (key === 'options') {
      widgetData[key] = ['', '']
    } else if (key === 'is_multiple_choice') {
      widgetData[key] = false
    } else {
      widgetData[key] = ''
    }
  })
  
  // Reset file upload state
  uploadedImage.value = null
  uploadedImagePreview.value = ''
  uploadedFile.value = null
  imageUploadError.value = ''
  fileUploadError.value = ''
}

const addOption = () => {
  if (widgetData.options.length < 6) {
    widgetData.options.push('')
  }
}

const removeOption = (index) => {
  if (widgetData.options.length > 2) {
    widgetData.options.splice(index, 1)
  }
}

// File upload methods
const triggerImageSelect = () => {
  if (imageInput.value) {
    imageInput.value.click()
  }
}

const triggerFileSelect = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

const handleImageSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    processImageFile(file)
  }
}

const handleImageDrop = (event) => {
  isDragOver.value = false
  const file = event.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    processImageFile(file)
  } else {
    imageUploadError.value = 'Please select a valid image file'
  }
}

const processImageFile = (file) => {
  // Validate file size (10MB max)
  if (file.size > 10 * 1024 * 1024) {
    imageUploadError.value = 'Image size must be less than 10MB'
    return
  }
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    imageUploadError.value = 'Please select a valid image file'
    return
  }
  
  uploadedImage.value = file
  uploadedImagePreview.value = URL.createObjectURL(file)
  imageUploadError.value = ''
  
  // Auto-fill filename if not set
  if (!widgetData.filename) {
    widgetData.filename = file.name
  }
}

const removeImage = () => {
  uploadedImage.value = null
  uploadedImagePreview.value = ''
  imageUploadError.value = ''
  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    processFile(file)
  }
}

const handleFileDrop = (event) => {
  isDragOver.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    processFile(file)
  } else {
    fileUploadError.value = 'Please select a valid file'
  }
}

const processFile = (file) => {
  // Validate file size (10MB max)
  if (file.size > 10 * 1024 * 1024) {
    fileUploadError.value = 'File size must be less than 10MB'
    return
  }
  
  // Validate file type
  const allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain',
    'text/csv'
  ]
  
  if (!allowedTypes.includes(file.type)) {
    fileUploadError.value = 'Please select a valid document file (PDF, DOC, XLS, PPT, TXT)'
    return
  }
  
  uploadedFile.value = file
  fileUploadError.value = ''
  
  // Auto-fill filename if not set
  if (!widgetData.filename) {
    widgetData.filename = file.name
  }
}

const removeFile = () => {
  uploadedFile.value = null
  fileUploadError.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const createWidget = async () => {
  try {
    loading.value = true
    
    // Validate required fields based on widget type
    if (!validateWidgetData()) {
      return
    }
    
    const widgetDataForAPI = getWidgetDataForAPI()
    
    // Create FormData if file upload is involved
    let requestData
    if (widgetDataForAPI.uploaded_file) {
      requestData = new FormData()
      requestData.append('widget_type', selectedType.value)
      requestData.append('widget_data', JSON.stringify({
        ...widgetDataForAPI,
        uploaded_file: undefined // Remove file from JSON data
      }))
      requestData.append('file', widgetDataForAPI.uploaded_file)
      requestData.append('order_index', '0')
    } else {
      requestData = {
        widget_type: selectedType.value,
        widget_data: widgetDataForAPI,
        order_index: 0
      }
    }
    
    console.log('Sending widget data:', {
      pageId: props.pageId,
      requestData: requestData,
      widgetType: selectedType.value,
      hasFile: widgetDataForAPI.uploaded_file ? true : false
    })
    
    const response = await courseBuilderAPI.addWidget(props.pageId, requestData)
    
    console.log('Widget creation response:', response)
    
    if (response.status === 'success') {
      toast.success('Widget added successfully')
      emit('widget-created', response.data)
      closeModal()
    } else {
      toast.error(response.message || 'Failed to add widget')
    }
  } catch (error) {
    console.error('Create widget error:', error)
    toast.error('Failed to add widget. Please try again.')
  } finally {
    loading.value = false
  }
}

const validateWidgetData = () => {
  switch (selectedType.value) {
    case 'text':
      if (!widgetData.content.trim()) {
        toast.warning('Text content is required')
        return false
      }
      break
    case 'image':
      if (!uploadedImage.value && !widgetData.url.trim()) {
        toast.warning('Please upload an image or provide an image URL')
        return false
      }
      break
    case 'video':
    case 'file':
      if (!uploadedFile.value && !widgetData.url.trim()) {
        toast.warning('Please upload a file or provide a file URL')
        return false
      }
      break
    case 'mcq':
    case 'poll':
      if (!widgetData.question.trim()) {
        toast.warning('Question is required')
        return false
      }
      if (widgetData.options.filter(opt => opt.trim()).length < 2) {
        toast.warning('At least 2 options are required')
        return false
      }
      break
    case 'code':
      if (!widgetData.code.trim()) {
        toast.warning('Code content is required')
        return false
      }
      break
    case 'embed':
      if (!widgetData.embed_code.trim()) {
        toast.warning('Embed code is required')
        return false
      }
      break
  }
  return true
}

const getWidgetDataForAPI = () => {
  const data = {}
  
  switch (selectedType.value) {
    case 'text':
      data.content = widgetData.content
      break
    case 'image':
      // If file is uploaded, we'll handle it in createWidget
      if (uploadedImage.value) {
        data.uploaded_file = uploadedImage.value
        data.filename = widgetData.filename || uploadedImage.value.name
      } else {
        data.url = widgetData.url
      }
      data.alt = widgetData.alt
      data.caption = widgetData.caption
      break
    case 'video':
      data.url = widgetData.url
      data.title = widgetData.title
      data.description = widgetData.description
      break
    case 'mcq':
      data.question = widgetData.question
      data.options = widgetData.options.filter(opt => opt.trim())
      data.is_multiple_choice = widgetData.is_multiple_choice
      break
    case 'poll':
      data.question = widgetData.question
      data.options = widgetData.options.filter(opt => opt.trim())
      data.is_multiple_choice = widgetData.is_multiple_choice
      break
    case 'file':
      // If file is uploaded, we'll handle it in createWidget
      if (uploadedFile.value) {
        data.uploaded_file = uploadedFile.value
        data.filename = widgetData.filename || uploadedFile.value.name
      } else {
        data.url = widgetData.url
        data.filename = widgetData.filename
      }
      data.description = widgetData.description
      break
    case 'code':
      data.code = widgetData.code
      data.language = widgetData.language
      data.title = widgetData.title
      break
    case 'embed':
      data.embed_code = widgetData.embed_code
      data.title = widgetData.title
      break
  }
  
  return data
}

// Helper methods
const getWidgetIcon = (type) => {
  const icons = {
    text: 'fas fa-font',
    image: 'fas fa-image',
    video: 'fas fa-video',
    mcq: 'fas fa-question-circle',
    poll: 'fas fa-poll',
    file: 'fas fa-file',
    code: 'fas fa-code',
    embed: 'fas fa-external-link-alt'
  }
  return icons[type] || 'fas fa-cube'
}

const getWidgetDescription = (type) => {
  const descriptions = {
    text: 'Rich text content with formatting',
    image: 'Images with captions and alt text',
    video: 'Embed videos from various platforms',
    mcq: 'Multiple choice questions for assessment',
    poll: 'Interactive polls for engagement',
    file: 'Downloadable files and documents',
    code: 'Code blocks with syntax highlighting',
    embed: 'Embed external content and tools'
  }
  return descriptions[type] || 'Content widget'
}

const getWidgetTypeName = (type) => {
  return availableWidgetTypes[type] || type
}
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50;
}

.modal-content {
  @apply bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto;
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

.widget-grid {
  @apply grid grid-cols-2 gap-4 mb-6;
}

.widget-option {
  @apply p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors;
}

.widget-icon {
  @apply text-2xl text-primary-600 mb-2;
}

.widget-name {
  @apply font-medium text-gray-900 mb-1;
}

.widget-description {
  @apply text-sm text-gray-600;
}

.widget-config {
  @apply border-t border-gray-200 pt-6;
}

.config-title {
  @apply text-lg font-medium text-gray-900 mb-4;
}

.widget-form {
  @apply space-y-4;
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

.code-textarea {
  @apply font-mono text-sm;
}

.options-list {
  @apply space-y-2 mb-2;
}

.option-item {
  @apply flex items-center space-x-2;
}

.remove-option-btn {
  @apply p-2 text-red-500 hover:text-red-700 transition-colors;
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

.btn-small {
  @apply px-3 py-1 text-sm;
}

.loading-spinner {
  @apply animate-spin rounded-full h-4 w-4 border-b-2 border-white;
}

/* File Upload Styles */
.file-upload-area {
  @apply border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors duration-200 cursor-pointer;
}

.file-upload-area:hover {
  @apply border-primary-400 bg-primary-50;
}

.file-upload-area.has-file {
  @apply border-green-300 bg-green-50 cursor-default;
}

.upload-placeholder {
  @apply space-y-2;
}

.upload-icon {
  @apply mx-auto h-12 w-12 text-gray-400;
}

.upload-text {
  @apply text-sm text-gray-600;
}

.upload-hint {
  @apply text-xs text-gray-500;
}

.file-input {
  @apply hidden;
}

.uploaded-file {
  @apply flex items-center space-x-4 p-4 bg-white rounded-lg border border-gray-200;
}

.file-preview {
  @apply w-16 h-16 object-cover rounded-lg border border-gray-300;
}

.file-icon {
  @apply flex-shrink-0;
}

.file-info {
  @apply flex-1 min-w-0;
}

.file-name {
  @apply text-sm font-medium text-gray-900 truncate;
}

.file-size {
  @apply text-xs text-gray-500;
}

.remove-file-btn {
  @apply p-1 text-red-500 hover:text-red-700 transition-colors;
}

.error-text {
  @apply text-sm text-red-600 mt-1;
}
</style>
