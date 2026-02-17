watch([modalMsisdn, modalProvider, payAmount, currency], async () => {
  if (!showMmModal.value) return
  const msisdn = String(modalMsisdn.value || '').replace(/\D/g, '')
  if (msisdn.length < 10) {
    previewSender.value = null
    previewError.value = ''
    return
  }
  try {
    previewLoading.value = true
    previewError.value = ''
    const { clickpesaAPI } = await import('@/services/clickpesa')
    const res = await clickpesaAPI.previewUssdPush({
      amount: String(payAmount.value),
      currency: 'TZS',
      orderReference: `PREVIEW-${Date.now()}`,
      phoneNumber: msisdn,
      fetchSenderDetails: true,
    })
    previewSender.value = res.data?.sender || null
  } catch (e) {
    previewSender.value = null
    previewError.value = e.response?.data?.message || e.message || 'Preview failed'
  } finally {
    previewLoading.value = false
  }
})
<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Complete Payment</h1>

      <!-- 1. Amount To Pay -->
      <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center space-x-2">
          <span class="w-6 h-6 rounded-full bg-yellow-100 text-yellow-800 flex items-center justify-center text-sm font-semibold">1</span>
          <h2 class="text-lg font-medium text-gray-900">Amount To Pay</h2>
        </div>
        <div class="p-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
          <div class="flex items-center space-x-3">
            <input v-model.number="payAmount" type="number" min="1" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            <select v-model="currency" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
              <option value="TZS">TZS</option>
              <option value="USD">USD</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 2. Select Payment Method -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center space-x-2">
          <span class="w-6 h-6 rounded-full bg-yellow-100 text-yellow-800 flex items-center justify-center text-sm font-semibold">2</span>
          <h2 class="text-lg font-medium text-gray-900">Select Payment Method</h2>
        </div>
        <div class="p-4 space-y-3">
          <button @click="openMobileModal('AIRTEL')" class="w-full p-4 bg-white border rounded-lg flex items-center justify-between hover:border-primary-400">
            <div class="flex items-center space-x-3">
              <svg class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
              <div class="text-left"><div class="font-medium text-gray-900">AirtelMoney</div><div class="text-xs text-gray-500">Pay with Mobile via Airtel Money</div></div>
            </div>
            <div class="text-xs text-gray-600">{{ currency }}</div>
          </button>
          <button @click="openMobileModal('TIGO')" class="w-full p-4 bg-white border rounded-lg flex items-center justify-between hover:border-primary-400">
            <div class="flex items-center space-x-3">
              <svg class="w-8 h-8 text-blue-500" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="4" width="16" height="16" rx="4"/></svg>
              <div class="text-left"><div class="font-medium text-gray-900">TigoPesa</div><div class="text-xs text-gray-500">Pay with Mobile via TIGOPESA</div></div>
          </div>
            <div class="text-xs text-gray-600">{{ currency }}</div>
          </button>
          <button @click="openMobileModal('VODACOM')" class="w-full p-4 bg-white border rounded-lg flex items-center justify-between hover:border-primary-400">
            <div class="flex items-center space-x-3">
              <svg class="w-8 h-8 text-red-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
              <div class="text-left"><div class="font-medium text-gray-900">Vodacom Mpesa</div><div class="text-xs text-gray-500">Pay with Mobile via M-PESA</div></div>
            </div>
            <div class="text-xs text-gray-600">{{ currency }}</div>
          </button>
          <button @click="startCardPayment" class="w-full p-4 bg-white border rounded-lg flex items-center justify-between hover:border-primary-400">
            <div class="flex items-center space-x-3">
              <div class="flex -space-x-1">
                <svg class="w-8 h-8 text-blue-700" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="6" width="18" height="12" rx="2"/></svg>
                <svg class="w-8 h-8 text-yellow-500" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
              </div>
              <div class="text-left"><div class="font-medium text-gray-900">Visa / Mastercard</div><div class="text-xs text-gray-500">Pay with VISA, MasterCard, or UnionPay</div></div>
              </div>
            <div class="text-xs text-gray-600">{{ currency }}</div>
          </button>
          <button class="w-full p-4 bg-white border rounded-lg flex items-center justify-between hover:border-primary-400" @click="startBankTransfer">
            <div class="flex items-center space-x-3">
              <svg class="w-8 h-8 text-yellow-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 6H3l9-6zM4 10h16v9H4z"/></svg>
              <div class="text-left"><div class="font-medium text-gray-900">Bank Transfer</div><div class="text-xs text-gray-500">Deposit cash via Bank/Agent or Online Transfer</div></div>
            </div>
            <div class="text-xs text-gray-600">{{ currency }}</div>
          </button>
              </div>
            </div>
          </div>

    <!-- Mobile Money Modal -->
    <div v-if="showMmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Mobile Money Payment</h3>
          <button @click="closeMobileModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
              </div>
        <div class="p-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
            <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700">
              {{ modalProvider === 'VODACOM' ? 'Vodacom (M-PESA)' : (modalProvider === 'AIRTEL' ? 'AirtelMoney' : 'TigoPesa') }}
              </div>
              </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number (MSISDN)</label>
            <input v-model="modalMsisdn" type="tel" placeholder="2557XXXXXXXX" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            <p v-if="previewLoading" class="mt-1 text-xs text-gray-500">Verifying number…</p>
            <div v-else-if="previewSender" class="mt-2 text-xs text-gray-700 bg-gray-50 border border-gray-200 rounded p-2">
              <div><span class="font-medium">Name:</span> {{ previewSender.accountName }}</div>
              <div><span class="font-medium">Account:</span> {{ previewSender.accountNumber }}</div>
              <div><span class="font-medium">Provider:</span> {{ previewSender.accountProvider }}</div>
                </div>
            <p v-else-if="previewError" class="mt-1 text-xs text-red-600">{{ previewError }}</p>
              </div>
          <div class="flex items-center justify-between text-sm text-gray-600">
            <span>Amount</span>
            <span class="font-medium">{{ payAmount }} {{ currency }}</span>
            </div>
          </div>
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
          <button @click="closeMobileModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
          <button @click="confirmMobileMoney" :disabled="processing || !modalMsisdn" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50">
            <span v-if="processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></span>
            Pay {{ payAmount }} {{ currency }}
            </button>
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useAuthStore } from '@/stores/auth'
import { paymentAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const courseStore = useCourseStore()
const authStore = useAuthStore()
const toast = useToast()

const course = ref(null)
const processing = ref(false)
const paymentMethod = ref('clickpesa')
const payAmount = ref(0)
const currency = ref('TZS')
const showMmModal = ref(false)
const modalProvider = ref('VODACOM')
const modalMsisdn = ref('')
const previewSender = ref(null)
const previewLoading = ref(false)
const previewError = ref('')
const acceptTerms = ref(false)
const errors = reactive({})

const cardDetails = reactive({
  number: '',
  expiry: '',
  cvv: '',
  name: ''
})

const billingAddress = reactive({
  first_name: '',
  last_name: '',
  address: '',
  city: '',
  postal_code: '',
  country: ''
})

const coursePrice = computed(() => Number(course.value?.price ?? 0))

const tax = computed(() => {
  const price = coursePrice.value
  return (price * 0.08).toFixed(2) // 8% tax
})

const processingFee = computed(() => {
  const price = coursePrice.value
  return (price * 0.029 + 0.30).toFixed(2) // 2.9% + $0.30
})

const total = computed(() => payAmount.value.toFixed(2))

const validateForm = () => {
  errors.value = {}
  
  if (paymentMethod.value === 'card') {
    if (!cardDetails.number) {
      errors.card_number = 'Card number is required'
    }
    if (!cardDetails.expiry) {
      errors.expiry = 'Expiry date is required'
    }
    if (!cardDetails.cvv) {
      errors.cvv = 'CVV is required'
    }
    if (!cardDetails.name) {
      errors.cardholder_name = 'Cardholder name is required'
    }
  }
  
  // For Flutterwave, keep billing fields optional
  if (paymentMethod.value !== 'flutterwave') {
    if (!billingAddress.first_name) {
      errors.first_name = 'First name is required'
    }
    if (!billingAddress.last_name) {
      errors.last_name = 'Last name is required'
    }
    if (!billingAddress.address) {
      errors.address = 'Address is required'
    }
    if (!billingAddress.city) {
      errors.city = 'City is required'
    }
    if (!billingAddress.postal_code) {
      errors.postal_code = 'Postal code is required'
    }
    if (!billingAddress.country) {
      errors.country = 'Country is required'
    }
  }
  
  return Object.keys(errors.value).length === 0
}

const handlePayment = async () => {
  if (!validateForm()) return
  if (!acceptTerms.value) {
    toast.error('Please accept the terms and conditions')
    return
  }
  
  processing.value = true
  
  try {
    if (paymentMethod.value === 'flutterwave') {
      if (!window.FlutterwaveCheckout) {
        await new Promise((resolve, reject) => {
          const s = document.createElement('script')
          s.src = 'https://checkout.flutterwave.com/v3.js'
          s.onload = resolve
          s.onerror = reject
          document.body.appendChild(s)
        })
      }

      const txRef = `TX-${Date.now()}-${Math.floor(Math.random()*1000)}`
      const amountNum = parseFloat(total.value)
      const currency = (course.value?.currency || 'USD').toUpperCase()
      const publicKey = (import.meta.env.VITE_FLW_PUBLIC_KEY || '').trim()
      if (!publicKey) {
        toast.error('Missing Flutterwave Public Key. Add VITE_FLW_PUBLIC_KEY (starts with FLWPUBK-) to elearning-frontend/.env')
        processing.value = false
        return
      }
      if (!/^FLWPUBK-/.test(publicKey)) {
        toast.error('Invalid key type. Use Flutterwave Public Key (FLWPUBK-...), not Client ID or Secret')
        processing.value = false
        return
      }
      window.FlutterwaveCheckout({
        public_key: publicKey,
        tx_ref: txRef,
        amount: amountNum,
        currency,
        customer: {
          email: authStore.user?.email || '',
          name: `${billingAddress.first_name} ${billingAddress.last_name}`.trim(),
        },
        customizations: {
          title: course.value?.title || 'Course Payment',
        },
        callback: async (res) => {
          try {
            const verify = await paymentAPI.verifyFlutterwave({
              transaction_id: res.transaction_id || res.id,
              amount: amountNum,
              currency,
              course_id: course.value.id,
            })
            if (verify.data?.status === 'success') {
              toast.success('Payment successful! You are now enrolled in the course.')
              router.push(`/courses/${course.value.slug}`)
            } else {
              toast.error('Payment verification failed.')
            }
          } catch (e) {
            console.error('Verify error:', e)
            toast.error('Payment verification failed.')
          }
        },
        onclose: () => {},
      })
      return
    }
    if (paymentMethod.value === 'clickpesa') {
      const { clickpesaAPI } = await import('@/services/clickpesa')
      const amountNum = parseFloat(total.value)
      const currency = (course.value?.currency || 'TZS').toUpperCase()
      const msisdn = prompt('Enter Mobile Number (MSISDN) e.g. 2557XXXXXXXX') || ''
      const provider = prompt('Enter Provider: AIRTEL, TIGO, or VODACOM', 'VODACOM') || 'VODACOM'
      if (!msisdn) {
        processing.value = false
        return
      }
      try {
        const createRes = await clickpesaAPI.createMobileMoneyPayment({
          amount: amountNum,
          currency,
          msisdn,
          provider,
          description: `Course payment: ${course.value?.title}`,
          metadata: { course_id: course.value?.id },
        })
        const pid = createRes.data?.id || createRes.data?.payment_id
        toast.info('Payment request sent. Please approve on your phone...')

        const start = Date.now()
        const poll = async () => {
          const res = await clickpesaAPI.getPaymentStatus(pid)
          const status = res.data?.status
          if (status === 'SUCCESS' || status === 'completed' || status === 'success') {
            toast.success('Payment successful! Enrolling...')
            router.push(`/courses/${course.value.slug}`)
            return true
          }
          if (status === 'FAILED' || status === 'failed' || status === 'canceled') {
            toast.error('Payment failed or canceled')
            return true
          }
          if (Date.now() - start > 180000) {
            toast.error('Payment timeout. Please try again.')
            return true
          }
          return false
        }
        const interval = setInterval(async () => {
          try {
            const done = await poll()
            if (done) {
              clearInterval(interval)
              processing.value = false
            }
          } catch (e) {
            clearInterval(interval)
            processing.value = false
            toast.error('Could not verify payment')
          }
        }, 5000)
        return
      } catch (e) {
        toast.error('Failed to initiate ClickPesa payment')
      }
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error('Payment failed. Please try again.')
    }
  } finally {
    processing.value = false
  }
}

onMounted(async () => {
  try {
    const courseData = await courseStore.fetchCourseBySlug(route.params.slug)
    course.value = courseData
    payAmount.value = Number(courseData?.price ?? 0)
    currency.value = (courseData?.currency || 'TZS').toUpperCase()
    
    // Pre-fill billing address with user data if available
    const user = authStore.user
    if (user) {
      billingAddress.first_name = user.name?.split(' ')[0] || ''
      billingAddress.last_name = user.name?.split(' ').slice(1).join(' ') || ''
    }
  } catch (error) {
    console.error('Error loading course:', error)
    toast.error('Failed to load course information')
  }
})

// Quick-start handlers matching the first screen UX
const openMobileModal = (provider) => {
  modalProvider.value = provider
  showMmModal.value = true
  previewSender.value = null
  previewError.value = ''
}

const closeMobileModal = () => {
  showMmModal.value = false
}

const confirmMobileMoney = async () => {
  processing.value = true
  try {
    const { clickpesaAPI } = await import('@/services/clickpesa')
    
    // Test credentials first
    const credTest = clickpesaAPI.testCredentials()
    console.log('Credentials Test Result:', credTest)
    
    if (!credTest.hasCredentials) {
      toast.error('ClickPesa credentials not configured properly')
      processing.value = false
      return
    }
    
    const orderRef = `ORD-${course.value?.id}-${Date.now()}`

    // Ensure Mobile Money uses TZS as per ClickPesa USSD push
    const cpCurrency = 'TZS'
    const msisdnSanitized = String(modalMsisdn.value || '').replace(/\D/g, '')
    const paymentMethod = modalProvider.value === 'TIGO'
      ? 'TIGO-PESA'
      : modalProvider.value === 'AIRTEL'
      ? 'AIRTEL-MONEY'
      : 'M-PESA'

    // 1) Preview to validate number, amount and methods
    await clickpesaAPI.previewUssdPush({
      amount: String(payAmount.value),
      currency: cpCurrency,
      orderReference: orderRef,
      phoneNumber: msisdnSanitized,
      fetchSenderDetails: false,
    })

    // 2) Initiate USSD push (maps to the right method)
    const initRes = await clickpesaAPI.initiateUssdPush({
      amount: String(payAmount.value),
      currency: cpCurrency,
      orderReference: orderRef,
      phoneNumber: msisdnSanitized,
      paymentMethod,
    })
    const pid = initRes.data?.id || initRes.data?.paymentId || initRes.data?.payment_id
    toast.info('Payment request sent. Approve on your phone...')
    showMmModal.value = false
    const { clickpesaAPI: api } = await import('@/services/clickpesa')
    const start = Date.now()
    const interval = setInterval(async () => {
      try {
        const res = await api.getPaymentStatus(pid)
        const status = (res.data?.status || '').toUpperCase()
        if (['SUCCESS','COMPLETED'].includes(status)) {
          clearInterval(interval); processing.value = false
          toast.success('Payment successful!')
          router.push(`/courses/${course.value.slug}`)
        } else if (['FAILED','CANCELED'].includes(status)) {
          clearInterval(interval); processing.value = false
          toast.error('Payment failed or canceled')
        } else if (Date.now() - start > 180000) {
          clearInterval(interval); processing.value = false
          toast.error('Payment timeout. Please try again.')
        }
      } catch (e) {
        clearInterval(interval); processing.value = false
        toast.error('Could not verify payment')
      }
    }, 5000)
  } catch (e) {
    processing.value = false
    const apiMsg = e.response?.data?.message || e.response?.data?.error || e.message || 'Failed to initiate payment'
    toast.error(apiMsg)
  }
}

// Card via Flutterwave (kept for card rails; uses total from amount box)
const startCardPayment = async () => {
  processing.value = true
  try {
    if (!window.FlutterwaveCheckout) {
      await new Promise((resolve, reject) => {
        const s = document.createElement('script')
        s.src = 'https://checkout.flutterwave.com/v3.js'
        s.onload = resolve
        s.onerror = reject
        document.body.appendChild(s)
      })
    }
    const txRef = `TX-${Date.now()}-${Math.floor(Math.random()*1000)}`
    const amountNum = parseFloat(payAmount.value)
    const publicKey = (import.meta.env.VITE_FLW_PUBLIC_KEY || '').trim()
    if (!publicKey) { toast.error('Missing Flutterwave Public Key'); processing.value = false; return }
    window.FlutterwaveCheckout({
      public_key: publicKey,
      tx_ref: txRef,
      amount: amountNum,
      currency: currency.value,
      customer: { email: authStore.user?.email || '', name: authStore.user?.name || '' },
      customizations: { title: course.value?.title || 'Course Payment' },
      callback: async () => {
        processing.value = false
        toast.success('Card payment successful!')
        router.push(`/courses/${course.value.slug}`)
      },
      onclose: () => { processing.value = false },
    })
  } catch (e) {
    processing.value = false
    toast.error('Failed to start card payment')
  }
}

// Basic bank transfer flow: show instructions and mark pending
const startBankTransfer = async () => {
  const instructions = `
Bank Transfer Instructions\n\nAccount Name: E-Learning Ltd\nAccount No: 000123456789\nBank: NMB (TZ)\nReference: COURSE-${route.params.slug}-${Date.now()}\nAmount: ${payAmount.value} ${currency.value}
`
  await navigator.clipboard.writeText(instructions).catch(() => {})
  toast.info('Bank transfer instructions copied. After paying, contact support with receipt.')
}
</script> 