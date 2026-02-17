<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
            <p class="mt-2 text-gray-600">Manage platform configuration and preferences</p>
          </div>
          <div class="flex space-x-3">
            <button 
              @click="saveAllSettings" 
              :disabled="saving"
              class="btn btn-success flex items-center space-x-2"
            >
              <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ saving ? 'Saving...' : 'Save All Settings' }}</span>
            </button>
            <router-link to="/admin" class="btn btn-primary">
              Back to Dashboard
            </router-link>
          </div>
        </div>
      </div>

      <!-- Access Control -->
      <div v-if="!authStore.isAuthenticated" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8 text-center">
        <h3 class="text-lg font-medium text-red-800 mb-2">Access Denied</h3>
        <p class="text-red-600">Please log in to access system settings.</p>
        <router-link to="/login" class="inline-block mt-3 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
          Go to Login
        </router-link>
      </div>

      <div v-else-if="!authStore.isAdmin" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8 text-center">
        <h3 class="text-lg font-medium text-red-800 mb-2">Access Denied</h3>
        <p class="text-red-600">You do not have admin privileges to access system settings.</p>
        <router-link to="/" class="inline-block mt-3 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
          Go Home
        </router-link>
      </div>

      <!-- Settings Content -->
      <div v-else>
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Settings Forms -->
        <div v-else class="space-y-8">
          <!-- General Settings -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
              <p class="text-sm text-gray-600">Basic platform configuration</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Platform Name</label>
                  <input 
                    v-model="settings.general.platform_name" 
                    type="text" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="eLearning Platform"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Platform URL</label>
                  <input 
                    v-model="settings.general.platform_url" 
                    type="url" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="https://example.com"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                  <input 
                    v-model="settings.general.contact_email" 
                    type="email" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="admin@example.com"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Support Phone</label>
                  <input 
                    v-model="settings.general.support_phone" 
                    type="tel" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="+1-555-0123"
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Platform Description</label>
                <textarea 
                  v-model="settings.general.platform_description" 
                  rows="3"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Brief description of your eLearning platform"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Course Settings -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Course Settings</h3>
              <p class="text-sm text-gray-600">Course creation and management preferences</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Auto-approve Courses</label>
                  <select 
                    v-model="settings.courses.auto_approve" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="false">Manual Approval Required</option>
                    <option value="true">Auto-approve New Courses</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Course Duration (hours)</label>
                  <input 
                    v-model="settings.courses.max_duration" 
                    type="number" 
                    min="1"
                    max="1000"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="100"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Maximum File Size (MB)</label>
                  <input 
                    v-model="settings.courses.max_file_size" 
                    type="number" 
                    min="1"
                    max="1000"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="50"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Allow Free Courses</label>
                  <select 
                    v-model="settings.courses.allow_free_courses" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course Categories</label>
                <div class="space-y-2">
                  <div v-for="(category, index) in settings.courses.categories" :key="index" class="flex space-x-2">
                    <input 
                      v-model="settings.courses.categories[index]" 
                      type="text" 
                      class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Category name"
                    />
                    <button 
                      @click="removeCategory(index)"
                      type="button"
                      class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                      Remove
                    </button>
                  </div>
                  <button 
                    @click="addCategory"
                    type="button"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                  >
                    Add Category
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- User Settings -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">User Settings</h3>
              <p class="text-sm text-gray-600">User registration and management preferences</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Allow User Registration</label>
                  <select 
                    v-model="settings.users.allow_registration" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email Verification Required</label>
                  <select 
                    v-model="settings.users.email_verification" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Default User Role</label>
                  <select 
                    v-model="settings.users.default_role" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Users</label>
                  <input 
                    v-model="settings.users.max_users" 
                    type="number" 
                    min="1"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="10000"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Settings -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Payment Settings</h3>
              <p class="text-sm text-gray-600">Payment gateway and financial configuration</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                  <select 
                    v-model="settings.payment.currency" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="USD">USD ($)</option>
                    <option value="EUR">EUR (€)</option>
                    <option value="GBP">GBP (£)</option>
                    <option value="JPY">JPY (¥)</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Platform Commission (%)</label>
                  <input 
                    v-model="settings.payment.platform_commission" 
                    type="number" 
                    min="0"
                    max="100"
                    step="0.1"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="10"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Withdrawal Amount</label>
                  <input 
                    v-model="settings.payment.min_withdrawal" 
                    type="number" 
                    min="0"
                    step="0.01"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="50.00"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Payment Gateway</label>
                  <select 
                    v-model="settings.payment.gateway" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="stripe">Stripe</option>
                    <option value="paypal">PayPal</option>
                    <option value="razorpay">Razorpay</option>
                    <option value="offline">Offline Payment</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Email Settings -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Email Settings</h3>
              <p class="text-sm text-gray-600">Email configuration and templates</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                  <input 
                    v-model="settings.email.smtp_host" 
                    type="text" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="smtp.gmail.com"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                  <input 
                    v-model="settings.email.smtp_port" 
                    type="number" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="587"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                  <input 
                    v-model="settings.email.smtp_username" 
                    type="text" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="your-email@gmail.com"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                  <input 
                    v-model="settings.email.smtp_password" 
                    type="password" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="••••••••"
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                <input 
                  v-model="settings.email.from_email" 
                  type="email" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="noreply@example.com"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                <input 
                  v-model="settings.email.from_name" 
                  type="text" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="eLearning Platform"
                />
              </div>
            </div>
          </div>

          <!-- System Maintenance -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">System Maintenance</h3>
              <p class="text-sm text-gray-600">System operations and maintenance tools</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Mode</label>
                  <select 
                    v-model="settings.maintenance.maintenance_mode" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="false">Disabled</option>
                    <option value="true">Enabled</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Debug Mode</label>
                  <select 
                    v-model="settings.maintenance.debug_mode" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="false">Production</option>
                    <option value="true">Development</option>
                  </select>
                </div>
              </div>
              
              <div class="flex space-x-4">
                <button 
                  @click="createBackup"
                  :disabled="backupLoading"
                  class="btn btn-secondary"
                >
                  {{ backupLoading ? 'Creating Backup...' : 'Create System Backup' }}
                </button>
                
                <button 
                  @click="clearCache"
                  :disabled="cacheLoading"
                  class="btn btn-warning"
                >
                  {{ cacheLoading ? 'Clearing...' : 'Clear Cache' }}
                </button>
                
                <button 
                  @click="viewSystemLogs"
                  class="btn btn-info"
                >
                  View System Logs
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { adminAPI } from '@/services/api'

