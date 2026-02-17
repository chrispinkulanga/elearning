<template>
  <div class="student-course-view">
    <!-- Top Navigation Bar -->
    <div class="top-navbar">
      <div class="navbar-left">
        <button @click="toggleSidebar" class="hamburger-btn">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="course-title">{{ currentLesson?.title || course?.title || 'Loading...' }}</h1>
      </div>
      
      <div class="navbar-right">
        <button @click="toggleSidebar" class="nav-btn">
          <i class="fas fa-list"></i>
          Content
        </button>
        <button @click="toggleDarkMode" class="nav-btn">
          <i class="fas fa-moon"></i>
        </button>
        <button @click="toggleContentSearch" class="nav-btn">
          <i class="fas fa-search"></i>
        </button>
        <button @click="toggleFullscreen" class="nav-btn">
          <i class="fas fa-expand"></i>
        </button>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="main-layout">
      <!-- Left Sidebar -->
      <div class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <!-- Sidebar Tabs -->
        <div class="sidebar-tabs">
          <button 
            class="tab-btn" 
            :class="{ 'active': activeTab === 'outline' }"
            @click="activeTab = 'outline'"
          >
            Course Outline
          </button>
          <button 
            class="tab-btn" 
            :class="{ 'active': activeTab === 'resources' }"
            @click="activeTab = 'resources'"
          >
            Resources
          </button>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
          <i class="fas fa-search search-icon"></i>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search course outline"
            class="search-input"
          />
        </div>

        <!-- Course Outline -->
        <div v-if="activeTab === 'outline'" class="course-outline">
          <!-- Main Course Modules -->
          <div 
            v-for="(section, index) in filteredSections" 
            :key="section.id"
            class="module-section"
          >
            <!-- Module Header -->
            <div 
              class="module-header"
              :class="{ 
                'active': currentPageId === section.id,
                'expanded': expandedSections.includes(section.id)
              }"
              :data-section-id="section.id"
              @click="selectPageFromSection(section.id)"
            >
              <div class="module-content">
                <div class="module-title-row">
                  <span class="module-number">Module {{ index + 1 }}:</span>
                  <i 
                    class="fas chevron-icon"
                    :class="expandedSections.includes(section.id) ? 'fa-chevron-up' : 'fa-chevron-down'"
                    @click.stop="toggleSection(section.id)"
                  ></i>
                </div>
                <div class="module-title-text">{{ section.title }}</div>
                <div class="module-progress-row">
                  <div class="progress-bar">
                    <div 
                      class="progress-fill" 
                      :style="{ width: section.progress + '%' }"
                    ></div>
                  </div>
                  <span class="progress-text">{{ section.progress }}%</span>
                </div>
              </div>
            </div>

            <!-- Module Lessons -->
            <div 
              v-if="expandedSections.includes(section.id)" 
              class="lessons-container"
            >
              <div 
                v-for="(lesson, lessonIndex) in section.lessons" 
                :key="lesson.id"
                class="lesson-row"
                :class="{ 
                  'active': currentLessonId === lesson.id,
                  'completed': lesson.completed
                }"
                @click="selectLesson(lesson)"
              >
                <div class="lesson-main">
                <div class="lesson-status">
                  <i 
                    v-if="lesson.completed" 
                      class="fas fa-check-circle"
                  ></i>
                  <i 
                    v-else-if="currentLessonId === lesson.id" 
                      class="fas fa-play-circle"
                  ></i>
                  <i 
                    v-else 
                      class="far fa-circle"
                  ></i>
                </div>
                <div class="lesson-info">
                    <span class="lesson-title">{{ index + 1 }}.{{ lessonIndex }}. {{ lesson.title }}</span>
                    <span class="lesson-progress">{{ getLessonProgress(lesson) }}</span>
                  </div>
                </div>
                
                <!-- Sub-items for this lesson -->
                <div class="sub-items">
                  <div 
                    v-for="(subItem, subIndex) in getLessonSubItems(lesson)" 
                    :key="subItem.id"
                    class="sub-item"
                    :class="{ 'active': currentWidgetId === subItem.id }"
                    @click.stop="selectWidget(subItem)"
                  >
                    <span class="sub-item-text">{{ subItem.title }}</span>
                    <i class="fas fa-chevron-right sub-chevron"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resources Tab -->
        <div v-if="activeTab === 'resources'" class="resources-content">
          <div class="resource-item">
            <i class="fas fa-file-pdf"></i>
            <span>Course Materials</span>
          </div>
          <div class="resource-item">
            <i class="fas fa-video"></i>
            <span>Video Resources</span>
          </div>
          <div class="resource-item">
            <i class="fas fa-link"></i>
            <span>External Links</span>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="main-content">
        <!-- Lesson Header -->
        <div v-if="currentPage" class="lesson-header">
          <div class="lesson-hero">
            <h1 class="lesson-title">{{ currentPage.title || 'Untitled Page' }}</h1>

          </div>
        </div>

        <!-- Lesson Content -->
        <div class="lesson-content">
          <div v-if="currentPage?.widgets" class="widgets-container">
            <div 
              v-for="widget in currentPage.widgets" 
              :key="widget.id"
              class="content-widget"
              :data-widget-id="widget.id"
            >
              <!-- Text Widget -->
              <div v-if="widget.widget_type === 'text'" class="text-widget">
                <div class="text-content" v-html="formatTextContent(widget.widget_data?.content)"></div>
              </div>

              <!-- Video Widget -->
              <div v-else-if="widget.widget_type === 'video'" class="video-widget">
                <div class="video-container">
                  <div v-if="widget.widget_data?.url" class="video-player">
                    <iframe 
                      v-if="isYouTubeUrl(widget.widget_data.url)"
                      :src="getYouTubeEmbedUrl(widget.widget_data.url)"
                      frameborder="0"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      allowfullscreen
                      class="youtube-iframe"
                    ></iframe>
                    <video 
                      v-else
                      :src="widget.widget_data.url"
                      controls
                      class="video-element"
                    ></video>
                  </div>
                  <div v-else class="video-placeholder">
                    <i class="fas fa-video"></i>
                    <p>Video content will be displayed here</p>
                  </div>
                </div>
              </div>

              <!-- Image Widget -->
              <div v-else-if="widget.widget_type === 'image'" class="image-widget">
                <img 
                  v-if="widget.widget_data?.url"
                  :src="widget.widget_data.url"
                  :alt="widget.widget_data?.alt || 'Course image'"
                  class="content-image"
                />
              </div>

              <!-- Quiz Widget -->
              <div v-else-if="widget.widget_type === 'quiz'" class="quiz-widget">
                <div class="quiz-container">
                  <h3>{{ widget.widget_data?.question || 'Quiz Question' }}</h3>
                  <div v-if="widget.widget_data?.options" class="quiz-options">
                    <label 
                      v-for="(option, index) in widget.widget_data.options" 
                      :key="index"
                      class="quiz-option"
                    >
                      <input type="radio" :name="`quiz-${widget.id}`" :value="index" />
                      <span>{{ option }}</span>
                    </label>
                  </div>
                  <button class="quiz-submit-btn">Submit Answer</button>
                </div>
              </div>

              <!-- Generic Widget -->
              <div v-else class="generic-widget">
                <h3>{{ widget.widget_type }}</h3>
                <pre>{{ JSON.stringify(widget.widget_data, null, 2) }}</pre>
              </div>
            </div>
          </div>

          <!-- No Content Message -->
          <div v-else class="no-content">
            <i class="fas fa-book-open"></i>
            <h3>No content available</h3>
            <p>This lesson doesn't have any content yet.</p>
          </div>
        </div>

        <!-- Course Comments Section -->
        <div v-if="course" class="comments-section">
          <CourseComments :course-id="course.id" />
        </div>

        <!-- Course Reviews Section -->
        <div v-if="course" class="reviews-section">
          <CourseReviews :course-id="course.id" :is-enrolled="true" />
        </div>

        <!-- Floating Action Buttons -->
        <div class="floating-actions">
          <button class="fab" title="Code View">
            <i class="fas fa-code"></i>
          </button>
                      <button 
              class="fab primary" 
              title="Mark Complete"
              @click="markPageComplete(currentPage, !currentPage?.completed)"
              :disabled="!currentPage"
            >
            <i class="fas fa-check"></i>
          </button>
          <button class="fab beta" title="Beta Features">
            <span>Beta</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Content Search Modal -->
    <div v-if="showContentSearch" class="search-modal-overlay" @click="closeContentSearch">
      <div class="search-modal" @click.stop>
        <div class="search-header">
          <h3>Search Content</h3>
          <button @click="closeContentSearch" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="search-input-container">
          <input 
            v-model="contentSearchQuery"
            @input="searchContent"
            type="text" 
            placeholder="Search within lesson content..."
            class="content-search-input"
            ref="contentSearchInput"
          />
          <i class="fas fa-search search-icon"></i>
        </div>
        
        <div class="search-results">
          <div v-if="contentSearchResults.length > 0" class="results-list">
            <div 
              v-for="(result, index) in contentSearchResults" 
              :key="index"
              class="search-result-item"
              @click="scrollToResult(result)"
            >
              <div class="result-preview">
                <span v-html="result.preview"></span>
              </div>
              <div class="result-context">
                Found in: {{ result.widgetType }}
              </div>
            </div>
          </div>
          <div v-else-if="contentSearchQuery && !isSearching" class="no-results">
            <i class="fas fa-search"></i>
            <p>No results found for "{{ contentSearchQuery }}"</p>
          </div>
          <div v-else-if="!contentSearchQuery" class="search-hint">
            <i class="fas fa-lightbulb"></i>
            <p>Type to search within the lesson content</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCourseStore } from '@/stores/courses'
