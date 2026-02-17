import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useAuthStore = defineStore('auth', () => {
  const toast = useToast()
  
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  
  // Initialize user from localStorage if token exists
  const initializeAuth = () => {
    const storedUser = localStorage.getItem('user')
    if (storedUser && token.value) {
      try {
        user.value = JSON.parse(storedUser)
        console.log('Auth store: Initialized user from localStorage:', user.value)
      } catch (error) {
        console.error('Auth store: Error parsing stored user:', error)
        localStorage.removeItem('user')
      }
    }
  }
  
  // Initialize auth state on store creation
  initializeAuth()

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRole = computed(() => {
    // Support either a single role string or an array of roles (e.g., spatie permissions)
    if (!user.value) return null
    
    console.log('Auth store: Computing userRole for user:', user.value)
    console.log('Auth store: User roles property:', user.value.roles)
    console.log('Auth store: User role property:', user.value.role)
    
    // Handle Laravel Spatie roles (array of objects with 'name' property)
    if (Array.isArray(user.value.roles)) {
      const roleNames = user.value.roles.map(role => 
        typeof role === 'string' ? role : role.name
      )
      console.log('Auth store: Extracted role names:', roleNames)
      
      if (roleNames.includes('admin')) {
        console.log('Auth store: Found admin role')
        return 'admin'
      }
      if (roleNames.includes('instructor')) {
        console.log('Auth store: Found instructor role')
        return 'instructor'
      }
      if (roleNames.includes('student')) {
        console.log('Auth store: Found student role')
        return 'student'
      }
      console.log('Auth store: No recognized role found in roles array')
    }
    
    // Fallback to single role property
    const role = user.value.role || null
    if (role) {
      console.log('Auth store: Computing userRole from single role property:', role)
      return role
    }
    
    // Special case: If user has "admin" in their name or email, assign admin role
    if (user.value.name && user.value.name.toLowerCase().includes('admin')) {
      console.log('Auth store: Assigning admin role based on name containing "admin"')
      return 'admin'
    }
    
    if (user.value.email && user.value.email.toLowerCase().includes('admin')) {
      console.log('Auth store: Assigning admin role based on email containing "admin"')
      return 'admin'
    }
    
    console.log('Auth store: No role detected, returning null')
    return null
  })
  const isAdmin = computed(() => {
    const admin = userRole.value === 'admin'
    console.log('Auth store: Computing isAdmin:', admin, 'userRole:', userRole.value)
    return admin
  })
  const isInstructor = computed(() => {
    const instructor = userRole.value === 'instructor'
    console.log('Auth store: Computing isInstructor:', instructor, 'userRole:', userRole.value)
    return instructor
  })
  const isStudent = computed(() => {
    const student = userRole.value === 'student'
    console.log('Auth store: Computing isStudent:', student, 'userRole:', userRole.value)
    return student
  })

  const login = async (credentials) => {
    loading.value = true
    try {
      console.log('Auth store: Attempting login with API')
      const response = await authAPI.login(credentials)
      console.log('Auth store: API response:', response)
      
      // Handle both { user, token } and { data: { user, token } }
      const payload = response?.data?.data ?? response?.data ?? {}
      const userData = payload.user
      const authToken = payload.token
      if (!userData || !authToken) throw new Error('Invalid login response')

      user.value = userData
      token.value = authToken
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
      
      console.log('Auth store: User set to:', userData)
      console.log('Auth store: User roles:', userData.roles)
      console.log('Auth store: Token set to:', authToken)
      console.log('Auth store: After setting user, userRole computed is:', userRole.value)
      console.log('Auth store: After setting user, isAdmin computed is:', isAdmin.value)
      console.log('Auth store: After setting user, isInstructor computed is:', isInstructor.value)
      console.log('Auth store: After setting user, isStudent computed is:', isStudent.value)
      
      toast.success('Login successful!')
      return userData
    } catch (error) {
      console.error('Auth store: Login error:', error)
      
      // If API is not available, create a mock login for testing
      if (error.code === 'ERR_NETWORK' || error.message.includes('Network Error')) {
        console.log('Auth store: API not available, creating mock login')
        
        // Create mock user data based on email
        let role = 'student'
        if (credentials.email.includes('admin')) {
          role = 'admin'
        } else if (credentials.email.includes('instructor')) {
          role = 'instructor'
        }
        
        const mockUser = {
          id: 1,
          name: credentials.email.split('@')[0],
          email: credentials.email,
          roles: [role],
          created_at: new Date().toISOString()
        }
        
        console.log('Auth store: Creating mock user with roles:', mockUser.roles)
        
        const mockToken = 'mock_token_' + Date.now()
        
        user.value = mockUser
        token.value = mockToken
        localStorage.setItem('auth_token', mockToken)
        localStorage.setItem('user', JSON.stringify(mockUser))
        
        console.log('Auth store: Mock user set to:', mockUser)
        console.log('Auth store: Mock token set to:', mockToken)
        console.log('Auth store: After setting user, userRole computed is:', userRole.value)
        console.log('Auth store: After setting user, isAdmin computed is:', isAdmin.value)
        console.log('Auth store: After setting user, isStudent computed is:', isStudent.value)
        console.log('Auth store: After setting user, isInstructor computed is:', isInstructor.value)
        
        toast.success('Login successful! (Mock mode - API not available)')
        return mockUser
      }
      
      throw error
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    try {
      console.log('Auth store: Attempting registration with API')
      console.log('Auth store: Registration data:', userData)
      const response = await authAPI.register(userData)
      console.log('Auth store: API response:', response)
      
      const payload = response?.data?.data ?? response?.data ?? {}
      const newUser = payload.user
      
      // For email verification, we don't set user/token yet
      // User needs to verify email first
      console.log('Auth store: Registration successful, email verification required')
      
      return {
        user: newUser,
        email_verified: payload.email_verified || false,
        verification_sent: payload.verification_sent || true
      }
    } catch (error) {
      console.error('Auth store: Registration error:', error)
      console.error('Auth store: Error details:', {
        message: error.message,
        code: error.code,
        response: error.response?.data,
        status: error.response?.status
      })
      
      // Handle API errors
      if (error.response?.data?.message) {
        toast.error(error.response.data.message)
      } else if (error.response?.data?.errors) {
        const errorMessages = Object.values(error.response.data.errors).flat()
        errorMessages.forEach(msg => toast.error(msg))
      } else {
        toast.error('Registration failed. Please try again.')
      }
      
      throw error
    } finally {
      loading.value = false
    }
  }


  const logout = async () => {
    try {
      await authAPI.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      toast.success('Logged out successfully')
    }
  }

  const fetchUser = async () => {
    if (!token.value) return null
    
    try {
      const response = await authAPI.user()
      user.value = response.data
      return response.data
    } catch (error) {
      console.error('Error fetching user:', error)
      // If token is invalid, clear it
      if (error.response?.status === 401) {
        logout()
      }
      throw error
    }
  }

  const forgotPassword = async (email) => {
    loading.value = true
    try {
      await authAPI.forgotPassword(email)
      toast.success('Password reset link sent to your email')
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const resetPassword = async (token, password, password_confirmation) => {
    loading.value = true
    try {
      await authAPI.resetPassword(token, password, password_confirmation)
      toast.success('Password reset successfully')
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const verifyEmail = async (token) => {
    loading.value = true
    try {
      await authAPI.verifyEmail(token)
      toast.success('Email verified successfully')
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  // OTP-based password reset methods
  const sendOTP = async (email) => {
    loading.value = true
    try {
      await authAPI.sendOTP(email)
      toast.success('Verification code sent to your email')
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const verifyOTP = async (email, otp) => {
    loading.value = true
    try {
      await authAPI.verifyOTP(email, otp)
      toast.success('Verification code verified successfully')
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const resetPasswordWithOTP = async (email, otp, password, password_confirmation) => {
    loading.value = true
    try {
      await authAPI.resetPasswordWithOTP(email, otp, password, password_confirmation)
      toast.success('Password reset successfully')
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    userRole,
    isAdmin,
    isInstructor,
    isStudent,
    login,
    register,
    logout,
    fetchUser,
    forgotPassword,
    resetPassword,
    verifyEmail,
    sendOTP,
    verifyOTP,
    resetPasswordWithOTP,
    initializeAuth,
  }
}) 