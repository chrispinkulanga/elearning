import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { courseAPI, api } from '@/services/api'
import { useToast } from '@/composables/useToast'
import { categoryAPI } from '@/services/api'
import { useAuthStore } from './auth'

export const useCourseStore = defineStore('courses', () => {
  const toast = useToast()
  
  // State
  const courses = ref([])
  const myCourses = ref([])
  const featuredCourses = ref([])
  const categories = ref([])
  const loading = ref(false)
  const currentCourse = ref(null)
  const searchQuery = ref('')
  const filters = ref({
    category: '',
    level: '',
    price: '',
    status: ''
  })
  const pagination = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
    last_page: 1
  })

  // Getters
  const getCourseById = computed(() => (id) => {
    return courses.value.find(course => course.id === id)
  })

  const getCourseBySlug = computed(() => (slug) => {
    return courses.value.find(course => course.slug === slug)
  })
  
  const filteredCourses = computed(() => {
    let filtered = courses.value

    // Search filter
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(course =>
        course.title.toLowerCase().includes(query) ||
        course.description.toLowerCase().includes(query) ||
        course.instructor?.name.toLowerCase().includes(query)
      )
    }

    // Category filter
    if (filters.value.category) {
      filtered = filtered.filter(course => course.category_id == filters.value.category)
    }

    // Level filter
    if (filters.value.level) {
      filtered = filtered.filter(course => course.level === filters.value.level)
    }

    // Price filter
    if (filters.value.price) {
      switch (filters.value.price) {
        case 'free':
          filtered = filtered.filter(course => course.is_free)
          break
        case 'paid':
          filtered = filtered.filter(course => !course.is_free)
          break
        case 'under_50':
          filtered = filtered.filter(course => !course.is_free && course.price < 50)
          break
        case 'under_100':
          filtered = filtered.filter(course => !course.is_free && course.price < 100)
          break
      }
    }

    // Status filter
    if (filters.value.status) {
      filtered = filtered.filter(course => course.status === filters.value.status)
    }

    return filtered
  })

  // Actions
  const fetchCourses = async (params = {}) => {
    loading.value = true
    try {
      const response = await courseAPI.getAll({
        page: pagination.value.current_page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
        ...filters.value,
        ...params
      })

      if (response.data) {
      courses.value = response.data.data || response.data
        pagination.value = {
          current_page: response.data.pagination?.current_page || 1,
          per_page: response.data.pagination?.per_page || 12,
          total: response.data.pagination?.total || 0,
          last_page: response.data.pagination?.last_page || 1
        }
      }

      return response.data || response
    } catch (error) {
      console.error('Error fetching courses:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const fetchMyCourses = async () => {
    loading.value = true
    try {
      // Check user role to determine correct endpoint
      const authStore = useAuthStore()
      
      let response
      if (authStore.isInstructor) {
        // For instructors, get their created courses
        response = await api.get('/instructor/courses')
      } else {
        // For students, get their enrolled courses
        response = await api.get('/my-enrollments')
      }
      
      const payload = response.data || response
      
      if (payload?.data?.data) {
        myCourses.value = payload.data.data
      } else if (Array.isArray(payload?.data)) {
        myCourses.value = payload.data
      } else if (Array.isArray(payload)) {
        myCourses.value = payload
      } else {
        myCourses.value = []
      }
      return payload
    } catch (error) {
      console.error('Error fetching my courses:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const fetchFeaturedCourses = async () => {
    try {
      const response = await courseAPI.getFeatured()
      if (response.data) {
        featuredCourses.value = response.data.data || response.data
      }
      return response.data || response
    } catch (error) {
      console.error('Error fetching featured courses:', error)
      throw error
    }
  }

  const fetchCategories = async () => {
    try {
      console.log('CourseStore: fetchCategories called')
      console.log('CourseStore: Making API call to courseAPI.categories()...')
      
      const response = await categoryAPI.getAll()
      console.log('CourseStore: API response received:', response)
      
      if (response.data) {
        console.log('CourseStore: Response has data, updating categories')
        console.log('CourseStore: Response data:', response.data)
        console.log('CourseStore: Response data.data:', response.data.data)
        
        categories.value = response.data.data || response.data
        console.log('CourseStore: Categories updated, new value:', categories.value)
        console.log('CourseStore: Categories length:', categories.value?.length)
      } else {
        console.log('CourseStore: Response has no data, response:', response)
      }
      
      return response.data || response
    } catch (error) {
      console.error('CourseStore: Error in fetchCategories:', error)
      console.error('CourseStore: Error response:', error.response)
      console.error('CourseStore: Error status:', error.response?.status)
      console.error('CourseStore: Error data:', error.response?.data)
      throw error
    }
  }

  const fetchCourse = async (identifier) => {
    loading.value = true
    try {
      const response = await courseAPI.getById(identifier)
      if (response.data) {
        const payload = response.data
        const nestedCourse = payload?.data?.course
        const course = nestedCourse || payload.data || payload
        currentCourse.value = course
        
        // Update in courses array if exists
        const existingIndex = courses.value.findIndex(c => c.id === course.id)
        if (existingIndex !== -1) {
          courses.value[existingIndex] = course
        } else {
          courses.value.push(course)
        }
        
        return course
      }
      return response.data || response
    } catch (error) {
      console.error('Error fetching course:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  // Fetch by slug (backend accepts slug on /courses/{slug})
  const fetchCourseBySlug = async (slug) => {
    loading.value = true
    try {
      const response = await courseAPI.getById(slug)
      const payload = response.data || response
      const course = payload?.data?.course || payload.data || payload
      const isEnrolled = payload?.data?.is_enrolled || false
      
      console.log('CourseStore: fetchCourseBySlug response:', {
        payload,
        course,
        isEnrolled,
        courseId: course?.id
      })
      
      if (course) {
        // Add enrollment status to course object
        course.is_enrolled = isEnrolled
        console.log('CourseStore: Set course.is_enrolled to:', isEnrolled)
        currentCourse.value = course
        // sync into courses array for quick lookup
        const idx = courses.value.findIndex(c => c.id === course.id)
        if (idx !== -1) {
          courses.value[idx] = course
        } else {
          courses.value.push(course)
        }
      }
      return course
    } catch (error) {
      console.error('Error fetching course by slug:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  // Course actions
  const createCourse = async (courseData) => {
    try {
      loading.value = true
      const response = await courseAPI.create(courseData)
      console.log('Course created:', response)
      
      // Add to courses array only if status is 'approved'
      if (response.data?.data?.status === 'approved') {
        courses.value.unshift(response.data.data)
      }
      
      // Always add to myCourses for instructors
      if (response.data?.data) {
        myCourses.value.unshift(response.data.data)
      }
      
      return response.data
    } catch (error) {
      console.error('Error creating course:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  // Category actions
  const createCategory = async (categoryData) => {
    try {
      loading.value = true
      const response = await categoryAPI.create(categoryData)
      console.log('Category created:', response)
      
      // Try to refresh categories list, but don't fail if it errors
      try {
        await fetchCategories()
      } catch (refreshError) {
        console.warn('Failed to refresh categories list:', refreshError)
        // Don't throw this error - category was created successfully
      }
      
      return response.data
    } catch (error) {
      console.error('Error creating category:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const updateCourse = async (courseId, courseData) => {
    loading.value = true
    try {
      const response = await courseAPI.update(courseId, courseData)
      const updatedCourse = response.data?.data || response.data || response
      
      // Update in arrays
      const updateInArray = (array) => {
        const index = array.findIndex(c => c.id === courseId)
      if (index !== -1) {
          array[index] = updatedCourse
        }
      }
      
      updateInArray(courses.value)
      updateInArray(myCourses.value)
      
      if (currentCourse.value?.id === courseId) {
        currentCourse.value = updatedCourse
      }
      
      toast.success('Course updated successfully!')
      return updatedCourse
    } catch (error) {
      console.error('Error updating course:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const deleteCourse = async (courseId) => {
    try {
      await courseAPI.delete(courseId)
      
      // Remove from arrays
      courses.value = courses.value.filter(c => c.id !== courseId)
      myCourses.value = myCourses.value.filter(c => c.id !== courseId)
      
      if (currentCourse.value?.id === courseId) {
        currentCourse.value = null
      }
      
      toast.success('Course deleted successfully!')
    } catch (error) {
      console.error('Error deleting course:', error)
      throw error
    }
  }

  const enrollInCourse = async (courseId) => {
    try {
      console.log('CourseStore: Attempting to enroll in course ID:', courseId)
      const response = await courseAPI.enroll(courseId)
      console.log('CourseStore: Enrollment response:', response)
      toast.success('Successfully enrolled in course!')
      return response.data || response
    } catch (error) {
      console.error('CourseStore: Error enrolling in course:', error)
      console.error('CourseStore: Error response:', error.response)
      console.error('CourseStore: Error data:', error.response?.data)
      
      // Don't show error toast for already enrolled case - let the component handle it
      if (error.response?.data?.message !== 'Already enrolled in this course') {
        toast.error('Failed to enroll in course')
      }
      
      throw error
    }
  }

  const fetchCourseReviews = async (courseId) => {
    try {
      const response = await courseAPI.getReviews(courseId)
      return response.data?.data || response.data || []
    } catch (error) {
      console.error('Fetch course reviews error:', error)
      return []
    }
  }

  const updateFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.current_page = 1 // Reset to first page
  }

  const updateSearchQuery = (query) => {
    searchQuery.value = query
    pagination.value.current_page = 1 // Reset to first page
  }

  const setPage = (page) => {
    pagination.value.current_page = page
  }

  const clearFilters = () => {
    filters.value = {
      category: '',
      level: '',
      price: '',
      status: ''
    }
    searchQuery.value = ''
    pagination.value.current_page = 1
  }

  // Debug methods
  const testBackendConnection = async () => {
    try {
      const response = await courseAPI.testConnection()
      console.log('Backend connection test successful:', response)
      return response
    } catch (error) {
      console.error('Backend connection test failed:', error)
      throw error
    }
  }

  const testArrayProcessing = async (data) => {
    try {
      const response = await courseAPI.testArrayProcessing(data)
      console.log('Array processing test successful:', response)
      return response
    } catch (error) {
      console.error('Array processing test failed:', error)
      throw error
    }
  }

  return {
    // State
    courses,
    myCourses,
    featuredCourses,
    categories,
    loading,
    currentCourse,
    searchQuery,
    filters,
    pagination,
    
    // Getters
    getCourseById,
    getCourseBySlug,
    filteredCourses,
    
    // Actions
    fetchCourses,
    fetchMyCourses,
    fetchFeaturedCourses,
    fetchCategories,
    fetchCourse,
    fetchCourseBySlug,
    createCourse,
    createCategory,
    updateCourse,
    deleteCourse,
    enrollInCourse,
    fetchCourseReviews,
    updateFilters,
    updateSearchQuery,
    setPage,
    clearFilters,
    
    // Debug methods
    testBackendConnection,
    testArrayProcessing
  }
}) 