import { useToast } from 'vue-toastification'
import api from '@/services/api'
import CourseComments from '@/components/CourseComments.vue'
import CourseReviews from '@/components/CourseReviews.vue'

const route = useRoute()
const router = useRouter()
const courseStore = useCourseStore()
const toast = useToast()

// Reactive data
const loading = ref(true)
const course = ref(null)
const sections = ref([])
const currentLessonId = ref(null)
const currentSectionId = ref(null)
const currentLesson = ref(null)
const currentPage = ref(null)
const currentPageId = ref(null)
const sidebarCollapsed = ref(false)
const activeTab = ref('outline')
const searchQuery = ref('')
const expandedSections = ref([])
const darkMode = ref(false)

// Content search variables
const showContentSearch = ref(false)
const contentSearchQuery = ref('')
const contentSearchResults = ref([])
const isSearching = ref(false)

// Progress tracking variables
const viewedWidgets = ref(new Set())
const intersectionObserver = ref(null)
const currentWidgetId = ref(null)

// Load viewed widgets from localStorage on component mount
const loadViewedWidgetsFromStorage = () => {
  if (course.value) {
    const storageKey = `viewed_widgets_${course.value.id}`
    const stored = localStorage.getItem(storageKey)
    if (stored) {
      try {
        const widgetIds = JSON.parse(stored)
        viewedWidgets.value = new Set(widgetIds)
      } catch (error) {
        console.error('Error loading viewed widgets from storage:', error)
      }
    }
  }
}

