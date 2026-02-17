<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Success Header -->
      <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
          <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
        <p class="text-lg text-gray-600">Thank you for your purchase. Your order has been confirmed.</p>
      </div>

      <!-- Order Details -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Details</h2>
        
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="order" class="space-y-4">
          <!-- Order Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500">Order ID</p>
              <p class="text-sm text-gray-900">{{ order.id }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Date</p>
              <p class="text-sm text-gray-900">{{ formatDate(order.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Payment Method</p>
              <p class="text-sm text-gray-900">{{ order.payment_method }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Status</p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ order.status }}
              </span>
            </div>
          </div>

          <!-- Course Details -->
          <div class="border-t border-gray-200 pt-4">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Course Details</h3>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                <img
                  v-if="order.course?.image"
                  :src="order.course.image"
                  :alt="order.course.title"
                  class="w-full h-full object-cover rounded-lg"
                />
                <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">{{ order.course?.title }}</h4>
                <p class="text-sm text-gray-500">{{ order.course?.instructor?.name }}</p>
                <p class="text-sm text-gray-500">{{ order.course?.total_duration }} hours</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-gray-900">${{ order.amount }}</p>
              </div>
            </div>
          </div>

          <!-- Billing Details -->
          <div class="border-t border-gray-200 pt-4">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Billing Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Name</p>
                <p class="text-sm text-gray-900">{{ order.billing_name }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Email</p>
                <p class="text-sm text-gray-900">{{ order.billing_email }}</p>
              </div>
              <div v-if="order.billing_address" class="md:col-span-2">
                <p class="text-sm font-medium text-gray-500">Address</p>
                <p class="text-sm text-gray-900">{{ order.billing_address }}</p>
              </div>
            </div>
          </div>

          <!-- Payment Summary -->
          <div class="border-t border-gray-200 pt-4">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Payment Summary</h3>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Course Price</span>
                <span class="text-sm text-gray-900">${{ order.course?.price || 0 }}</span>
              </div>
              <div v-if="order.discount_amount > 0" class="flex justify-between">
                <span class="text-sm text-gray-600">Discount</span>
                <span class="text-sm text-green-600">-${{ order.discount_amount }}</span>
              </div>
              <div class="flex justify-between border-t border-gray-200 pt-2">
                <span class="font-medium text-gray-900">Total Paid</span>
                <span class="font-medium text-gray-900">${{ order.amount }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Order not found</h3>
          <p class="mt-1 text-sm text-gray-500">Unable to load order details.</p>
        </div>
      </div>

      <!-- Next Steps -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">What's Next?</h2>
        <div class="space-y-4">
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center">
              <span class="text-primary-600 text-sm font-medium">1</span>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Access Your Course</h3>
              <p class="text-sm text-gray-600">You can now access your course from your dashboard.</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center">
              <span class="text-primary-600 text-sm font-medium">2</span>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Start Learning</h3>
              <p class="text-sm text-gray-600">Begin your learning journey with the course content.</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center">
              <span class="text-primary-600 text-sm font-medium">3</span>
            </div>
            <div>
              <h3 class="font-medium text-gray-900">Track Progress</h3>
              <p class="text-sm text-gray-600">Monitor your progress and complete lessons at your own pace.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4">
        <router-link
          :to="`/courses/${order?.course?.slug}`"
          class="flex-1 btn btn-primary"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          Start Learning
        </router-link>
        <router-link
          to="/my-courses"
          class="flex-1 btn btn-secondary"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          My Courses
        </router-link>
      </div>

      <!-- Additional Info -->
      <div class="mt-8 text-center">
        <p class="text-sm text-gray-500">
          A confirmation email has been sent to your email address.
        </p>
        <p class="text-sm text-gray-500 mt-1">
          If you have any questions, please contact our support team.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { paymentAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()

const loading = ref(false)
const order = ref(null)

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchOrderDetails = async () => {
  loading.value = true
  try {
    const orderId = route.params.orderId || route.query.order_id
    if (!orderId) {
      toast.error('Order ID not found')
      return
    }

    const response = await paymentAPI.getOrderDetails(orderId)
    order.value = response.data
  } catch (error) {
    console.error('Error fetching order details:', error)
    toast.error('Failed to load order details')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchOrderDetails()
})
</script> 