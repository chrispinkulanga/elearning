<template>
  <div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
      <div class="flex flex-col lg:flex-row gap-4 sm:gap-8">
        <!-- Left Sidebar - Categories -->
        <div class="hidden lg:block w-full lg:w-64 flex-shrink-0">
          <div class="sticky top-4 bg-white rounded-lg shadow-sm p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Categories</h3>
            <div class="grid grid-cols-1 gap-2 sm:gap-3">
              <div
                v-for="category in categories"
                :key="category.value"
                @click="selectCategory(category.value)"
                :class="[
                  'p-3 sm:p-4 rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md',
                  selectedCategory === category.value ? 'ring-2 ring-primary-500 bg-primary-50' : 'bg-gray-50 hover:bg-gray-100'
                ]"
              >
                <div class="  flex items-center space-x-2 sm:space-x-3">
                  <div 
                    class="w-2 h-2 sm:w-3 sm:h-3 rounded-full flex-shrink-0"
                    :style="{ backgroundColor: category.color }"
                  ></div>
                  <div class="min-w-0 flex-1">
                    <h4 class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ category.name }}</h4>
                    <p class="text-xs sm:text-sm text-gray-500 whitespace-nowrap">{{ category.count }} topics</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">
      <!-- Header -->
      <div class="mb-4 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Community Forum</h1>
            <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Connect with other learners and share your thoughts</p>
          </div>
                  <div class="flex flex-row gap-2 sm:gap-3 items-center">
          <router-link
            to="/forum/topics/create"
            class="btn btn-primary text-sm sm:text-base px-4 py-2 w-fit"
          >
            <span class="hidden sm:inline">Create New Topic</span>
            <span class="sm:hidden">+ New Topic</span>
          </router-link>
          <router-link
            to="/forum/search"
            class="btn btn-secondary text-sm sm:text-base px-4 py-2 w-fit"
          >
            Search Topics
          </router-link>

        </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="mb-4 sm:mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto">
            <button
              @click="setActiveTab('trending')"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 whitespace-nowrap',
                activeTab === 'trending'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Trending
            </button>
            <button
              @click="setActiveTab('all')"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 whitespace-nowrap',
                activeTab === 'all'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              All Forum
            </button>
            <button
              @click="setActiveTab('new')"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 whitespace-nowrap',
                activeTab === 'new'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              New Post
            </button>
          </nav>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow-sm mb-4 sm:mb-6">
        <div class="p-4 sm:p-6">
          <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="col-span-2 lg:col-span-2">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Search Topics</label>
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search topics..."
                  class="w-full pl-8 sm:pl-10 pr-3 sm:pr-4 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                />
                <svg class="absolute left-2 sm:left-3 top-2.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
            <div class="col-span-1 lg:col-span-1">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Category</label>
              <select
                v-model="selectedCategory"
                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option 
                  v-for="category in categories" 
                  :key="category.value" 
                  :value="category.value"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div class="col-span-1 lg:col-span-1">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Sort By</label>
              <select
                v-model="sortBy"
                class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="latest">Latest</option>
                <option value="popular">Most Popular</option>
                <option value="replies">Most Replies</option>
                <option value="views">Most Views</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Topics List -->
      <div v-else-if="topics.length > 0" class="space-y-3 sm:space-y-4">
        <div
          v-for="topic in filteredTopics"
          :key="topic.id"
          class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="p-4 sm:p-6">
            <div class="flex items-start space-x-3 sm:space-x-4">
              <!-- User Avatar -->
              <div class="flex-shrink-0">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-primary-100 flex items-center justify-center overflow-hidden">
                  <img
                    v-if="topic.user?.avatar"
                    :src="`${storageUrl}/storage/${topic.user.avatar}`"
                    :alt="topic.user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-primary-600 font-medium text-xs sm:text-sm">
                    {{ topic.user?.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>
              </div>

              <!-- Topic Content -->
              <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 mb-2">
                  <router-link
                    :to="`/forum/topics/${topic.id}`"
                    class="text-base sm:text-lg font-medium text-gray-900 hover:text-primary-600 line-clamp-2 sm:truncate"
                  >
                    {{ topic.title }}
                  </router-link>
                  <div class="flex flex-wrap gap-1 sm:gap-2">
                    <span
                      :class="[
                        'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap',
                        getCategoryColor(topic.category)
                      ]"
                    >
                      {{ topic.category }}
                    </span>
                    <span v-if="topic.is_pinned" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 whitespace-nowrap">
                      Pinned
                    </span>
                    <span v-if="topic.is_locked" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 whitespace-nowrap">
                      Locked
                    </span>
                  </div>
                </div>

                <p class="text-xs sm:text-sm text-gray-600 mb-3 line-clamp-2">
                  {{ topic.content }}
                </p>

                <!-- Poll Display -->
                <div v-if="topic.poll_question" class="mb-3 p-2 sm:p-3 bg-blue-50 rounded-lg border border-blue-200">
                  <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="text-xs sm:text-sm font-medium text-blue-800">Poll: {{ topic.poll_question }}</span>
                  </div>
                  <div class="space-y-2">
                    <div 
                      v-for="(option, index) in topic.poll_options" 
                      :key="index"
                      :class="[
                        'flex items-center justify-between p-2 rounded-md border transition-all duration-200 cursor-pointer',
                        hasUserVotedForOption(topic, index) 
                          ? 'bg-green-50 border-green-300' 
                          : topic.poll_is_multiple_choice
                            ? 'bg-blue-50 border-blue-200 hover:bg-blue-100'
                            : hasUserVotedInPoll(topic) 
                              ? 'bg-gray-50 border-gray-200 cursor-not-allowed opacity-60'
                              : 'bg-blue-50 border-blue-200 hover:bg-blue-100'
                      ]"
                      @click="topic.poll_is_multiple_choice || !hasUserVotedInPoll(topic) ? votePoll(topic, index) : null"
                    >
                      <div class="flex items-center space-x-2 flex-1">
                        <input 
                          :type="topic.poll_is_multiple_choice ? 'checkbox' : 'radio'" 
                          :name="'poll-' + topic.id" 
                          :value="option"
                          :checked="hasUserVotedForOption(topic, index)"
                          :disabled="!topic.poll_is_multiple_choice && hasUserVotedInPoll(topic) && !hasUserVotedForOption(topic, index)"
                          @click.stop="votePoll(topic, index)"
                          class="w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer"
                        />
                        <span class="text-sm text-blue-800 flex-1">{{ option }}</span>
                        <div v-if="hasUserVotedForOption(topic, index)" class="flex items-center">
                          <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                          </svg>
                          <span class="text-xs text-green-600 font-medium ml-1">Your vote</span>
                        </div>
                      </div>
                      <div class="flex items-center space-x-2 ml-3">
                        <span class="text-xs text-blue-600 font-medium">
                          {{ getCurrentVoteCount(topic, index) }} votes
                        </span>
                        <div class="w-16 h-2 bg-blue-200 rounded-full overflow-hidden">
                          <div 
                            class="h-full bg-blue-500 transition-all duration-300"
                            :style="{ width: getPollVotePercentage(topic, index) + '%' }"
                          ></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Attachments Display -->
                <div v-if="topic.attachments_count > 0 && topic.attachments && topic.attachments.length > 0" class="mb-3 p-2 sm:p-3 bg-gray-50 rounded-lg border border-gray-200">
                  <div class="flex items-center space-x-2 mb-3">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    <span class="text-xs sm:text-sm font-medium text-gray-800">{{ topic.attachments_count }} Attachment{{ topic.attachments_count > 1 ? 's' : '' }}</span>
                  </div>
                  
                  
                  <!-- Images Grid - Optimized for better fit -->
                  <div v-if="getImageAttachments(topic.attachments).length > 0" class="mb-3">
                    <div class="flex flex-wrap gap-1 max-w-md sm:max-w-lg">
                      <!-- Dynamic grid based on number of images -->
                      <div 
                        v-for="(attachment, index) in getImageAttachments(topic.attachments).slice(0, 4)" 
                        :key="attachment.filename"
                        :class="[
                          'relative group cursor-pointer overflow-hidden rounded-sm',
                          getImageAttachments(topic.attachments).length === 1 ? 'w-full h-80 sm:h-96' : '',
                          getImageAttachments(topic.attachments).length === 2 ? 'flex-1 h-48 sm:h-56' : '',
                          getImageAttachments(topic.attachments).length === 3 ? (index === 0 ? 'w-full h-48 sm:h-56 mb-1' : 'flex-1 h-36 sm:h-40') : '',
                          getImageAttachments(topic.attachments).length >= 4 ? 'w-[calc(50%-2px)] h-36 sm:h-40' : ''
                        ]"
                        @click="openImageModal(attachment)"
                      >
                        <img 
                          :src="getAttachmentUrl(attachment.file_path)"
                          :alt="attachment.original_filename"
                          :class="[
                            'w-full h-full object-cover transition-transform duration-200',
                            getImageAttachments(topic.attachments).length === 1 ? '' : 'hover:scale-105'
                          ]"
                          @error="handleImageError"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                          <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                          </svg>
                        </div>
                      </div>
                      
                      <!-- 5th image with "+X more" overlay if there are more than 4 images -->
                      <div 
                        v-if="getImageAttachments(topic.attachments).length > 4"
                        class="relative group cursor-pointer w-[calc(50%-2px)] h-36 sm:h-40 overflow-hidden rounded-sm"
                        @click="openImageModal(getImageAttachments(topic.attachments)[4])"
                      >
                        <img 
                          :src="getAttachmentUrl(getImageAttachments(topic.attachments)[4].file_path)"
                          :alt="getImageAttachments(topic.attachments)[4].original_filename"
                          class="w-full h-full object-cover"
                          @error="handleImageError"
                        />
                        <!-- Overlay with "+X more" text -->
                        <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                          <div class="text-white text-center">
                            <div class="text-sm font-bold">+{{ getImageAttachments(topic.attachments).length - 4 }}</div>
                            <div class="text-xs">more</div>
                          </div>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                          <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Fallback: Direct Image Display - Optimized for better fit -->
                  <div v-else-if="topic.attachments && topic.attachments.some(att => att.is_image === true)" class="mb-3">
                    <div class="flex flex-wrap gap-1 max-w-md sm:max-w-lg">
                      <!-- Dynamic grid based on number of images -->
                      <div 
                        v-for="(attachment, index) in topic.attachments.filter(att => att.is_image === true).slice(0, 4)" 
                        :key="attachment.filename"
                        :class="[
                          'relative group cursor-pointer overflow-hidden rounded-sm',
                          topic.attachments.filter(att => att.is_image === true).length === 1 ? 'w-full h-80 sm:h-96' : '',
                          topic.attachments.filter(att => att.is_image === true).length === 2 ? 'flex-1 h-48 sm:h-56' : '',
                          topic.attachments.filter(att => att.is_image === true).length === 3 ? (index === 0 ? 'w-full h-48 sm:h-56 mb-1' : 'flex-1 h-36 sm:h-40') : '',
                          topic.attachments.filter(att => att.is_image === true).length >= 4 ? 'w-[calc(50%-2px)] h-36 sm:h-40' : ''
                        ]"
                        @click="openImageModal(attachment)"
                      >
                        <img 
                          :src="getAttachmentUrl(attachment.file_path)"
                          :alt="attachment.original_filename"
                          :class="[
                            'w-full h-full object-cover transition-transform duration-200',
                            topic.attachments.filter(att => att.is_image === true).length === 1 ? '' : 'hover:scale-105'
                          ]"
                          @error="handleImageError"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                          <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                          </svg>
                        </div>
                      </div>
                      
                      <!-- 5th image with "+X more" overlay if there are more than 4 images -->
                      <div 
                        v-if="topic.attachments.filter(att => att.is_image === true).length > 4"
                        class="relative group cursor-pointer w-[calc(50%-2px)] h-36 sm:h-40 overflow-hidden rounded-sm"
                        @click="openImageModal(topic.attachments.filter(att => att.is_image === true)[4])"
                      >
                        <img 
                          :src="getAttachmentUrl(topic.attachments.filter(att => att.is_image === true)[4].file_path)"
                          :alt="topic.attachments.filter(att => att.is_image === true)[4].original_filename"
                          class="w-full h-full object-cover"
                          @error="handleImageError"
                        />
                        <!-- Overlay with "+X more" text -->
                        <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                          <div class="text-white text-center">
                            <div class="text-sm font-bold">+{{ topic.attachments.filter(att => att.is_image === true).length - 4 }}</div>
                            <div class="text-xs">more</div>
                          </div>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                          <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Videos Grid -->
                  <div v-if="getVideoAttachments(topic.attachments).length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div 
                      v-for="attachment in getVideoAttachments(topic.attachments)" 
                      :key="attachment.filename"
                      class="relative group cursor-pointer"
                      @click="openVideoModal(attachment)"
                    >
                      <video 
                        :src="getAttachmentUrl(attachment.file_path)"
                        class="w-full h-48 object-cover rounded-md border border-gray-200 hover:border-gray-300 transition-colors"
                        preload="metadata"
                        @error="handleVideoError"
                      />
                      <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center rounded-md">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M8 5v14l11-7z"/>
                        </svg>
                      </div>
                      <div class="absolute bottom-2 left-2 bg-black bg-opacity-70 text-white text-sm px-3 py-1 rounded">
                        {{ formatFileSize(attachment.file_size) }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Documents List -->
                  <div v-if="getDocumentAttachments(topic.attachments).length > 0" class="space-y-2">
                    <div 
                      v-for="attachment in getDocumentAttachments(topic.attachments)" 
                      :key="attachment.filename"
                      class="flex items-center space-x-3 p-2 bg-white rounded-md border border-gray-200 hover:border-gray-300 transition-colors"
                    >
                      <!-- Document Icon -->
                      <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                      </div>
                      
                      <!-- File Info -->
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.original_filename }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(attachment.file_size) }} • {{ attachment.file_type.toUpperCase() }}</p>
                      </div>
                      
                      <!-- Download Button -->
                      <button 
                        @click="downloadAttachment(attachment)"
                        class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                        title="Download file"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs sm:text-sm text-gray-500">
                  <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <span>By {{ topic.user?.name }}</span>
                    <span class="hidden sm:inline">{{ formatDate(topic.created_at) }}</span>
                    <span class="sm:hidden">{{ formatDateShort(topic.created_at) }}</span>
                    <div class="flex items-center space-x-1 cursor-pointer hover:text-primary-600 transition-colors" @click="openRepliesModal(topic)">
                      <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                      <span>{{ topic.replies_count || 0 }} replies</span>
                    </div>
                    <div class="flex items-center space-x-1">
                      <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      <span>{{ topic.views || 0 }} views</span>
                    </div>
                     <div class="flex items-center space-x-1">
                       <button
                         @click="likeTopic(topic)"
                         :class="[
                           'flex items-center space-x-1 transition-colors duration-200',
                           topic.is_liked ? 'text-red-600' : 'text-gray-500 hover:text-red-600'
                         ]"
                       >
                         <svg class="w-3 h-3 sm:w-4 sm:h-4" :fill="topic.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                         </svg>
                         <span>{{ topic.likes_count || 0 }}</span>
                       </button>
                     </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex flex-wrap items-center gap-2">
                    <router-link
                      :to="`/forum/topics/${topic.id}`"
                      class="btn btn-sm btn-primary text-xs sm:text-sm px-3 py-1"
                    >
                      <span class="hidden sm:inline">View Discussion</span>
                      <span class="sm:hidden">View</span>
                    </router-link>
                    <button
                      v-if="canEditTopic(topic)"
                      @click="editTopic(topic)"
                      class="btn btn-sm btn-secondary text-xs sm:text-sm px-2 py-1"
                      :title="'Edit topic'"
                    >
                      <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                      <span class="hidden sm:inline">Edit</span>
                    </button>
                    <button
                      v-if="canDeleteTopic(topic)"
                      @click="deleteTopic(topic.id)"
                      class="btn btn-sm btn-danger text-xs sm:text-sm px-2 py-1"
                      :title="'Delete topic'"
                    >
                      <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                      <span class="hidden sm:inline">Delete</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex justify-center mt-8">
          <nav class="flex items-center space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                page === pagination.current_page
                  ? 'bg-primary-600 text-white'
                  : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </nav>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No topics found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ searchQuery ? 'No topics match your search criteria.' : 'Be the first to start a discussion!' }}
        </p>
        <div class="mt-6">
          <router-link
            to="/forum/topics/create"
            class="btn btn-primary w-fit"
          >
            Create First Topic
          </router-link>
        </div>
      </div>
    </div>

    <!-- Create Topic Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-medium text-gray-900">Create New Topic</h3>
          <button
            @click="showCreateModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="createTopic" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              v-model="newTopic.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': topicErrors.title }"
              placeholder="Enter topic title"
            />
            <p v-if="topicErrors.title" class="mt-1 text-sm text-red-600">{{ topicErrors.title }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <select
              v-model="newTopic.category"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': topicErrors.category }"
            >
              <option value="">Select Category</option>
              <option 
                v-for="category in categories.filter(cat => cat.value !== '')" 
                :key="category.value" 
                :value="category.value"
              >
                {{ category.name }}
              </option>
            </select>
            <p v-if="topicErrors.category" class="mt-1 text-sm text-red-600">{{ topicErrors.category }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
            <textarea
              v-model="newTopic.content"
              rows="6"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': topicErrors.content }"
              placeholder="Share your thoughts, questions, or start a discussion..."
            ></textarea>
            <p v-if="topicErrors.content" class="mt-1 text-sm text-red-600">{{ topicErrors.content }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="showCreateModal = false"
              class="btn btn-secondary w-fit"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="creating"
              class="btn btn-primary w-fit"
            >
              <span v-if="creating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ creating ? 'Creating...' : 'Create Topic' }}
            </button>
          </div>
        </form>
      </div>
        </div>
      </div>
    </div>

    <!-- Replies Modal -->
    <div v-if="showRepliesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeRepliesModal">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Replies to "{{ selectedTopic?.title }}"</h3>
            <button @click="closeRepliesModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Original Topic -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                  <img
                    v-if="selectedTopic?.user?.avatar"
                    :src="`${storageUrl}/storage/${selectedTopic.user.avatar}`"
                    :alt="selectedTopic.user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-sm font-medium text-primary-600">
                    {{ selectedTopic?.user?.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                  <span class="text-sm font-medium text-gray-900">{{ selectedTopic?.user?.name }}</span>
                  <span class="text-xs text-gray-500">{{ formatDate(selectedTopic?.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-700">{{ selectedTopic?.content }}</p>
                <div class="flex items-center space-x-1 mt-2">
                  <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                  </svg>
                  <span class="text-xs text-gray-500">{{ selectedTopic?.likes_count || 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Replies List -->
          <div class="mb-6 max-h-96 overflow-y-auto">
            <div v-if="loadingReplies" class="text-center py-4">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
              <p class="text-sm text-gray-500 mt-2">Loading replies...</p>
            </div>
            <div v-else-if="topicReplies.length === 0" class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              <p>No replies yet. Be the first to reply!</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="reply in getTopLevelReplies()" :key="reply.id" class="p-4 bg-white border rounded-lg">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                      <img
                        v-if="reply.user?.avatar"
                        :src="`${storageUrl}/storage/${reply.user.avatar}`"
                        :alt="reply.user.name"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-sm font-medium text-primary-600">
                        {{ reply.user?.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-1">
                      <span class="text-sm font-medium text-gray-900">{{ reply.user?.name }}</span>
                      <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mb-2">{{ reply.content }}</p>
                    <div class="flex items-center space-x-4">
                      <button 
                        @click="likeReply(reply)"
                        class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-500 transition-colors"
                      >
                        <svg class="w-4 h-4" :class="reply.is_liked ? 'text-red-500 fill-current' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span>{{ reply.upvotes || 0 }}</span>
                      </button>
                      <button 
                        @click="openReplyModal(reply)"
                        class="text-xs text-gray-500 hover:text-primary-600 transition-colors"
                        :disabled="selectedTopic?.is_locked"
                        :title="selectedTopic?.is_locked ? 'This topic is locked' : 'Reply to this message'"
                      >
                        Reply
                      </button>
                    </div>
                    
                    <!-- Inline Reply Form for Top-Level Replies (INSIDE MODAL) -->
                    <div v-if="showInlineReplyForm && inlineReplyParent === reply.id" class="mt-4 p-4 bg-yellow-100 border-2 border-red-500 rounded-lg shadow-xl relative z-50" style="background-color: #fef3c7 !important; border: 3px solid #ef4444 !important; min-height: 200px;">
                      <!-- Debug info -->
                      <div class="text-xs text-gray-500 mb-2">
                        Debug: showInlineReplyForm={{ showInlineReplyForm }}, inlineReplyParent={{ inlineReplyParent }}, reply.id={{ reply.id }}
                      </div>
                      <h5 class="text-sm font-medium text-gray-900 mb-2">Reply to {{ reply.user?.name }}</h5>
                      <form @submit.prevent="submitInlineReply">
                        <textarea
                          v-model="inlineReply.content"
                          rows="3"
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                          :class="{ 'border-red-500': inlineReplyErrors.content }"
                          placeholder="Write your reply..."
                        ></textarea>
                        <p v-if="inlineReplyErrors.content" class="mt-1 text-xs text-red-600">{{ inlineReplyErrors.content }}</p>
                        <div class="flex justify-end space-x-2 mt-2">
                          <button
                            type="button"
                            @click="closeInlineReplyForm"
                            class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                          >
                            Cancel
                          </button>
                          <button
                            type="submit"
                            :disabled="submittingInlineReply"
                            class="px-3 py-1 text-xs font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                          >
                            <span v-if="submittingInlineReply" class="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-1 inline-block"></span>
                            {{ submittingInlineReply ? 'Posting...' : 'Post Reply' }}
                          </button>
                        </div>
                      </form>
                    </div>
                    
                    <!-- View Replies Button -->
                    <div v-if="shouldShowViewRepliesButton(reply.id)" class="mt-2">
                      <button 
                        @click="toggleReplies(reply.id)"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center space-x-1 transition-colors duration-200 hover:bg-blue-50 px-2 py-1 rounded"
                      >
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': isRepliesExpanded(reply.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span>
                          {{ isRepliesExpanded(reply.id) ? 'Hide' : 'View' }} {{ getAllDescendants(reply.id).length }} {{ getAllDescendants(reply.id).length === 1 ? 'reply' : 'replies' }}
                        </span>
                      </button>
                    </div>
                    
                    <!-- Nested Replies (Collapsible; indent capped at this level) -->
                    <div v-if="isRepliesExpanded(reply.id) && getRepliesToMessage(reply.id).length > 0" class="mt-2 ml-4 space-y-2">
                      <div v-for="(nestedReply, nestedIndex) in getRepliesToMessage(reply.id)" :key="nestedReply.id" class="relative">
                        <!-- Arrow Connector -->
                        <div class="absolute -left-6 top-3 w-4 h-4 flex items-center justify-center">
                          <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                        
                        <!-- Vertical Line -->
                        <div class="absolute -left-6 top-6 w-px h-8 bg-gray-300"></div>
                        
                        <!-- Nested Reply Content -->
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg ml-2">
                          <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                              <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center overflow-hidden">
                                <img
                                  v-if="nestedReply.user?.avatar"
                                  :src="`${storageUrl}/storage/${nestedReply.user.avatar}`"
                                  :alt="nestedReply.user.name"
                                  class="w-full h-full object-cover"
                                />
                                <span v-else class="text-xs font-medium text-blue-600">
                                  {{ nestedReply.user?.name?.charAt(0)?.toUpperCase() }}
                                </span>
                              </div>
                            </div>
                            <div class="flex-1 min-w-0">
                              <div class="flex items-center space-x-2 mb-1">
                                <span class="text-xs font-medium text-blue-900">{{ nestedReply.user?.name }}</span>
                                <span class="text-xs text-blue-600">{{ formatDate(nestedReply.created_at) }}</span>
                              </div>
                              <p class="text-xs text-blue-800 mb-2">{{ nestedReply.content }}</p>
                              <div class="flex items-center space-x-3">
                                <button 
                                  @click="likeReply(nestedReply)"
                                  class="flex items-center space-x-1 text-xs text-blue-500 hover:text-red-500 transition-colors"
                                >
                                  <svg class="w-3 h-3" :class="nestedReply.is_liked ? 'text-red-500 fill-current' : 'text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                  </svg>
                                  <span>{{ nestedReply.upvotes || 0 }}</span>
                                </button>
                                <button 
                                  @click="openReplyModal(nestedReply)"
                                  class="text-xs text-blue-500 hover:text-blue-700 transition-colors"
                                  :disabled="selectedTopic?.is_locked"
                                  :title="selectedTopic?.is_locked ? 'This topic is locked' : 'Reply to this message'"
                                >
                                  Reply
                                </button>
                                <button 
                                  v-if="getRepliesToMessage(nestedReply.id).length > 0" 
                                  @click="toggleReplies(nestedReply.id)"
                                  class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center space-x-1 transition-colors duration-200 hover:bg-blue-50 px-2 py-1 rounded"
                                >
                                  <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': isRepliesExpanded(nestedReply.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                  </svg>
                                  <span>
                                    {{ isRepliesExpanded(nestedReply.id) ? 'Hide' : 'View' }} {{ getRepliesToMessage(nestedReply.id).length }} {{ getRepliesToMessage(nestedReply.id).length === 1 ? 'reply' : 'replies' }}
                                  </span>
                                </button>
                              </div>
                            
                            <!-- Inline Reply Form for Nested Replies (INSIDE MODAL) -->
                            <div v-if="showInlineReplyForm && inlineReplyParent === nestedReply.id" class="mt-4 p-4 bg-yellow-100 border-2 border-red-500 rounded-lg shadow-xl relative z-50" style="background-color: #fef3c7 !important; border: 3px solid #ef4444 !important; min-height: 200px;">
                              <!-- Debug info -->
                              <div class="text-xs text-gray-500 mb-2">
                                Debug: showInlineReplyForm={{ showInlineReplyForm }}, inlineReplyParent={{ inlineReplyParent }}, nestedReply.id={{ nestedReply.id }}
                              </div>
                              <h5 class="text-sm font-medium text-gray-900 mb-2">Reply to {{ nestedReply.user?.name }}</h5>
                              <form @submit.prevent="submitInlineReply">
                                <textarea
                                  v-model="inlineReply.content"
                                  rows="3"
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                                  :class="{ 'border-red-500': inlineReplyErrors.content }"
                                  placeholder="Write your reply..."
                                ></textarea>
                                <p v-if="inlineReplyErrors.content" class="mt-1 text-xs text-red-600">{{ inlineReplyErrors.content }}</p>
                                <div class="flex justify-end space-x-2 mt-2">
                                  <button
                                    type="button"
                                    @click="closeInlineReplyForm"
                                    class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                  >
                                    Cancel
                                  </button>
                                  <button
                                    type="submit"
                                    :disabled="submittingInlineReply"
                                    class="px-3 py-1 text-xs font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                                  >
                                    <span v-if="submittingInlineReply" class="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-1 inline-block"></span>
                                    {{ submittingInlineReply ? 'Posting...' : 'Post Reply' }}
                                  </button>
                                </div>
                              </form>
                            </div>
                            
                            <!-- Nested Replies for this nested reply -->
                            <div v-if="isRepliesExpanded(nestedReply.id) && getRepliesToMessage(nestedReply.id).length > 0" class="mt-2 space-y-2">
                              <div v-for="(subReply, subIndex) in getRepliesToMessage(nestedReply.id)" :key="subReply.id" class="relative">
                                <!-- Arrow Connector -->
                                <div class="absolute -left-6 top-3 w-4 h-4 flex items-center justify-center">
                                  <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                  </svg>
                                </div>
                                
                                <!-- Vertical Line -->
                                <div class="absolute -left-6 top-6 w-px h-8 bg-gray-300"></div>
                                
                                <!-- Sub Reply Content -->
                                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                  <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                      <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center overflow-hidden">
                                        <img
                                          v-if="subReply.user?.avatar"
                                          :src="`${storageUrl}/storage/${subReply.user.avatar}`"
                                          :alt="subReply.user.name"
                                          class="w-full h-full object-cover"
                                        />
                                        <span v-else class="text-xs font-medium text-green-600">
                                          {{ subReply.user?.name?.charAt(0)?.toUpperCase() }}
                                        </span>
                                      </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                      <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-medium text-green-900">{{ subReply.user?.name }}</span>
                                        <span class="text-xs text-green-600">{{ formatDate(subReply.created_at) }}</span>
                                      </div>
                                      <p class="text-xs text-green-800 mb-2">{{ subReply.content }}</p>
                                      <div class="flex items-center space-x-3">
                                        <button 
                                          @click="likeReply(subReply)"
                                          class="flex items-center space-x-1 text-xs text-green-500 hover:text-red-500 transition-colors"
                                        >
                                          <svg class="w-3 h-3" :class="subReply.is_liked ? 'text-red-500 fill-current' : 'text-green-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                          </svg>
                                          <span>{{ subReply.upvotes || 0 }}</span>
                                        </button>
                                        <button 
                                          @click="openReplyModal(subReply)"
                                          class="text-xs text-green-500 hover:text-green-700 transition-colors"
                                          :disabled="selectedTopic?.is_locked"
                                          :title="selectedTopic?.is_locked ? 'This topic is locked' : 'Reply to this message'"
                                        >
                                          Reply
                                        </button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                
                                <!-- Inline Reply Form for Sub-Replies (Green Messages - INSIDE MODAL) -->
                                <div v-if="showInlineReplyForm && inlineReplyParent === subReply.id" class="mt-4 p-4 bg-yellow-100 border-2 border-red-500 rounded-lg shadow-xl relative z-50" style="background-color: #fef3c7 !important; border: 3px solid #ef4444 !important; min-height: 200px;">
                                  <!-- Debug info -->
                                  <div class="text-xs text-gray-500 mb-2">
                                    Debug: showInlineReplyForm={{ showInlineReplyForm }}, inlineReplyParent={{ inlineReplyParent }}, subReply.id={{ subReply.id }}
                              </div>
                                  <h5 class="text-sm font-medium text-gray-900 mb-2">Reply to {{ subReply.user?.name }} (GREEN LEVEL)</h5>
                                  <form @submit.prevent="submitInlineReply">
                                    <textarea
                                      v-model="inlineReply.content"
                                      rows="4"
                                      required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm resize-none"
                                      :class="{ 'border-red-500': inlineReplyErrors.content }"
                                      placeholder="Write your reply to this green level message..."
                                    ></textarea>
                                    <p v-if="inlineReplyErrors.content" class="mt-1 text-xs text-red-600">{{ inlineReplyErrors.content }}</p>
                                    <div class="flex justify-end space-x-2 mt-2">
                                      <button
                                        type="button"
                                        @click="closeInlineReplyForm"
                                        class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition-colors"
                                      >
                                        Cancel
                                      </button>
                                      <button
                                        type="submit"
                                        :disabled="submittingInlineReply"
                                        class="px-4 py-1 text-sm bg-primary-600 text-white rounded hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                      >
                                        {{ submittingInlineReply ? 'Posting...' : 'Post Reply' }}
                                      </button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                                      </div>
                                    </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Inline Reply Forms for Nested Replies -->
          <!-- Debug Panel for Modal -->
          <div class="bg-gray-100 p-2 text-xs border rounded mb-4">
            <div>Modal Level Debug</div>
            <div>Show Inline Reply Form: {{ showInlineReplyForm }}</div>
            <div>Inline Reply Parent: {{ inlineReplyParent }}</div>
            <div>Total Replies: {{ topicReplies.length }}</div>
          </div>

          <!-- Reply Form -->
          <div v-if="!selectedTopic?.is_locked" class="border-t pt-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Your Reply</h4>
            <form @submit.prevent="submitReply">
              <textarea
                v-model="newReply.content"
                rows="4"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': replyErrors.content }"
                placeholder="Write your reply..."
              ></textarea>
              <p v-if="replyErrors.content" class="mt-1 text-sm text-red-600">{{ replyErrors.content }}</p>
              <div class="flex justify-end space-x-3 mt-3">
                <button
                  type="button"
                  @click="closeRepliesModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submittingReply"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  <span v-if="submittingReply" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></span>
                  {{ submittingReply ? 'Posting...' : 'Post Reply' }}
                </button>
              </div>
            </form>
          </div>
          <div v-else class="border-t pt-4">
            <div class="bg-red-50 border border-red-200 rounded-md p-3">
              <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="ml-3">
                  <p class="text-sm text-red-800">This discussion is locked. You cannot reply to locked topics.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Nested Reply Modal -->
    <div v-if="showNestedReplyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeNestedReplyModal">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Reply to Message</h3>
            <button @click="closeNestedReplyModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Original Message Being Replied To -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                  <img
                    v-if="selectedReply?.user?.avatar"
                    :src="`${storageUrl}/storage/${selectedReply.user.avatar}`"
                    :alt="selectedReply.user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-sm font-medium text-primary-600">
                    {{ selectedReply?.user?.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                  <span class="text-sm font-medium text-gray-900">{{ selectedReply?.user?.name }}</span>
                  <span class="text-xs text-gray-500">{{ formatDate(selectedReply?.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-700">{{ selectedReply?.content }}</p>
                <div class="flex items-center space-x-1 mt-2">
                  <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                  </svg>
                  <span class="text-xs text-gray-500">{{ selectedReply?.upvotes || 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Existing Replies to This Message -->
          <div v-if="getRepliesToMessage(selectedReply?.id).length > 0" class="mb-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Replies to this message:</h4>
            <div class="max-h-48 overflow-y-auto space-y-3">
              <div v-for="nestedReply in getRepliesToMessage(selectedReply?.id)" :key="nestedReply.id" class="p-3 bg-white border rounded-lg">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                      <img
                        v-if="nestedReply.user?.avatar"
                        :src="`${apiUrl}/storage/${nestedReply.user.avatar}`"
                        :alt="nestedReply.user.name"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-xs font-medium text-primary-600">
                        {{ nestedReply.user?.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-1">
                      <span class="text-xs font-medium text-gray-900">{{ nestedReply.user?.name }}</span>
                      <span class="text-xs text-gray-500">{{ formatDate(nestedReply.created_at) }}</span>
                    </div>
                    <p class="text-xs text-gray-700 mb-2">{{ nestedReply.content }}</p>
                    <div class="flex items-center space-x-3">
                      <button 
                        @click="likeReply(nestedReply)"
                        class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-500 transition-colors"
                      >
                        <svg class="w-3 h-3" :class="nestedReply.is_liked ? 'text-red-500 fill-current' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span>{{ nestedReply.upvotes || 0 }}</span>
                      </button>
                      <button 
                        @click="openReplyModal(nestedReply)"
                        class="text-xs text-gray-500 hover:text-primary-600 transition-colors"
                        :disabled="selectedTopic?.is_locked"
                        :title="selectedTopic?.is_locked ? 'This topic is locked' : 'Reply to this message'"
                      >
                        Reply
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Your Reply Form -->
          <div v-if="!selectedTopic?.is_locked" class="border-t pt-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Your Reply</h4>
            <form @submit.prevent="submitNestedReply">
              <textarea
                v-model="nestedReply.content"
                rows="4"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': nestedReplyErrors.content }"
                placeholder="Write your reply..."
              ></textarea>
              <p v-if="nestedReplyErrors.content" class="mt-1 text-sm text-red-600">{{ nestedReplyErrors.content }}</p>
              <div class="flex justify-end space-x-3 mt-3">
                <button
                  type="button"
                  @click="closeNestedReplyModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submittingNestedReply"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  <span v-if="submittingNestedReply" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></span>
                  {{ submittingNestedReply ? 'Posting...' : 'Post Reply' }}
                </button>
              </div>
            </form>
          </div>
          <div v-else class="border-t pt-4">
            <div class="bg-red-50 border border-red-200 rounded-md p-3">
              <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="ml-3">
                  <p class="text-sm text-red-800">This discussion is locked. You cannot reply to locked topics.</p>
                </div>
              </div>
            </div>
      </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Image Gallery Modal -->
  <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeModals">
    <div class="max-w-6xl max-h-[90vh] w-full mx-4" @click.stop>
      <div class="bg-white rounded-lg overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <h3 class="text-lg font-medium text-gray-900">
            {{ selectedAttachment?.original_filename }}
            <span v-if="imageGallery.length > 1" class="text-sm text-gray-500 ml-2">
              ({{ currentImageIndex + 1 }} of {{ imageGallery.length }})
            </span>
          </h3>
          <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <!-- Image Container with Navigation -->
        <div class="relative p-4">
          <!-- Previous Button -->
          <button 
            v-if="imageGallery.length > 1"
            @click="previousImage"
            :disabled="currentImageIndex === 0"
            class="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          
          <!-- Next Button -->
          <button 
            v-if="imageGallery.length > 1"
            @click="nextImage"
            :disabled="currentImageIndex === imageGallery.length - 1"
            class="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
          
          <!-- Main Image -->
          <img 
            :src="getAttachmentUrl(selectedAttachment?.file_path)"
            :alt="selectedAttachment?.original_filename"
            class="max-w-full max-h-[70vh] object-contain mx-auto"
            @error="handleImageError"
          />
        </div>
        
        <!-- Thumbnail Navigation (if multiple images) -->
        <div v-if="imageGallery.length > 1" class="p-4 border-t bg-gray-50">
          <div class="flex justify-center space-x-2 overflow-x-auto">
            <button
              v-for="(attachment, index) in imageGallery"
              :key="attachment.filename"
              @click="goToImage(index)"
              :class="[
                'flex-shrink-0 w-16 h-16 rounded-md overflow-hidden border-2 transition-all',
                index === currentImageIndex 
                  ? 'border-blue-500 ring-2 ring-blue-200' 
                  : 'border-gray-300 hover:border-gray-400'
              ]"
            >
              <img 
                :src="getAttachmentUrl(attachment.file_path)"
                :alt="attachment.original_filename"
                class="w-full h-full object-cover"
              />
            </button>
          </div>
        </div>
        
        <div class="flex items-center justify-between p-4 border-t bg-gray-50">
          <div class="text-sm text-gray-500">
            {{ formatFileSize(selectedAttachment?.file_size) }} • {{ selectedAttachment?.file_type?.toUpperCase() }}
          </div>
          <button 
            @click="downloadAttachment(selectedAttachment)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Download
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Video Modal -->
  <div v-if="showVideoModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeModals">
    <div class="max-w-4xl max-h-[90vh] w-full mx-4" @click.stop>
      <div class="bg-white rounded-lg overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
          <h3 class="text-lg font-medium text-gray-900">{{ selectedAttachment?.original_filename }}</h3>
          <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="p-4">
          <video 
            :src="getAttachmentUrl(selectedAttachment?.file_path)"
            controls
            class="max-w-full max-h-[70vh] mx-auto"
            @error="handleVideoError"
          >
            Your browser does not support the video tag.
          </video>
        </div>
        <div class="flex items-center justify-between p-4 border-t bg-gray-50">
          <div class="text-sm text-gray-500">
            {{ formatFileSize(selectedAttachment?.file_size) }} • {{ selectedAttachment?.file_type?.toUpperCase() }}
          </div>
          <button 
            @click="downloadAttachment(selectedAttachment)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Download
          </button>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { forumAPI, forumCategoryAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import appConfig from '@/config/app.config'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const creating = ref(false)
const showCreateModal = ref(false)
const topics = ref([])
const pagination = ref(null)
const searchQuery = ref('')
const selectedCategory = ref('')
const sortBy = ref('latest')
const currentPage = ref(1)
const activeTab = ref('trending')

// Categories data
const categories = ref([
  { value: '', name: 'All Categories', color: 'bg-gray-400', count: 0 }
])

const newTopic = reactive({
  title: '',
  category: '',
  content: ''
})

const topicErrors = reactive({})

// Replies modal variables
const showRepliesModal = ref(false)
const selectedTopic = ref(null)
const topicReplies = ref([])
const loadingReplies = ref(false)
const submittingReply = ref(false)
const newReply = reactive({
  content: ''
})
const replyErrors = reactive({})

// Nested reply modal variables
const showNestedReplyModal = ref(false)
const selectedReply = ref(null)
const nestedReply = reactive({
  content: '',
  parent_id: null
})
const nestedReplyErrors = reactive({})
const submittingNestedReply = ref(false)

// Inline reply form variables
const showInlineReplyForm = ref(false)
const inlineReplyParent = ref(null)
const inlineReply = reactive({
  content: '',
  parent_id: null
})
const inlineReplyErrors = reactive({})
const submittingInlineReply = ref(false)

// View replies system
const expandedReplies = ref({}) // Track which replies are expanded
const maxNestingLevel = 2 // Maximum nesting level to prevent excessive indentation

const filteredTopics = computed(() => {
  let filtered = topics.value

  if (selectedCategory.value) {
    filtered = filtered.filter(topic => topic.category === selectedCategory.value)
  }

  // Sort topics - Pinned topics always appear first
  filtered.sort((a, b) => {
    // First, prioritize pinned topics
    if (a.is_pinned && !b.is_pinned) return -1
    if (!a.is_pinned && b.is_pinned) return 1
    
    // If both are pinned or both are not pinned, sort by selected criteria
    switch (sortBy.value) {
      case 'popular':
        return (b.replies_count || 0) - (a.replies_count || 0)
      case 'replies':
        return (b.replies_count || 0) - (a.replies_count || 0)
      case 'views':
        return (b.views || 0) - (a.views || 0)
      case 'latest':
      default:
        return new Date(b.created_at) - new Date(a.created_at)
    }
  })

  return filtered
})

const visiblePages = computed(() => {
  if (!pagination.value) return []
  
  const pages = []
  const start = Math.max(1, pagination.value.current_page - 2)
  const end = Math.min(pagination.value.last_page, pagination.value.current_page + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const apiUrl = computed(() => appConfig.apiUrl)

const storageUrl = computed(() => appConfig.baseUrl)

const getCategoryColor = (category) => {
  const colors = {
    'general': 'bg-blue-100 text-blue-800',
    'technical': 'bg-green-100 text-green-800',
    'course-specific': 'bg-purple-100 text-purple-800',
    'feedback': 'bg-orange-100 text-orange-800'
  }
  return colors[category] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatDateShort = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Poll helper functions
const getPollVoteCount = (topic, optionIndex) => {
  // Use real poll vote data from the backend
  if (topic.poll_votes && Array.isArray(topic.poll_votes)) {
    return topic.poll_votes[optionIndex] || 0
  }
  // If no poll_votes data, return 0 (no votes yet)
  return 0
}

// Reactive vote counts for real-time updates
const pollVoteCounts = ref({})

// Initialize vote counts for topics
const initializeVoteCounts = (topics) => {
  topics.forEach(topic => {
    if (topic.poll_options) {
      pollVoteCounts.value[topic.id] = topic.poll_options.map((_, index) => getPollVoteCount(topic, index))
    }
  })
}

// Get current vote count for an option
const getCurrentVoteCount = (topic, optionIndex) => {
  if (pollVoteCounts.value[topic.id]) {
    return pollVoteCounts.value[topic.id][optionIndex] || 0
  }
  return getPollVoteCount(topic, optionIndex)
}

// Update vote count for an option
const updateVoteCount = (topic, optionIndex, increment = true) => {
  if (!pollVoteCounts.value[topic.id]) {
    pollVoteCounts.value[topic.id] = topic.poll_options.map((_, index) => getPollVoteCount(topic, index))
  }
  
  if (increment) {
    pollVoteCounts.value[topic.id][optionIndex] = (pollVoteCounts.value[topic.id][optionIndex] || 0) + 1
  } else {
    pollVoteCounts.value[topic.id][optionIndex] = Math.max(0, (pollVoteCounts.value[topic.id][optionIndex] || 0) - 1)
  }
}

const getPollVotePercentage = (topic, optionIndex) => {
  const voteCount = getCurrentVoteCount(topic, optionIndex)
  const totalVotes = topic.poll_options?.reduce((total, _, index) => {
    return total + getCurrentVoteCount(topic, index)
  }, 0) || 1
  
  return totalVotes > 0 ? Math.round((voteCount / totalVotes) * 100) : 0
}

// Check if user has voted for a specific poll option
const hasUserVotedForOption = (topic, optionIndex) => {
  const voteKey = `poll_${topic.id}_option_${optionIndex}`
  return localStorage.getItem(voteKey) === 'true'
}

// Check if user has voted for any option in this poll
const hasUserVotedInPoll = (topic) => {
  const pollKey = `poll_${topic.id}_voted`
  return localStorage.getItem(pollKey) === 'true'
}

// Get user's voted options for this poll (for multiple choice)
const getUserVotedOptions = (topic) => {
  const votedOptions = []
  topic.poll_options?.forEach((_, index) => {
    if (hasUserVotedForOption(topic, index)) {
      votedOptions.push(index)
    }
  })
  return votedOptions
}

// Get user's voted option index for this poll (for single choice)
const getUserVotedOption = (topic) => {
  const pollKey = `poll_${topic.id}_voted_option`
  return localStorage.getItem(pollKey) ? parseInt(localStorage.getItem(pollKey)) : null
}

// Handle poll voting
const votePoll = async (topic, optionIndex) => {
  // For single choice polls, prevent multiple votes
  if (!topic.poll_is_multiple_choice && hasUserVotedInPoll(topic)) {
    toast.warning('You have already voted in this poll')
    return
  }

  try {
    const optionKey = `poll_${topic.id}_option_${optionIndex}`
    const isCurrentlyVoted = hasUserVotedForOption(topic, optionIndex)
    
    if (isCurrentlyVoted) {
      // Toggle off the vote (for multiple choice polls)
      if (topic.poll_is_multiple_choice) {
        localStorage.removeItem(optionKey)
        updateVoteCount(topic, optionIndex, false) // Decrement vote count
        toast.success('Vote removed!')
      } else {
        toast.warning('You have already voted for this option')
        return
      }
    } else {
      // Add the vote
      localStorage.setItem(optionKey, 'true')
      updateVoteCount(topic, optionIndex, true) // Increment vote count
      
      // For single choice polls, mark that user has voted
      if (!topic.poll_is_multiple_choice) {
        const pollKey = `poll_${topic.id}_voted`
        const votedOptionKey = `poll_${topic.id}_voted_option`
        localStorage.setItem(pollKey, 'true')
        localStorage.setItem(votedOptionKey, optionIndex.toString())
      }
      
      toast.success('Vote recorded successfully!')
    }
    
    // No need to refresh the page - the UI will update automatically
    // In a real implementation, you would refetch the topic data from the backend
  } catch (error) {
    toast.error('Failed to record vote')
  }
}

// Attachment helper functions
const getAttachmentUrl = (filePath) => {
  return `${storageUrl.value}/storage/${filePath}`
}

const getImageAttachments = (attachments) => {
  if (!attachments || !Array.isArray(attachments)) {
    return []
  }
  return attachments.filter(attachment => attachment.is_image === true)
}

const getVideoAttachments = (attachments) => {
  if (!attachments || !Array.isArray(attachments)) {
    return []
  }
  return attachments.filter(attachment => attachment.is_video === true)
}

const getDocumentAttachments = (attachments) => {
  if (!attachments || !Array.isArray(attachments)) {
    return []
  }
  return attachments.filter(attachment => attachment.is_image !== true && attachment.is_video !== true)
}

// Modal state
const showImageModal = ref(false)
const showVideoModal = ref(false)
const selectedAttachment = ref(null)

// Image gallery state
const imageGallery = ref([])
const currentImageIndex = ref(0)
const currentTopicImages = ref([])

const openImageModal = (attachment) => {
  selectedAttachment.value = attachment
  showImageModal.value = true
  
  // Find the topic that contains this attachment
  const topic = topics.value.find(t => 
    t.attachments && t.attachments.some(att => att.filename === attachment.filename)
  )
  
  if (topic) {
    // Get all images from this topic
    const topicImages = getImageAttachments(topic.attachments)
    imageGallery.value = topicImages
    currentTopicImages.value = topicImages
    
    // Find the index of the clicked image
    const index = topicImages.findIndex(img => img.filename === attachment.filename)
    currentImageIndex.value = index >= 0 ? index : 0
  } else {
    // Fallback for single image
    imageGallery.value = [attachment]
    currentImageIndex.value = 0
  }
}

const openVideoModal = (attachment) => {
  selectedAttachment.value = attachment
  showVideoModal.value = true
}

const closeModals = () => {
  showImageModal.value = false
  showVideoModal.value = false
  selectedAttachment.value = null
  imageGallery.value = []
  currentImageIndex.value = 0
  currentTopicImages.value = []
}

// Image gallery navigation functions
const nextImage = () => {
  if (currentImageIndex.value < imageGallery.value.length - 1) {
    currentImageIndex.value++
    selectedAttachment.value = imageGallery.value[currentImageIndex.value]
  }
}

const previousImage = () => {
  if (currentImageIndex.value > 0) {
    currentImageIndex.value--
    selectedAttachment.value = imageGallery.value[currentImageIndex.value]
  }
}

const goToImage = (index) => {
  if (index >= 0 && index < imageGallery.value.length) {
    currentImageIndex.value = index
    selectedAttachment.value = imageGallery.value[index]
  }
}

const downloadAttachment = (attachment) => {
  const link = document.createElement('a')
  link.href = getAttachmentUrl(attachment.file_path)
  link.download = attachment.original_filename
  link.target = '_blank'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

const handleVideoError = (event) => {
  event.target.style.display = 'none'
}

const canEditTopic = (topic) => {
  return authStore.user?.id === topic.user_id || authStore.isInstructor
}

const canDeleteTopic = (topic) => {
  return authStore.user?.id === topic.user_id || authStore.isInstructor
}

const fetchCategories = async () => {
  try {
    const response = await forumCategoryAPI.getAll()
    if (response.data.status === 'success') {
      const apiCategories = response.data.data.map(cat => ({
        value: cat.slug,
        name: cat.name,
        color: cat.color, // Store the actual hex color
        count: cat.topics_count || 0
      }))
      
      // Add "All Categories" at the beginning
      categories.value = [
        { value: '', name: 'All Categories', color: '#9CA3AF', count: 0 }, // gray-400 hex color
        ...apiCategories
      ]
    }
  } catch (error) {
    console.error('Error fetching categories:', error)
    // Keep default categories if API fails
  }
}

const fetchTopics = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 20,
      category: selectedCategory.value || undefined,
      sort: sortBy.value
    }
    
    const response = await forumAPI.getAllTopics(params)
    
    if (response.data.status === 'success') {
      // Handle both paginated and non-paginated responses
      if (response.data.data.data) {
        // Paginated response
        topics.value = response.data.data.data
        pagination.value = response.data.data
        initializeVoteCounts(response.data.data.data) // Initialize vote counts
      } else {
        // Non-paginated response
        topics.value = response.data.data
        pagination.value = null
        initializeVoteCounts(response.data.data) // Initialize vote counts
      }
      
    } else {
      topics.value = []
      pagination.value = null
    }
    
    // Update category counts after loading topics
    updateCategoryCounts()
  } catch (error) {
    toast.error('Failed to load topics')
    topics.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const createTopic = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to create topics')
    router.push('/auth/login')
    return
  }

  creating.value = true
  topicErrors.value = {}

  try {
    const response = await forumAPI.createStandaloneTopic(newTopic)
    
    if (response.data.status === 'success') {
      const newTopicData = response.data.data
      
      // Add to the beginning of the list
      topics.value.unshift(newTopicData)
      
      // Reset form
      newTopic.title = ''
      newTopic.category = ''
      newTopic.content = ''
      
      showCreateModal.value = false
      toast.success('Topic created successfully!')
      
      // Refresh the list to get updated counts
      await fetchTopics(currentPage.value)
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      topicErrors.value = error.response.data.errors
    } else {
      toast.error('Failed to create topic')
    }
  } finally {
    creating.value = false
  }
}

const editTopic = (topic) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to edit topics')
    router.push('/auth/login')
    return
  }
  
  // Navigate to edit page or open edit modal
  router.push(`/forum/topics/${topic.id}/edit`)
}

const deleteTopic = async (topicId) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to delete topics')
    router.push('/auth/login')
    return
  }

  if (!confirm('Are you sure you want to delete this topic?')) return

  try {
    await forumAPI.deleteStandaloneTopic(topicId)
    
    // Remove from local list
    topics.value = topics.value.filter(topic => topic.id !== topicId)
    toast.success('Topic deleted successfully!')
    
    // Refresh the list to get updated counts
    await fetchTopics(currentPage.value)
  } catch (error) {
    toast.error('Failed to delete topic')
  }
}

const changePage = async (page) => {
  if (page < 1 || (pagination.value && page > pagination.value.last_page)) return
  
  currentPage.value = page
  await fetchTopics(page)
}

const performSearch = async () => {
  if (!searchQuery.value.trim()) {
    await fetchTopics(1)
    return
  }
  
  loading.value = true
  try {
    const response = await forumAPI.searchAllTopics(searchQuery.value)
    
    if (response.data.status === 'success') {
      // Handle both paginated and non-paginated responses
      if (response.data.data.data) {
        // Paginated response
        topics.value = response.data.data.data
        pagination.value = response.data.data
      } else {
        // Non-paginated response
        topics.value = response.data.data
        pagination.value = null
      }
    } else {
      topics.value = []
      pagination.value = null
    }
  } catch (error) {
    toast.error('Search failed')
    topics.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

// Watch for filter/sort changes
watch([selectedCategory, sortBy], () => {
  currentPage.value = 1
  fetchTopics(1)
})

// Watch for search query changes with debounce
watch(searchQuery, (newQuery) => {
  if (newQuery.trim().length >= 2) {
    // Debounce search
    const timeoutId = setTimeout(() => {
      performSearch()
    }, 500)
    return () => clearTimeout(timeoutId)
  } else if (newQuery.trim().length === 0) {
    fetchTopics(1)
  }
})

const likeTopic = async (topic) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to like topics')
    router.push('/auth/login')
    return
  }

  try {
    const response = await forumAPI.likeStandaloneTopic(topic.id)
    if (response.data.status === 'success') {
      // Update the topic's like status and count
      topic.is_liked = response.data.data.is_liked
      topic.likes_count = response.data.data.likes_count
      toast.success(response.data.message)
    }
  } catch (error) {
    toast.error('Failed to like topic')
  }
}

const setActiveTab = (tab) => {
  activeTab.value = tab
  
  // Update sort based on tab
  switch (tab) {
    case 'trending':
      sortBy.value = 'popular'
      break
    case 'all':
      sortBy.value = 'latest'
      break
    case 'new':
      sortBy.value = 'latest'
      break
  }
  
  // Reset to first page and fetch topics
  currentPage.value = 1
  fetchTopics(1)
}

const selectCategory = (categoryValue) => {
  selectedCategory.value = categoryValue
  currentPage.value = 1
  fetchTopics(1)
}

const updateCategoryCounts = () => {
  // Update category counts based on current topics
  categories.value.forEach(category => {
    if (category.value === '') {
      category.count = topics.value.length
    } else {
      category.count = topics.value.filter(topic => topic.category === category.value).length
    }
  })
}

// Replies modal functions
const openRepliesModal = async (topic) => {
  selectedTopic.value = topic
  showRepliesModal.value = true
  loadingReplies.value = true
  topicReplies.value = []
  newReply.content = ''
  replyErrors.value = {}

  try {
    // Fetch replies for this topic
    const response = await forumAPI.getStandaloneTopic(topic.id)
    if (response.data.status === 'success') {
      topicReplies.value = response.data.data.replies || []
    }
  } catch (error) {
    toast.error('Failed to load replies')
  } finally {
    loadingReplies.value = false
  }
}

const closeRepliesModal = () => {
  showRepliesModal.value = false
  selectedTopic.value = null
  topicReplies.value = []
  newReply.content = ''
  replyErrors.value = {}
}

const submitReply = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    router.push('/auth/login')
    return
  }

  // Validate content is not empty
  if (!newReply.content || newReply.content.trim().length === 0) {
    replyErrors.value = { content: 'Reply cannot be empty' }
    toast.error('Reply cannot be empty')
    return
  }

  submittingReply.value = true
  replyErrors.value = {}

  try {
    const response = await forumAPI.createStandaloneReply(selectedTopic.value.id, newReply)
    if (response.data.status === 'success') {
      // Force refresh the replies to ensure the new reply appears
      await refreshTopicReplies()
      
      // Clear the reply form
      newReply.content = ''
      
      toast.success('Reply posted successfully!')
      
      // Update the topic's reply count
      const topicIndex = topics.value.findIndex(t => t.id === selectedTopic.value.id)
      if (topicIndex !== -1) {
        topics.value[topicIndex].replies_count = (topics.value[topicIndex].replies_count || 0) + 1
      }
    } else {
      toast.error('Failed to post reply')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      replyErrors.value = error.response.data.errors
      toast.error('Validation error: ' + Object.values(error.response.data.errors).flat().join(', '))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to post reply: ' + (error.message || 'Unknown error'))
    }
  } finally {
    submittingReply.value = false
  }
}

const likeReply = async (reply) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to like replies')
    return
  }

  try {
    const response = await forumAPI.likeStandaloneReply(reply.id)
    if (response.data.status === 'success') {
      // Update the reply's like status and count
      const replyIndex = topicReplies.value.findIndex(r => r.id === reply.id)
      if (replyIndex !== -1) {
        topicReplies.value[replyIndex].is_liked = response.data.data.is_liked
        topicReplies.value[replyIndex].upvotes = response.data.data.upvotes
      }
    }
  } catch (error) {
    toast.error('Failed to like reply')
  }
}

const openReplyModal = (reply) => {
  console.log('openReplyModal called with reply:', reply)
  console.log('Current showInlineReplyForm:', showInlineReplyForm.value)
  console.log('Current inlineReplyParent:', inlineReplyParent.value)
  
  // Close any existing inline reply form
  showInlineReplyForm.value = false
  
  // Set up inline reply form
  inlineReplyParent.value = reply.id
  showInlineReplyForm.value = true
  inlineReply.content = ''
  inlineReply.parent_id = reply.id
  inlineReplyErrors.value = {}
  
  console.log('After setting - showInlineReplyForm:', showInlineReplyForm.value)
  console.log('After setting - inlineReplyParent:', inlineReplyParent.value)
}

const closeNestedReplyModal = () => {
  showNestedReplyModal.value = false
  selectedReply.value = null
  nestedReply.content = ''
  nestedReply.parent_id = null
  nestedReplyErrors.value = {}
}

const submitNestedReply = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    router.push('/auth/login')
    return
  }

  // Validate content is not empty
  if (!nestedReply.content || nestedReply.content.trim().length === 0) {
    nestedReplyErrors.value = { content: 'Reply cannot be empty' }
    toast.error('Reply cannot be empty')
    return
  }

  submittingNestedReply.value = true
  nestedReplyErrors.value = {}

  try {
    const response = await forumAPI.createStandaloneReply(selectedTopic.value.id, nestedReply)
    if (response.data.status === 'success') {
      // Auto-expand the parent reply to show the new nested reply
      if (nestedReply.parent_id) {
        expandedReplies.value[nestedReply.parent_id] = true
      }
      
      // Close the nested reply modal first
      closeNestedReplyModal()
      
      // Force refresh the replies to ensure the new reply appears
      await refreshTopicReplies()
      
      toast.success('Reply posted successfully!')
      
      // Update the topic's reply count
      const topicIndex = topics.value.findIndex(t => t.id === selectedTopic.value.id)
      if (topicIndex !== -1) {
        topics.value[topicIndex].replies_count = (topics.value[topicIndex].replies_count || 0) + 1
      }
    } else {
      toast.error('Failed to post reply')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      nestedReplyErrors.value = error.response.data.errors
      toast.error('Validation error: ' + Object.values(error.response.data.errors).flat().join(', '))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to post reply: ' + (error.message || 'Unknown error'))
    }
  } finally {
    submittingNestedReply.value = false
  }
}

const getRepliesToMessage = (parentId) => {
  if (!parentId) return []
  return topicReplies.value.filter(reply => reply.parent_id === parentId)
}

// Gather all descendant replies depth-first for a given parentId, used to cap visual indentation
const getAllDescendants = (parentId) => {
  const collected = []
  const walk = (pid) => {
    const children = topicReplies.value.filter(r => r.parent_id === pid)
    for (const child of children) {
      collected.push(child)
      walk(child.id)
    }
  }
  walk(parentId)
  return collected
}

const getTopLevelReplies = () => {
  return topicReplies.value.filter(reply => !reply.parent_id)
}

const toggleReplies = (replyId) => {
  expandedReplies.value[replyId] = !expandedReplies.value[replyId]
}

const isRepliesExpanded = (replyId) => {
  return !!expandedReplies.value[replyId]
}

const getRepliesCount = (parentId) => {
  return getRepliesToMessage(parentId).length
}

const shouldShowViewRepliesButton = (parentId) => {
  return getAllDescendants(parentId).length > 0
}

const closeInlineReplyForm = () => {
  showInlineReplyForm.value = false
  inlineReplyParent.value = null
  inlineReply.content = ''
  inlineReply.parent_id = null
  inlineReplyErrors.value = {}
}

const refreshTopicReplies = async () => {
  if (!selectedTopic.value) {
    console.log('No selected topic, cannot refresh')
    return
  }
  
  try {
    console.log('Refreshing topic replies for topic ID:', selectedTopic.value.id)
    const response = await forumAPI.getStandaloneTopic(selectedTopic.value.id)
    console.log('API response:', response.data)
    
    if (response.data.status === 'success') {
      const newReplies = response.data.data.replies || []
      console.log('Old replies count:', topicReplies.value.length)
      console.log('New replies count:', newReplies.length)
      console.log('New replies data:', newReplies.map(r => ({ id: r.id, parent_id: r.parent_id, content: r.content })))
      
      topicReplies.value = newReplies
      console.log('Replies refreshed successfully:', topicReplies.value.length)
    } else {
      console.error('API returned error status:', response.data)
    }
  } catch (error) {
    console.error('Failed to refresh replies:', error)
  }
}

const submitInlineReply = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    router.push('/auth/login')
    return
  }

  // Validate content is not empty
  if (!inlineReply.content || inlineReply.content.trim().length === 0) {
    inlineReplyErrors.value = { content: 'Reply cannot be empty' }
    toast.error('Reply cannot be empty')
    return
  }

  submittingInlineReply.value = true
  inlineReplyErrors.value = {}

  try {
    const response = await forumAPI.createStandaloneReply(selectedTopic.value.id, inlineReply)
    if (response.data.status === 'success') {
      console.log('=== REPLY SUBMITTED SUCCESSFULLY ===')
      console.log('New reply data:', response.data.data)
      console.log('Parent ID:', inlineReply.parent_id)
      console.log('Topic Replies before:', topicReplies.value.length)
      
      // Auto-expand the parent reply to show the new nested reply
      if (inlineReply.parent_id) {
        expandedReplies.value[inlineReply.parent_id] = true
      }
      
      // Close the inline reply form first
      closeInlineReplyForm()
      
      // Force refresh the replies to ensure the new reply appears
      await refreshTopicReplies()
      
      console.log('Topic Replies after refresh:', topicReplies.value.length)
      console.log('All replies:', topicReplies.value.map(r => ({ id: r.id, parent_id: r.parent_id, content: r.content })))
      
      toast.success('Reply posted successfully!')
      
      // Update the topic's reply count
      const topicIndex = topics.value.findIndex(t => t.id === selectedTopic.value.id)
      if (topicIndex !== -1) {
        topics.value[topicIndex].replies_count = (topics.value[topicIndex].replies_count || 0) + 1
      }
    } else {
      toast.error('Failed to post reply')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      inlineReplyErrors.value = error.response.data.errors
      toast.error('Validation error: ' + Object.values(error.response.data.errors).flat().join(', '))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to post reply: ' + (error.message || 'Unknown error'))
    }
  } finally {
    submittingInlineReply.value = false
  }
}

onMounted(() => {
  fetchCategories()
  fetchTopics(1)
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
}

.btn-primary {
  @apply text-white bg-primary-600 hover:bg-primary-700 focus:ring-primary-500;
}

.btn-secondary {
  @apply text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-500;
}

.btn-danger {
  @apply text-white bg-red-600 hover:bg-red-700 focus:ring-red-500;
}

.btn-sm {
  @apply px-3 py-1.5 text-xs;
}
</style>
