<template>
  <div class="course-builder">
    <!-- Top Header Bar -->
    <div class="builder-header">
      <div class="left-section">
        <button @click="toggleMobilePanel" class="mobile-toggle-btn md:hidden">
          <i class="fas fa-bars"></i>
        </button>
        <div class="course-info">
          <h1 class="course-title">{{ course?.title || 'Loading...' }}</h1>
          <div class="course-actions">
        <button @click="goBack" class="btn-back">
          <i class="fas fa-arrow-left"></i> Back to Courses
        </button>
        <span v-if="course" class="course-status" :class="course.status">
          {{ course.status }}
        </span>
          </div>
        </div>
      </div>
      
      <div class="center-section">
        <div class="view-mode-selector">
          <button 
            @click="viewMode = 'builder'"
            :class="{ 'active': viewMode === 'builder' }"
            class="mode-btn"
          >
            <i class="fas fa-edit"></i> Builder
          </button>
          <button 
            @click="viewMode = 'student'"
            :class="{ 'active': viewMode === 'student' }"
            class="mode-btn"
          >
            <i class="fas fa-eye"></i> Student View
          </button>
        </div>
        
        <div v-if="viewMode === 'builder'" class="page-selector">
          <span class="page-label">Page:</span>
          <div class="page-dropdown-container">
                        <select v-model="currentPageId" @change="selectPage($event.target.value)" class="page-dropdown">
            <option v-for="page in course?.pages" :key="page.id" :value="page.id">
                {{ truncateText(page.title || 'Untitled Page', 30) }}
            </option>
          </select>
          </div>
          <button @click="showAddPageModal = true" class="btn-primary btn-add-page">
            + Add Page
          </button>
        </div>
      </div>
      
      <div class="right-section">
        <button @click="previewCourse" class="btn-secondary">
          <i class="fas fa-eye"></i> Preview
        </button>
        <button @click="saveCourse" class="btn-secondary">
          <i class="fas fa-save"></i> Save
        </button>
        <button @click="publishCourse" class="btn-primary">
          <i class="fas fa-rocket"></i> Publish
        </button>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="builder-layout">
      <!-- Mobile Overlay -->
      <div 
        v-if="mobilePanelOpen" 
        class="mobile-overlay md:hidden"
        @click="mobilePanelOpen = false"
      ></div>
      
      <!-- Left Panel: Course Structure -->
      <div class="structure-panel" :class="{ 'mobile-open': mobilePanelOpen }">
        <!-- Builder Mode Sidebar -->
        <div v-if="viewMode === 'builder'">
        <div class="panel-header">
          <h3>Course Structure</h3>
          <button @click="showAddPageModal = true" class="btn-small">
            + Page
          </button>
        </div>
        
        <!-- Hierarchical Course Structure -->
        <div class="course-tree">
          <div v-for="page in course?.pages" :key="page.id" class="page-item">
              <div class="page-header" @click="selectPage(page.id)" :class="{ 'active': currentPageId === page.id }">
              <i class="fas fa-file-alt"></i>
              <span>{{ page.title || 'Untitled Page' }}</span>
              <div class="page-actions">
                <button @click.stop="duplicatePage(page.id)" class="btn-icon" title="Duplicate">
                  <i class="fas fa-copy"></i>
                </button>
                <button @click.stop="deletePage(page.id)" class="btn-icon text-red-500" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
            
            <!-- Page Sections -->
            <div class="sections-list">
              <div v-for="widget in page.widgets" :key="widget.id" class="widget-item">
                <div class="widget-header">
                  <i :class="getWidgetIcon(widget.widget_type)"></i>
                  <span>{{ getWidgetTypeName(widget.widget_type) }}</span>
                  <button @click.stop="deleteWidget(widget.id)" class="btn-icon text-red-500" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              
              <button @click="openAddWidgetModal(page.id)" class="btn-small">
                + Widget
              </button>
            </div>
          </div>
        </div>
      </div>

        <!-- Student Mode Sidebar -->
        <div v-else class="student-sidebar">
          <div class="sidebar-tabs">
            <button 
              class="tab-btn active"
            >
              Course Outline
            </button>
            <button 
              class="tab-btn"
            >
              Resources
            </button>
          </div>

          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search course outline"
              class="search-input"
            />
          </div>

          <div class="course-outline">
            <div 
              v-for="page in course?.pages" 
              :key="page.id"
              class="outline-section"
            >
              <div 
                class="section-header"
                :class="{ 
                  'active': currentPageId === page.id,
                  'completed': page.progress === 100
                }"
                @click="selectPage(page.id)"
              >
                <div class="section-info">
                  <h3 class="section-title">{{ page.title || 'Untitled Page' }}</h3>
                  <div class="progress-info">
                    <div class="progress-bar">
                      <div 
                        class="progress-fill" 
                        :style="{ width: (page.progress || 0) + '%' }"
                      ></div>
                    </div>
                    <span class="progress-text">{{ page.progress || 0 }}%</span>
                  </div>
                </div>
                <i 
                  class="fas section-arrow"
                  :class="isSectionExpanded(page.id) ? 'fa-chevron-up' : 'fa-chevron-down'"
                  @click.stop="toggleSection(page.id)"
                ></i>
              </div>

              <div v-if="isSectionExpanded(page.id)" class="section-lessons">
                <div 
                  v-for="widget in page.widgets" 
                  :key="widget.id"
                  class="lesson-item"
                  :class="{ 
                    'active': currentPageId === page.id,
                    'completed': widget.completed
                  }"
                  @click="selectPage(page.id)"
                >
                  <div class="lesson-status">
                    <i 
                      v-if="widget.completed" 
                      class="fas fa-check-circle completed-icon"
                    ></i>
                    <i 
                      v-else-if="currentPageId === page.id" 
                      class="fas fa-play-circle active-icon"
                    ></i>
                    <i 
                      v-else 
                      class="far fa-circle pending-icon"
                    ></i>
                  </div>
                  <div class="lesson-info">
                    <h4 class="lesson-title">{{ getWidgetTypeName(widget.widget_type) }}</h4>
                    <p v-if="widget.duration && widget.duration > 0" class="lesson-duration">{{ widget.duration }} min</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Content Area -->
      <div class="content-editor">
        <!-- Builder Mode Content -->
        <div v-if="viewMode === 'builder'">
        <div v-if="currentPage" class="page-editor">
          <!-- Page Header -->
          <div class="page-header">
            <input 
              v-model="currentPage.title" 
              class="page-title-input"
              placeholder="Untitled Page"
              @blur="updatePage"
            />
            <div class="page-actions">
              <button @click="savePage" class="btn-secondary">Save Page</button>
              <button @click="previewPage" class="btn-primary">Preview</button>
            </div>
          </div>
          
          <!-- Content Canvas -->
          <div class="content-canvas">
              <div v-if="currentPage.widgets && currentPage.widgets.length > 0" class="widgets-container">
                <div v-for="widget in currentPage.widgets" :key="widget.id" class="widget-item">
                <ContentWidget 
                  :widget="widget"
                  @update="updateWidget"
                  @delete="deleteWidget"
                />
                </div>
              </div>
              <div v-else class="no-widgets">
                <p>No widgets in this page yet.</p>
              </div>
            
            <!-- Add Widget Button -->
            <button @click="openAddWidgetModal(currentPage.id)" class="add-widget-btn">
              <i class="fas fa-plus"></i> Add Content
            </button>
          </div>
        </div>
        
        <div v-else class="no-page-selected">
          <div class="empty-state">
            <i class="fas fa-file-alt text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No Page Selected</h3>
            <p class="text-gray-600 mb-4">Select a page from the left panel or create a new one to start building your course content.</p>
            <div class="text-center">
              <button @click="showAddPageModal = true" class="btn-primary">
                Create Your First Page
              </button>
            </div>
          </div>
        </div>
        </div>

        <!-- Student Mode Content -->
        <div v-else class="student-content">
          <!-- Lesson Header -->
          <div v-if="currentPage" class="lesson-header">
            <div class="lesson-hero">
              <h1 class="lesson-title">{{ currentPage.title || 'Untitled Page' }}</h1>

            </div>
          </div>

          <!-- Lesson Content -->
          <div class="lesson-content">
            <div v-if="currentPage?.widgets" class="widgets-container student-widgets">
              <div 
                v-for="widget in currentPage.widgets" 
                :key="widget.id"
                class="content-widget"
              >
                <!-- Text Widget -->
                <div v-if="widget.widget_type === 'text'" class="text-widget">
                  <div class="text-content" v-html="formatTextContent(widget.widget_data?.content)"></div>
                </div>

                <!-- Video Widget -->
                <div v-else-if="widget.widget_type === 'video'" class="video-widget">
                  <div class="video-container">
                    <video 
                      v-if="widget.widget_data?.url"
                      :src="widget.widget_data.url"
                      controls
                      class="video-player"
                    ></video>
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

          <!-- Floating Action Buttons -->
          <div class="floating-actions">
            <button class="fab" title="Code View">
              <i class="fas fa-code"></i>
            </button>
            <button 
              class="fab primary" 
              title="Mark Complete"
              @click="markLessonComplete(currentPage, !currentPage?.completed)"
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
    </div>

    <!-- Add Page Modal -->
    <AddPageModal 
      v-if="showAddPageModal"
      @close="showAddPageModal = false"
      @page-created="onPageCreated"
    />

    <!-- Add Widget Modal -->
    <AddWidgetModal 
      v-if="showAddWidgetModal"
      :page-id="selectedPageId"
      @close="showAddWidgetModal = false"
      @widget-created="onWidgetCreated"
    />

    <!-- Confirm Delete Modal -->
    <div v-if="confirmDialog.visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ confirmDialog.title }}</h3>
        </div>
        <div class="px-6 py-4">
          <p class="text-gray-700">{{ confirmDialog.message }}</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end space-x-3">
          <button class="btn-secondary" @click="closeConfirmDialog">Cancel</button>
          <button class="btn-primary" @click="confirmDialogConfirm">Confirm</button>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Preview — {{ currentPage?.title || 'Untitled Page' }}</h3>
          <button class="btn-secondary" @click="closePreview">Close</button>
        </div>
        <div class="px-6 py-6 space-y-8">
          <template v-if="currentPage">
            <div v-for="widget in currentPage.widgets" :key="widget.id" class="border border-gray-200 rounded-md">
              <div class="px-4 py-2 border-b border-gray-200 text-sm text-gray-600">{{ getWidgetTypeName(widget.widget_type) }}</div>
              <div class="p-4">
                <component :is="getPreviewRenderer(widget)" :widget="widget" />
              </div>
            </div>
          </template>
          <div v-else class="text-gray-600">No page selected.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Top Header Layout */
