<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ isEdit ? 'Edit Category' : 'Add New Category' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Category Name *
            </label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Description *
            </label>
            <textarea
              v-model="formData.description"
              required
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.description }"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
              {{ errors.description }}
            </p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Color (hex code) *
            </label>
            <input
              v-model="formData.color"
              type="text"
              required
              placeholder="#1976d2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.color }"
            />
            <p v-if="errors.color" class="mt-1 text-sm text-red-600">
              {{ errors.color }}
            </p>
            <div class="mt-2 flex items-center gap-2">
              <div class="w-4 h-4 rounded-full border border-gray-300" :style="{ backgroundColor: formData.color || '#1976d2' }"></div>
              <span class="text-sm text-gray-500">Preview</span>
            </div>
          </div>
          
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isEdit ? 'Updating...' : 'Creating...' }}
              </span>
              <span v-else>{{ isEdit ? 'Update' : 'Create' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  category: {
    type: Object,
    default: null
  },
  isEdit: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'saved'])

const authStore = useAuthStore()
const toast = useToast()

// State
const loading = ref(false)
const formData = reactive({
  name: '',
  description: '',
  color: '#1976d2'
})

const errors = reactive({
  name: '',
  description: '',
  color: ''
})

// Validation rules
const validationRules = {
  name: (value) => {
    if (!value.trim()) return 'Name is required'
    if (value.length < 2) return 'Name must be at least 2 characters'
    return ''
  },
  description: (value) => {
    if (!value.trim()) return 'Description is required'
    if (value.length < 10) return 'Description must be at least 10 characters'
    return ''
  },
  color: (value) => {
    if (!value.trim()) return 'Color is required'
    if (!/^#[0-9A-F]{6}$/i.test(value)) return 'Color must be a valid hex code (e.g., #1976d2)'
    return ''
  }
}

// Methods
const validateForm = () => {
  let isValid = true
  
  // Clear previous errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  // Validate each field
  Object.keys(validationRules).forEach(field => {
    const error = validationRules[field](formData[field])
    if (error) {
      errors[field] = error
      isValid = false
    }
  })
  
  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return
  
  try {
    loading.value = true
    
    const url = props.isEdit 
      ? `/api/admin/categories/${props.category.id}`
      : `/api/admin/categories`
    
    const method = props.isEdit ? 'PUT' : 'POST'
    
    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to save category')
    }
    
    toast.success(`Category ${props.isEdit ? 'updated' : 'created'} successfully!`)
    emit('saved')
    
  } catch (error) {
    console.error('Error saving category:', error)
    toast.error(error.message || 'Failed to save category')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  formData.name = ''
  formData.description = ''
  formData.color = '#1976d2'
  
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

// Watchers
watch(() => props.category, (newCategory) => {
  if (newCategory) {
    formData.name = newCategory.name || ''
    formData.description = newCategory.description || ''
    formData.color = newCategory.color || '#1976d2'
  } else {
    resetForm()
  }
}, { immediate: true })

// Lifecycle
onMounted(() => {
  if (props.category) {
    formData.name = props.category.name || ''
    formData.description = props.category.description || ''
    formData.color = props.category.color || '#1976d2'
  }
})
</script>
