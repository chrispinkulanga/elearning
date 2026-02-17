<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100">
          <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Check your email
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          We have sent a verification mail to <strong class="text-primary-600">{{ email }}</strong>. Click the link in that mail to verify your email address on E-Learning Platform.
        </p>
        <p class="mt-4 text-center text-sm text-gray-500">
          Do you not see the email? Check your spam folder.
        </p>
      </div>

      <div class="mt-8 space-y-6">
        <div class="text-center">
          <button
            @click="resendVerification"
            :disabled="resendLoading || resendCooldown > 0"
            class="font-medium text-primary-600 hover:text-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="resendLoading">Sending...</span>
            <span v-else-if="resendCooldown > 0">Resend in {{ resendCooldown }}s</span>
            <span v-else>Resend verification email</span>
          </button>
        </div>

        <div class="text-center">
          <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
            Go to login
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const email = ref('')
const resendLoading = ref(false)
const resendCooldown = ref(0)

let cooldownTimer = null

onMounted(() => {
  email.value = route.query.email || ''
  if (!email.value) {
    toast.error('Email address is required')
    router.push('/register')
  }
})

onUnmounted(() => {
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
  }
})

const resendVerification = async () => {
  if (resendCooldown.value > 0) return
  
  resendLoading.value = true
  
  try {
    // Here you would call an API to resend verification email
    // For now, we'll just show a success message
    toast.success('Verification email sent!')
    
    // Start cooldown timer
    resendCooldown.value = 60
    cooldownTimer = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) {
        clearInterval(cooldownTimer)
        cooldownTimer = null
      }
    }, 1000)
  } catch (error) {
    console.error('Resend verification error:', error)
    toast.error('Failed to resend verification email. Please try again.')
  } finally {
    resendLoading.value = false
  }
}
</script>
