<template>
  <div class="file-upload">
    <div class="upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
      <input
        ref="fileInput"
        type="file"
        multiple
        @change="handleFileSelect"
        class="hidden"
        accept="image/*,video/*,.pdf,.doc,.docx,.txt,.csv"
      />
      
      <div v-if="!files.length" @click="triggerFileSelect" class="cursor-pointer">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <p class="mt-1 text-sm text-gray-600">
          <span class="font-medium text-primary-600 hover:text-primary-500">Click to upload</span>
          or drag and drop
        </p>
        <p class="mt-1 text-xs text-gray-500">
          Images, videos, PDFs, documents up to 10MB
        </p>
      </div>
      
      <div v-else class="space-y-3">
        <div v-for="(file, index) in files" :key="index" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center space-x-4">
            <div v-if="file.isImage" class="w-16 h-16 flex-shrink-0 cursor-pointer group relative" @click="openImagePreview(file.preview, file.name)">
              <img :src="file.preview" :alt="file.name" class="w-full h-full object-cover rounded border border-gray-200 group-hover:border-primary-300 transition-colors duration-200" />
              <!-- Enhancement indicator -->
              <div v-if="file.enhanced" class="absolute top-1 right-1 bg-green-500 text-white text-xs px-1 py-0.5 rounded-full" title="Auto-enhanced for better visibility">
                ✨
              </div>
            </div>
            <div v-else-if="file.isVideo" class="w-16 h-16 flex-shrink-0 video-preview">
              <video :src="file.preview" class="w-full h-full object-cover rounded border border-gray-200" muted>
                <source :src="file.preview" :type="file.type">
              </video>
            </div>
            <div v-else class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
              <span class="text-gray-500 text-xs">{{ getFileExtension(file.name) }}</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ file.name }}</p>
              <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <div v-if="file.uploading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-600"></div>
            <div v-else-if="file.uploaded" class="text-green-500">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <button
              @click="removeFile(index)"
              class="text-red-500 hover:text-red-700"
              :disabled="file.uploading"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        
        <button
          type="button"
          @click="triggerFileSelect"
          class="mt-3 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
        >
          Add More Files
        </button>
      </div>
    </div>
    
    <div v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</div>
    
    <!-- Image Preview Modal -->
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeImageModal">
      <div class="max-w-4xl max-h-[90vh] w-full mx-4" @click.stop>
        <div class="bg-white rounded-lg overflow-hidden">
          <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-medium text-gray-900">{{ selectedImageName }}</h3>
            <button @click="closeImageModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="p-4">
            <img 
              :src="selectedImageSrc"
              :alt="selectedImageName"
              class="max-w-full max-h-[70vh] object-contain mx-auto"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { autoEnhanceImage, shouldEnhanceImage, getEnhancementOptions, createEnhancedImageUrl } from '@/utils/imageEnhancement'