.builder-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 16px;
}

.left-section {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
  flex: 1;
  flex-wrap: nowrap;
  overflow: hidden;
}

.course-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
  flex: 1;
}

.course-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: nowrap;
}

.center-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  min-width: 0;
  flex: 2;
}

.right-section {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  flex: 1;
  justify-content: flex-end;
}

/* View Mode Selector */
.view-mode-selector {
  display: flex;
  gap: 8px;
  margin-bottom: 0;
}

.mode-btn {
  padding: 8px 16px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.mode-btn:hover {
  background: #f8fafc;
}

.mode-btn.active {
  background: #059669;
  color: white;
  border-color: #059669;
}

/* Page Selector */
.page-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
}

.page-label {
  font-weight: 500;
  color: #374151;
  white-space: nowrap;
}

.page-dropdown-container {
  position: relative;
  min-width: 200px;
  max-width: 300px;
}

.page-dropdown {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 14px;
  color: #374151;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 8px center;
  background-repeat: no-repeat;
  background-size: 16px;
  padding-right: 32px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.page-dropdown:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.page-dropdown option {
  padding: 8px;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn-add-page {
  white-space: nowrap;
  padding: 8px 16px;
  font-size: 14px;
}

/* Back Button */
.btn-back {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-back:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* Course Title */
.course-title {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 300px;
  flex-shrink: 1;
  line-height: 1.2;
}

/* Course Status */
.course-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  flex-shrink: 0;
  min-width: fit-content;
}

.course-status.pending {
  background: #fef3c7;
  color: #92400e;
}

.course-status.published {
  background: #d1fae5;
  color: #065f46;
}

.course-status.draft {
  background: #e5e7eb;
  color: #374151;
}

/* Action Buttons */
.btn-secondary, .btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  border: 1px solid transparent;
}

/* Mobile Toggle Button */
.mobile-toggle-btn {
  display: none;
  padding: 8px 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  color: #374151;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
}

.mobile-toggle-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* Mobile Overlay */
.mobile-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 40;
  top: 65px;
}