// Save viewed widgets to localStorage
const saveViewedWidgetsToStorage = () => {
  if (course.value) {
    const storageKey = `viewed_widgets_${course.value.id}`
    const widgetIds = Array.from(viewedWidgets.value)
    localStorage.setItem(storageKey, JSON.stringify(widgetIds))
  }
}

// Computed properties
const filteredSections = computed(() => {
  if (!searchQuery.value) return sections.value
  
  return sections.value.map(section => ({
    ...section,
    lessons: section.lessons.filter(lesson => 
      lesson.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  })).filter(section => section.lessons.length > 0)
})

// Helper function to clean and format text content
const formatTextContent = (content) => {
  if (!content) return 'No content available'
  
  // If content is a string of repeated characters (like 'oooo...'), format it better
  if (typeof content === 'string') {
    // Check if it's a long string of repeated characters
    const repeatedCharPattern = /^(.)\1{50,}$/
    if (repeatedCharPattern.test(content)) {
      return `<p style="word-break: break-all; overflow-wrap: break-word; max-width: 100%;">${content}</p>`
    }
    
    // For normal text, ensure proper wrapping
    return `<div style="word-break: break-word; overflow-wrap: break-word; max-width: 100%; white-space: pre-wrap;">${content}</div>`
  }
  
  return content
}

// Methods
const loadCourseData = async () => {
  try {
    loading.value = true
    const courseSlug = route.params.slug
    const courseData = await courseStore.fetchCourseBySlug(courseSlug)
    
    if (courseData) {
      course.value = courseData
      processCourseData(courseData)
    }
  } catch (error) {
    console.error('Error loading course:', error)
    toast.error('Failed to load course')
  } finally {
    loading.value = false
  }
}

const processCourseData = async (courseData) => {
  // Transform course data into sections and lessons
  if (courseData.pages && courseData.pages.length > 0) {
    // Load progress data
    const progressData = await loadCourseProgress(courseData.id)
    
    // Restore viewed widgets from backend progress data
    if (progressData?.pages) {
      progressData.pages.forEach(pageProgress => {
        if (pageProgress.widgets) {
          pageProgress.widgets.forEach(widgetProgress => {
            if (widgetProgress.viewed || widgetProgress.completed) {
              viewedWidgets.value.add(widgetProgress.widget_id.toString())
            }
          })
        }
      })
    }
    
    // Also load from localStorage as backup
    loadViewedWidgetsFromStorage()
    
    sections.value = courseData.pages.map(page => {
      const pageProgress = progressData?.pages?.find(p => p.page_id === page.id)
      
      // Calculate progress based on widgets
      const totalWidgets = page.widgets?.length || 0
      const completedWidgets = page.widgets?.filter(widget => {
        const widgetProgress = pageProgress?.widgets?.find(w => w.widget_id === widget.id)
        return widgetProgress?.completed || false
      }).length || 0
      
      // Calculate progress based on viewed widgets (restored from backend)
      const viewedWidgetsCount = page.widgets?.filter(widget => 
        viewedWidgets.value.has(widget.id.toString())
      ).length || 0
      
      // Use backend progress if available, otherwise use viewed widgets
      let progressPercentage = 0
      if (pageProgress?.progress_percentage !== undefined) {
        progressPercentage = pageProgress.progress_percentage
      } else if (totalWidgets > 0) {
        progressPercentage = Math.round((viewedWidgetsCount / totalWidgets) * 100)
      }
      
      return {
        id: page.id,
        title: page.title,
        progress: progressPercentage,
        lessons: (page.widgets || []).map(widget => {
          const widgetProgress = pageProgress?.widgets?.find(w => w.widget_id === widget.id)
          const isViewed = viewedWidgets.value.has(widget.id.toString())
          
          return {
            id: widget.id,
            title: widget.widget_type === 'text' ? 'Text Content' : widget.widget_type,
            type: widget.widget_type,
            duration: widget.widget_data?.duration || 0,
            completed: widgetProgress?.completed || isViewed,
            widgets: [widget]
          }
        })
      }
    })
    
    // Set initial page
    if (courseData.pages.length > 0) {
      selectPage(courseData.pages[0])
      expandedSections.value = [courseData.pages[0].id]
    }
    
    // Refresh progress for all sections
    refreshAllSectionProgress()
  } else {
    // Fallback to sections/lessons structure
    sections.value = courseData.sections || []
  }
}

const calculateSectionProgress = (page) => {
  if (!page.widgets || page.widgets.length === 0) return 0
  const completedWidgets = page.widgets.filter(w => w.completed).length
  return Math.round((completedWidgets / page.widgets.length) * 100)
}

const selectPage = (page) => {
  currentPageId.value = page.id
  currentPage.value = page
  currentSectionId.value = page.id
}

const selectPageFromSection = async (sectionId) => {
  console.log('Selecting page from section:', sectionId)
  
  // Find the page data from the sections array (which contains the full page data)
  const section = sections.value.find(s => s.id === sectionId)
  console.log('Found section:', section)
  
  if (section) {
    // Find the original page data from the course
    if (course.value?.pages) {
      const page = course.value.pages.find(p => p.id === sectionId)
      console.log('Found page:', page)
      
      if (page) {
        selectPage(page)
        console.log('Selected page:', currentPage.value)
        
        // Wait for DOM to update and set up intersection observer
        await nextTick()
        setupIntersectionObserver()
        // Update progress for the selected section
        updateCurrentModuleProgress()
      } else {
        console.error('Page not found in course.pages for section ID:', sectionId)
      }
    } else {
      console.error('No pages found in course')
    }
  } else {
    console.error('Section not found for ID:', sectionId)
  }
}

const selectLesson = (lesson) => {
  currentLessonId.value = lesson.id
  currentLesson.value = lesson
  currentSectionId.value = sections.value.find(s => 
    s.lessons.some(l => l.id === lesson.id)
  )?.id
}

const toggleSection = (sectionId) => {
  const index = expandedSections.value.indexOf(sectionId)
  if (index > -1) {
    expandedSections.value.splice(index, 1)
  } else {
    expandedSections.value.push(sectionId)
  }
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleDarkMode = () => {
  darkMode.value = !darkMode.value
  // Apply dark mode to the document
  if (darkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

const toggleContentSearch = () => {
  showContentSearch.value = !showContentSearch.value
  if (showContentSearch.value) {
    // Focus on the search input when modal opens
    setTimeout(() => {
      const searchInput = document.querySelector('.content-search-input')
      if (searchInput) {
        searchInput.focus()
      }
    }, 100)
  } else {
    // Clear search when closing
    contentSearchQuery.value = ''
    contentSearchResults.value = []
  }
}

const closeContentSearch = () => {
  showContentSearch.value = false
  contentSearchQuery.value = ''
  contentSearchResults.value = []
}

const searchContent = () => {
  if (!contentSearchQuery.value.trim()) {
    contentSearchResults.value = []
    return
  }

  isSearching.value = true
  const query = contentSearchQuery.value.toLowerCase()
  const results = []

  // Search through current page widgets
  if (currentPage.value?.widgets) {
    currentPage.value.widgets.forEach((widget, widgetIndex) => {
      let content = ''
      let widgetType = ''

      switch (widget.widget_type) {
        case 'text':
          content = widget.widget_data?.content || ''
          widgetType = 'Text Content'
          break
        case 'video':
          content = widget.widget_data?.title || widget.widget_data?.description || ''
          widgetType = 'Video'
          break
        case 'image':
          content = widget.widget_data?.alt || widget.widget_data?.caption || ''
          widgetType = 'Image'
          break
        case 'quiz':
          content = widget.widget_data?.question || ''
          widgetType = 'Quiz'
          break
        case 'mcq':
          content = widget.widget_data?.question || ''
          widgetType = 'Multiple Choice'
          break
        default:
          content = JSON.stringify(widget.widget_data || {})
          widgetType = 'Content'
      }

      if (content.toLowerCase().includes(query)) {
        // Create preview with highlighted search term
        const preview = content.replace(
          new RegExp(`(${query})`, 'gi'),
          '<mark>$1</mark>'
        )
        
        results.push({
          widgetIndex,
          widgetType,
          preview: preview.substring(0, 200) + (content.length > 200 ? '...' : ''),
          fullContent: content
        })
      }
    })
  }

  contentSearchResults.value = results
  isSearching.value = false
}

const scrollToResult = (result) => {
  // Close search modal
  showContentSearch.value = false
  
  // Find and scroll to the widget
  const widgets = document.querySelectorAll('.content-widget')
  if (widgets[result.widgetIndex]) {
    widgets[result.widgetIndex].scrollIntoView({ 
      behavior: 'smooth', 
      block: 'center' 
    })
    
    // Highlight the widget temporarily
    widgets[result.widgetIndex].style.backgroundColor = '#fef3c7'
    widgets[result.widgetIndex].style.border = '2px solid #f59e0b'
    setTimeout(() => {
      widgets[result.widgetIndex].style.backgroundColor = ''
      widgets[result.widgetIndex].style.border = ''
    }, 3000)
  }
}

// Intersection Observer for progress tracking
const setupIntersectionObserver = () => {
  if (intersectionObserver.value) {
    intersectionObserver.value.disconnect()
  }

  intersectionObserver.value = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const widgetId = entry.target.dataset.widgetId
          if (widgetId && !viewedWidgets.value.has(widgetId)) {
            viewedWidgets.value.add(widgetId)
            updateWidgetProgress(widgetId, true)
            // Update the current module's progress immediately
            updateCurrentModuleProgress()
          }
        }
      })
    },
    {
      threshold: 0.6, // Widget must be 60% visible to count as viewed
      rootMargin: '0px 0px -5% 0px' // Start tracking when widget is 5% from bottom
    }
  )

  // Observe all content widgets
  const widgets = document.querySelectorAll('.content-widget')
  widgets.forEach((widget) => {
    intersectionObserver.value.observe(widget)
  })
}

