<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
            Alumni Network Invitation
          </h2>
          <p class="mt-2 text-sm text-gray-600">
            You have been invited to join our alumni network!
          </p>
        </div>

        <div v-if="loading" class="mt-8">
          <div class="flex justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>
        </div>

        <div v-else-if="alumni" class="mt-8">
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Invitation Details</h3>
            <div class="space-y-2 text-sm text-gray-600">
              <div v-if="alumni.invited_at">
                <span class="font-medium">Invited on:</span> {{ formatDate(alumni.invited_at) }}
              </div>
              <div v-if="alumni.user">
                <span class="font-medium">For:</span> {{ alumni.user.name }} ({{ alumni.user.email }})
              </div>
              <div>
                <span class="font-medium">Status:</span> 
                <span :class="getStatusClass(alumni.status)">{{ alumni.status }}</span>
              </div>
            </div>
          </div>

          <div v-if="alumni.status === 'pending'" class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800">
                    Join Our Alumni Network
                  </h3>
                  <div class="mt-2 text-sm text-blue-700">
                    <p>By accepting this invitation, you will:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                      <li>Connect with other alumni from our platform</li>
                      <li>Access exclusive alumni resources and events</li>
                      <li>Share your professional achievements and updates</li>
                      <li>Mentor current students and instructors</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex space-x-4">
              <button
                @click="acceptInvitation"
                :disabled="submitting"
                class="flex-1 bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                <span v-if="submitting">Accepting...</span>
                <span v-else>Accept Invitation</span>
              </button>
              <button
                @click="declineInvitation"
                :disabled="submitting"
                class="flex-1 bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50"
              >
                Decline
              </button>
            </div>
          </div>

          <div v-else-if="alumni.status === 'active'" class="text-center">
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-green-800">
                    Welcome to the Alumni Network!
                  </h3>
                  <div class="mt-2 text-sm text-green-700">
                    <p>You have successfully joined our alumni network. Thank you for being part of our community!</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-6">
              <router-link
                to="/"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200"
              >
                Go to Dashboard
              </router-link>
            </div>
          </div>

          <div v-else class="text-center">
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
              <h3 class="text-sm font-medium text-gray-800">
                Invitation Status: {{ alumni.status }}
              </h3>
              <p class="mt-2 text-sm text-gray-600">
                This invitation is no longer available.
              </p>
            </div>
          </div>
        </div>

        <div v-else class="mt-8 text-center">
          <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <h3 class="text-sm font-medium text-red-800">
              Invalid Invitation
            </h3>
            <p class="mt-2 text-sm text-red-700">
              This invitation link is invalid or has expired.
            </p>
          </div>
          <div class="mt-6">
            <router-link
              to="/"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200"
            >
              Go to Dashboard
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { alumniAPI } from '@/services/api'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

const loading = ref(false)
const submitting = ref(false)
const alumni = ref(null)

// Check if user is authenticated
const isAuthenticated = computed(() => {
  return authStore.isAuthenticated
})

// Load invitation details
const loadInvitation = async () => {
  const alumniId = route.params.id
  if (!alumniId) {
    return
  }

  loading.value = true
  try {
    // For now, we'll simulate loading the invitation
    // In a real implementation, you'd fetch the invitation details
    alumni.value = {
      id: alumniId,
      status: 'pending',
      invited_at: new Date().toISOString(),
      user: {
        name: authStore.user?.name || 'User',
        email: authStore.user?.email || 'user@example.com'
      }
    }
  } catch (error) {
    console.error('Error loading invitation:', error)
    toast.error('Failed to load invitation details')
  } finally {
    loading.value = false
  }
}

// Accept invitation
const acceptInvitation = async () => {
  if (!alumni.value) return

  submitting.value = true
  try {
    await alumniAPI.acceptInvitation(alumni.value.id)
    alumni.value.status = 'active'
    toast.success('Welcome to the alumni network!')
  } catch (error) {
    console.error('Error accepting invitation:', error)
    toast.error('Failed to accept invitation')
  } finally {
    submitting.value = false
  }
}

// Decline invitation
const declineInvitation = () => {
  if (confirm('Are you sure you want to decline this invitation?')) {
    alumni.value.status = 'declined'
    toast.info('Invitation declined')
  }
}

// Helper functions
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    active: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    declined: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'
  }
  return classes[status] || 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
}

// Component lifecycle
onMounted(async () => {
  if (!isAuthenticated.value) {
    toast.error('Please log in to view this invitation')
    router.push('/login')
    return
  }

  await loadInvitation()
})
</script>
