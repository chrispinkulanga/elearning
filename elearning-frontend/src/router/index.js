import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import views
import Home from '@/views/Home.vue'
import Login from '@/views/auth/Login.vue'
import Register from '@/views/auth/Register.vue'
import ForgotPassword from '@/views/auth/ForgotPassword.vue'
import VerifyOTP from '@/views/auth/VerifyOTP.vue'
import ResetPassword from '@/views/auth/ResetPassword.vue'
import EmailVerification from '@/views/auth/EmailVerification.vue'
import PaymentForm from '@/views/payment/PaymentForm.vue'
import PaymentSuccess from '@/views/payment/PaymentSuccess.vue'
import PaymentFailed from '@/views/payment/PaymentFailed.vue'
import ForumTopics from '@/views/forum/ForumTopics.vue'
import ForumTopicDetail from '@/views/forum/ForumTopicDetail.vue'
import ForumTopicEdit from '@/views/forum/ForumTopicEdit.vue'
import ForumTopicCreate from '@/views/forum/ForumTopicCreate.vue'
import ForumManage from '@/views/forum/ForumManage.vue'
import ForumSearch from '@/views/forum/ForumSearch.vue'
import Dashboard from '@/views/Dashboard.vue'
import Courses from '@/views/courses/Courses.vue'
import CourseDetail from '@/views/courses/CourseDetail.vue'
import CourseCreate from '@/views/courses/CourseCreate.vue'
import CourseEdit from '@/views/courses/CourseEdit.vue'
import CourseLessons from '@/views/courses/CourseLessons.vue'
import CourseList from '@/views/courses/CourseList.vue'
import MyCourses from '@/views/courses/MyCourses.vue'
import Profile from '@/views/Profile.vue'
import Notifications from '@/views/Notifications.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import InstructorDashboard from '@/views/instructor/InstructorDashboard.vue'
import InstructorLayout from '@/views/instructor/InstructorLayout.vue'
import StudentDashboard from '@/views/student/StudentDashboard.vue'
import CategoryCreate from '@/views/categories/CategoryCreate.vue'
import NotFound from '@/views/NotFound.vue'
import CourseBuilder from '@/views/courses/CourseBuilder.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'Home' }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { title: 'Login', guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { title: 'Register', guest: true }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { title: 'Forgot Password', guest: true }
  },
  {
    path: '/verify-otp',
    name: 'VerifyOTP',
    component: VerifyOTP,
    meta: { title: 'Verify OTP', guest: true }
  },
  {
    path: '/email-verification',
    name: 'EmailVerification',
    component: EmailVerification,
    meta: { title: 'Email Verification' }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { title: 'Reset Password', guest: true }
  },
  {
    path: '/payment/:slug',
    name: 'PaymentForm',
    component: PaymentForm,
    meta: { title: 'Payment', requiresAuth: true }
  },
  {
    path: '/payment/success',
    name: 'PaymentSuccess',
    component: PaymentSuccess,
    meta: { title: 'Payment Successful' }
  },
  {
    path: '/payment/failed',
    name: 'PaymentFailed',
    component: PaymentFailed,
    meta: { title: 'Payment Failed' }
  },
  {
    path: '/forum',
    name: 'ForumTopics',
    component: ForumTopics,
    meta: { title: 'Forum' }
  },
  {
    path: '/forum/topics/create',
    name: 'ForumTopicCreate',
    component: ForumTopicCreate,
    meta: { title: 'Create Topic', requiresAuth: true }
  },
  {
    path: '/forum/topics/:id',
    name: 'ForumTopicDetail',
    component: ForumTopicDetail,
    meta: { title: 'Topic Detail' }
  },
  {
    path: '/forum/topics/:id/edit',
    name: 'ForumTopicEdit',
    component: ForumTopicEdit,
    meta: { title: 'Edit Topic', requiresAuth: true }
  },

  {
    path: '/forum/search',
    name: 'ForumSearch',
    component: ForumSearch,
    meta: { title: 'Search Results' }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/Profile.vue'),
    meta: { title: 'Profile', requiresAuth: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { title: 'Dashboard', requiresAuth: true },
    beforeEnter: async (to, from, next) => {
      console.log('Dashboard route: beforeEnter guard executing...')
      const authStore = useAuthStore()
      
      console.log('Dashboard route: Auth state in guard:', {
        isAuthenticated: authStore.isAuthenticated,
        user: authStore.user,
        userRole: authStore.userRole,
        isAdmin: authStore.isAdmin,
        isInstructor: authStore.isInstructor,
        isStudent: authStore.isStudent
      })
      
      // Ensure user data is loaded
      if (authStore.isAuthenticated && !authStore.user) {
        console.log('Dashboard route: Fetching user data...')
        try {
          await authStore.fetchUser()
        } catch (error) {
          console.error('Dashboard route: Failed to fetch user data:', error)
          next('/login')
          return
        }
      }
      
      // Redirect to role-specific dashboard
      if (authStore.isAdmin) {
        console.log('Dashboard route: Redirecting admin to /admin')
        next('/admin')
      } else if (authStore.isInstructor) {
        console.log('Dashboard route: Redirecting instructor to /instructor')
        next('/instructor')
      } else if (authStore.isStudent) {
        console.log('Dashboard route: Redirecting student to /student-dashboard')
        next('/student-dashboard')
      } else {
        console.log('Dashboard route: No specific role, staying on generic dashboard')
        next()
      }
    }
  },
  {
    path: '/courses',
    name: 'Courses',
    component: Courses,
    meta: { title: 'Courses' }
  },
  {
    path: '/courses/list',
    name: 'CourseList',
    component: CourseList,
    meta: { title: 'Browse Courses' }
  },
  {
    path: '/courses/:slug',
    name: 'CourseDetail',
    component: CourseDetail,
    meta: { title: 'Course Detail' }
  },
  {
    path: '/courses/:slug/lessons',
    name: 'CourseLessons',
    component: CourseLessons,
    meta: { title: 'Course Lessons', requiresAuth: true }
  },
  {
    path: '/courses/:slug/edit',
    name: 'CourseEdit',
    component: CourseEdit,
    meta: { title: 'Edit Course', requiresAuth: true, requiresInstructor: true }
  },
  {
    path: '/my-courses',
    name: 'MyCourses',
    component: MyCourses,
    meta: { title: 'My Learning', requiresAuth: true }
  },
  {
    path: '/course-builder',
    name: 'CourseBuilder',
    component: CourseBuilder,
    meta: { title: 'Course Builder', requiresAuth: true, requiresInstructor: true }
  },
  {
    path: '/courses/:courseId/builder',
    name: 'CourseBuilder',
    component: CourseBuilder,
    meta: { title: 'Course Builder', requiresAuth: true, requiresInstructor: true }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: Notifications,
    meta: { title: 'Notifications', requiresAuth: true }
  },
  {
    path: '/alumni/invitation/:id',
    name: 'AlumniInvitation',
    component: () => import('@/views/AlumniInvitation.vue'),
    meta: { title: 'Alumni Invitation', requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'GeneralProfile',
    component: Profile,
    meta: { title: 'Profile', requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'AdminLayout',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { title: 'Admin Panel', requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { title: 'Admin Dashboard', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'users',
        name: 'AdminUsers',
        component: () => import('@/views/admin/AdminUsers.vue'),
        meta: { title: 'Users Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'courses',
        name: 'AdminCourses',
        component: () => import('@/views/admin/AdminCourses.vue'),
        meta: { title: 'Courses Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'categories',
        name: 'AdminCategories',
        component: () => import('@/views/admin/AdminCategories.vue'),
        meta: { title: 'Categories Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'forum',
        name: 'AdminForum',
        component: () => import('@/views/forum/ForumManage.vue'),
        meta: { title: 'Forum Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'forum-categories',
        name: 'AdminForumCategories',
        component: () => import('@/views/admin/AdminForumCategories.vue'),
        meta: { title: 'Forum Categories Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'enrollments',
        name: 'AdminEnrollments',
        component: () => import('@/views/admin/AdminEnrollments.vue'),
        meta: { title: 'Enrollments Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'analytics',
        name: 'AdminAnalytics',
        component: () => import('@/views/admin/AdminAnalytics.vue'),
        meta: { title: 'Analytics', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'alumni',
        name: 'AdminAlumni',
        component: () => import('@/views/admin/AdminAlumni.vue'),
        meta: { title: 'Alumni Management', requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'settings',
        name: 'AdminSettings',
        component: () => import('@/views/admin/AdminSettings.vue'),
        meta: { title: 'Admin Settings', requiresAuth: true, requiresAdmin: true }
      }
    ]
  },
  {
    path: '/instructor',
    component: InstructorLayout,
    meta: { requiresAuth: true, requiresInstructor: true },
    children: [
      { path: '', name: 'InstructorDashboard', component: InstructorDashboard, meta: { title: 'Instructor Dashboard' } },
      { path: 'courses', name: 'InstructorCourses', component: () => import('@/views/courses/MyCourses.vue'), meta: { title: 'My Courses' } },
      { path: 'students', name: 'InstructorStudents', component: () => import('@/views/instructor/InstructorStudents.vue'), meta: { title: 'Enrolled Students' } },
      { path: 'create-course', name: 'CourseCreate', component: () => import('@/views/courses/CourseCreate.vue'), meta: { title: 'Create Course' } },
      { path: 'create-category', name: 'CategoryCreate', component: () => import('@/views/categories/CategoryCreate.vue'), meta: { title: 'Create Category' } },
      { path: 'analytics', name: 'InstructorAnalytics', component: () => import('@/views/instructor/InstructorAnalytics.vue'), meta: { title: 'Analytics' } },
      { path: 'profile', name: 'Profile', component: () => import('@/views/Profile.vue'), meta: { title: 'Profile Settings' } },
    ]
  },
  {
    path: '/student-dashboard',
    name: 'StudentDashboard',
    component: StudentDashboard,
    meta: { title: 'Student Dashboard', requiresAuth: true, requiresStudent: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: { title: 'Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  console.log('Router guard: Navigating to', to.path)
  console.log('Router guard: From', from.path)
  
  // Set page title
  document.title = to.meta.title ? `${to.meta.title} - eLearning Platform` : 'eLearning Platform'

  const authStore = useAuthStore()
  
  console.log('Router guard: Auth state:', {
    isAuthenticated: authStore.isAuthenticated,
    userRole: authStore.userRole,
    isAdmin: authStore.isAdmin,
    isInstructor: authStore.isInstructor,
    isStudent: authStore.isStudent,
    user: authStore.user
  })

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    console.log('Router guard: Redirecting to login - not authenticated')
    next('/login')
    return
  }

  // Check if route is for guests only (login, register, etc.)
  if (to.meta.guest && authStore.isAuthenticated) {
    console.log('Router guard: Redirecting to appropriate dashboard - authenticated user on guest route')
    
    // Redirect to appropriate dashboard based on user role
    if (authStore.isInstructor) {
      next('/instructor')
    } else if (authStore.isStudent) {
      next('/student-dashboard')
    } else if (authStore.isAdmin) {
      next('/admin')
    } else {
      next('/dashboard')
    }
    return
  }

  // Check role-based access
  if (to.meta.requiresInstructor && !authStore.isInstructor) {
    console.log('Router guard: Redirecting to appropriate dashboard - not instructor')
    console.log('Router guard: User role is:', authStore.userRole)
    console.log('Router guard: Is instructor check:', authStore.isInstructor)
    
    // Redirect to appropriate dashboard based on user role
    if (authStore.isStudent) {
      next('/student-dashboard')
    } else if (authStore.isAdmin) {
      next('/admin')
    } else {
      next('/dashboard')
    }
    return
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    console.log('Router guard: Redirecting to appropriate dashboard - not admin')
    console.log('Router guard: User role is:', authStore.userRole)
    console.log('Router guard: Is admin check:', authStore.isAdmin)
    
    // Redirect to appropriate dashboard based on user role
    if (authStore.isInstructor) {
      next('/instructor')
    } else if (authStore.isStudent) {
      next('/student-dashboard')
    } else {
      next('/dashboard')
    }
    return
  }

  if (to.meta.requiresStudent && !authStore.isStudent) {
    console.log('Router guard: Redirecting to appropriate dashboard - not student')
    console.log('Router guard: User role is:', authStore.userRole)
    console.log('Router guard: Is student check:', authStore.isStudent)
    
    // Redirect to appropriate dashboard based on user role
    if (authStore.isInstructor) {
      next('/instructor')
    } else if (authStore.isAdmin) {
      next('/admin')
    } else {
      next('/dashboard')
    }
    return
  }

  // If user is authenticated but doesn't have user data, fetch it
  if (authStore.isAuthenticated && !authStore.user) {
    console.log('Router guard: Fetching user data')
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.error('Error fetching user data:', error)
      authStore.logout()
      next('/login')
      return
    }
  }

  // Debug: Log the final auth state before navigation
  console.log('Router guard: Final auth state before navigation:', {
    isAuthenticated: authStore.isAuthenticated,
    userRole: authStore.userRole,
    isAdmin: authStore.isAdmin,
    isInstructor: authStore.isInstructor,
    isStudent: authStore.isStudent,
    user: authStore.user,
    token: authStore.token
  })

  console.log('Router guard: Allowing navigation to', to.path)
  next()
})

export default router 