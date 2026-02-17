<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
          <p class="text-gray-600 mt-1">Manage your account information and preferences</p>
        </div>

        <div class="p-6">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          </div>

          <div v-else class="space-y-8">
            <!-- Profile Information -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
              <form @submit.prevent="updateProfile" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input
                      v-model="form.name"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      :class="{ 'border-red-500': errors.name }"
                    />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                      v-model="form.email"
                      type="email"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      :class="{ 'border-red-500': errors.phone }"
                    />
                    <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <input
                      :value="authStore.userRole"
                      type="text"
                      disabled
                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                  <textarea
                    v-model="form.bio"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    :class="{ 'border-red-500': errors.bio }"
                    placeholder="Tell us about yourself..."
                  ></textarea>
                  <p v-if="errors.bio" class="text-red-500 text-xs mt-1">{{ errors.bio }}</p>
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="updating"
                    class="btn btn-primary"
                  >
                    <span v-if="updating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                    {{ updating ? 'Updating...' : 'Update Profile' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Change Password -->
            <div class="border-t border-gray-200 pt-8">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
              <form @submit.prevent="changePassword" @submit="console.log('Form submit event triggered!')" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input
                      v-model="passwordForm.current_password"
                      type="password"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      :class="{ 'border-red-500': passwordErrors.current_password }"
                    />
                    <p v-if="passwordErrors.current_password" class="text-red-500 text-xs mt-1">{{ passwordErrors.current_password }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input
                      v-model="passwordForm.new_password"
                      type="password"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      :class="{ 'border-red-500': passwordErrors.new_password }"
                    />
                    <p v-if="passwordErrors.new_password" class="text-red-500 text-xs mt-1">{{ passwordErrors.new_password }}</p>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                  <input
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    :class="{ 'border-red-500': passwordErrors.new_password_confirmation }"
                  />
                  <p v-if="passwordErrors.new_password_confirmation" class="text-red-500 text-xs mt-1">{{ passwordErrors.new_password_confirmation }}</p>
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="changingPassword"
                    class="btn btn-secondary"
                    @click="console.log('Button clicked!', { changingPassword: changingPassword, formData: passwordForm })"
                  >
                    <span v-if="changingPassword" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                    {{ changingPassword ? 'Changing...' : 'Change Password' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Account Settings -->
            <div class="border-t border-gray-200 pt-8">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Account Settings</h3>
              <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                    <p class="text-sm text-gray-500">Receive email notifications about course updates and announcements</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      v-model="settings.emailNotifications"
                      type="checkbox"
                      class="sr-only peer"
                      @change="updateSettings"
                    />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                  </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Push Notifications</h4>
                    <p class="text-sm text-gray-500">Receive push notifications on your device</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      v-model="settings.pushNotifications"
                      type="checkbox"
                      class="sr-only peer"
                      @change="updateSettings"
                    />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Danger Zone -->
            <div class="border-t border-gray-200 pt-8">
              <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
              <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-red-900">Delete Account</h4>
                    <p class="text-sm text-red-700">Permanently delete your account and all associated data</p>
                  </div>
                  <button
                    @click="showDeleteModal = true"
                    class="btn btn-danger"
                  >
                    Delete Account
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Account</h3>
        <p class="text-sm text-gray-600 mb-6">
          Are you sure you want to delete your account? This action cannot be undone and will permanently remove all your data.
        </p>
        <div class="flex space-x-3">
          <button
            @click="showDeleteModal = false"
            class="btn btn-secondary flex-1"
          >
            Cancel
          </button>
          <button
            @click="deleteAccount"
            :disabled="deleting"
            class="btn btn-danger flex-1"
          >
            <span v-if="deleting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
            {{ deleting ? 'Deleting...' : 'Delete Account' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { userAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const updating = ref(false)
const changingPassword = ref(false)
const deleting = ref(false)
const showDeleteModal = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  bio: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const settings = reactive({
  emailNotifications: true,
  pushNotifications: true
})

const errors = reactive({})
const passwordErrors = reactive({})

const loadUserData = async () => {
  loading.value = true
  
  // First, try to use cached data from auth store for immediate display
  const user = authStore.user
  if (user) {
    form.name = user.name || ''
    form.email = user.email || ''
    form.phone = user.phone || ''
    form.bio = user.bio || ''
  }
  
  // Then try to fetch fresh data from backend in background
  try {
    const response = await userAPI.getProfile()
    if (response.data.status === 'success') {
      const userData = response.data.data
      form.name = userData.name || ''
      form.email = userData.email || ''
      form.phone = userData.phone || ''
      form.bio = userData.bio || ''
      
      // Update auth store with fresh data, preserving existing role information
      const currentUser = authStore.user
      const updatedUser = {
        ...userData,
        // Preserve existing role information if not provided in response
        roles: userData.roles || currentUser?.roles || [],
        role: userData.role || currentUser?.role || 'student'
      }
      authStore.user = updatedUser
    }
  } catch (error) {
    // Silently fail - user already has data from auth store
    console.log('Could not fetch fresh profile data, using cached data')
  } finally {
    loading.value = false
  }
}

const updateProfile = async () => {
  updating.value = true
  errors.value = {}
  
  try {
    const response = await userAPI.updateProfile(form)
    if (response.data.status === 'success') {
      const userData = response.data.data
      
      // Update auth store with fresh data, preserving existing role information
      const currentUser = authStore.user
      const updatedUser = {
        ...userData,
        // Preserve existing role information if not provided in response
        roles: userData.roles || currentUser?.roles || [],
        role: userData.role || currentUser?.role || 'student'
      }
      authStore.user = updatedUser
    }
    toast.success('Profile updated successfully')
  } catch (error) {
    console.error('Profile update error:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to update profile')
    }
  } finally {
    updating.value = false
  }
}

const changePassword = async () => {
  console.log('changePassword function called!')
  console.log('changingPassword.value:', changingPassword.value)
  
  // Prevent multiple submissions
  if (changingPassword.value) {
    console.log('Already processing, ignoring duplicate submission')
    return
  }
  
  console.log('Starting password change process...')
  changingPassword.value = true
  passwordErrors.value = {}
  
  // Client-side validation
  const errors = {}
  
  if (!passwordForm.current_password) {
    errors.current_password = 'Current password is required'
  }
  
  if (!passwordForm.new_password) {
    errors.new_password = 'New password is required'
  } else if (passwordForm.new_password.length < 8) {
    errors.new_password = 'Password must be at least 8 characters'
  }
  
  if (!passwordForm.new_password_confirmation) {
    errors.new_password_confirmation = 'Password confirmation is required'
  } else if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    errors.new_password_confirmation = 'Passwords do not match'
  }
  
  // If there are validation errors, show them and stop
  if (Object.keys(errors).length > 0) {
    passwordErrors.value = errors
    changingPassword.value = false
    return
  }
  
  try {
    await userAPI.changePassword(passwordForm)
    toast.success('Password changed successfully')
    // Clear form
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''
  } catch (error) {
    console.error('Password change error:', error)
    if (error.response?.data?.errors) {
      passwordErrors.value = error.response.data.errors
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to change password')
    }
  } finally {
    changingPassword.value = false
  }
}

const updateSettings = async () => {
  try {
    await userAPI.updateSettings(settings)
    toast.success('Settings updated successfully')
  } catch (error) {
    toast.error('Failed to update settings')
  }
}

const deleteAccount = async () => {
  deleting.value = true
  try {
    await userAPI.deleteAccount()
    await authStore.logout()
    toast.success('Account deleted successfully')
    router.push('/')
  } catch (error) {
    toast.error('Failed to delete account')
  } finally {
    deleting.value = false
    showDeleteModal.value = false
  }
}

onMounted(() => {
  loadUserData()
})
</script> 