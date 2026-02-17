<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100">
          <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/register" class="font-medium text-primary-600 hover:text-primary-500">
            create a new account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4">
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
                autocomplete="current-password"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                :class="{ 'border-red-500': errors.password }"
                placeholder="Enter your password"
              />
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              v-model="form.remember"
              id="remember"
              name="remember"
              type="checkbox"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <router-link to="/forgot-password" class="font-medium text-primary-600 hover:text-primary-500">
              Forgot your password?
            </router-link>
          </div>
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
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </div>

        <div class="flex items-center justify-center">
          <div class="text-sm">
            <span class="text-gray-600">Don't have an account?</span>
            <router-link to="/register" class="font-medium text-primary-600 hover:text-primary-500 ml-1">
              Sign up
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

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const form = reactive({
  email: '',
  password: '',
  remember: false
})
const errors = reactive({})

const validateForm = () => {
  errors.value = {}
  
  if (!form.email) {
    errors.email = 'Email is required'
    return false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    return false
  }
  
  if (!form.password) {
    errors.password = 'Password is required'
    return false
  }
  
  if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters'
    return false
  }
  
  return true
}



const handleLogin = async () => {
  if (!validateForm()) return
  
  loading.value = true
  errors.value = {}
  
  try {
    console.log('Attempting login with:', { email: form.email, password: form.password })
    
    const result = await authStore.login({
      email: form.email,
      password: form.password,
      remember: form.remember
    })
    
    console.log('Login result:', result)
    console.log('User role:', authStore.userRole)
    console.log('Is instructor:', authStore.isInstructor)
    console.log('Is student:', authStore.isStudent)
    
    // Use Vue Router for navigation instead of window.location
    console.log('Login successful, redirecting...')
    console.log('User role:', authStore.userRole)
    console.log('Is instructor:', authStore.isInstructor)
    console.log('Is student:', authStore.isStudent)
    console.log('User object:', authStore.user)
    
    // Remove the alert that blocks navigation
    // alert('Login successful! Redirecting...')
    
    // Use Vue Router navigation with nextTick to ensure state is updated
    await nextTick()
    console.log('After nextTick - User role:', authStore.userRole)
    console.log('After nextTick - Is instructor:', authStore.isInstructor)
    console.log('After nextTick - Is student:', authStore.isStudent)
    
    if (authStore.isAdmin) {
      console.log('Redirecting to admin dashboard')
      router.push('/admin')
    } else if (authStore.isInstructor) {
      console.log('Redirecting to instructor dashboard')
      router.push('/instructor')
    } else if (authStore.isStudent) {
      console.log('Redirecting to student dashboard')
      router.push('/student-dashboard')
    } else {
      console.log('Redirecting to general dashboard')
      router.push('/dashboard')
    }
    
  } catch (error) {
    console.error('Login error:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Login failed. Please check your credentials.')
    }
  } finally {
    loading.value = false
  }
}
</script> 