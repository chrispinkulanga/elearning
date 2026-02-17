<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Create New Course</h1>
            <p class="mt-2 text-gray-600">Create a new course and start sharing your knowledge</p>
          </div>
          <router-link to="/dashboard" class="btn btn-secondary">
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <!-- Course Form -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Course Information</h2>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 space-y-8">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Course Title *</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.title }"
                placeholder="Enter course title"
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
              <p class="mt-1 text-sm text-gray-500">
                {{ form.title.length }}/255 characters (minimum 3 characters required)
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
              <div class="flex space-x-2">
                <select
                  v-model="form.category_id"
                  required
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  :class="{ 'border-red-500': errors.category_id }"
                  :disabled="loadingCategories"
                >
                  <option value="">
                    {{ loadingCategories ? 'Loading categories...' : 'Select a category' }}
                  </option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <button
                  type="button"
                  @click="openCategoryModal"
                  class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  :disabled="loadingCategories"
                >
                  + New
                </button>
              </div>
              <p v-if="loadingCategories" class="mt-1 text-sm text-gray-600">Loading categories...</p>
              <p v-else-if="categories.length === 0" class="mt-1 text-sm text-gray-600">No categories available. Create one using the + New button.</p>
              <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Level *</label>
              <select
                v-model="form.level"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.level }"
              >
                <option value="">Select level</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Price ($)</label>
              <input
                v-model="priceValue"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.price }"
                placeholder="0.00 for free courses"
              />
              <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
            </div>
          </div>

          <!-- Short Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
            <textarea
              v-model="form.short_description"
              rows="3"
              maxlength="500"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': errors.short_description }"
              placeholder="Brief summary of your course (max 500 characters)..."
            ></textarea>
            <p v-if="errors.short_description" class="mt-1 text-sm text-red-600">{{ errors.short_description }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ form.short_description.length }}/500 characters</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Course Description *</label>
            <textarea
              v-model="form.description"
              rows="6"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': errors.description }"
              placeholder="Describe your course content, what students will learn, and any prerequisites..."
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            <p class="mt-1 text-sm text-gray-500">
              {{ form.description.length }}/∞ characters (minimum 10 characters required)
            </p>
          </div>

          <!-- Learning Outcomes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
            <div class="space-y-2">
              <div v-for="(outcome, index) in form.outcomes" :key="index" class="flex items-center space-x-2">
                <input
                  v-model="form.outcomes[index]"
                  type="text"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="What will students learn?"
                />
                <button
                  type="button"
                  @click="removeLearningOutcome(index)"
                  class="p-2 text-red-600 hover:text-red-800"
                  v-if="form.outcomes.length > 1"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
            <button
              type="button"
              @click="addLearningOutcome"
              class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium"
            >
              + Add Learning Outcome
            </button>
            <p v-if="errors.outcomes" class="mt-1 text-sm text-red-600">{{ errors.outcomes }}</p>
            <p class="mt-1 text-sm text-gray-500">At least one learning outcome is required</p>
          </div>

          <!-- Requirements -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
            <div class="space-y-2">
              <div v-for="(requirement, index) in form.requirements" :key="index" class="flex items-center space-x-2">
                <input
                  v-model="form.requirements[index]"
                  type="text"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="What do students need to know before starting?"
                />
                <button
                  type="button"
                  @click="removeRequirement(index)"
                  class="p-2 text-red-600 hover:text-red-800"
                  v-if="form.requirements.length > 1"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
            <button
              type="button"
              @click="addRequirement"
              class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium"
            >
              + Add Requirement
            </button>
            <p v-if="errors.requirements" class="mt-1 text-sm text-red-600">{{ errors.requirements }}</p>
            <p class="mt-1 text-sm text-gray-500">At least one requirement is required</p>
          </div>

          <!-- Course Image -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Course Image (Optional)</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors duration-200" 
                 :class="{ 
                   'border-green-300 bg-green-50': form.thumbnail, 
                   'border-gray-300': !form.thumbnail && !isDragOver,
                   'border-blue-400 bg-blue-50': isDragOver
                 }"
                 @dragover.prevent="isDragOver = true"
                 @dragenter.prevent="isDragOver = true"
                 @dragleave.prevent="isDragOver = false"
                 @drop.prevent="handleFileDrop">
              <div class="space-y-1 text-center">
                <svg v-if="!form.thumbnail" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <svg v-else class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="flex text-sm" :class="form.thumbnail ? 'text-green-600' : 'text-gray-600'">
                  <input
                    ref="fileInputRef"
                    id="file-upload"
                    name="file-upload"
                    type="file"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    accept="image/*"
                    @change="handleImageUpload"
                  />
                  <p class="pl-1">{{ form.thumbnail ? 'or drag and drop a new one' : 'or drag and drop' }}</p>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                <p class="text-xs text-gray-400">Thumbnail is optional - you can add it later</p>
              </div>
            </div>
            
            <!-- Thumbnail Preview and Clear -->
            <div v-if="form.thumbnail" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                      <img 
                    v-if="form.thumbnail instanceof File" 
                    :src="URL.createObjectURL(form.thumbnail)" 
                        alt="Course thumbnail preview" 
                    class="h-24 w-24 object-cover rounded-lg border border-gray-300 shadow-sm"
                      />
                  </div>
                  <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-medium text-gray-900 mb-1">Selected Image</h4>
                  <p class="text-sm font-medium text-gray-700 truncate mb-1">
                      {{ form.thumbnail.name }}
                    </p>
                    <p class="text-xs text-gray-500 mb-2">
                    {{ (form.thumbnail.size / 1024 / 1024).toFixed(2) }} MB • {{ form.thumbnail.type }}
                    </p>
                    <button
                      type="button"
                      @click="clearThumbnail"
                    class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                      Remove Image
                    </button>
                </div>
              </div>
            </div>
            
            <p v-if="errors.thumbnail" class="mt-1 text-sm text-red-600">{{ errors.thumbnail }}</p>
          </div>

          <!-- Course Settings -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Course Settings</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.is_featured"
                  id="featured"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label for="featured" class="ml-2 block text-sm text-gray-900">
                  Feature this course on the homepage
                </label>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="saveDraft"
              :disabled="saving"
              class="btn btn-secondary"
            >
              <span v-if="saving" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ saving ? 'Saving...' : 'Save as Draft' }}
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="btn btn-primary"
            >
              <span v-if="saving" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ saving ? 'Creating...' : 'Create Course' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Category Creation Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Category</h3>
          <form @submit.prevent="createCategory">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
              <input
                ref="categoryNameInput"
                v-model="newCategory.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': categoryErrors.name }"
                placeholder="Enter category name"
                @input="clearCategoryError('name')"
              />
              <p v-if="categoryErrors.name" class="mt-1 text-sm text-red-600">{{ categoryErrors.name }}</p>
            </div>
            <!-- Category Description -->
            <div class="mb-4">
              <label for="category-description" class="block text-sm font-medium text-gray-700 mb-2">
                Description (Optional)
              </label>
              <textarea
                id="category-description"
                v-model="newCategory.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Enter category description"
              ></textarea>
            </div>

            <!-- Category Color -->
            <div class="mb-4">
              <label for="category-color" class="block text-sm font-medium text-gray-700 mb-2">
                Color
              </label>
              <div class="flex items-center space-x-3">
                <input
                  id="category-color"
                  v-model="newCategory.color"
                  type="color"
                  class="w-12 h-10 border border-gray-300 rounded-md cursor-pointer"
                />
                <input
                  type="text"
                  v-model="newCategory.color"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="#3B82F6"
                />
              </div>
              <p v-if="categoryErrors.color" class="mt-1 text-sm text-red-600">{{ categoryErrors.color }}</p>
              <p class="mt-1 text-sm text-gray-500">Choose a color to represent this category</p>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCategoryModal"
                class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="creatingCategory"
                class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50"
              >
                <span v-if="creatingCategory" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                {{ creatingCategory ? 'Creating...' : 'Create Category' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'

const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

const saving = ref(false)
const categories = ref([])
const loadingCategories = ref(false)
const errors = reactive({})
const showCategoryModal = ref(false)
const creatingCategory = ref(false)
const fileInputRef = ref(null)
const isDragOver = ref(false)
const categoryErrors = reactive({
  name: '',
  color: ''
})
const categoryNameInput = ref(null)
const newCategory = reactive({
  name: '',
  description: '',
  color: '#3B82F6'
})

        // Initialize form data
const form = reactive({
  title: '',
            short_description: '',
  description: '',
  category_id: '',
            level: 'beginner',
  price: 0,
            is_free: false,
            thumbnail: null,
            preview_video: '',
            tags: [],
  requirements: [''],
            outcomes: [''],
  is_featured: false,
            status: 'draft' // Only use valid enum values: draft, pending, approved, rejected
        })

// Add a computed property to ensure price is always numeric
const priceValue = computed({
  get: () => {
    const val = parseFloat(form.price) || 0
    return val
  },
  set: (value) => {
    // Ensure only numeric input
    const numValue = parseFloat(value) || 0
    form.price = numValue
    form.is_free = numValue === 0
  }
})

const addLearningOutcome = () => {
  form.outcomes.push('')  // Updated to use outcomes
}

const removeLearningOutcome = (index) => {
  form.outcomes.splice(index, 1)  // Updated to use outcomes
}

const addRequirement = () => {
  form.requirements.push('')
}

const removeRequirement = (index) => {
  form.requirements.splice(index, 1)
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  
  if (file) {
    if (file.size > 10 * 1024 * 1024) { // 10MB limit
      toast.error('Image size must be less than 10MB')
      event.target.value = '' // Clear the input
      form.thumbnail = null
      return
    }
    
    // Set the thumbnail file
    form.thumbnail = file
  } else {
    form.thumbnail = null
  }
}

const handleFileDrop = (event) => {
  isDragOver.value = false
  const files = event.dataTransfer.files
  
  if (files && files.length > 0) {
    const file = files[0]
    
    // Check if it's an image file
    if (!file.type.startsWith('image/')) {
      toast.error('Please select an image file')
      return
    }
    
    if (file.size > 10 * 1024 * 1024) { // 10MB limit
      toast.error('Image size must be less than 10MB')
      return
    }
    
    // Set the thumbnail file
    form.thumbnail = file
    
    // Also update the file input to keep it in sync
    if (fileInputRef.value) {
      // Create a new FileList-like object
      const dataTransfer = new DataTransfer()
      dataTransfer.items.add(file)
      fileInputRef.value.files = dataTransfer.files
    }
  }
}

const clearThumbnail = () => {
  form.thumbnail = null
  // Clear the file input using ref
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const validateForm = () => {
  errors.value = {}
  
  // Required field validation
  if (!form.title || form.title.trim() === '') {
    errors.title = 'Course title is required'
  }
  
  if (!form.category_id || form.category_id === '') {
    errors.category_id = 'Please select a category'
  }
  
  if (!form.level || form.level === '') {
    errors.level = 'Please select a level'
  }
  
  if (!form.description || form.description.trim() === '') {
    errors.description = 'Course description is required'
  }
  
  // Ensure price is numeric and valid
  const price = parseFloat(form.price) || 0
  if (isNaN(price) || price < 0) {
    errors.price = 'Price must be a valid number greater than or equal to 0'
  }
  
  // Set is_free based on price
  form.is_free = price === 0
  
  // Filter out empty learning outcomes and requirements
  form.outcomes = form.outcomes.filter(outcome => outcome.trim())
  form.requirements = form.requirements.filter(requirement => requirement.trim())
  
  // Additional validation with better error messages
  if (form.title && form.title.trim().length < 3) {
    errors.title = `Course title must be at least 3 characters long (currently ${form.title.trim().length})`
  }
  
  if (form.description && form.description.trim().length < 10) {
    errors.description = `Course description must be at least 10 characters long (currently ${form.description.trim().length})`
  }
  
  if (form.short_description && form.short_description.trim().length > 500) {
    errors.short_description = 'Short description cannot exceed 500 characters'
  }
  
  // Validate that outcomes and requirements are not empty if they exist
  if (form.outcomes.length === 0 || (form.outcomes.length === 1 && form.outcomes[0].trim() === '')) {
    errors.outcomes = 'At least one learning outcome is required'
  }
  
  if (form.requirements.length === 0 || (form.requirements.length === 1 && form.requirements[0].trim() === '')) {
    errors.requirements = 'At least one requirement is required'
  }
  
  // Thumbnail validation (optional but if provided, must be valid)
  if (form.thumbnail && !(form.thumbnail instanceof File)) {
    errors.thumbnail = 'Thumbnail must be a valid image file'
  }
  
  console.log('Form validation result:', {
    hasErrors: Object.keys(errors.value).length > 0,
    errors: errors.value,
    formData: form
  })
  
  return Object.keys(errors.value).length === 0
}

const saveDraft = async () => {
  form.status = 'draft'
  await submitForm()
}

const handleSubmit = async () => {
  form.status = 'pending' // Use 'pending' instead of 'published' - valid enum values are: draft, pending, approved, rejected
  await submitForm()
}

const submitForm = async () => {
  if (!validateForm()) return
  
  saving.value = true
  
  try {
    const formData = new FormData()
    
    // Debug: Log the form data before processing
    console.log('Form data before processing:', form)
    
    // Ensure price is numeric
    const price = parseFloat(form.price) || 0
    form.price = price
    form.is_free = price === 0
    
    // Append form fields with correct backend field names and data types
    Object.keys(form).forEach(key => {
      if (key === 'outcomes' || key === 'requirements') {
        // Send arrays in Laravel's expected format: field[0], field[1], etc.
        const filteredArray = form[key].filter(item => item.trim())
        if (filteredArray.length > 0) {
          filteredArray.forEach((item, index) => {
            formData.append(`${key}[${index}]`, item.trim())
          })
          console.log(`Appending ${key} as array:`, filteredArray)
        }
      } else if (key === 'thumbnail') {
        // Only append thumbnail if it's actually a valid file
        if (form[key] && form[key] instanceof File) {
          formData.append('thumbnail', form[key])
          console.log('Appending thumbnail file:', form[key].name, form[key].size, form[key].type)
        } else {
          // Don't append thumbnail field if no file is selected
          console.log('No valid thumbnail file to append - skipping thumbnail field entirely')
        }
      } else if (key === 'price') {
        // Ensure price is numeric
        formData.append(key, price)
        console.log('Appending price:', price)
      } else if (key === 'is_free' || key === 'is_featured') {
        // Ensure boolean fields are sent as proper boolean values
        formData.append(key, form[key] ? '1' : '0')
        console.log(`Appending ${key}:`, form[key] ? '1' : '0')
      } else if (key !== 'thumbnail') {
        formData.append(key, form[key])
        console.log(`Appending ${key}:`, form[key])
      }
    })
    
    // Debug: Log the FormData contents
    console.log('FormData contents:')
    for (let [key, value] of formData.entries()) {
      console.log(`${key}:`, value)
    }
    
    console.log('Submitting course creation request...')
    const course = await courseStore.createCourse(formData)
    console.log('Course created successfully:', course)
    toast.success('Course created successfully!')
    router.push('/instructor')
  } catch (error) {
    console.error('Course creation error:', error)
    console.error('Error response:', error.response)
    console.error('Error status:', error.response?.status)
    console.error('Error data:', error.response?.data)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      console.log('Validation errors:', errors.value)
      
      // Show specific error messages
      Object.keys(errors.value).forEach(field => {
        if (errors.value[field]) {
          toast.error(`${field}: ${errors.value[field]}`)
        }
      })
    } else if (error.response?.status === 404) {
      toast.error('Backend endpoint not found. Please ensure the server is running.')
    } else if (error.response?.status === 500) {
      toast.error('Server error. Please check the backend logs.')
    } else {
      toast.error('Failed to create course. Please try again.')
    }
  } finally {
    saving.value = false
  }
}

const openCategoryModal = () => {
  // Clear previous values
  newCategory.name = ''
  newCategory.description = ''
  newCategory.color = '#3B82F6' // Reset color
  categoryErrors.name = ''
  categoryErrors.color = ''
  showCategoryModal.value = true
  
  // Focus on category name input after modal opens
  nextTick(() => {
    if (categoryNameInput.value) {
      categoryNameInput.value.focus()
    }
  })
}

const closeCategoryModal = () => {
  showCategoryModal.value = false
  newCategory.name = ''
  newCategory.description = ''
  newCategory.color = '#3B82F6' // Reset color
  categoryErrors.name = ''
  categoryErrors.color = ''
}

const clearCategoryError = (field) => {
  if (categoryErrors[field]) {
    categoryErrors[field] = ''
  }
}

const validateCategoryForm = () => {
  categoryErrors.name = ''
  categoryErrors.color = ''
  
  if (!newCategory.name.trim()) {
    categoryErrors.name = 'Category name is required'
    return false
  }
  
  if (newCategory.name.trim().length > 255) {
    categoryErrors.name = 'Category name must not exceed 255 characters'
    return false
  }
  
  // Check if category name already exists
  const existingCategory = categories.value.find(
    cat => cat.name.toLowerCase() === newCategory.name.trim().toLowerCase()
  )
  
  if (existingCategory) {
    categoryErrors.name = 'A category with this name already exists'
    return false
  }
  
  // Validate color format if provided
  if (newCategory.color && !/^#[0-9A-F]{6}$/i.test(newCategory.color)) {
    categoryErrors.color = 'Color must be a valid hex color code (e.g., #FF5733)'
    return false
  }
  
  return true
}

const createCategory = async () => {
  if (!validateCategoryForm()) {
    return
  }

  creatingCategory.value = true
  try {
    const response = await courseStore.createCategory({
      name: newCategory.name.trim(),
      description: newCategory.description.trim(),
      color: newCategory.color // Include color in the request
    })
    
    // Add new category to the list
    categories.value.push(response)
    
    // Automatically select the new category
    form.category_id = response.id
    
    // Close modal and show success message
    closeCategoryModal()
    toast.success('Category created successfully!')
  } catch (error) {
    console.error('Error creating category:', error)
    console.error('Error response:', error.response)
    console.error('Error status:', error.response?.status)
    console.error('Error data:', error.response?.data)
    
    if (error.response?.data?.errors) {
      // Handle backend validation errors
      Object.keys(error.response.data.errors).forEach(field => {
        categoryErrors[field] = error.response.data.errors[field][0]
      })
    } else if (error.response?.data?.message) {
      // Handle backend error messages
      toast.error(error.response.data.message)
    } else if (error.response?.status === 401) {
      toast.error('Authentication failed. Please log in again.')
    } else if (error.response?.status === 403) {
      toast.error('Access denied. You do not have permission to create categories.')
    } else if (error.response?.status === 422) {
      toast.error('Validation failed. Please check your input.')
    } else if (error.response?.status >= 500) {
      toast.error('Server error. Please try again later.')
    } else {
      toast.error(`Failed to create category: ${error.message}`)
    }
  } finally {
    creatingCategory.value = false
  }
}

onMounted(async () => {
  try {
    console.log('CourseCreate: Starting to fetch categories...')
    loadingCategories.value = true
    
    console.log('CourseCreate: Calling courseStore.fetchCategories()...')
    await courseStore.fetchCategories()
    
    console.log('CourseCreate: fetchCategories completed')
    console.log('CourseCreate: courseStore.categories value:', courseStore.categories)
    console.log('CourseCreate: courseStore.categories length:', courseStore.categories?.length)
    
    categories.value = courseStore.categories
    console.log('CourseCreate: Local categories value set:', categories.value)
    console.log('CourseCreate: Local categories length:', categories.value?.length)
    
  } catch (error) {
    console.error('CourseCreate: Error fetching categories:', error)
    console.error('CourseCreate: Error response:', error.response)
    console.error('CourseCreate: Error status:', error.response?.status)
    console.error('CourseCreate: Error data:', error.response?.data)
    // Don't show error toast to avoid more error messages
    // toast.error('Failed to load categories. Please refresh the page.')
  } finally {
    loadingCategories.value = false
    console.log('CourseCreate: Loading categories completed, loadingCategories.value:', loadingCategories.value)
  }
})
</script> 