const updateWidgetProgress = async (widgetId, viewed) => {
  if (!currentPage.value || !course.value) return

  try {
    // Update progress on server
    await api.post('/progress/mark-viewed', {
      course_id: course.value.id,
      page_id: currentPage.value.id,
      widget_id: widgetId,
      viewed: viewed
    })

    // Update local progress calculation
    updateSectionProgress()
    
    // Save to localStorage as backup
    saveViewedWidgetsToStorage()
  } catch (error) {
    console.error('Error updating widget progress:', error)
    // Fallback: still update local progress even if server fails
    updateSectionProgress()
    // Still save to localStorage even if server fails
    saveViewedWidgetsToStorage()
  }
}

const updateSectionProgress = () => {
  if (!currentPage.value) return

  const section = sections.value.find(s => s.id === currentPage.value.id)
  if (!section) return

  // Count viewed widgets in current page
  const totalWidgets = currentPage.value.widgets?.length || 0
  const viewedCount = currentPage.value.widgets?.filter(widget => 
    viewedWidgets.value.has(widget.id.toString())
  ).length || 0

  // Calculate progress percentage
  const progressPercentage = totalWidgets > 0 ? Math.round((viewedCount / totalWidgets) * 100) : 0
  section.progress = progressPercentage

  // Update lesson completion status
  section.lessons.forEach(lesson => {
    if (lesson.widgets && lesson.widgets.length > 0) {
      const lessonViewedCount = lesson.widgets.filter(widget => 
        viewedWidgets.value.has(widget.id.toString())
      ).length
      lesson.completed = lessonViewedCount === lesson.widgets.length
    }
  })
}

