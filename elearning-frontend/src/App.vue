<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <!-- Logo -->
            <router-link to="/" class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <span class="text-xl font-bold text-gray-900">eLearning</span>
            </router-link>
          </div>

          <!-- Desktop Navigation -->
          <div class="hidden md:flex items-center space-x-8">
            <router-link to="/" class="text-gray-600 hover:text-gray-900">Home</router-link>
            <router-link to="/courses" class="text-gray-600 hover:text-gray-900">Courses</router-link>
            <router-link to="/forum" class="text-gray-600 hover:text-gray-900">Forum</router-link>
            
            <!-- Authenticated User Menu -->
            <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
              <NotificationDropdown />
              
              <!-- Role-specific navigation -->
              <router-link 
                v-if="authStore.isAdmin" 
                to="/admin" 
                class="text-gray-600 hover:text-gray-900"
              >
                Admin Panel
              </router-link>
              <router-link 
                v-else-if="authStore.isInstructor" 
                to="/instructor" 
                class="text-gray-600 hover:text-gray-900"
              >
                Instructor Dashboard
              </router-link>
              <router-link 
                v-else 
                to="/dashboard" 
                class="text-gray-600 hover:text-gray-900"
              >
                Dashboard
              </router-link>
              
              <router-link to="/my-courses" class="text-gray-600 hover:text-gray-900">My Learning</router-link>
              <router-link 
                :to="authStore.isInstructor ? '/instructor/profile' : '/profile'" 
                class="text-gray-600 hover:text-gray-900"
              >
                Profile
              </router-link>
              <button @click="logout" class="text-gray-600 hover:text-gray-900">Logout</button>
            </div>

            <!-- Guest User Menu -->
            <div v-else class="flex items-center space-x-4">
              <router-link to="/login" class="text-gray-600 hover:text-gray-900">Login</router-link>
              <router-link to="/register" class="btn btn-primary">Sign Up</router-link>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="md:hidden flex items-center">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-md p-2"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile Menu Overlay (backdrop) -->
    <div 
      v-if="mobileMenuOpen" 
      class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
      @click="mobileMenuOpen = false"
    ></div>

    <!-- Mobile Navigation -->
    <div v-if="mobileMenuOpen" class="fixed left-0 right-0 top-16 bg-white z-50 shadow-lg md:hidden max-h-[calc(100vh-4rem)] overflow-y-auto">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <router-link
          to="/"
          class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
          @click="mobileMenuOpen = false"
        >
          Home
        </router-link>
            <router-link
              to="/courses"
              class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
              @click="mobileMenuOpen = false"
            >
              Courses
            </router-link>
            <router-link
              to="/forum"
              class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
              @click="mobileMenuOpen = false"
            >
              Forum
            </router-link>

            <!-- Authenticated User Mobile Menu -->
            <div v-if="authStore.isAuthenticated" class="space-y-1">
              <!-- Role-specific mobile navigation -->
              <router-link
                v-if="authStore.isAdmin"
                to="/admin"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                Admin Panel
              </router-link>
              <router-link
                v-else-if="authStore.isInstructor"
                to="/instructor"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                Instructor Dashboard
              </router-link>
              <router-link
                v-else
                to="/dashboard"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                Dashboard
              </router-link>
              <router-link
                to="/my-courses"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                My Learning
              </router-link>
              <router-link
                :to="authStore.isInstructor ? '/instructor/profile' : '/profile'"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                Profile
              </router-link>
              <button
                @click="logout"
                class="block w-full text-left px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
              >
                Logout
              </button>
            </div>

            <!-- Guest User Mobile Menu -->
            <div v-else class="space-y-1">
              <router-link
                to="/login"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                Login
              </router-link>
              <router-link
                to="/register"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"
                @click="mobileMenuOpen = false"
              >
                Sign Up
              </router-link>
            </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="pt-16">
      <router-view />
    </main>

    <!-- Toast Notifications -->
    <ToastContainer />

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="col-span-1">
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <span class="text-xl font-bold text-gray-900">eLearning</span>
            </div>
            <p class="text-gray-600 mb-4">
              Empowering learners worldwide with quality education and interactive learning experiences.
            </p>
            <div class="flex space-x-4">
              <a href="#" class="text-gray-400 hover:text-gray-600">
                <span class="sr-only">Facebook</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-600">
                <span class="sr-only">Twitter</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-gray-600">
                <span class="sr-only">LinkedIn</span>
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"></path>
                </svg>
              </a>
            </div>
          </div>

          <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-8">
            <div>
              <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Platform</h3>
              <ul class="space-y-2">
                <li><router-link to="/courses" class="text-gray-600 hover:text-gray-900">Browse Courses</router-link></li>
                <li><router-link to="/forum" class="text-gray-600 hover:text-gray-900">Community Forum</router-link></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Learning Paths</a></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Certificates</a></li>
              </ul>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Support</h3>
              <ul class="space-y-2">
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Help Center</a></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Contact Us</a></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Privacy Policy</a></li>
                <li><a href="#" class="text-gray-600 hover:text-gray-900">Terms of Service</a></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="mt-8 pt-8 border-t border-gray-200">
          <p class="text-gray-400 text-sm text-center">
            © 2024 eLearning Platform. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NotificationDropdown from '@/components/NotificationDropdown.vue'
import ToastContainer from '@/components/ToastContainer.vue'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const logout = async () => {
  mobileMenuOpen.value = false
  await authStore.logout()
  router.push('/')
}
</script> 