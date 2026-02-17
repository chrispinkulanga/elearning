import axios from 'axios'
import appConfig from '@/config/app.config'

const api = axios.create({
  baseURL: appConfig.apiUrl,
  timeout: 30000, // Increased to 30 seconds for slow email sending
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    console.log('API Request:', {
      method: config.method?.toUpperCase(),
      url: config.url,
      baseURL: config.baseURL,
      fullURL: `${config.baseURL}${config.url}`,
      data: config.data
    })
    
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Don't set Content-Type for FormData - let browser set it with boundary
    if (config.data instanceof FormData) {
      delete config.headers['Content-Type']
    }
    
    return config
  },
  (error) => {
    console.error('API Request Error:', error)
    return Promise.reject(error)
  }
)

// Enhanced response interceptor
api.interceptors.response.use(
  (response) => {
    console.log('API Response Success:', {
      status: response.status,
      url: response.config.url,
      data: response.data
    })
    return response
  },
  (error) => {
    console.error('API Response Error:', {
      message: error.message,
      code: error.code,
      status: error.response?.status,
      statusText: error.response?.statusText,
      url: error.config?.url,
      data: error.response?.data
    })
    // Check if offline
    if (!navigator.onLine) {
      console.error('You are currently offline. Please check your connection.')
      return Promise.reject(new Error('Offline'))
    }

    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 401:
          if (!error.config.url.includes('/auth/')) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            window.location.href = '/login'
          }
          break
        case 403:
          console.error(data.message || 'You do not have permission to perform this action')
          break
        case 404:
          console.error(data.message || 'Resource not found')
          break
        case 422:
          // Validation errors - don't show toast, let component handle
          break
        case 429:
          console.error('Too many requests. Please slow down.')
          break
        case 500:
          console.error('Server error. Please try again later.')
          break
        default:
          console.error(data.message || 'An error occurred. Please try again.')
      }
    } else if (error.request) {
      // Network error
      console.error('Network error. Please check your connection.')
    }
    
    return Promise.reject(error)
  }
)

// Add retry logic for failed requests
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const { config, response } = error
    
    if (response?.status >= 500 && config && !config._retry) {
      config._retry = true
      
      // Wait 1 second before retrying
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      return api(config)
    }
    
    return Promise.reject(error)
  }
)

// Authentication API
export const authAPI = {
  register: (data) => api.post('/register', data),
  login: (data) => api.post('/login', data),
  logout: () => api.post('/auth/logout'),
  user: () => api.get('/auth/user'),
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (token, password, password_confirmation) => 
    api.post('/auth/reset-password', { token, password, password_confirmation }),
  verifyEmail: (token) => api.post('/auth/verify-email', { token }),
  
  // OTP-based password reset methods
  sendOTP: (email) => api.post('/auth/send-otp', { email }),
  verifyOTP: (email, otp) => api.post('/auth/verify-otp', { email, otp }),
  resetPasswordWithOTP: (email, otp, password, password_confirmation) => 
    api.post('/auth/reset-password-otp', { email, otp, password, password_confirmation }),
}

// Course API
export const courseAPI = {
  getAll: (params = {}) => api.get('/courses', { params }),
  getMyCourses: () => api.get('/courses/my-courses'),
  getFeatured: (params = {}) => api.get('/courses/featured', { params }),
  getByCategory: (categoryId, params = {}) => api.get(`/courses/category/${categoryId}`, { params }),
  getById: (id) => api.get(`/courses/${id}`),
  getReviews: (id) => api.get(`/courses/${id}/reviews`),
  create: (data) => {
    // If data is FormData, remove Content-Type header to let axios handle it automatically
    if (data instanceof FormData) {
      return api.post('/courses', data, {
        headers: {
          'Content-Type': undefined, // Let axios set the boundary automatically
        }
      })
    }
    return api.post('/courses', data)
  },
  update: (id, data) => api.put(`/courses/${id}`, data),
  delete: (id) => api.delete(`/courses/${id}`),
  enroll: (id) => api.post(`/courses/${id}/enroll`),
  unenroll: (id) => api.delete(`/courses/${id}/enroll`),
  getLessons: (id) => api.get(`/courses/${id}/lessons`),
  getProgress: (id) => api.get(`/courses/${id}/progress`),
  updateProgress: (id, data) => api.put(`/courses/${id}/progress`, data),
  categories: () => api.get('/categories'),
  search: (query, params = {}) => api.get('/courses/search', { params: { q: query, ...params } }),
  
  // Course Comments
  getComments: (courseId, params = {}) => api.get(`/courses/${courseId}/comments`, { params }),
  createComment: (courseId, data) => api.post(`/courses/${courseId}/comments`, data),
  updateComment: (courseId, commentId, data) => api.put(`/courses/${courseId}/comments/${commentId}`, data),
  deleteComment: (courseId, commentId) => api.delete(`/courses/${courseId}/comments/${commentId}`),
  getCommentStats: (courseId) => api.get(`/courses/${courseId}/comments/stats`),
  
  // Course Reviews
  getReviews: (courseId, params = {}) => api.get(`/courses/${courseId}/reviews`, { params }),
  createReview: (courseId, data) => api.post(`/courses/${courseId}/reviews`, data),
  updateReview: (courseId, reviewId, data) => api.put(`/courses/${courseId}/reviews/${reviewId}`, data),
  deleteReview: (courseId, reviewId) => api.delete(`/courses/${courseId}/reviews/${reviewId}`),
}

