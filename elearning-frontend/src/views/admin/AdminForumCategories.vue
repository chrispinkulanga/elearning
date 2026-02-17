<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Forum Categories</h1>
      <p class="mt-2 text-gray-600">Manage forum categories for organizing discussions</p>
    </div>

    <!-- Add Category Button -->
    <div class="mb-6">
      <button
        @click="showCreateModal = true"
        class="btn btn-primary"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add New Category
      </button>
    </div>

    <!-- Categories List -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="categories.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Description
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Topics
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Sort Order
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div 
                    class="w-4 h-4 rounded-full mr-3"
                    :style="{ backgroundColor: category.color }"
                  ></div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                    <div class="text-sm text-gray-500">{{ category.slug }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate">
                  {{ category.description || 'No description' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ category.topics_count || 0 }} topics
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    category.is_active 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ category.sort_order }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    @click="editCategory(category)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit category"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deleteCategory(category)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete category"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new forum category.</p>
      <div class="mt-6">
        <button
          @click="showCreateModal = true"
          class="btn btn-primary"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Category
        </button>
      </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ showEditModal ? 'Edit Category' : 'Create New Category' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Category name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Category description (optional)"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
              <div class="flex items-center space-x-3">
                <input
                  v-model="form.color"
                  type="color"
                  class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                />
                <input
                  v-model="form.color"
                  type="text"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="#3B82F6"
                />
              </div>
              <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
              <input
                v-model="form.icon"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.icon }"
                placeholder="Icon name (optional)"
              />
              <p v-if="errors.icon" class="mt-1 text-sm text-red-600">{{ errors.icon }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
              <input
                v-model.number="form.sort_order"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.sort_order }"
                placeholder="0"
              />
              <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order }}</p>
            </div>

            <div v-if="showEditModal">
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700">Active</span>
              </label>
            </div>
          </div>

          <div class="flex space-x-3 mt-6">
            <button
              type="button"
              @click="closeModal"
              class="btn btn-secondary flex-1"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="btn btn-primary flex-1"
            >
              <span v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ submitting ? 'Saving...' : (showEditModal ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">Delete Category</h3>
          </div>
        </div>
        <div class="mb-6">
          <p class="text-sm text-gray-600">
            Are you sure you want to delete the category "{{ categoryToDelete?.name }}"? 
            This action cannot be undone.
          </p>
          <p v-if="categoryToDelete?.topics_count > 0" class="text-sm text-red-600 mt-2">
            Warning: This category has {{ categoryToDelete.topics_count }} topics. 
            You cannot delete a category with existing topics.
          </p>
        </div>
        <div class="flex space-x-3">
          <button @click="closeDeleteModal" class="btn btn-secondary flex-1">Cancel</button>
          <button 
            @click="confirmDelete" 
            :disabled="categoryToDelete?.topics_count > 0"
            class="btn btn-danger flex-1"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { forumCategoryAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()

// State
const loading = ref(false)
const submitting = ref(false)
const categories = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const categoryToDelete = ref(null)
const editingCategory = ref(null)

// Form data
const form = reactive({
  name: '',
  description: '',
  color: '#3B82F6',
  icon: '',
  sort_order: 0,
  is_active: true
})

const errors = reactive({})

// Methods
const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await forumCategoryAPI.getAll()
    if (response.data.status === 'success') {
      categories.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching categories:', error)
    toast.error('Failed to load categories')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.name = ''
  form.description = ''
  form.color = '#3B82F6'
  form.icon = ''
  form.sort_order = 0
  form.is_active = true
  Object.keys(errors).forEach(key => delete errors[key])
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  resetForm()
  editingCategory.value = null
}

const editCategory = (category) => {
  editingCategory.value = category
  form.name = category.name
  form.description = category.description || ''
  form.color = category.color
  form.icon = category.icon || ''
  form.sort_order = category.sort_order
  form.is_active = category.is_active
  showEditModal.value = true
}

const deleteCategory = (category) => {
  categoryToDelete.value = category
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  categoryToDelete.value = null
}

const submitForm = async () => {
  submitting.value = true
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    if (showEditModal.value) {
      const response = await forumCategoryAPI.update(editingCategory.value.id, form)
      if (response.data.status === 'success') {
        toast.success('Category updated successfully')
        await fetchCategories()
        closeModal()
      }
    } else {
      const response = await forumCategoryAPI.create(form)
      if (response.data.status === 'success') {
        toast.success('Category created successfully')
        await fetchCategories()
        closeModal()
      }
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      toast.error('Failed to save category')
    }
  } finally {
    submitting.value = false
  }
}

const confirmDelete = async () => {
  if (!categoryToDelete.value) return

  try {
    const response = await forumCategoryAPI.delete(categoryToDelete.value.id)
    if (response.data.status === 'success') {
      toast.success('Category deleted successfully')
      await fetchCategories()
      closeDeleteModal()
    }
  } catch (error) {
    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to delete category')
    }
  }
}

// Lifecycle
onMounted(() => {
  fetchCategories()
})
</script>