const props = defineProps({
  maxFiles: {
    type: Number,
    default: 5
  },
  maxFileSize: {
    type: Number,
    default: 10 * 1024 * 1024 // 10MB
  },
  autoEnhance: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['files-selected', 'files-removed'])

const fileInput = ref(null)
const files = ref([])
const error = ref('')
const showImageModal = ref(false)
const selectedImageSrc = ref('')
const selectedImageName = ref('')

const triggerFileSelect = () => {
  fileInput.value.click()
}

const handleFileSelect = (event) => {
  const selectedFiles = Array.from(event.target.files)
  error.value = ''
  
  // Validate number of files
  if (files.value.length + selectedFiles.length > props.maxFiles) {
    error.value = `Maximum ${props.maxFiles} files allowed`
    return
  }
  
  selectedFiles.forEach(file => {
    // Validate file size
    if (file.size > props.maxFileSize) {
      error.value = `File ${file.name} is too large. Maximum size is ${formatFileSize(props.maxFileSize)}`
      return
    }
    
    // Validate file type
    if (!isValidFileType(file)) {
      error.value = `File type not supported for ${file.name}`
      return
    }
    
    const fileObj = {
      file,
      name: file.name,
      size: file.size,
      type: file.type,
      isImage: file.type.startsWith('image/'),
      isVideo: file.type.startsWith('video/'),
      uploading: false,
      uploaded: false,
      preview: null,
      enhanced: false,
      originalFile: file
    }
    
    // Create preview for images with automatic enhancement
    if (fileObj.isImage) {
      if (props.autoEnhance && shouldEnhanceImage(file)) {
        // Auto-enhance the image
        enhanceImageAsync(fileObj)
      } else {
        // Use original image
        const reader = new FileReader()
        reader.onload = (e) => {
          fileObj.preview = e.target.result
        }
        reader.readAsDataURL(file)
      }
    }
    
    // Create preview for videos
    if (fileObj.isVideo) {
      const reader = new FileReader()
      reader.onload = (e) => {
        fileObj.preview = e.target.result
      }
      reader.readAsDataURL(file)
    }
    
    files.value.push(fileObj)
  })
  
  emit('files-selected', files.value)
  event.target.value = '' // Reset input
}

const removeFile = (index) => {
  const removedFile = files.value[index]
  files.value.splice(index, 1)
  emit('files-removed', files.value, removedFile)
}

const isValidFileType = (file) => {
  const allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/webm',
    'video/ogg',
    'video/quicktime',
    'video/x-msvideo',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain',
    'text/csv'
  ]
  return allowedTypes.includes(file.type)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileExtension = (filename) => {
  return filename.split('.').pop().toUpperCase()
}

const openImagePreview = (imageSrc, imageName) => {
  selectedImageSrc.value = imageSrc
  selectedImageName.value = imageName
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  selectedImageSrc.value = ''
  selectedImageName.value = ''
}

/**
 * Asynchronously enhance an image
 */
const enhanceImageAsync = async (fileObj) => {
  try {
    const options = getEnhancementOptions(fileObj.originalFile)
    const enhancedBlob = await autoEnhanceImage(fileObj.originalFile, options)
    
    // Create enhanced file object
    const enhancedFile = new File([enhancedBlob], fileObj.name, {
      type: fileObj.type,
      lastModified: Date.now()
    })
    
    // Update file object with enhanced version
    fileObj.file = enhancedFile
    fileObj.enhanced = true
    fileObj.size = enhancedBlob.size
    
    // Create preview from enhanced image
    const reader = new FileReader()
    reader.onload = (e) => {
      fileObj.preview = e.target.result
    }
    reader.readAsDataURL(enhancedBlob)
    
  } catch (error) {
    console.warn('Image enhancement failed, using original:', error)
    // Fallback to original image
    const reader = new FileReader()
    reader.onload = (e) => {
      fileObj.preview = e.target.result
    }
    reader.readAsDataURL(fileObj.originalFile)
  }
}

const uploadFiles = async () => {
  const uploadPromises = files.value.map(async (fileObj) => {
    if (fileObj.uploaded) return fileObj
    
    fileObj.uploading = true
    try {
      // Simulate upload - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      fileObj.uploading = false
      fileObj.uploaded = true
      return fileObj
    } catch (error) {
      fileObj.uploading = false
      throw error
    }
  })
  
  try {
    await Promise.all(uploadPromises)
    return files.value
  } catch (error) {
    console.error('Upload failed:', error)
    throw error
  }
}

// Expose methods for parent component
defineExpose({
  uploadFiles,
  files: computed(() => files.value)
})
</script>

<style scoped>
.file-upload {
  @apply w-full;
}

.upload-area {
  @apply cursor-pointer;
}

.upload-area:hover {
  @apply border-gray-400;
}

/* Video preview styles */
.file-upload video {
  object-fit: cover;
  border-radius: 0.375rem;
}

.file-upload .video-preview {
  position: relative;
}

.file-upload .video-preview::after {
  content: '▶';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 1.5rem;
  text-shadow: 0 0 4px rgba(0, 0, 0, 0.8);
  pointer-events: none;
}
</style>