// Forum API
export const forumAPI = {
  // Legacy forum endpoints (course-specific)
  getTopics: (params = {}) => api.get('/forum/topics', { params }),
  getTopic: (id) => api.get(`/forum/topics/${id}`),
  createTopic: (data) => api.post('/forum/topics', data),
  updateTopic: (id, data) => api.put(`/forum/topics/${id}`, data),
  deleteTopic: (id) => api.delete(`/forum/topics/${id}`),
  getReplies: (topicId, params = {}) => api.get(`/forum/topics/${topicId}/replies`, { params }),
  createReply: (topicId, data) => api.post(`/forum/topics/${topicId}/replies`, data),
  updateReply: (replyId, data) => api.put(`/forum/replies/${replyId}`, data),
  deleteReply: (replyId) => api.delete(`/forum/replies/${replyId}`),
  likeTopic: (id) => api.post(`/forum/topics/${id}/like`),
  unlikeTopic: (id) => api.delete(`/forum/topics/${id}/like`),
  searchTopics: (query, params = {}) => api.get('/forum/search', { params: { q: query, ...params } }),
  
  // Standalone forum endpoints (general discussion)
  getAllForums: () => api.get('/forums'),
  getAllTopics: (params = {}) => api.get('/forums/topics', { params }),
  getStandaloneTopic: (id) => api.get(`/forums/topics/${id}`),
  createStandaloneTopic: (data) => api.post('/forums/topics', data),
  updateStandaloneTopic: (id, data) => api.put(`/forums/topics/${id}`, data),
  deleteStandaloneTopic: (id) => api.delete(`/forums/topics/${id}`),
  createStandaloneReply: (topicId, data) => api.post(`/forums/topics/${topicId}/replies`, data),
  likeStandaloneTopic: (id) => api.post(`/forums/topics/${id}/like`),
  likeStandaloneReply: (replyId) => api.post(`/forums/replies/${replyId}/like`),
  pinStandaloneTopic: (id) => api.put(`/forums/topics/${id}/pin`),
  lockStandaloneTopic: (id) => api.put(`/forums/topics/${id}/lock`),
  createPoll: (topicId, data) => api.post(`/forums/topics/${topicId}/polls`, data),
  votePoll: (topicId, pollId, data) => api.post(`/forums/topics/${topicId}/polls/${pollId}/vote`, data),
  getPollResults: (topicId, pollId) => api.get(`/forums/topics/${topicId}/polls/${pollId}/results`),
  uploadAttachment: (topicId, formData) => api.post(`/forums/topics/${topicId}/attachments`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  deleteAttachment: (topicId, attachmentId) => api.delete(`/forums/topics/${topicId}/attachments/${attachmentId}`),
  getAttachmentInfo: () => api.get('/forums/attachments/info'),
  searchAllTopics: (query, params = {}) => api.get('/forums/search', { params: { q: query, ...params } }),
}

// Forum Category API
export const forumCategoryAPI = {
  getAll: () => api.get('/forum-categories'),
  getById: (id) => api.get(`/forum-categories/${id}`),
  create: (data) => api.post('/forum-categories', data),
  update: (id, data) => api.put(`/forum-categories/${id}`, data),
  delete: (id) => api.delete(`/forum-categories/${id}`),
}

// Category API
export const categoryAPI = {
  getAll: () => api.get('/categories'),
  getById: (id) => api.get(`/categories/${id}`),
  getCourses: (id) => api.get(`/categories/${id}/courses`),
  create: (categoryData) => api.post('/categories', categoryData),
}

// User API
export const userAPI = {
  getProfile: () => api.get('/profile'),
  updateProfile: (data) => api.put('/profile', data),
  changePassword: (data) => api.put('/profile/password', data),
  updateSettings: (data) => api.put('/user/settings', data),
  deleteAccount: () => api.delete('/user/account'),
  uploadAvatar: (formData) => api.post('/profile/avatar', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),
  // Fixed path: backend exposes GET /my-enrollments for authenticated user's enrollments
  getEnrollments: () => api.get('/my-enrollments'),
  getCertificates: () => api.get('/my-certificates'),
  downloadCertificate: (id) => api.get(`/certificates/${id}/download`, { responseType: 'blob' }),
}

// Enrollment API
export const enrollmentAPI = {
  enroll: (courseId) => api.post('/enrollments', { course_id: courseId }),
  unenroll: (enrollmentId) => api.delete(`/enrollments/${enrollmentId}`),
  getProgress: (enrollmentId) => api.get(`/enrollments/${enrollmentId}/progress`),
  updateProgress: (enrollmentId, data) => api.put(`/enrollments/${enrollmentId}/progress`, data),
  markLessonComplete: (enrollmentId, lessonId) => api.post(`/enrollments/${enrollmentId}/lessons/${lessonId}/complete`),
  markLessonIncomplete: (enrollmentId, lessonId) => api.delete(`/enrollments/${enrollmentId}/lessons/${lessonId}/complete`),
}

// Payment API
export const paymentAPI = {
  createPayment: (data) => api.post('/payments', data),
  processPayment: (paymentId) => api.post(`/payments/${paymentId}/process`),
  getPaymentHistory: () => api.get('/payments'),
  getPaymentDetails: (paymentId) => api.get(`/payments/${paymentId}`),
  refundPayment: (paymentId) => api.post(`/payments/${paymentId}/refund`),
  verifyFlutterwave: (data) => api.post('/payments/flutterwave/verify', data),
}

// Dashboard API
export const dashboardAPI = {
  // Align with backend: student dashboard is /dashboard/student (single payload)
  getStudentStats: () => api.get('/dashboard/student'),
  getStudentCourses: () => api.get('/my-enrollments'),
  getStudentActivity: () => api.get('/activity'),
  getStudentCertificates: () => api.get('/user/certificates'),
  
  getInstructorStats: () => api.get('/dashboard/instructor/stats'),
  getInstructorCourses: () => api.get('/dashboard/instructor/courses'),
  getInstructorEnrollments: () => api.get('/dashboard/instructor/enrollments'),
  getInstructorRevenue: () => api.get('/dashboard/instructor/revenue'),
}

// Instructor API
export const instructorAPI = {
  getDashboardStats: () => api.get('/dashboard/instructor'),
  getRecentEnrollments: () => api.get('/instructor/students'),
  getStudents: (params = {}) => api.get('/instructor/students', { params }),
  getAnalytics: (params = {}) => api.get('/instructor/analytics', { params }),
  getCourseAnalytics: (courseId) => api.get(`/instructor/courses/${courseId}/analytics`),
  getStudentProgress: (courseId) => api.get(`/instructor/courses/${courseId}/students`),
  updateCourseStatus: (courseId, status) => api.patch(`/instructor/courses/${courseId}/status`, { status }),
  getRevenue: (params = {}) => api.get('/instructor/earnings', { params }),
}

// Student API
export const studentAPI = {
  // Corrected paths to existing backend routes
  getDashboardStats: () => api.get('/dashboard/student'),
  getEnrolledCourses: () => api.get('/my-enrollments'),
  // No dedicated activity endpoint found; keep a harmless placeholder
  getRecentActivity: () => Promise.resolve({ data: [] }),
  // Match backend: /my-certificates and /certificates/{id}/download
  getCertificates: () => api.get('/my-certificates'),
  downloadCertificate: (certificateId) => api.get(`/certificates/${certificateId}/download`, { responseType: 'blob' }),
  getLearningPath: () => api.get('/student/learning-path'),
  getAchievements: () => api.get('/student/achievements'),
}

// Admin API (for future use)
export const adminAPI = {
  getDashboardStats: () => api.get('/dashboard/admin'),
  getAllUsers: (params = {}) => api.get('/admin/users', { params }),
  updateUserRole: (userId, role) => api.patch(`/admin/users/${userId}/role`, { role }),
  getAllCourses: (params = {}) => api.get('/admin/courses/all', { params }),
  getPendingCourses: () => api.get('/admin/courses/pending'),
  approveCourse: (courseId) => api.put(`/admin/courses/${courseId}/approve`),
  rejectCourse: (courseId, reason) => api.put(`/admin/courses/${courseId}/reject`, { reason }),
  bulkApproveCourses: (data) => api.post('/admin/courses/approve', data),
  getCategories: () => api.get('/admin/categories'),
  createCategory: (data) => api.post('/admin/categories', data),
  updateCategory: (categoryId, data) => api.put(`/admin/categories/${categoryId}`, data),
  deleteCategory: (categoryId) => api.delete(`/admin/categories/${categoryId}`),
  
  // Analytics API endpoints
  getOverviewReport: () => api.get('/admin/reports/overview'),
  getCourseReport: () => api.get('/admin/reports/courses'),
  getRevenueReport: (days = 30) => api.get(`/admin/reports/revenue?days=${days}`),
  getSalesReport: (days = 30) => api.get(`/admin/reports/sales?days=${days}`),
  getEngagementReport: () => api.get('/admin/reports/engagement'),
  getUserReport: () => api.get('/admin/reports/users'),
  
  // Alumni management
  getAlumni: (params = {}) => api.get('/admin/alumni', { params }),
  createAlumni: (data) => api.post('/admin/alumni', data),
  updateAlumni: (alumniId, data) => api.put(`/admin/alumni/${alumniId}`, data),
  deleteAlumni: (alumniId) => api.delete(`/admin/alumni/${alumniId}`),
  inviteToAlumni: (data) => api.post('/admin/alumni/invite', data),
  getEligibleUsers: (params = {}) => api.get('/admin/alumni/eligible-users', { params }),
}

// Search API
export const searchAPI = {
  searchCourses: (query, params = {}) => api.get('/search/courses', { params: { q: query, ...params } }),
  searchUsers: (query, params = {}) => api.get('/search/users', { params: { q: query, ...params } }),
  searchTopics: (query, params = {}) => api.get('/search/topics', { params: { q: query, ...params } }),
}

// Notification API
export const notificationAPI = {
  getAll: (params = {}) => api.get('/notifications', { params }),
  markAsRead: (notificationId) => api.put(`/notifications/${notificationId}/read`),
  markAllAsRead: () => api.put('/notifications/mark-all-read'),
  deleteNotification: (notificationId) => api.delete(`/notifications/${notificationId}`),
  getUnreadCount: () => api.get('/notifications/unread-count'),
}

// Alumni API (for all users)
export const alumniAPI = {
  acceptInvitation: (alumniId) => api.post(`/alumni/${alumniId}/accept`),
}

// Profile API
export const profileAPI = {
  getProfile: () => api.get('/profile'),
  updateProfile: (data) => api.put('/profile', data),
  getDocuments: () => api.get('/profile/documents'),
  uploadDocument: (formData) => api.post('/profile/documents', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  deleteDocument: (documentId) => api.delete(`/profile/documents/${documentId}`),
  addSkill: (data) => api.post('/profile/skills', data),
  deleteSkill: (skillId) => api.delete(`/profile/skills/${skillId}`),
  addExperience: (data) => api.post('/profile/experiences', data),
  updateExperience: (experienceId, data) => api.put(`/profile/experiences/${experienceId}`, data),
  deleteExperience: (experienceId) => api.delete(`/profile/experiences/${experienceId}`),
  addEducation: (data) => api.post('/profile/educations', data),
  updateEducation: (educationId, data) => api.put(`/profile/educations/${educationId}`, data),
  deleteEducation: (educationId) => api.delete(`/profile/educations/${educationId}`),
  getPublicProfile: (userId) => api.get(`/profiles/${userId}`),
}

// File Upload API
export const uploadAPI = {
  uploadImage: (file, type = 'course') => {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)
    return api.post('/upload/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },
  uploadVideo: (file, courseId) => {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('course_id', courseId)
    return api.post('/upload/video', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },
  uploadDocument: (file, type = 'lesson') => {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)
    return api.post('/upload/document', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },
}

export default api 
export { api }

export { courseBuilderAPI } from './courseBuilderAPI' 