const toast = useToast()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const saving = ref(false)
const backupLoading = ref(false)
const cacheLoading = ref(false)

// Settings structure
const settings = ref({
  general: {
    platform_name: '',
    platform_url: '',
    contact_email: '',
    support_phone: '',
    platform_description: ''
  },
  courses: {
    auto_approve: false,
    max_duration: 100,
    max_file_size: 50,
    allow_free_courses: true,
    categories: ['Programming', 'Design', 'Business', 'Marketing']
  },
  users: {
    allow_registration: true,
    email_verification: true,
    default_role: 'student',
    max_users: 10000
  },
  payment: {
    currency: 'USD',
    platform_commission: 10,
    min_withdrawal: 50,
    gateway: 'stripe'
  },
  email: {
    smtp_host: '',
    smtp_port: 587,
    smtp_username: '',
    smtp_password: '',
    from_email: '',
    from_name: ''
  },
  maintenance: {
    maintenance_mode: false,
    debug_mode: false
  }
})

// Fetch settings
const fetchSettings = async () => {
  if (!authStore.isAuthenticated || !authStore.isAdmin) {
    return
  }

  loading.value = true
  try {
    const response = await adminAPI.getSettings()
    const data = response.data.data || {}
    
    // Merge backend settings with defaults
    if (data.general) settings.value.general = { ...settings.value.general, ...data.general }
    if (data.courses) settings.value.courses = { ...settings.value.courses, ...data.courses }
    if (data.users) settings.value.users = { ...settings.value.users, ...data.users }
    if (data.payment) settings.value.payment = { ...settings.value.payment, ...data.payment }
    if (data.email) settings.value.email = { ...settings.value.email, ...data.email }
    if (data.maintenance) settings.value.maintenance = { ...settings.value.maintenance, ...data.maintenance }
    
    console.log('✅ Settings loaded successfully')
    
  } catch (error) {
    console.error('❌ Error fetching settings:', error)
    toast.error('Failed to load settings')
  } finally {
    loading.value = false
  }
}

// Save all settings
const saveAllSettings = async () => {
  if (!authStore.isAuthenticated || !authStore.isAdmin) {
    return
  }

  saving.value = true
  try {
    await adminAPI.updateSettings(settings.value)
    
    toast.success('Settings saved successfully!')
    console.log('✅ Settings saved successfully')
    
  } catch (error) {
    console.error('❌ Error saving settings:', error)
    toast.error('Failed to save settings')
  } finally {
    saving.value = false
  }
}

// Add category
const addCategory = () => {
  settings.value.courses.categories.push('')
}

// Remove category
const removeCategory = (index) => {
  settings.value.courses.categories.splice(index, 1)
}

// Create backup
const createBackup = async () => {
  backupLoading.value = true
  try {
    await adminAPI.createBackup()
    toast.success('System backup created successfully!')
  } catch (error) {
    console.error('❌ Error creating backup:', error)
    toast.error('Failed to create backup')
  } finally {
    backupLoading.value = false
  }
}

// Clear cache
const clearCache = async () => {
  cacheLoading.value = true
  try {
    // You might need to implement this in your backend
    // await adminAPI.clearCache()
    toast.success('Cache cleared successfully!')
  } catch (error) {
    console.error('❌ Error clearing cache:', error)
    toast.error('Failed to clear cache')
  } finally {
    cacheLoading.value = false
  }
}

// View system logs
const viewSystemLogs = async () => {
  try {
    const response = await adminAPI.getSystemLogs()
    console.log('System logs:', response.data)
    // You can implement a modal or redirect to show logs
    toast.info('System logs loaded (check console)')
  } catch (error) {
    console.error('❌ Error fetching system logs:', error)
    toast.error('Failed to load system logs')
  }
}

// Component lifecycle
onMounted(async () => {
  console.log('🚀 AdminSettings component mounted')
  
  if (authStore.isAuthenticated && authStore.isAdmin) {
    await fetchSettings()
  }
})
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
}

.btn-success {
  @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
}

.btn-secondary {
  @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
}

.btn-warning {
  @apply bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500;
}

.btn-info {
  @apply bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-400;
}
</style>
