<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Course</h1>
            <p class="mt-2 text-gray-600">Update your course information and content</p>
          </div>
          <router-link :to="`/courses/${course?.slug}`" class="btn btn-secondary">
            Back to Course
          </router-link>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Course Form -->
      <div v-else-if="course" class="bg-white rounded-lg shadow-sm">
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
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
              <select
                v-model="form.category_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': errors.category_id }"
              >
                <option value="">Select a category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
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
                v-model="form.price"
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
          </div>

          <!-- Learning Outcomes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
            <div class="space-y-2">
              <div v-for="(outcome, index) in form.learning_outcomes" :key="index" class="flex items-center space-x-2">
                <input
                  v-model="form.learning_outcomes[index]"
                  type="text"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="What will students learn?"
                />
                <button
                  type="button"
                  @click="removeLearningOutcome(index)"
                  class="p-2 text-red-600 hover:text-red-800"
                  v-if="form.learning_outcomes.length > 1"
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
          </div>

          <!-- Course Image -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Course Image</label>
            <div v-if="course.image" class="mb-4">
              <img :src="course.image" alt="Current course image" class="w-32 h-20 object-cover rounded-md" />
              <p class="text-sm text-gray-500 mt-1">Current image</p>
            </div>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                    <span>Upload a file</span>
                    <input
                      id="file-upload"
                      name="file-upload"
                      type="file"
                      class="sr-only"
                      accept="image/*"
                      @change="handleImageUpload"
                    />
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
              </div>
            </div>
            <p v-if="errors.image" class="mt-1 text-sm text-red-600">{{ errors.image }}</p>
          </div>

          <!-- Course Settings -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Course Settings</h3>
            <div class="space-y-4">
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

              <div class="flex items-center">
                <input
                  v-model="form.allow_reviews"
                  id="reviews"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label for="reviews" class="ml-2 block text-sm text-gray-900">
                  Allow student reviews
                </label>
              </div>

              <div class="flex items-center">
                <input
                  v-model="form.allow_certificates"
                  id="certificates"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label for="certificates" class="ml-2 block text-sm text-gray-900">
                  Issue certificates upon completion
                </label>
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
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
              {{ saving ? 'Updating...' : 'Update Course' }}
            </button>
          </div>
        </form>
      </div>

      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Course not found</h3>
        <p class="mt-1 text-sm text-gray-500">The course you're looking for doesn't exist.</p>
        <div class="mt-6">
          <router-link to="/dashboard" class="btn btn-primary">
            Back to Dashboard
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

const loading = ref(false)
const saving = ref(false)
const course = ref(null)
const categories = ref([])
const errors = reactive({})

const form = reactive({
  title: '',
  subtitle: '',
  description: '',
  category_id: '',
  level: '',
  price: 0,
  learning_outcomes: [''],
  requirements: [''],
  image: null,
  is_featured: false,
  allow_reviews: true,
  allow_certificates: true,
  status: 'draft'
})

const addLearningOutcome = () => {
  form.learning_outcomes.push('')
}

const removeLearningOutcome = (index) => {
  form.learning_outcomes.splice(index, 1)
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
      return
    }
    form.image = file
  }
}

const populateForm = () => {
  if (course.value) {
    form.title = course.value.title || ''
    form.subtitle = course.value.subtitle || ''
    form.description = course.value.description || ''
    form.category_id = course.value.category_id || ''
    form.level = course.value.level || ''
    form.price = course.value.price || 0
    form.learning_outcomes = course.value.learning_outcomes || ['']
    form.requirements = course.value.requirements || ['']
    form.is_featured = course.value.is_featured || false
    form.allow_reviews = course.value.allow_reviews !== false
    form.allow_certificates = course.value.allow_certificates !== false
    form.status = course.value.status || 'draft'
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.title) {
    errors.title = 'Course title is required'
  }
  
  if (!form.category_id) {
    errors.category_id = 'Please select a category'
  }
  
  if (!form.level) {
    errors.level = 'Please select a level'
  }
  
  if (!form.description) {
    errors.description = 'Course description is required'
  }
  
  if (form.price < 0) {
    errors.price = 'Price cannot be negative'
  }
  
  // Filter out empty learning outcomes and requirements
  form.learning_outcomes = form.learning_outcomes.filter(outcome => outcome.trim())
  form.requirements = form.requirements.filter(requirement => requirement.trim())
  
  return Object.keys(errors.value).length === 0
}

const saveDraft = async () => {
  form.status = 'draft'
  await submitForm()
}

const handleSubmit = async () => {
  form.status = 'published'
  await submitForm()
}

const submitForm = async () => {
  if (!validateForm()) return
  
  saving.value = true
  
  try {
    const formData = new FormData()
    
    // Append form fields
    Object.keys(form).forEach(key => {
      if (key === 'learning_outcomes' || key === 'requirements') {
        formData.append(key, JSON.stringify(form[key]))
      } else if (key === 'image' && form[key]) {
        formData.append('image', form[key])
      } else if (key !== 'image') {
        formData.append(key, form[key])
      }
    })
    
    await courseStore.updateCourse(route.params.slug, formData)
    router.push(`/courses/${course.value.slug}`)
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error('Failed to update course. Please try again.')
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  loading.value = true
  
  try {
    // Fetch course data and categories
    const [courseData, categoriesData] = await Promise.all([
      courseStore.fetchCourseBySlug(route.params.slug),
      courseStore.fetchCategories()
    ])
    
    course.value = courseData
    categories.value = categoriesData
    populateForm()
  } catch (error) {
    console.error('Error fetching course data:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when course doesn't exist)
    if (error.response?.status >= 400) {
      toast.error('Failed to load course data')
    }
  } finally {
    loading.value = false
  }
})
</script> 