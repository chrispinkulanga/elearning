<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Error Header -->
      <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
          <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Failed</h1>
        <p class="text-lg text-gray-600">We couldn't process your payment. Please try again or contact support.</p>
      </div>

      <!-- Error Details -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Details</h2>
        
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="payment" class="space-y-4">
          <!-- Payment Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500">Payment ID</p>
              <p class="text-sm text-gray-900">{{ payment.id }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Date</p>
              <p class="text-sm text-gray-900">{{ formatDate(payment.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Payment Method</p>
              <p class="text-sm text-gray-900">{{ payment.payment_method }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Status</p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                {{ payment.status }}
              </span>
            </div>
          </div>

          <!-- Course Details -->
          <div class="border-t border-gray-200 pt-4">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Course Details</h3>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                <img
                  v-if="payment.course?.image"
                  :src="payment.course.image"
                  :alt="payment.course.title"
                  class="w-full h-full object-cover rounded-lg"
                />
                <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">{{ payment.course?.title }}</h4>
                <p class="text-sm text-gray-500">{{ payment.course?.instructor?.name }}</p>
                <p class="text-sm text-gray-500">{{ payment.course?.total_duration }} hours</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-gray-900">${{ payment.amount }}</p>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="payment.error_message" class="border-t border-gray-200 pt-4">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Error Details</h3>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
              <p class="text-sm text-red-800">{{ payment.error_message }}</p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Payment not found</h3>
          <p class="mt-1 text-sm text-gray-500">Unable to load payment details.</p>
        </div>
      </div>

      <!-- Common Issues -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Common Issues</h2>
        <div class="space-y-4">
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Insufficient Funds</h3>
              <p class="text-sm text-gray-600">Make sure your account has sufficient funds to complete the transaction.</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Card Declined</h3>
              <p class="text-sm text-gray-600">Your bank may have declined the transaction. Contact your bank for more information.</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Invalid Card Details</h3>
              <p class="text-sm text-gray-600">Double-check your card number, expiry date, and CVV code.</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Network Issues</h3>
              <p class="text-sm text-gray-600">Temporary network issues may have caused the failure. Try again in a few minutes.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <button
          @click="retryPayment"
          :disabled="retrying"
          class="flex-1 btn btn-primary"
        >
          <span v-if="retrying" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
          <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          {{ retrying ? 'Processing...' : 'Try Again' }}
        </button>
        <router-link
          :to="`/payment/${payment?.course?.slug}`"
          class="flex-1 btn btn-secondary"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
          </svg>
          Use Different Method
        </router-link>
      </div>

      <!-- Support Section -->
      <div class="bg-blue-50 rounded-lg p-6">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h3 class="font-medium text-blue-900">Need Help?</h3>
            <p class="text-sm text-blue-700 mt-1">
              If you continue to experience issues, please contact our support team. 
              We're here to help you complete your purchase.
            </p>
            <div class="mt-3 flex flex-col sm:flex-row gap-2">
              <a
                href="mailto:support@elearning.com"
                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                support@elearning.com
              </a>
              <a
                href="tel:+1234567890"
                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                +1 (234) 567-890
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { paymentAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(false)
const retrying = ref(false)
const payment = ref(null)

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchPaymentDetails = async () => {
  loading.value = true
  try {
    const paymentId = route.params.paymentId || route.query.payment_id
    if (!paymentId) {
      toast.error('Payment ID not found')
      return
    }

    const response = await paymentAPI.getPaymentDetails(paymentId)
    payment.value = response.data
  } catch (error) {
    console.error('Error fetching payment details:', error)
    toast.error('Failed to load payment details')
  } finally {
    loading.value = false
  }
}

const retryPayment = async () => {
  if (!payment.value) {
    toast.error('No payment details available')
    return
  }

  retrying.value = true
  try {
    // Redirect to payment form with retry flag
    router.push({
      path: `/payment/${payment.value.course?.slug}`,
      query: { 
        retry: 'true',
        payment_id: payment.value.id 
      }
    })
  } catch (error) {
    console.error('Error retrying payment:', error)
    toast.error('Failed to retry payment')
  } finally {
    retrying.value = false
  }
}

onMounted(() => {
  fetchPaymentDetails()
})
</script> 