<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100">
          <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
            sign in to your existing account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Full Name
            </label>
            <div class="mt-1">
              <input
                id="name"
                v-model="form.name"
                name="name"
                type="text"
                autocomplete="name"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter your full name"
              />
            </div>
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                name="email"
                type="email"
                autocomplete="email"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                :class="{ 'border-red-500': errors.email }"
                placeholder="Enter your email address"
              />
            </div>
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <PhoneInput
                v-model="form.phone"
            label="Phone Number (Optional)"
                placeholder="Enter your phone number"
            :error="errors.phone"
            help-text="Select your country and enter your phone number"
            :show-formatted="true"
            @change="handlePhoneChange"
              />

          <div>
            <label for="role" class="block text-sm font-medium text-gray-700">
              I want to
            </label>
            <div class="mt-1">
              <select
                id="role"
                v-model="form.role"
                name="role"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                :class="{ 'border-red-500': errors.role }"
              >
                <option value="">Select your role</option>
                <option value="student">Learn courses as a student</option>
                <option value="instructor">Create and teach courses</option>
              </select>
            </div>
            <p v-if="errors.role" class="mt-2 text-sm text-red-600">{{ errors.role }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password
            </label>
            <div class="mt-1">
              <input
                id="password"
                v-model="form.password"
                name="password"
                type="password"
                autocomplete="new-password"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                :class="{ 'border-red-500': errors.password }"
                placeholder="Create a password"
              />
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm Password
            </label>
            <div class="mt-1">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                :class="{ 'border-red-500': errors.password_confirmation }"
                placeholder="Confirm your password"
              />
            </div>
            <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ errors.password_confirmation }}</p>
          </div>
        </div>

        <div class="flex items-center">
          <input
            v-model="form.agree_to_terms"
            id="agree_to_terms"
            name="agree_to_terms"
            type="checkbox"
            required
            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
          />
          <label for="agree_to_terms" class="ml-2 block text-sm text-gray-900">
            I agree to the
            <a href="#" class="text-primary-600 hover:text-primary-500">Terms of Service</a>
            and
            <a href="#" class="text-primary-600 hover:text-primary-500">Privacy Policy</a>
          </label>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Creating account...' : 'Create account' }}
          </button>
        </div>

        <div class="flex items-center justify-center">
          <div class="text-sm">
            <span class="text-gray-600">Already have an account?</span>
            <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500 ml-1">
              Sign in
            </router-link>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import PhoneInput from '@/components/PhoneInput.vue'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const form = reactive({
  name: '',
  email: '',
  phone: { countryCode: '', phoneNumber: '', dialCode: '', formatted: '' },
  role: '',
  password: '',
  password_confirmation: '',
  agree_to_terms: false
})
const errors = reactive({})

const validateForm = () => {
  errors.value = {}
  
  if (!form.name) {
    errors.name = 'Full name is required'
    return false
  }
  
  if (!form.email) {
    errors.email = 'Email is required'
    return false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    return false
  }
  
  if (form.phone.phoneNumber) {
    const phoneRegex = /^[0-9]{7,15}$/
    if (!phoneRegex.test(form.phone.phoneNumber)) {
      errors.phone = 'Please enter a valid phone number (7-15 digits)'
      return false
    }
  }
  
  if (!form.role) {
    errors.role = 'Please select your role'
    return false
  }
  
  if (!form.password) {
    errors.password = 'Password is required'
    return false
  }
  
  if (form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
    return false
  }
  
  if (!form.password_confirmation) {
    errors.password_confirmation = 'Please confirm your password'
    return false
  }
  
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match'
    return false
  }
  
  if (!form.agree_to_terms) {
    toast.error('Please agree to the Terms of Service and Privacy Policy')
    return false
  }
  
  return true
}

const handlePhoneChange = (phoneData) => {
  form.phone = phoneData
}

const handleRegister = async () => {
  if (!validateForm()) return
  
  loading.value = true
  errors.value = {}
  
  try {
    console.log('Registration data being sent:', {
      name: form.name,
      email: form.email,
      phone: form.phone.formatted || null,
      role: form.role,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    
    const result = await authStore.register({
      name: form.name,
      email: form.email,
      phone: form.phone.formatted || null,
      role: form.role,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    
    console.log('Registration result:', result)
    
    // Check if email verification is required
    if (result.email_verified === false) {
      // Redirect to email verification page
      router.push({
        name: 'EmailVerification',
        query: { email: form.email }
      })
    } else {
      // If somehow email is already verified, redirect to appropriate dashboard
      if (result.user.roles && result.user.roles.includes('instructor')) {
      router.push('/instructor')
      } else if (result.user.roles && result.user.roles.includes('student')) {
      router.push('/student-dashboard')
    } else {
      router.push('/dashboard')
      }
    }
  } catch (error) {
    console.error('Registration error details:', error)
    console.error('Error response:', error.response)
    console.error('Error message:', error.message)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      console.log('Validation errors:', error.response.data.errors)
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
      console.log('API error message:', error.response.data.message)
    } else if (error.code === 'ERR_NETWORK') {
      toast.error('Network error. Please check if the server is running.')
      console.log('Network error - server might be down')
    } else {
      toast.error('Registration failed. Please try again.')
      console.log('Unknown error:', error)
    }
  } finally {
    loading.value = false
  }
}
</script> 