const updateCurrentModuleProgress = () => {
  if (!currentPage.value) return

  const section = sections.value.find(s => s.id === currentPage.value.id)
  if (!section) return

  // Count viewed widgets in current page
  const totalWidgets = currentPage.value.widgets?.length || 0
  const viewedCount = currentPage.value.widgets?.filter(widget => 
    viewedWidgets.value.has(widget.id.toString())
  ).length || 0

  // Calculate progress percentage with smooth animation
  const progressPercentage = totalWidgets > 0 ? Math.round((viewedCount / totalWidgets) * 100) : 0
  
  // Store previous progress for comparison
  const previousProgress = section.progress
  
  // Update section progress with visual feedback
  section.progress = progressPercentage
  
  // Add visual feedback if progress increased
  if (progressPercentage > previousProgress) {
    // Add updating class to progress text
    const progressTextElement = document.querySelector(`[data-section-id="${section.id}"] .progress-text`)
    if (progressTextElement) {
      progressTextElement.classList.add('updating')
      setTimeout(() => {
        progressTextElement.classList.remove('updating')
      }, 1000)
    }
  }
  
  // Update lesson progress counts
  section.lessons.forEach(lesson => {
    if (lesson.widgets && lesson.widgets.length > 0) {
      const lessonViewedCount = lesson.widgets.filter(widget => 
        viewedWidgets.value.has(widget.id.toString())
      ).length
      lesson.completed = lessonViewedCount === lesson.widgets.length
    }
  })

  // Show progress update notification for significant milestones
  if (progressPercentage > 0 && progressPercentage % 25 === 0 && progressPercentage !== previousProgress) {
    toast.info(`Module progress: ${progressPercentage}%`)
  }
}

const refreshAllSectionProgress = () => {
  sections.value.forEach(section => {
    const totalWidgets = section.lessons?.reduce((total, lesson) => 
      total + (lesson.widgets?.length || 0), 0) || 0
    
    const viewedCount = section.lessons?.reduce((total, lesson) => 
      total + (lesson.widgets?.filter(widget => 
        viewedWidgets.value.has(widget.id.toString())
      ).length || 0), 0) || 0
    
    section.progress = totalWidgets > 0 ? Math.round((viewedCount / totalWidgets) * 100) : 0
  })
}

// Helper functions for new sidebar structure
const getLessonProgress = (lesson) => {
  if (!lesson.widgets || lesson.widgets.length === 0) return '0 / 0'
  
  const viewedCount = lesson.widgets.filter(widget => 
    viewedWidgets.value.has(widget.id.toString())
  ).length
  
  return `${viewedCount} / ${lesson.widgets.length}`
}

const getLessonSubItems = (lesson) => {
  if (!lesson.widgets || lesson.widgets.length === 0) return []
  
  return lesson.widgets.map((widget, index) => ({
    id: widget.id,
    title: getWidgetTitle(widget, index + 1),
    type: widget.widget_type,
    widget: widget
  }))
}

const getWidgetTitle = (widget, index) => {
  const type = widget.widget_type
  const data = widget.widget_data || {}
  
  switch (type) {
    case 'video':
      return `${index}. Video - ${data.title || 'Video Content'}`
    case 'text':
      return `${index}. ${data.title || 'Text Content'}`
    case 'image':
      return `${index}. Image - ${data.alt || 'Image Content'}`
    case 'quiz':
      return `${index}. Quiz - ${data.question || 'Quiz Question'}`
    case 'mcq':
      return `${index}. Multiple Choice - ${data.question || 'MCQ Question'}`
    default:
      return `${index}. ${type.charAt(0).toUpperCase() + type.slice(1)} Content`
  }
}

// YouTube URL detection and conversion
const isYouTubeUrl = (url) => {
  if (!url) return false
  const youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/
  return youtubeRegex.test(url)
}

