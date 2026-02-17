<template>
  <div class="min-h-screen bg-gray-50">
    <div class="flex">
      <!-- Sidebar -->
      <aside :class="[
        'fixed inset-0 left-0 z-40 w-64 bg-white shadow-sm transform transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]">
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Admin Menu</h2>
          <button @click="sidebarOpen = false" class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <nav class="pt-4 px-2">
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
      <main class="flex-1" @click="closeSidebarOnMobile">
        <!-- Mobile Menu Toggle Bar -->
        <div class="md:hidden bg-white border-b border-gray-200 px-4 py-3">
          <button
            @click.stop="sidebarOpen = !sidebarOpen"
            class="flex items-center text-gray-600 hover:text-gray-900"
          >
            <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="text-sm font-medium">Menu</span>
          </button>
        </div>
        
        <div class="p-4 sm:p-6 lg:p-8">
          <router-view />
        </div>
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
  UsersIcon,
  AcademicCapIcon,
  TagIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  BellIcon,
  ChatBubbleLeftRightIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()
const userMenuOpen = ref(false)
const sidebarOpen = ref(false)
const unreadNotifications = ref(0)

const user = computed(() => authStore.user)

const currentPageTitle = computed(() => {
  const route = router.currentRoute.value
  return route.meta?.title || 'Admin Panel'
})

import appConfig from '@/config/app.config'

const apiUrl = computed(() => {
  return appConfig.apiUrl
})

const navigation = [
  { name: 'Dashboard', href: '/admin', icon: HomeIcon },
  { name: 'Users', href: '/admin/users', icon: UsersIcon },
  { name: 'Courses', href: '/admin/courses', icon: AcademicCapIcon },
  { name: 'Categories', href: '/admin/categories', icon: TagIcon },
  { name: 'Forum Management', href: '/admin/forum', icon: ChatBubbleLeftRightIcon },
  { name: 'Forum Categories', href: '/admin/forum-categories', icon: TagIcon },
  { name: 'Enrollments', href: '/admin/enrollments', icon: ClipboardDocumentListIcon },
  { name: 'Alumni Management', href: '/admin/alumni', icon: UserGroupIcon },
  { name: 'Analytics', href: '/admin/analytics', icon: ChartBarIcon },
  { name: 'Settings', href: '/admin/settings', icon: Cog6ToothIcon },
]

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const closeSidebarOnMobile = () => {
  if (window.innerWidth < 768 && sidebarOpen.value) {
    sidebarOpen.value = false
  }
}

// Close user menu when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      userMenuOpen.value = false
    }
  })
})
</script>
