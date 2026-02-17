<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Instructor Dashboard</h1>
        <p class="mt-2 text-gray-600">Manage your courses and track your performance</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Courses</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalCourses }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Students</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.totalStudents }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">New This Month</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.newStudentsThisMonth }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Total Revenue</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">${{ stats.totalRevenue }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Completion Rate</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.completionRate }}%</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-2 sm:ml-4">
              <p class="text-xs sm:text-sm font-medium text-gray-600">Avg Rating</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ stats.averageRating }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="mb-8">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8">
            <button
              @click="activeTab = 'overview'"
              :class="[
                activeTab === 'overview'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              Overview
            </button>
            <button
              @click="activeTab = 'students'"
              :class="[
                activeTab === 'students'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              Enrolled Students
            </button>
            <button
              @click="activeTab = 'analytics'"
              :class="[
                activeTab === 'analytics'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              Analytics
            </button>
          </nav>
        </div>
      </div>

      <!-- Tab Content -->
      <div v-if="activeTab === 'overview'">
        <!-- Overview Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Recent Courses -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm">
              <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900">Recent Courses</h3>
                  <div class="flex items-center space-x-2">
                    <router-link 
                      to="/instructor/create-course" 
                      class="btn btn-primary flex items-center"
                      title="Create Course"
                    >
                      <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span class="hidden sm:inline">Create Course</span>
                    </router-link>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <div v-if="loading" class="flex justify-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                </div>

                <div v-else-if="recentCourses.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div
                    v-for="course in recentCourses"
                    :key="course.id"
                    class="flex flex-col space-y-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                  >
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0 w-16 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                        <img
                          v-if="course.image"
                          :src="course.image"
                          :alt="course.title"
                          class="w-full h-full object-cover rounded-lg"
                        />
                        <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                      </div>

                      <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ course.title }}</h4>
                        <p class="text-sm text-gray-500">{{ course.enrollments_count || 0 }} students</p>
                        <div class="flex items-center mt-1">
                          <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm text-gray-600 ml-1">{{ course.average_rating || 0 }}</span>
                          </div>
                          <span class="mx-2 text-gray-300">•</span>
                          <span class="text-sm text-gray-500">{{ course.price === 0 || course.price === null || course.price === undefined ? 'Free' : `$${course.price}` }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="flex items-center gap-1 justify-center flex-wrap">
                      <router-link
                        :to="`/courses/${course.id}/builder`"
                        class="btn btn-sm btn-primary flex items-center flex-1 min-w-0 justify-center"
                        title="Course Builder"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0l1.403 5.777c.194.8.8 1.406 1.6 1.6l5.777 1.403c1.756.426 1.756 2.924 0 3.35l-5.777 1.403c-.8.194-1.406.8-1.6 1.6l-1.403 5.777c-.426 1.756-2.924 1.756-3.35 0l-1.403-5.777c-.194-.8-.8-1.406-1.6-1.6l-5.777-1.403c-1.756-.426-1.756-2.924 0-3.35l5.777-1.403c.8-.194 1.406-.8 1.6-1.6l1.403-5.777z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </router-link>
                      <router-link
                        :to="`/courses/${course.slug}/edit`"
                        class="btn btn-sm btn-secondary flex items-center flex-1 min-w-0 justify-center"
                        title="Edit Course"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </router-link>
                      <router-link
                        :to="`/courses/${course.slug}`"
                        class="btn btn-sm btn-outline flex items-center flex-1 min-w-0 justify-center"
                        title="View Course"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </router-link>
                    </div>
                  </div>
                </div>

                <div v-else class="text-center py-8">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No courses yet</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by creating your first course.</p>
                  <div class="mt-6">
                    <router-link to="/instructor/create-course" class="btn btn-primary">
                      Create Your First Course
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Enrollments -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Enrollments</h3>
              </div>
              <div class="p-6">
                <div v-if="loading" class="flex justify-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                </div>

                <div v-else-if="recentEnrollments.length > 0" class="space-y-4">
                  <div
                    v-for="enrollment in recentEnrollments"
                    :key="enrollment.id"
                    class="flex items-center space-x-3"
                  >
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                      <span class="text-sm font-medium text-primary-600">
                        {{ enrollment.student?.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ enrollment.student?.name }}</p>
                      <p class="text-xs text-gray-500">{{ enrollment.course?.title }}</p>
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ formatDate(enrollment.created_at) }}
                    </div>
                  </div>
                </div>

                <div v-else class="text-center py-8">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No enrollments yet</h3>
                  <p class="mt-1 text-sm text-gray-500">Students will appear here once they enroll in your courses.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Students Tab -->
      <div v-else-if="activeTab === 'students'">
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3">Enrolled Students</h3>
            <div class="grid grid-cols-2 gap-2 sm:gap-4">
              <div class="relative">
                <input
                  v-model="studentSearch"
                  type="text"
                  placeholder="Search students..."
                  class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  @input="searchStudents"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
              </div>
              <select
                v-model="selectedCourse"
                @change="filterStudents"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">All Courses</option>
                <option v-for="course in availableCourses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
            </div>
          </div>
          <div class="p-4 sm:p-6">
            <div v-if="studentsLoading" class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            </div>

            <div v-else-if="enrolledStudents.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="enrollment in enrolledStudents" :key="enrollment.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-primary-600">
                              {{ enrollment.student?.name?.charAt(0)?.toUpperCase() }}
                            </span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ enrollment.student?.name }}</div>
                          <div class="text-sm text-gray-500">{{ enrollment.student?.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ enrollment.course?.title }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(enrollment.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                          <div 
                            class="bg-primary-600 h-2 rounded-full" 
                            :style="{ width: `${enrollment.progress_percentage || 0}%` }"
                          ></div>
                        </div>
                        <span class="text-sm text-gray-600">{{ enrollment.progress_percentage || 0 }}%</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="[
                          enrollment.status === 'active' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-gray-100 text-gray-800',
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full'
                        ]"
                      >
                        {{ enrollment.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else class="text-center py-8">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
              <p class="mt-1 text-sm text-gray-500">No students have enrolled in your courses yet.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics Tab -->
      <div v-else-if="activeTab === 'analytics'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Course Performance Chart -->
          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Course Performance</h3>
            </div>
            <div class="p-6">
              <div v-if="analyticsLoading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>
              <div v-else-if="coursePerformance.length > 0" class="space-y-4">
                <div
                  v-for="course in coursePerformance.slice(0, 5)"
                  :key="course.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                >
                  <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900">{{ course.title }}</h4>
                    <p class="text-xs text-gray-500">{{ course.category }}</p>
                  </div>
                  <div class="flex items-center space-x-4 text-sm">
                    <div class="text-center">
                      <div class="font-medium text-gray-900">{{ course.enrollments }}</div>
                      <div class="text-gray-500">Enrollments</div>
                    </div>
                    <div class="text-center">
                      <div class="font-medium text-gray-900">{{ course.completion_rate }}%</div>
                      <div class="text-gray-500">Completion</div>
                    </div>
                    <div class="text-center">
                      <div class="font-medium text-gray-900">${{ course.revenue }}</div>
                      <div class="text-gray-500">Revenue</div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8">
                <p class="text-gray-500">No course performance data available</p>
              </div>
            </div>
          </div>

          <!-- Revenue Overview -->
          <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Revenue Overview</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">${{ stats.totalRevenue }}</div>
                  <div class="text-sm text-gray-500">Total Revenue</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">${{ stats.monthlyRevenue }}</div>
                  <div class="text-sm text-gray-500">This Month</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-6">
        <router-link
          to="/instructor/create-course"
          class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Create Course</h3>
              <p class="text-sm text-gray-600">Start building your next course</p>
            </div>
          </div>
        </router-link>

        <router-link
          to="/my-courses"
          class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Manage Courses</h3>
              <p class="text-sm text-gray-600">Edit and update your courses</p>
            </div>
          </div>
        </router-link>

        <router-link
          to="/instructor/analytics"
          class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Analytics</h3>
              <p class="text-sm text-gray-600">View detailed performance metrics</p>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useCourseStore } from '@/stores/courses'
import { instructorAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

const courseStore = useCourseStore()
const toast = useToast()

// Tab management
const activeTab = ref('overview')

// Loading states
const loading = ref(false)
const studentsLoading = ref(false)
const analyticsLoading = ref(false)

// Data
const stats = ref({
  totalCourses: 0,
  totalStudents: 0,
  newStudentsThisMonth: 0,
  totalRevenue: 0,
  monthlyRevenue: 0,
  totalEnrollments: 0,
  completionRate: 0,
  averageRating: 0
})
const recentCourses = ref([])
const recentEnrollments = ref([])
const enrolledStudents = ref([])
const coursePerformance = ref([])
const availableCourses = ref([])

// Filters
const studentSearch = ref('')
const selectedCourse = ref('')

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const fetchDashboardData = async () => {
  loading.value = true
  try {
    console.log('🔄 Fetching dashboard data...')
    
    const [statsSettled, coursesSettled, enrollmentsSettled] = await Promise.allSettled([
      instructorAPI.getDashboardStats(),
      courseStore.fetchMyCourses(),
      instructorAPI.getRecentEnrollments()
    ])
    
    // Debug: settled results
    console.log('📊 Stats settled:', statsSettled.status)
    console.log('📚 My courses count:', Array.isArray(courseStore.myCourses) ? courseStore.myCourses.length : 0)
    console.log('👥 Enrollments settled:', enrollmentsSettled.status)
    
    // Accept either {status:'success', data:{stats,...}} or plain object
    if (statsSettled.status === 'fulfilled') {
      const statsResp = statsSettled.value
      const payload = statsResp.data?.data || statsResp.data || {}
      const backendStats = payload.stats || {}
      console.log('🔧 Backend Stats:', backendStats)
      
      stats.value = {
        totalCourses: backendStats.total_courses || 0,
        totalStudents: backendStats.total_students || 0,
        newStudentsThisMonth: backendStats.new_students_this_month || 0,
        totalRevenue: backendStats.total_revenue || 0,
        monthlyRevenue: backendStats.monthly_revenue || 0,
        totalEnrollments: backendStats.total_enrollments || 0,
        completionRate: backendStats.completion_rate || 0,
        averageRating: backendStats.average_rating || 0
      }
      
      recentCourses.value = payload.recent_courses || []
      coursePerformance.value = payload.course_performance || []
    } else {
      console.warn('⚠️ Stats request failed:', statsSettled.reason)
    }

    // Always prefer store data for instructor's own courses
    recentCourses.value = (recentCourses.value && recentCourses.value.length > 0)
      ? recentCourses.value
      : (Array.isArray(courseStore.myCourses) ? courseStore.myCourses.slice(0, 5) : [])

    // Fallback: set totals from store if stats API failed or returned zeros
    if (!stats.value.totalCourses || stats.value.totalCourses === 0) {
      stats.value.totalCourses = Array.isArray(courseStore.myCourses) ? courseStore.myCourses.length : 0
    }

    if (enrollmentsSettled.status === 'fulfilled') {
      recentEnrollments.value = enrollmentsSettled.value.data || []
    } else {
      console.warn('⚠️ Enrollments request failed:', enrollmentsSettled.reason)
      recentEnrollments.value = []
    }
    
    console.log('📚 Recent Courses:', recentCourses.value)
    console.log('👥 Recent Enrollments:', recentEnrollments.value)
    
  } catch (error) {
    console.error('❌ Error fetching dashboard data:', error)
    console.error('❌ Error details:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    })
    
    // Set default values on error
    stats.value = {
      totalCourses: 0,
      totalStudents: 0,
      newStudentsThisMonth: 0,
      totalRevenue: 0,
      monthlyRevenue: 0,
      totalEnrollments: 0,
      completionRate: 0,
      averageRating: 0
    }
    recentCourses.value = []
    recentEnrollments.value = []
    coursePerformance.value = []
    
    toast.error(`Failed to load dashboard data: ${error.message}`)
  } finally {
    loading.value = false
  }
}

const fetchStudents = async () => {
  studentsLoading.value = true
  try {
    const params = {
      search: studentSearch.value,
      course_id: selectedCourse.value,
      per_page: 50
    }
    
    const response = await instructorAPI.getStudents(params)
    enrolledStudents.value = response.data.enrollments.data || []
    availableCourses.value = response.data.courses || []
    
  } catch (error) {
    console.error('❌ Error fetching students:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no students are enrolled)
    if (error.response?.status >= 400) {
      toast.error('Failed to load students data')
    }
    enrolledStudents.value = []
  } finally {
    studentsLoading.value = false
  }
}

const fetchAnalytics = async () => {
  analyticsLoading.value = true
  try {
    const response = await instructorAPI.getAnalytics({ period: 30 })
    coursePerformance.value = response.data.course_performance || []
    
  } catch (error) {
    console.error('❌ Error fetching analytics:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when no analytics data exists)
    if (error.response?.status >= 400) {
      toast.error('Failed to load analytics data')
    }
    coursePerformance.value = []
  } finally {
    analyticsLoading.value = false
  }
}

const searchStudents = () => {
  fetchStudents()
}

const filterStudents = () => {
  fetchStudents()
}

// Watch for tab changes
watch(activeTab, (newTab) => {
  if (newTab === 'students' && enrolledStudents.value.length === 0) {
    fetchStudents()
  } else if (newTab === 'analytics' && coursePerformance.value.length === 0) {
    fetchAnalytics()
  }
})

onMounted(() => {
  fetchDashboardData()
})
</script> 