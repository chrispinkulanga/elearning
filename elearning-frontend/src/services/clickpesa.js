import axios from 'axios'
import CryptoJS from 'crypto-js'
import appConfig from '@/config/app.config'

// Use backend API instead of direct ClickPesa API to avoid CORS issues
const BASE = `${appConfig.apiUrl}/clickpesa`

const raw = axios.create({
  baseURL: BASE,
  timeout: 25000,
  headers: { 
    'Content-Type': 'application/json', 
    'Accept': 'application/json',
    'User-Agent': 'eLearning-App/1.0'
  },
})

// Cached token in memory to avoid multiple calls
let cachedToken = null
let tokenFetchedAt = 0

// Generate checksum for data integrity - including fetchSenderDetails
function generateChecksum(payload, secretKey) {
  // Include fetchSenderDetails in the checksum
  const fetchSender = payload.fetchSenderDetails ? 'true' : 'false'
  const concatenatedString = payload.amount + payload.currency + fetchSender + payload.orderReference + payload.phoneNumber
  
  console.log('Debug - Concatenated string:', concatenatedString)
  
  // Generate HMAC-SHA256 hash
  const checksum = CryptoJS.HmacSHA256(concatenatedString, secretKey).toString()
  
  console.log('Debug - Generated checksum:', checksum)
  
  return checksum
}

async function getAuthToken() {
  // Reuse token for 50 minutes (ClickPesa tokens expire in 1 hour)
  if (cachedToken && Date.now() - tokenFetchedAt < 50 * 60 * 1000) return cachedToken

  try {
    console.log('Requesting token from backend...')
    
    const res = await axios.post(`${BASE}/generate-token`)
    
    console.log('ClickPesa Token Response:', {
      status: res.status,
      data: res.data
    })
    
    if (res.data?.success && res.data?.token) {
      cachedToken = res.data.token
      tokenFetchedAt = Date.now()
      return res.data.token
    } else {
      throw new Error('Failed to obtain ClickPesa token - invalid response')
    }
  } catch (error) {
    console.error('ClickPesa Token Error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
      statusText: error.response?.statusText
    })
    throw new Error(`Failed to obtain ClickPesa token: ${error.message}`)
  }
}

async function authPost(path, data) {
  return raw.post(path, data)
}

async function authGet(path) {
  return raw.get(path)
}

export const clickpesaAPI = {
  // Test credentials
  testCredentials: () => {
    const clientId = (appConfig.clickpesa.clientId || '').trim()
    const apiKey = (appConfig.clickpesa.apiKey || '').trim()
    
    console.log('ClickPesa Credentials Test:', {
      clientId: clientId ? `${clientId.substring(0, 10)}...` : 'MISSING',
      apiKey: apiKey ? `${apiKey.substring(0, 10)}...` : 'MISSING',
      hasClientId: !!clientId,
      hasApiKey: !!apiKey,
      credentials: {
        clientId: appConfig.clickpesa.clientId,
        apiKey: appConfig.clickpesa.apiKey
      }
    })
    
    return {
      hasCredentials: !!(clientId && apiKey),
      clientId: clientId ? `${clientId.substring(0, 10)}...` : null,
      apiKey: apiKey ? `${apiKey.substring(0, 10)}...` : null
    }
  },

  // Validate details and get active methods/fees
  previewUssdPush: (payload) => {
    // Skip preview and go directly to initiate for now
    return authPost('/initiate-ussd-push', payload)
  },

  // Initiate USSD push - user will receive a prompt to enter PIN
  initiateUssdPush: (payload) => {
    return authPost('/initiate-ussd-push', payload)
  },

  // Check payment status by ID or orderReference
  getPaymentStatus: (paymentId) => authGet(`/payments/${paymentId}`),

  // Enhanced live payment methods
  checkPaymentStatus: async (transactionId) => {
    try {
      console.log(`Checking payment status for transaction: ${transactionId}`)
      
      const response = await authGet(`/payment-status/${transactionId}`)
      
      if (response.data.success) {
        return {
          success: true,
          status: response.data.data.status,
          transaction: response.data.data,
          message: response.data.message || 'Payment status retrieved successfully'
        }
      } else {
        throw new Error(response.data.message || 'Failed to check payment status')
      }
    } catch (error) {
      console.error('Payment Status Check Error:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status
      })
      throw new Error(`Failed to check payment status: ${error.message}`)
    }
  },

  // Enhanced USSD push initiation for live payments
  initiateLivePayment: async (paymentData) => {
    try {
      console.log('Initiating live ClickPesa payment:', paymentData)
      
      // Validate required fields
      const requiredFields = ['amount', 'currency', 'orderReference', 'phoneNumber']
      for (const field of requiredFields) {
        if (!paymentData[field]) {
          throw new Error(`Missing required field: ${field}`)
        }
      }
      
      // Ensure phone number format (Tanzanian format)
      let phoneNumber = paymentData.phoneNumber
      if (!phoneNumber.startsWith('255')) {
        phoneNumber = '255' + phoneNumber.replace(/^\+/, '').replace(/^0/, '')
      }
      
      const payload = {
        amount: paymentData.amount.toString(),
        currency: paymentData.currency || 'TZS',
        orderReference: paymentData.orderReference,
        phoneNumber: phoneNumber,
        fetchSenderDetails: paymentData.fetchSenderDetails || false
      }
      
      const response = await authPost('/initiate-ussd-push', payload)
      
      if (response.data.success) {
        return {
          success: true,
          transactionId: response.data.transaction_id,
          status: response.data.status,
          channel: response.data.channel,
          data: response.data.data,
          message: response.data.message || 'Live payment initiated successfully'
        }
      } else {
        throw new Error(response.data.message || 'Failed to initiate live payment')
      }
    } catch (error) {
      console.error('Live Payment Initiation Error:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status
      })
      throw new Error(`Failed to initiate live payment: ${error.message}`)
    }
  }
}

export default raw