.btn-secondary {
  background: #f8fafc;
  color: #374151;
  border-color: #e2e8f0;
}

.btn-secondary:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.btn-primary {
  background: #059669;
  color: white;
  border-color: #059669;
}

.btn-primary:hover {
  background: #047857;
  border-color: #047857;
}

/* Student Sidebar Styles */
.student-sidebar {
  height: 100%;
  display: flex;
  flex-direction: column;
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
  cursor: pointer;
  transition: transform 0.2s ease;
  padding: 4px;
  border-radius: 4px;
}

.section-arrow:hover {
  background: rgba(5, 150, 105, 0.1);
  transform: scale(1.1);
}

.section-lessons {
  margin-left: 16px;
  border-left: 2px solid #e2e8f0;
  padding-left: 16px;
  transition: all 0.3s ease;
  overflow: hidden;
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

.section-lessons .lesson-item .lesson-title {
  font-size: 13px !important;
  font-weight: 400;
  color: #374151;
  margin: 0 0 2px 0;
}

.lesson-duration {
  font-size: 11px;
  color: #64748b;
  margin: 0;
}

/* Student Content Styles */
.student-content {
  height: 100%;
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
  font-size: 24px;
  font-weight: 700;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}



.lesson-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 32px;
  background: white;
  max-width: 100%;
  word-wrap: break-word;
  word-break: break-word;
  position: relative;
}