const getYouTubeEmbedUrl = (url) => {
  if (!url) return ''
  
  // Extract video ID from various YouTube URL formats
  let videoId = ''
  
  // Handle youtu.be format
  if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1].split('?')[0]
  }
  // Handle youtube.com format
  else if (url.includes('youtube.com/watch')) {
    const urlParams = new URLSearchParams(url.split('?')[1])
    videoId = urlParams.get('v')
  }
  // Handle youtube.com/embed format
  else if (url.includes('youtube.com/embed/')) {
    videoId = url.split('youtube.com/embed/')[1].split('?')[0]
  }
  
  if (videoId) {
    return `https://www.youtube.com/embed/${videoId}`
  }
  
  return url
}

const selectWidget = (subItem) => {
  currentWidgetId.value = subItem.id
  // Scroll to the specific widget in the content area
  const widgetElement = document.querySelector(`[data-widget-id="${subItem.id}"]`)
  if (widgetElement) {
    widgetElement.scrollIntoView({ 
      behavior: 'smooth', 
      block: 'center' 
    })
    
    // Highlight the widget temporarily
    widgetElement.style.backgroundColor = '#fef3c7'
    widgetElement.style.border = '2px solid #f59e0b'
    setTimeout(() => {
      widgetElement.style.backgroundColor = ''
      widgetElement.style.border = ''
    }, 3000)
  }
}

const loadCourseProgress = async (courseId) => {
  try {
    const response = await api.get(`/progress/course/${courseId}`)
    return response.data.data
  } catch (error) {
    console.error('Error loading course progress:', error)
    return null
  }
}

const markLessonComplete = async (lesson, completed = true) => {
  try {
    const widget = lesson.widgets[0]
    if (!widget) return

    await api.post('/progress/mark-complete', {
      course_id: course.value.id,
      page_id: currentSectionId.value,
      widget_id: widget.id,
      completed: completed
    })

    // Update local state
    lesson.completed = completed
    
    // Update section progress
    const section = sections.value.find(s => s.id === currentSectionId.value)
    if (section) {
      const completedLessons = section.lessons.filter(l => l.completed).length
      section.progress = Math.round((completedLessons / section.lessons.length) * 100)
    }

    toast.success(completed ? 'Marked as complete' : 'Marked as incomplete')
  } catch (error) {
    console.error('Error updating progress:', error)
    toast.error('Failed to update progress')
  }
}

const markPageComplete = async (page, completed) => {
  if (!page) return
  
  try {
    // Mark all widgets in the page as complete
    for (const widget of page.widgets || []) {
      await api.post('/progress/mark-complete', {
        course_id: course.value.id,
        page_id: page.id,
        widget_id: widget.id,
        completed: completed
      })
    }
    
    toast.success(completed ? 'Page marked as complete!' : 'Page marked as incomplete')
    
    // Refresh the course data to update progress
    await loadCourse()
  } catch (error) {
    console.error('Error marking page complete:', error)
    toast.error('Failed to update page status')
  }
}

// Lifecycle
onMounted(() => {
  loadCourseData()
})

onUnmounted(() => {
  if (intersectionObserver.value) {
    intersectionObserver.value.disconnect()
  }
})

// Watch for current page changes to set up intersection observer
watch(currentPage, async (newPage) => {
  if (newPage) {
    // Wait for DOM to update
    await nextTick()
    setupIntersectionObserver()
    // Update progress for current section
    updateSectionProgress()
  }
}, { immediate: true })

// Watch for route changes
watch(() => route.params.slug, () => {
  loadCourseData()
})
</script>

<style scoped>
.student-course-view {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
}

