<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header removed - using main App.vue header instead -->

    <div class="flex">
      <!-- Mobile sidebar overlay -->
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 md:hidden" @click="sidebarOpen = false">
        <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
      </div>

      <!-- Sidebar -->
      <aside :class="[
        'fixed inset-0 left-0 z-40 w-64 bg-white shadow-sm transform transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]">
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 md:hidden">
          <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
          <button @click="sidebarOpen = false" class="p-2 rounded-md text-gray-400 hover:text-gray-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <nav class="pt-20 px-2">
          <div class="space-y-1">
            <router-link
              v-for="item in navigation"
              :key="item.name"
              :to="item.href"
              @click="sidebarOpen = false"
              :class="[
                $route.path === item.href
                  ? 'bg-primary-100 border-primary-500 text-primary-700'
                  : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                'group flex items-center px-3 py-2 text-sm font-medium border-l-4'
              ]"
            >
              <component
                :is="item.icon"
                :class="[
                  $route.path === item.href ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500',
                  'mr-3 flex-shrink-0 h-6 w-6'
                ]"
              />
              {{ item.name }}
            </router-link>
          </div>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon,
  AcademicCapIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  TagIcon,
  UsersIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()
const userMenuOpen = ref(false)
const sidebarOpen = ref(false)

const user = computed(() => authStore.user)

const currentPageTitle = computed(() => {
  const route = router.currentRoute.value
  return route.meta?.title || 'Instructor Panel'
})

import appConfig from '@/config/app.config'

const apiUrl = computed(() => {
  return appConfig.apiUrl
})

const navigation = [
  { name: 'Dashboard', href: '/instructor', icon: HomeIcon },
  { name: 'My Courses', href: '/instructor/courses', icon: AcademicCapIcon },
  { name: 'Enrolled Students', href: '/instructor/students', icon: UsersIcon },
  { name: 'Create Course', href: '/instructor/create-course', icon: ClipboardDocumentListIcon },
  { name: 'Categories', href: '/instructor/create-category', icon: TagIcon },
  { name: 'Analytics', href: '/instructor/analytics', icon: ChartBarIcon },
  { name: 'Settings', href: '/instructor/profile', icon: Cog6ToothIcon },
]

const logout = () => {
  authStore.logout()
  router.push('/login')
}

onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      userMenuOpen.value = false
    }
  })
})
</script>


