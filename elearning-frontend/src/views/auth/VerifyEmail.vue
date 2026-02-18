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
          Verifying your email
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600" v-if="loading">
          Please wait while we verify your email address...
        </p>
        <p class="mt-2 text-center text-sm text-gray-600" v-if="error">
          {{ error }}
        </p>
        <p class="mt-2 text-center text-sm text-gray-600" v-if="success">
          {{ success }}
        </p>
      </div>

      <div class="mt-8 space-y-6" v-if="!loading && success">
        <div class="text-center">
          <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
            Go to login
          </router-link>
        </div>
      </div>

      <div class="mt-8 space-y-6" v-if="!loading && error">
        <div class="text-center">
          <button
            @click="verifyEmail"
            class="font-medium text-primary-600 hover:text-primary-500"
          >
            Try again
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const error = ref('')
const success = ref('')

const verifyEmail = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  const token = route.query.token
  const email = route.query.email

  if (!token || !email) {
    loading.value = false
    error.value = 'Invalid verification link. Please check your email for the correct link.'
    return
  }

  try {
    await authStore.verifyEmail(token, email)
    success.value = 'Email verified successfully! You can now login to your account.'
  } catch (err) {
    console.error('Email verification error:', err)
    error.value = err.response?.data?.message || 'Failed to verify email. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  verifyEmail()
})
</script>