/* Top Navigation */
.top-navbar {
  height: 60px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.hamburger-btn {
  background: none;
  border: none;
  font-size: 18px;
  color: #64748b;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.hamburger-btn:hover {
  background: #f1f5f9;
}

.course-title {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-btn {
  background: none;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  color: #64748b;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.nav-btn:hover {
  background: #f1f5f9;
  color: #334155;
}

/* Main Layout */
.main-layout {
  flex: 1;
  display: flex;
  overflow: hidden;
}

/* Sidebar */
.sidebar {
  width: 320px;
  background: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
}

.sidebar-collapsed {
  width: 0;
  overflow: hidden;
}

.sidebar-tabs {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
}

.tab-btn {
  flex: 1;
  padding: 16px;
  background: none;
  border: none;
  font-size: 14px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  position: relative;
  transition: color 0.2s;
}

.tab-btn.active {
  color: #059669;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: #059669;
}

/* Search */
.search-container {
  padding: 16px;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 28px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 14px;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  background: #f8fafc;
}

.search-input:focus {
  outline: none;
  border-color: #059669;
  background: white;
}

/* Course Outline */
.course-outline {
  flex: 1;
  overflow-y: auto;
  padding: 0 16px 16px;
}

.outline-section {
  margin-bottom: 8px;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.section-header:hover {
  background: #f8fafc;
}

.section-header.active {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
}

.section-header.completed {
  background: #f0fdf4;
}

.section-info {
  flex: 1;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.progress-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.progress-bar {
  width: 60px;
  height: 4px;
  background: #e2e8f0;
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #059669;
  transition: width 0.3s;
}

.progress-text {
  font-size: 12px;
  color: #64748b;
}

.section-arrow {
  color: #059669;
  font-size: 12px;
}

.section-lessons {
  margin-left: 16px;
  border-left: 2px solid #e2e8f0;
  padding-left: 16px;
}

.lesson-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.lesson-item:hover {
  background: #f8fafc;
}

.lesson-item.active {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
}

.lesson-item.completed {
  background: #f0fdf4;
}

.lesson-status {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}

.completed-icon {
  color: #059669;
  font-size: 16px;
}

.active-icon {
  color: #059669;
  font-size: 16px;
}

.pending-icon {
  color: #cbd5e1;
  font-size: 14px;
}

.lesson-info {
  flex: 1;
}

.lesson-title {
  font-size: 13px;
  font-weight: 500;
  color: #1e293b;
  margin: 0 0 2px 0;
}

.lesson-duration {
  font-size: 11px;
  color: #64748b;
  margin: 0;
}

/* New Sidebar Structure Styles */
.course-outline {
  padding: 0;
  background: white;
}

.chevron-icon {
  color: #28a745;
  font-size: 12px;
}

/* Module Section */
.module-section {
  margin-bottom: 8px;
}

.module-header {
  background: #d4edda;
  border: 2px solid #28a745;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 4px;
}

.module-header:hover {
  background: #c3e6cb;
}

.module-header.active {
  background: #d4edda;
  border-color: #28a745;
}

.module-content {
  padding: 12px 16px;
}

.module-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
}

.module-number {
  font-size: 14px;
  font-weight: 600;
  color: #155724;
}

.module-title-text {
  font-size: 13px;
  color: #155724;
  margin-bottom: 8px;
  line-height: 1.3;
}

.module-progress-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.progress-bar {
  flex: 1;
  height: 4px;
  background: #e9ecef;
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #28a745;
  transition: width 0.8s ease-in-out;
  border-radius: 2px;
}

.progress-text {
  font-size: 12px;
  color: #155724;
  font-weight: 500;
  min-width: 30px;
  transition: all 0.3s ease;
}

.progress-text.updating {
  color: #007bff;
  font-weight: 600;
  transform: scale(1.1);
}

/* Lessons Container */
.lessons-container {
  background: white;
  padding: 8px 0;
}

.lesson-row {
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: all 0.2s;
  border-bottom: 1px solid #f8f9fa;
  padding: 8px 0;
}

.lesson-row:hover {
  background: #f8f9fa;
}

.lesson-row.active {
  background: #fff3cd;
}

.lesson-main {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 16px;
}

.lesson-status {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
}

.lesson-status i {
  font-size: 14px;
  color: #6c757d;
}

.lesson-status .fa-check-circle {
  color: #28a745;
}

.lesson-status .fa-play-circle {
  color: #007bff;
}

.lesson-info {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.lesson-title {
  font-size: 11px;
  color: #212529;
  font-weight: 500;
}

.lesson-progress {
  font-size: 11px;
  color: #6c757d;
  background: #f8f9fa;
  padding: 2px 6px;
  border-radius: 10px;
}

/* Sub-items */
.sub-items {
  margin-left: 28px;
  margin-top: 4px;
  border-left: 2px dashed #dee2e6;
  padding-left: 12px;
}

.sub-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 8px;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 4px;
  margin-bottom: 2px;
}

.sub-item:hover {
  background: #f8f9fa;
}

.sub-item.active {
  background: #fff3cd;
  border-left: 2px solid #ffc107;
}

.sub-item-text {
  font-size: 12px;
  color: #6c757d;
  font-weight: 400;
}

.sub-chevron {
  font-size: 10px;
  color: #adb5bd;
}

/* Resources */
.resources-content {
  padding: 16px;
}

.resource-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.resource-item:hover {
  background: #f8fafc;
}

/* Main Content */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  max-width: 100%;
  width: 100%;
}

.lesson-header {
  position: relative;
  height: 50px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: flex-start;
  color: white;
  text-align: left;
  padding-left: 24px;
}

.lesson-hero {
  width: 100%;
  padding: 0;
}

.lesson-title {
  font-size: 15px;
  font-weight: 700;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.lesson-subtitle {
  font-size: 26px;
  opacity: 0.9;
  margin: 0 0 24px 0;
}



/* Lesson Content */
.lesson-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 32px;
  background: white;
  max-width: 100%;
  word-wrap: break-word;
  word-break: break-word;
}

.widgets-container {
  max-width: 100%;
  margin: 0;
  padding: 0;
}

.content-widget {
  margin-bottom: 32px;
}

.text-widget {
  font-size: 16px;
  line-height: 1.6;
  color: #374151;
  text-align: left;
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
}

.video-widget {
  margin: 24px 0;
}

.video-container {
  position: relative;
  width: 100%;
  height: 400px;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
}

.video-player {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.youtube-iframe {
  width: 100%;
  height: 100%;
  border: none;
  border-radius: 8px;
}

.video-element {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.video-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
  font-size: 18px;
}

.image-widget {
  margin: 24px 0;
}

.content-image {
  width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.quiz-widget {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 24px;
  margin: 24px 0;
}

.quiz-container h3 {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 16px 0;
  color: #1e293b;
}

.quiz-options {
  margin: 16px 0;
}

.quiz-option {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
  cursor: pointer;
}

.quiz-submit-btn {
  background: #059669;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.quiz-submit-btn:hover {
  background: #047857;
}

.generic-widget {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 24px;
}

.no-content {
  text-align: center;
  padding: 64px 24px;
  color: #64748b;
}

.no-content i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #cbd5e1;
}

/* Floating Actions */
/* Comments Section */
.comments-section {
  margin-top: 32px;
  padding: 24px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.dark .comments-section {
  background: #1e293b;
  border-color: #334155;
}

/* Reviews Section */
.reviews-section {
  margin-top: 32px;
  padding: 24px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.dark .reviews-section {
  background: #1e293b;
  border-color: #334155;
}

.floating-actions {
  position: fixed;
  right: 24px;
  bottom: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.fab {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s;
}

.fab:hover {
  transform: scale(1.1);
}

.fab:not(.primary):not(.beta) {
  background: #374151;
}

.fab.primary {
  background: #059669;
}

.fab.beta {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-size: 12px;
  font-weight: 600;
}

.fab:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.fab:disabled:hover {
  transform: none;
}

/* Text Content Widget */
.text-widget {
  font-size: 16px;
  line-height: 1.6;
  color: #374151;
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
}

.text-content {
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  overflow-x: hidden;
  hyphens: auto;
  -webkit-hyphens: auto;
  -ms-hyphens: auto;
  text-align: left;
}

/* Ensure all child elements of text content also wrap properly */
.text-content * {
  max-width: 100% !important;
  overflow-wrap: break-word !important;
  word-wrap: break-word !important;
  word-break: break-word !important;
  white-space: pre-wrap !important;
  overflow-x: hidden !important;
  text-align: left !important;
}

/* Content Widget Container */
.content-widget {
  margin-bottom: 32px;
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
}

/* Lesson Content Container */
.lesson-content {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

/* Main Content Container */
.main-content {
  max-width: 100%;
  overflow-x: hidden;
}

/* Global overflow prevention */
* {
  box-sizing: border-box;
}

.student-course-view {
  max-width: 100vw;
  overflow-x: hidden;
}

/* Ensure all text content wraps properly */
h1, h2, h3, h4, h5, h6, p, span, div {
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
}

/* Lesson Title in Sidebar */
.lesson-item .lesson-title {
  font-size: 13px !important;
  font-weight: 400;
  color: #374151;
  margin: 0 0 2px 0;
}

/* Dark Mode Styles */
.dark .student-course-view {
  background-color: #1f2937;
  color: #f9fafb;
}

.dark .top-navbar {
  background-color: #374151;
  border-bottom-color: #4b5563;
}

.dark .sidebar {
  background-color: #374151;
  border-right-color: #4b5563;
}

.dark .main-content {
  background-color: #1f2937;
}

.dark .lesson-header {
  background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%);
}

.dark .lesson-content {
  background-color: #1f2937;
  color: #f9fafb;
}

.dark .nav-btn {
  color: #f9fafb;
  border-color: #4b5563;
}

.dark .nav-btn:hover {
  background-color: #4b5563;
}

/* Content Search Modal */
.search-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  z-index: 1000;
  padding-top: 10vh;
}

.search-modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.search-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.search-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  color: #6b7280;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s;
}

.close-btn:hover {
  background-color: #f3f4f6;
  color: #374151;
}

.search-input-container {
  position: relative;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.content-search-input {
  width: 100%;
  padding: 12px 16px 12px 40px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 16px;
  outline: none;
  transition: border-color 0.2s;
}

.content-search-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-input-container .search-icon {
  position: absolute;
  left: 36px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 16px;
}

.search-results {
  flex: 1;
  overflow-y: auto;
  padding: 0 24px 24px;
}

.results-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.search-result-item {
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.search-result-item:hover {
  background-color: #f9fafb;
  border-color: #3b82f6;
}

.result-preview {
  font-size: 14px;
  line-height: 1.5;
  color: #374151;
  margin-bottom: 8px;
}

.result-preview mark {
  background-color: #fef3c7;
  color: #92400e;
  padding: 2px 4px;
  border-radius: 4px;
}

.result-context {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

.no-results, .search-hint {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.no-results i, .search-hint i {
  font-size: 24px;
  margin-bottom: 12px;
  display: block;
}

.no-results p, .search-hint p {
  margin: 0;
  font-size: 14px;
}

/* Dark mode for search modal */
.dark .search-modal {
  background-color: #374151;
  color: #f9fafb;
}

.dark .search-header {
  border-bottom-color: #4b5563;
}

.dark .search-header h3 {
  color: #f9fafb;
}

.dark .close-btn {
  color: #9ca3af;
}

.dark .close-btn:hover {
  background-color: #4b5563;
  color: #f9fafb;
}

.dark .search-input-container {
  border-bottom-color: #4b5563;
}

.dark .content-search-input {
  background-color: #4b5563;
  border-color: #6b7280;
  color: #f9fafb;
}

.dark .content-search-input:focus {
  border-color: #3b82f6;
}

.dark .search-result-item {
  background-color: #4b5563;
  border-color: #6b7280;
}

.dark .search-result-item:hover {
  background-color: #6b7280;
}

.dark .result-preview {
  color: #f9fafb;
}

.dark .result-context {
  color: #d1d5db;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    top: 60px;
    left: 0;
    height: calc(100vh - 60px);
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar:not(.sidebar-collapsed) {
    transform: translateX(0);
  }
  
  .main-content {
    width: 100%;
  }
  
  .lesson-content {
    padding: 16px;
  }
  
  .floating-actions {
    right: 16px;
    bottom: 16px;
  }
}
</style>
