<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Create Category</h1>
            <p class="mt-2 text-sm text-gray-600">Add a new category to organize your courses</p>
          </div>
          <router-link to="/dashboard" class="btn btn-secondary">
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-lg shadow-sm">
        <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
          <!-- Category Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': errors.name }"
              placeholder="Enter category name"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Enter category description (optional)"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>

          <!-- Color -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
            <div class="flex items-center space-x-3">
              <input
                v-model="form.color"
                type="color"
                class="w-12 h-10 border border-gray-300 rounded-md cursor-pointer"
              />
              <input
                v-model="form.color"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="#3B82F6"
              />
            </div>
            <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color }}</p>
            <p class="mt-1 text-sm text-gray-500">Choose a color to represent this category</p>
          </div>

          <!-- Sort Order -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
            <input
              v-model.number="form.sort_order"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="0"
            />
            <p class="mt-1 text-sm text-gray-500">Lower numbers appear first in lists</p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <router-link to="/dashboard" class="btn btn-secondary">
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="saving"
              class="btn btn-primary"
            >
              <span v-if="saving" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ saving ? 'Creating...' : 'Create Category' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

const saving = ref(false)
const errors = reactive({})

const form = reactive({
  name: '',
  description: '',
  color: '#3B82F6',
  sort_order: 0
})

// Watch color field to ensure it's always valid
watch(() => form.color, (newColor) => {
  if (!newColor || !/^#[0-9A-F]{6}$/i.test(newColor)) {
    form.color = '#3B82F6'
  }
}, { immediate: true })

const validateForm = () => {
  errors.name = ''
  errors.description = ''
  errors.color = ''
  
  if (!form.name.trim()) {
    errors.name = 'Category name is required'
    return false
  }
  
  if (form.name.trim().length < 2) {
    errors.name = 'Category name must be at least 2 characters'
    return false
  }
  
  if (form.name.trim().length > 255) {
    errors.name = 'Category name must not exceed 255 characters'
    return false
  }
  
  if (form.description && form.description.trim().length > 0 && form.description.trim().length > 1000) {
    errors.description = 'Description must not exceed 1000 characters'
    return false
  }
  
  // Ensure color is always a valid hex color
  if (!form.color || !/^#[0-9A-F]{6}$/i.test(form.color)) {
    form.color = '#3B82F6' // Set default if invalid or missing
  }
  
  return true
}

const handleSubmit = async () => {
  if (!validateForm()) return
  
  saving.value = true
  
  try {
    // Ensure color has a valid value before submission
    if (!form.color || !/^#[0-9A-F]{6}$/i.test(form.color)) {
      form.color = '#3B82F6'
    }
    
    const categoryData = {
      name: form.name.trim(),
      description: form.description.trim() || null,
      color: form.color,
      sort_order: form.sort_order || 0
    }
    
    console.log('CategoryCreate: Form state before submission:', { ...form })
    console.log('CategoryCreate: Category data to be sent:', categoryData)
    console.log('CategoryCreate: Color field value:', categoryData.color)
    console.log('CategoryCreate: Color field type:', typeof categoryData.color)
    console.log('CategoryCreate: API endpoint will be: POST /api/categories')
    console.log('CategoryCreate: Auth token:', localStorage.getItem('auth_token'))
    
    const response = await courseStore.createCategory(categoryData)
    console.log('CategoryCreate: Response received:', response)
    
    // Always show success message if we get here (no error thrown)
    console.log('CategoryCreate: Category created successfully, showing success message')
    toast.success('Category created successfully!')
    console.log('CategoryCreate: Success toast shown, redirecting to dashboard')
    
    // Wait a moment for the toast to be visible before redirecting
    setTimeout(() => {
      router.push('/dashboard')
    }, 1000)
  } catch (error) {
    console.error('CategoryCreate: Error creating category:', error)
    console.error('CategoryCreate: Error response:', error.response)
    console.error('CategoryCreate: Error status:', error.response?.status)
    console.error('CategoryCreate: Error data:', error.response?.data)
    console.error('CategoryCreate: Error message:', error.message)
    console.error('CategoryCreate: Full error object:', error)
    
    // Show specific error message based on status
    if (error.response?.status === 404) {
      toast.error('Category endpoint not found. Please check backend routes.')
    } else if (error.response?.status === 401) {
      toast.error('Authentication failed. Please log in again.')
    } else if (error.response?.status === 403) {
      toast.error('Access denied. You do not have permission to create categories.')
    } else if (error.response?.status === 422) {
      // Handle validation errors more specifically
      if (error.response?.data?.errors) {
        const validationErrors = error.response.data.errors
        console.log('CategoryCreate: Validation errors received:', validationErrors)
        Object.keys(validationErrors).forEach(field => {
          errors[field] = validationErrors[field][0]
        })
        toast.error('Please fix the validation errors below.')
      } else {
        toast.error('Validation failed. Please check your input.')
      }
    } else if (error.response?.status >= 500) {
      toast.error('Server error. Please try again later.')
    } else if (error.response?.data?.errors) {
      // Handle backend validation errors
      Object.keys(error.response.data.errors).forEach(field => {
        errors[field] = error.response.data.errors[field][0]
      })
    } else if (error.response?.data?.message) {
      // Handle backend error messages
      toast.error(error.response.data.message)
    } else {
      toast.error(`Failed to create category: ${error.message}`)
    }
  } finally {
    saving.value = false
  }
}
</script>