/* Ensure the lesson content container has proper width constraints */
.lesson-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 1;
}

.content-widget {
  margin-bottom: 32px;
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
}

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
}

/* Ensure all child elements of text content also wrap properly */
.text-content * {
  max-width: 100% !important;
  overflow-wrap: break-word !important;
  word-wrap: break-word !important;
  word-break: break-word !important;
  white-space: pre-wrap !important;
  overflow-x: hidden !important;
}

.video-widget {
  margin: 24px 0;
}

.video-container {
  position: relative;
  width: 100%;
  max-width: 100%;
  height: 400px;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
}

.video-player {
  width: 100%;
  height: 100%;
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
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  object-fit: contain;
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
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
}

.generic-widget pre {
  max-width: 100%;
  overflow-x: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
  word-break: break-word;
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

/* Builder View Content Canvas */
.content-canvas {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

.widgets-container {
  max-width: 100%;
  overflow-x: hidden;
}

.student-widgets {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
}

.widget-item {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

/* Page Title Input */
.page-title-input {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

/* Main Layout Container */
.builder-layout {
  max-width: 100%;
  overflow-x: hidden;
}

.content-editor {
  max-width: 100%;
  overflow-x: hidden;
}

/* Structure Panel */
.structure-panel {
  max-width: 100%;
  overflow-x: hidden;
}

.course-tree {
  max-width: 100%;
  overflow-x: hidden;
}

.page-item {
  max-width: 100%;
  overflow-x: hidden;
}

.page-header {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

.sections-list {
  max-width: 100%;
  overflow-x: hidden;
}

.widget-header {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

/* Global overflow prevention */
* {
  box-sizing: border-box;
}

.course-builder {
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

/* Input and textarea elements */
input, textarea {
  max-width: 100%;
  overflow-x: hidden;
  word-wrap: break-word;
  word-break: break-word;
}

/* Pre and code elements */
pre, code {
  max-width: 100%;
  overflow-x: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
  word-break: break-word;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .builder-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .left-section, .center-section, .right-section {
    flex: none;
    justify-content: center;
  }
  
  .page-dropdown-container {
    min-width: 150px;
    max-width: 250px;
  }
}

@media (max-width: 768px) {
  .builder-header {
    padding: 12px 16px;
  }
  
  .mobile-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .mobile-overlay {
    display: block;
  }
  
  .structure-panel {
    position: fixed;
    left: 0;
    top: 65px;
    bottom: 0;
    width: 280px;
    background: white;
    z-index: 50;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  }
  
  .structure-panel.mobile-open {
    transform: translateX(0);
  }
  
  .left-section {
    flex-wrap: wrap;
    gap: 8px;
  }
  
  .course-info {
    gap: 6px;
    width: 100%;
  }
  
  .course-title {
    max-width: 100%;
    font-size: 16px;
  }
  
  .course-actions {
    gap: 8px;
    width: 100%;
    flex-wrap: wrap;
  }
  
  .btn-back {
    flex: 1;
    min-width: 150px;
  }
  
  .center-section {
    width: 100%;
    order: 3;
  }
  
  .view-mode-selector {
    width: 100%;
    justify-content: center;
  }
  
  .mode-btn {
    flex: 1;
  }
  
  .page-selector {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  
  .page-dropdown-container {
    width: 100%;
    max-width: none;
  }
  
  .btn-add-page {
    width: 100%;
  }
  
  .right-section {
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    width: 100%;
  }
  
  .btn-secondary, .btn-primary {
    flex: 1;
    min-width: 100px;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .view-mode-selector {
    flex-direction: column;
    width: 100%;
  }
  
  .mode-btn {
    width: 100%;
    justify-content: center;
  }
  
  .left-section {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .course-info {
    width: 100%;
    gap: 4px;
  }
  
  .course-title {
    max-width: 100%;
    font-size: 14px;
  }
  
  .course-actions {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .course-status {
    font-size: 10px;
    padding: 2px 8px;
  }
  
  .right-section {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-secondary, .btn-primary {
    width: 100%;
  }
}
</style>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import draggable from 'vuedraggable'
import AddPageModal from '@/components/course-builder/AddPageModal.vue'
import AddWidgetModal from '@/components/course-builder/AddWidgetModal.vue'
import ContentWidget from '@/components/course-builder/ContentWidget.vue'
import { courseBuilderAPI } from '@/services/courseBuilderAPI'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const toast = useToast()

// Reactive data
const course = ref(null)
const currentPageId = ref(null)
const currentPage = ref(null)
const showAddPageModal = ref(false)
const showAddWidgetModal = ref(false)
const selectedPageId = ref(null)
const loading = ref(false)
const showPreviewModal = ref(false)
const viewMode = ref('builder') // 'builder' or 'student'
const searchQuery = ref('')
const expandedSections = ref(new Set())
const mobilePanelOpen = ref(false) // Track which sections are expanded

// Simple confirm dialog state
const confirmDialog = reactive({
  visible: false,
  title: 'Confirm Deletion',
  message: '',
  onConfirm: null
})

// Computed
const hasContent = computed(() => {
  return course.value?.pages?.some(page => page.widgets?.length > 0)
})

// Watchers
watch(currentPageId, (newPageId) => {
  if (newPageId && course.value?.pages) {
    selectPage(newPageId)
  }
})

// Lifecycle
onMounted(async () => {
  await loadCourse()
})

// Methods
const loadCourse = async () => {
  try {
    loading.value = true
    const response = await courseBuilderAPI.show(route.params.courseId)
    if (response.status === 'success') {
      course.value = response.data.course
      
      if (course.value.pages?.length > 0) {
        // Set the first page as current
        currentPageId.value = course.value.pages[0].id
        currentPage.value = course.value.pages[0]
      }
    }
  } catch (error) {
    toast.error('Failed to load course')
    console.error('Load course error:', error)
  } finally {
    loading.value = false
  }
}

const selectPage = (pageId) => {
  // Convert pageId to number if it's a string (from dropdown)
  const numericPageId = typeof pageId === 'string' ? parseInt(pageId) : pageId
  
  // Only update if the page actually exists and is different
  if (course.value?.pages && numericPageId !== currentPageId.value) {
    const foundPage = course.value.pages.find(p => p.id === numericPageId)
    if (foundPage) {
      currentPageId.value = numericPageId
      currentPage.value = foundPage
    }
  } else if (course.value?.pages && numericPageId === currentPageId.value) {
    // If it's the same page, just ensure currentPage is set
    currentPage.value = course.value.pages.find(p => p.id === numericPageId)
  }
}

const goBack = () => {
  router.push('/courses')
}

const saveCourse = async () => {
  try {
    // Save all pages and widgets
    if (course.value?.pages) {
      for (const page of course.value.pages) {
        // Update page if it has changes
        await courseBuilderAPI.updatePage(route.params.courseId, page.id, {
          title: page.title,
          description: page.description,
          is_published: false // Keep as draft when saving
        })
        
        // Update all widgets in the page
        if (page.widgets) {
          for (const widget of page.widgets) {
            await courseBuilderAPI.updateWidget(widget.id, {
              widget_data: widget.widget_data,
              is_active: widget.is_active
            })
          }
        }
      }
    }
    toast.success('Course saved successfully')
  } catch (error) {
    console.error('Save course error:', error)
    toast.error('Failed to save course: ' + (error.response?.data?.message || error.message))
  }
}

const previewCourse = () => {
  if (!currentPage.value) {
    toast.info('Select a page to preview')
    return
  }
  showPreviewModal.value = true
}

const publishCourse = async () => {
  if (!hasContent.value) {
    toast.warning('Course must have content before publishing')
    return
  }
  
  try {
    // First save all content
    await saveCourse()
    
    // Then publish all pages with content
    if (course.value?.pages) {
      for (const page of course.value.pages) {
        if (page.widgets && page.widgets.length > 0) {
          // Check if page has active widgets with content
          const hasActiveContent = page.widgets.some(widget => 
            widget.is_active && widget.widget_data && 
            Object.keys(widget.widget_data).length > 0
          )
          
          if (hasActiveContent) {
            await courseBuilderAPI.updatePage(route.params.courseId, page.id, {
              is_published: true
            })
          }
        }
      }
    }
    
    // Finally, submit course for review
    const response = await courseBuilderAPI.publishCourse(route.params.courseId)
    
    if (response.success) {
      toast.success(response.message || 'Course submitted for review successfully')
      // Reload course to get updated status
      await loadCourse()
    } else {
      toast.error(response.message || 'Failed to publish course')
    }
  } catch (error) {
    console.error('Publish course error:', error)
    toast.error('Failed to publish course: ' + (error.response?.data?.message || error.message))
  }
}

const savePage = async () => {
  if (!currentPage.value) return
  
  try {
    await courseBuilderAPI.updatePage(route.params.courseId, currentPage.value.id, {
      title: currentPage.value.title,
      description: currentPage.value.description
    })
    toast.success('Page saved successfully')
  } catch (error) {
    toast.error('Failed to save page')
  }
}

const previewPage = () => {
  if (!currentPage.value) {
    toast.info('Select a page to preview')
    return
  }
  showPreviewModal.value = true
}

const onPageCreated = async (newPage) => {
  await loadCourse()
  currentPageId.value = newPage.id
  selectPage(newPage.id)
  showAddPageModal.value = false
  toast.success('Page created successfully')
}

const onWidgetCreated = async (newWidget) => {
  await loadCourse()
  showAddWidgetModal.value = false
  toast.success('Widget added successfully')
}

const duplicatePage = async (pageId) => {
  try {
    await courseBuilderAPI.duplicatePage(route.params.courseId, pageId)
    await loadCourse()
    toast.success('Page duplicated successfully')
  } catch (error) {
    toast.error('Failed to duplicate page')
  }
}

const deletePage = async (pageId) => {
  openConfirmDialog(
    'Are you sure you want to delete this page? This action cannot be undone.',
    async () => {
      try {
        await courseBuilderAPI.deletePage(route.params.courseId, pageId)
        await loadCourse()
        if (currentPageId.value === pageId) {
          currentPageId.value = null
          currentPage.value = null
        }
        toast.success('Page deleted successfully')
      } catch (error) {
        toast.error('Failed to delete page')
      }
    }
  )
}

const updatePage = async () => {
  if (!currentPage.value) return
  
  try {
    await courseBuilderAPI.updatePage(route.params.courseId, currentPage.value.id, {
      title: currentPage.value.title,
      description: currentPage.value.description
    })
  } catch (error) {
    toast.error('Failed to update page')
  }
}

const updateWidget = async (widgetId, data) => {
  try {
    await courseBuilderAPI.updateWidget(widgetId, data)
  } catch (error) {
    toast.error('Failed to update widget')
  }
}

const deleteWidget = async (widgetId) => {
  try {
    await courseBuilderAPI.deleteWidget(widgetId)
    await loadCourse()
    toast.success('Widget deleted successfully')
  } catch (error) {
    toast.error('Failed to delete widget')
  }
}

const onWidgetReorder = async (evt) => {
  try {
    const items = currentPage.value.widgets.map((widget, index) => ({
      id: widget.id,
      order_index: index
    }))
    
    await courseBuilderAPI.reorderContent(route.params.courseId, {
      type: 'widgets',
      page_id: currentPage.value.id,
      items
    })
  } catch (error) {
    toast.error('Failed to reorder widgets')
    await loadCourse() // Reload to restore original order
  }
}

const openAddWidgetModal = (pageId) => {
  selectedPageId.value = pageId
  showAddWidgetModal.value = true
}

// Helper methods
const openConfirmDialog = (message, onConfirm) => {
  confirmDialog.title = 'Confirm Deletion'
  confirmDialog.message = message
  confirmDialog.onConfirm = onConfirm
  confirmDialog.visible = true
}

const markLessonComplete = async (page, completed = true) => {
  try {
    if (!page || !page.widgets || page.widgets.length === 0) return

    // Mark all widgets in the page as complete
    for (const widget of page.widgets) {
      await api.post('/progress/mark-complete', {
        course_id: course.value.id,
        page_id: page.id,
        widget_id: widget.id,
        completed: completed
      })
    }

    // Update local state
    page.completed = completed
    page.widgets.forEach(widget => {
      widget.completed = completed
    })

    toast.success(completed ? 'Marked as complete' : 'Marked as incomplete')
  } catch (error) {
    console.error('Error updating progress:', error)
    toast.error('Failed to update progress')
  }
}

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

// Helper function to truncate text for display
const truncateText = (text, maxLength = 30) => {
  if (!text) return 'Untitled'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

// Toggle section expansion
const toggleSection = (pageId) => {
  if (expandedSections.value.has(pageId)) {
    expandedSections.value.delete(pageId)
  } else {
    expandedSections.value.add(pageId)
  }
}

const toggleMobilePanel = () => {
  mobilePanelOpen.value = !mobilePanelOpen.value
}

// Check if section is expanded
const isSectionExpanded = (pageId) => {
  return expandedSections.value.has(pageId)
}

const closeConfirmDialog = () => {
  confirmDialog.visible = false
  confirmDialog.onConfirm = null
}

const confirmDialogConfirm = async () => {
  const fn = confirmDialog.onConfirm
  closeConfirmDialog()
  if (typeof fn === 'function') {
    await fn()
  }
}
const getWidgetIcon = (widgetType) => {
  const icons = {
    text: 'fas fa-font',
    image: 'fas fa-image',
    video: 'fas fa-video',
    mcq: 'fas fa-question-circle',
    poll: 'fas fa-poll',
    file: 'fas fa-file',
    code: 'fas fa-code',
    embed: 'fas fa-external-link-alt',
    quiz: 'fas fa-clipboard-check',
    assignment: 'fas fa-tasks'
  }
  return icons[widgetType] || 'fas fa-cube'
}

const getWidgetTypeName = (widgetType) => {
  const names = {
    text: 'Text',
    image: 'Image',
    video: 'Video',
    mcq: 'MCQ',
    poll: 'Poll',
    file: 'File',
    code: 'Code',
    embed: 'Embed',
    quiz: 'Quiz',
    assignment: 'Assignment'
  }
  return names[widgetType] || 'Widget'
}

// Preview helpers
const closePreview = () => {
  showPreviewModal.value = false
}

// very light-weight preview renderers for common widget types
const PreviewText = {
  props: ['widget'],
  template: `<div class=\"prose max-w-none whitespace-pre-wrap\">{{ widget.widget_data?.content || '' }}</div>`
}
const PreviewImage = {
  props: ['widget'],
  template: `<img :src=\"widget.widget_data?.url\" :alt=\"widget.widget_data?.alt || ''\" class=\"max-w-full rounded\" />`
}
const PreviewVideo = {
  props: ['widget'],
  template: `<div class=\"aspect-video\"><iframe :src=\"widget.widget_data?.url\" frameborder=\"0\" allowfullscreen class=\"w-full h-full\"></iframe></div>`
}
const PreviewEmbed = { props: ['widget'], template: `<div v-html=\"widget.widget_data?.embed_code || ''\"></div>` }
const PreviewFile = { props: ['widget'], template: `<a :href=\"widget.widget_data?.url\" target=\"_blank\" class=\"text-primary-600 underline\">Download file</a>` }
const PreviewCode = { props: ['widget'], template: `<pre class=\"bg-gray-100 rounded p-4 overflow-auto\"><code>{{ widget.widget_data?.code || '' }}</code></pre>` }
const PreviewMCQ = { props: ['widget'], template: `<div><p class=\"font-medium mb-2\">{{ widget.widget_data?.question }}</p><ul class=\"list-disc ml-6\"><li v-for=\"(opt,i) in (widget.widget_data?.options||[])\" :key=\"i\">{{ opt }}</li></ul></div>` }
const PreviewPoll = PreviewMCQ
const PreviewPlaceholder = { template: `<div class=\"text-gray-500\">Preview not available for this widget type.</div>` }

const getPreviewRenderer = (widget) => {
  const type = widget.widget_type
  const map = {
    text: PreviewText,
    image: PreviewImage,
    video: PreviewVideo,
    mcq: PreviewMCQ,
    poll: PreviewPoll,
    file: PreviewFile,
    code: PreviewCode,
    embed: PreviewEmbed
  }
  return map[type] || PreviewPlaceholder
}
</script>

<style scoped>
/* Component-specific styles only */
</style>
