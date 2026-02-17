/**
 * Application Configuration
 * Centralized configuration for the e-learning frontend
 */

// Read API URL from environment variable (standard way)
const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const baseUrl = import.meta.env.VITE_BASE_URL || 'http://localhost:8000'

// Detect environment
const isDevelopment = import.meta.env.DEV
const isProduction = import.meta.env.PROD

// ClickPesa Configuration
// ⚠️ NOTE: These are client-side credentials (public)
// For security, use backend proxy for sensitive operations
const CLICKPESA_CONFIG = {
  development: {
    clientId: 'YOUR_DEV_CLIENT_ID',
    apiKey: 'YOUR_DEV_API_KEY'
  },
  production: {
    clientId: 'YOUR_PROD_CLIENT_ID',
    apiKey: 'YOUR_PROD_API_KEY'
  }
}

// Export configuration
export default {
  apiUrl,
  baseUrl,
  clickpesa: isDevelopment ? CLICKPESA_CONFIG.development : CLICKPESA_CONFIG.production,
  isDevelopment,
  isProduction
}
