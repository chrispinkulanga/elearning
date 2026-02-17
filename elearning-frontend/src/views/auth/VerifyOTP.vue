<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100">
          <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Verify OTP Code
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter the 6-digit code sent to your email
        </p>
        <p v-if="email" class="mt-1 text-center text-sm text-gray-500">
          {{ email }}
        </p>
        <div v-if="timeRemaining > 0" class="mt-2 text-center">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            Code expires in {{ formatTime(timeRemaining) }}
          </span>
        </div>
        <div v-else class="mt-2 text-center">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            Code has expired
          </span>
        </div>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleVerifyOTP">
                 <div class="space-y-4">
           <div>
             <div class="mt-1">
              <input
                id="otp"
                v-model="form.otp"
                name="otp"
                type="text"
                maxlength="6"
                required
                autocomplete="one-time-code"
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm text-center text-2xl tracking-widest"
                :class="{ 'border-red-500': errors.otp }"
                placeholder="000000"
                @input="formatOTP"
                @keypress="validateOTPInput"
              />
            </div>
                         <p v-if="errors.otp" class="mt-2 text-sm text-red-600">{{ errors.otp }}</p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || form.otp.length !== 6"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Verifying...' : 'Verify Code' }}
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm">
            <span class="text-gray-600">Didn't receive the code?</span>
            <button
              type="button"
              @click="resendOTP"
              :disabled="resendLoading || resendCooldown > 0 || timeRemaining > 0"
              class="font-medium text-primary-600 hover:text-primary-500 ml-1 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="resendLoading">Sending...</span>
              <span v-else-if="resendCooldown > 0">Resend in {{ resendCooldown }}s</span>
              <span v-else-if="timeRemaining > 0">Wait for expiry</span>
              <span v-else>Resend Code</span>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-center">
          <div class="text-sm">
            <span class="text-gray-600">Remember your password?</span>
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
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const resendLoading = ref(false)
const resendCooldown = ref(0)
const timeRemaining = ref(60) // 60 seconds
const email = ref('')
const form = reactive({
  otp: ''
})
const errors = reactive({})

// Get email from query parameter and start countdown
onMounted(() => {
  email.value = route.query.email || ''
  if (!email.value) {
    toast.error('Email address is required')
    router.push('/forgot-password')
  } else {
    // Start the countdown timer
    startCountdown()
  }
})

// Timers
let cooldownTimer = null
let countdownTimer = null

// Format time for display
const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

// Start countdown timer
const startCountdown = () => {
  countdownTimer = setInterval(() => {
    timeRemaining.value--
    if (timeRemaining.value <= 0) {
      clearInterval(countdownTimer)
      countdownTimer = null
    }
  }, 1000)
}

onUnmounted(() => {
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
  }
  if (countdownTimer) {
    clearInterval(countdownTimer)
  }
})

const formatOTP = (event) => {
  // Remove any non-digit characters
  let value = event.target.value.replace(/\D/g, '')
  
  // Limit to 6 digits
  if (value.length > 6) {
    value = value.substring(0, 6)
  }
  
  form.otp = value
  event.target.value = value
}

const validateOTPInput = (event) => {
  // Only allow digits
  const char = String.fromCharCode(event.which)
  if (!/[0-9]/.test(char)) {
    event.preventDefault()
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.otp) {
    errors.otp = 'Verification code is required'
    return false
  }
  
  if (form.otp.length !== 6) {
    errors.otp = 'Verification code must be 6 digits'
    return false
  }
  
  if (!/^\d{6}$/.test(form.otp)) {
    errors.otp = 'Verification code must contain only numbers'
    return false
  }
  
  return true
}

const handleVerifyOTP = async () => {
  if (!validateForm()) return
  
  loading.value = true
  errors.value = {}
  
  try {
    console.log('Verifying OTP:', { email: email.value, otp: form.otp })
    
    await authStore.verifyOTP(email.value, form.otp)
    
    console.log('OTP verified successfully')
    toast.success('Code verified successfully!')
    
    // Redirect to reset password page with email and OTP
    router.push({
      path: '/reset-password',
      query: {
        email: email.value,
        otp: form.otp
      }
    })
  } catch (error) {
    console.error('OTP verification error:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else if (error.code === 'ERR_NETWORK') {
      toast.error('Network error. Please check your connection.')
    } else {
      toast.error('Verification failed. Please try again.')
    }
  } finally {
    loading.value = false
  }
}

const resendOTP = async () => {
  if (resendCooldown.value > 0 || timeRemaining.value > 0) return
  
  resendLoading.value = true
  
  try {
    await authStore.sendOTP(email.value)
    toast.success('New verification code sent to your email')
    
    // Reset countdown timer
    timeRemaining.value = 60 // 60 seconds
    startCountdown()
    
    // Start cooldown timer for resend button
    resendCooldown.value = 60
    cooldownTimer = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) {
        clearInterval(cooldownTimer)
        cooldownTimer = null
      }
    }, 1000)
  } catch (error) {
    console.error('Resend OTP error:', error)
    
    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to resend code. Please try again.')
    }
  } finally {
    resendLoading.value = false
  }
}
</script>
