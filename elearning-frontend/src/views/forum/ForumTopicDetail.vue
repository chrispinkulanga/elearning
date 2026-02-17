<template>
  <div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-4xl mx-auto px-3 sm:px-4 lg:px-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Topic Content -->
      <div v-else-if="topic" class="space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="flex items-center space-x-2 sm:space-x-4 overflow-x-auto">
            <li>
              <router-link to="/forum" class="text-gray-500 hover:text-gray-700 text-sm sm:text-base">
                Forum
              </router-link>
            </li>
            <li>
              <div class="flex items-center">
                <svg class="flex-shrink-0 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="ml-2 sm:ml-4 text-xs sm:text-sm font-medium text-gray-500 truncate">{{ topic.title }}</span>
              </div>
            </li>
          </ol>
        </nav>

        <!-- Topic Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                <img
                  v-if="topic.user?.avatar"
                  :src="`${storageUrl}/storage/${topic.user.avatar}`"
                  :alt="topic.user.name"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-primary-600 font-medium text-sm sm:text-base">
                  {{ topic.user?.name?.charAt(0)?.toUpperCase() }}
                </span>
              </div>
              <div>
                <p class="font-medium text-gray-900 text-sm sm:text-base">{{ topic.user?.name }}</p>
                <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(topic.created_at) }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap',
                  getCategoryColor(topic.category)
                ]"
              >
                {{ topic.category }}
              </span>
              <div v-if="canEditTopic(topic)" class="flex items-center space-x-1">
                <button
                  @click="editTopic"
                  class="text-gray-400 hover:text-gray-600 p-1"
                  title="Edit topic"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  @click="deleteTopic"
                  class="text-gray-400 hover:text-red-600 p-1"
                  title="Delete topic"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ topic.title }}</h1>
          
          <div class="prose max-w-none">
            <div v-html="formatContent(topic.content)" class="text-gray-700"></div>
          </div>

          <!-- Attachments Display -->
          <div v-if="topic.attachments_count > 0 && topic.attachments && topic.attachments.length > 0" class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center space-x-2 mb-3">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
              </svg>
              <span class="text-sm font-medium text-gray-800">{{ topic.attachments_count }} Attachment{{ topic.attachments_count > 1 ? 's' : '' }}</span>
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

          <!-- Topic Stats -->
          <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
            <div class="flex items-center space-x-4 text-sm text-gray-500">
              <span>{{ totalRepliesCount }} replies</span>
              <span>{{ topic.views || 0 }} views</span>
              <span>{{ topic.likes_count || 0 }} likes</span>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="likeTopic"
                :class="[
                  'flex items-center space-x-1 px-3 py-1 rounded-md text-sm transition-colors duration-200 whitespace-nowrap',
                  topic.is_liked ? 'text-red-600 bg-red-50' : 'text-gray-500 hover:text-red-600 hover:bg-red-50'
                ]"
              >
                <svg class="w-4 h-4" :fill="topic.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 20 20">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ topic.is_liked ? 'Unlike' : 'Like' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Poll Display -->
        <div v-if="topic.poll_question" class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
          <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center space-x-2 mb-3">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <span class="text-sm font-medium text-blue-800">Poll: {{ topic.poll_question }}</span>
            </div>
            <div class="space-y-2">
              <div 
                v-for="(option, index) in topic.poll_options" 
                :key="index"
                :class="[
                  'flex items-center justify-between p-3 rounded-md border transition-all duration-200 cursor-pointer',
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
                <div class="flex items-center space-x-3 flex-1">
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
                <div class="flex items-center space-x-3 ml-4">
                  <span class="text-sm text-blue-600 font-medium">
                    {{ getCurrentVoteCount(topic, index) }} votes
                  </span>
                  <div class="w-20 h-2 bg-blue-200 rounded-full overflow-hidden">
                    <div 
                      class="h-full bg-blue-500 transition-all duration-300"
                      :style="{ width: getPollVotePercentage(topic, index) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Replies Section -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
              Replies ({{ totalRepliesCount }})
            </h2>
          </div>

          <!-- Replies List -->
          <div class="divide-y divide-gray-200">
            <div
              v-for="reply in topLevelReplies"
              :key="reply.id"
              :id="`reply-${reply.id}`"
              class="p-6"
            >
              <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                  <img
                    v-if="reply.user?.avatar"
                    :src="`${storageUrl}/storage/${reply.user.avatar}`"
                    :alt="reply.user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-primary-600 text-sm font-medium">
                    {{ reply.user?.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-2">
                    <div>
                      <p class="font-medium text-gray-900">{{ reply.user?.name }}</p>
                      <p class="text-sm text-gray-500">{{ formatDate(reply.created_at) }}</p>
                    </div>
                    <div v-if="canEditReply(reply)" class="flex items-center space-x-1">
                      <button
                        @click="editReply(reply)"
                        class="text-gray-400 hover:text-gray-600 p-1"
                        title="Edit reply"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                      <button
                        @click="deleteReply(reply.id)"
                        class="text-gray-400 hover:text-red-600 p-1"
                        title="Delete reply"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div class="prose max-w-none">
                    <div v-html="formatContent(reply.content)" class="text-gray-700"></div>
                  </div>
                  <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                      <button
                        @click="likeReply(reply)"
                        :class="[
                          'flex items-center space-x-1 transition-colors duration-200',
                          reply.is_liked ? 'text-red-600' : 'text-gray-500 hover:text-red-600'
                        ]"
                      >
                        <svg class="w-4 h-4" :fill="reply.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 20 20">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ reply.upvotes || 0 }}</span>
                      </button>
                      <button
                        @click="openReplyModal(reply)"
                        :disabled="topic?.is_locked"
                        :class="[
                          'flex items-center space-x-1 transition-colors duration-200',
                          topic?.is_locked 
                            ? 'text-gray-400 cursor-not-allowed' 
                            : 'hover:text-primary-600'
                        ]"
                        :title="topic?.is_locked ? 'Cannot reply to locked discussion' : 'Reply to this message'"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span>Reply</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Unlimited Nested Replies using recursive component -->
              <div v-if="getDirectChildren(reply.id).length > 0" class="ml-11 mt-4">
                <ForumReplyThread
                  :replies="getDirectChildren(reply.id)"
                  :all-replies="replies"
                  :level="2"
                  :expanded-nested="expandedNested"
                  :is-topic-locked="topic?.is_locked"
                  :storage-url="storageUrl"
                  :topic-id="topic?.id"
                  @toggle-replies="toggleReplies"
                  @like-reply="likeReply"
                  @open-reply-modal="openReplyModal"
                  @edit-reply="editReply"
                  @delete-reply="deleteReply"
                  @reply-added="handleReplyAdded"
                />
              </div>
            </div>
          </div>

          <!-- No Replies Message -->
          <div v-if="replies.length === 0" class="p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No replies yet</h3>
            <p class="mt-1 text-sm text-gray-500">Be the first to reply to this topic.</p>
          </div>
        </div>

        <!-- Add Reply Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ newReply.parent_id ? 'Reply to Message' : 'Add Reply' }}
          </h3>
          <div v-if="newReply.parent_id" class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-800">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              Replying to a specific message
            </p>
            <button
              @click="newReply.parent_id = null"
              class="mt-2 text-xs text-blue-600 hover:text-blue-800 underline"
            >
              Cancel and post as new reply
            </button>
          </div>
          <!-- Locked Topic Message -->
          <div v-if="topic?.is_locked" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
              <div>
                <h3 class="text-sm font-medium text-red-800">Discussion Locked</h3>
                <p class="text-sm text-red-600 mt-1">This discussion is locked and cannot receive new replies.</p>
              </div>
            </div>
          </div>

          <!-- Reply Form -->
          <form v-if="!topic?.is_locked" @submit.prevent="submitReply">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Your Reply</label>
              <textarea
                v-model="newReply.content"
                rows="6"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                :class="{ 'border-red-500': replyErrors.content }"
                placeholder="Write your reply..."
              ></textarea>
              <p v-if="replyErrors.content" class="mt-1 text-sm text-red-600">{{ replyErrors.content }}</p>
            </div>
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="submitting"
                class="btn btn-primary w-fit"
              >
                <span v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                {{ submitting ? 'Posting...' : 'Post Reply' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Topic not found</h3>
        <p class="mt-1 text-sm text-gray-500">The topic you're looking for doesn't exist or has been removed.</p>
        <div class="mt-6">
          <router-link to="/forum" class="btn btn-primary w-fit">
            Back to Forum
          </router-link>
        </div>
      </div>
    </div>

    <!-- Facebook-style Reply Modal -->
    <div v-if="showReplyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="background-color: rgba(255, 0, 0, 0.8) !important; border: 5px solid yellow !important;">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Reply to Message (DEBUG: Modal is showing!)</h3>
          <button
            @click="closeReplyModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
          <!-- Original Message -->
          <div class="p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-start space-x-3">
              <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden">
                <img
                  v-if="selectedReply?.user?.avatar"
                  :src="`${storageUrl}/storage/${selectedReply.user.avatar}`"
                  :alt="selectedReply.user.name"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-primary-600 text-sm font-medium">
                  {{ selectedReply?.user?.name?.charAt(0)?.toUpperCase() }}
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                  <div>
                    <p class="font-medium text-gray-900">{{ selectedReply?.user?.name }}</p>
                    <p class="text-sm text-gray-500">{{ formatDate(selectedReply?.created_at) }}</p>
                  </div>
                </div>
                <div class="prose max-w-none">
                  <div v-html="formatContent(selectedReply?.content)" class="text-gray-700"></div>
                </div>
                <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                  <button
                    @click="likeReply(selectedReply)"
                    :class="[
                      'flex items-center space-x-1 transition-colors duration-200',
                      selectedReply?.is_liked ? 'text-red-600' : 'text-gray-500 hover:text-red-600'
                    ]"
                  >
                    <svg class="w-4 h-4" :fill="selectedReply?.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 20 20">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ selectedReply?.upvotes || 0 }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Existing Replies to this message -->
          <div v-if="getRepliesToMessage(selectedReply?.id).length > 0" class="p-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Replies to this message</h4>
            <div class="space-y-3">
              <div
                v-for="reply in getRepliesToMessage(selectedReply?.id)"
                :key="reply.id"
                class="flex items-start space-x-3"
              >
                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                  <img
                    v-if="reply.user?.avatar"
                    :src="`${storageUrl}/storage/${reply.user.avatar}`"
                    :alt="reply.user.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-gray-600 text-xs font-medium">
                    {{ reply.user?.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ reply.user?.name }}</p>
                      <p class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</p>
                    </div>
                  </div>
                  <div class="prose max-w-none">
                    <div v-html="formatContent(reply.content)" class="text-sm text-gray-700"></div>
                  </div>
                  <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500">
                    <button
                      @click="likeReply(reply)"
                      :class="[
                        'flex items-center space-x-1 transition-colors duration-200',
                        reply.is_liked ? 'text-red-600' : 'text-gray-500 hover:text-red-600'
                      ]"
                    >
                      <svg class="w-3 h-3" :fill="reply.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 20 20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                      </svg>
                      <span>{{ reply.upvotes || 0 }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Reply Form -->
          <div class="p-4 border-t border-gray-100">
            <form @submit.prevent="submitModalReply">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Reply</label>
                <textarea
                  v-model="modalReply.content"
                  rows="4"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  :class="{ 'border-red-500': modalReplyErrors.content }"
                  placeholder="Write your reply..."
                ></textarea>
                <p v-if="modalReplyErrors.content" class="mt-1 text-sm text-red-600">{{ modalReplyErrors.content }}</p>
              </div>
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="closeReplyModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submittingModalReply"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50"
                >
                  <span v-if="submittingModalReply" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></span>
                  {{ submittingModalReply ? 'Posting...' : 'Post Reply' }}
                </button>
              </div>
            </form>
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
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { forumAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import appConfig from '@/config/app.config'
import { useToast } from 'vue-toastification'
import ForumReplyThread from '@/components/forum/ForumReplyThread.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const submitting = ref(false)
const topic = ref(null)
const replies = ref([])
const newReply = reactive({
  content: '',
  parent_id: null
})
const replyErrors = reactive({})

// Modal-related variables
const showReplyModal = ref(false)
const selectedReply = ref(null)
const submittingModalReply = ref(false)
const modalReply = reactive({
  content: '',
  parent_id: null
})
const modalReplyErrors = reactive({})

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

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Modal state
const showImageModal = ref(false)
const showVideoModal = ref(false)
const selectedAttachment = ref(null)

// Image gallery state
const imageGallery = ref([])
const currentImageIndex = ref(0)

const openImageModal = (attachment) => {
  selectedAttachment.value = attachment
  showImageModal.value = true
  
  // Get all images from the current topic
  const topicImages = getImageAttachments(topic.value?.attachments || [])
  imageGallery.value = topicImages
  
  // Find the index of the clicked image
  const index = topicImages.findIndex(img => img.filename === attachment.filename)
  currentImageIndex.value = index >= 0 ? index : 0
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

const canEditReply = (reply) => {
  return authStore.user?.id === reply.user_id || authStore.isInstructor
}

// Computed properties for nested replies
const topLevelReplies = computed(() => {
  return replies.value.filter(reply => !reply.parent_id)
})

// Get direct children only (not all descendants)
const getDirectChildren = (parentId) => {
  return replies.value.filter(reply => reply.parent_id === parentId)
}

// Expand/collapse helpers for nested replies
const expandedNested = ref(new Set())
const toggleReplies = (replyId) => {
  if (expandedNested.value.has(replyId)) {
    expandedNested.value.delete(replyId)
  } else {
    expandedNested.value.add(replyId)
  }
}
const isRepliesExpanded = (replyId) => expandedNested.value.has(replyId)
const getRepliesCount = (parentId) => getDirectChildren(parentId).length

const totalRepliesCount = computed(() => {
  return replies.value.length
})

const apiUrl = computed(() => appConfig.apiUrl)

const storageUrl = computed(() => appConfig.baseUrl)

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatContent = (content) => {
  // Simple content formatting - in a real app, you'd use a markdown parser
  return content.replace(/\n/g, '<br>')
}

const getCategoryColor = (category) => {
  const colors = {
    'general': 'bg-gray-100 text-gray-800',
    'technical': 'bg-blue-100 text-blue-800',
    'help': 'bg-green-100 text-green-800',
    'discussion': 'bg-purple-100 text-purple-800',
    'announcement': 'bg-yellow-100 text-yellow-800'
  }
  return colors[category] || 'bg-gray-100 text-gray-800'
}

const fetchTopic = async () => {
  loading.value = true
  try {
    const topicId = route.params.id
    const response = await forumAPI.getStandaloneTopic(topicId)
    if (response.data.status === 'success') {
      topic.value = response.data.data
      replies.value = response.data.data.replies || []
      
      // Initialize poll vote counts if topic has a poll
      if (topic.value.poll_question) {
        initializeVoteCounts(topic.value)
      }
    } else {
      toast.error('Failed to load topic')
    }
  } catch (error) {
    console.error('Error fetching topic:', error)
    // Only show error message for actual errors (network, server errors, etc.)
    // Don't show error for empty results (which is normal when topic doesn't exist)
    if (error.response?.status >= 400) {
      toast.error('Failed to load topic')
    }
  } finally {
    loading.value = false
  }
}

const submitReply = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    router.push('/auth/login')
    return
  }

  // Check if topic is locked
  if (topic.value?.is_locked) {
    toast.error('This discussion is locked. You cannot reply to locked topics.')
    return
  }

  submitting.value = true
  replyErrors.value = {}

  try {
    const response = await forumAPI.createStandaloneReply(topic.value.id, newReply)
    if (response.data.status === 'success') {
      replies.value.push(response.data.data)
    } else {
      toast.error('Failed to post reply')
      return
    }
    newReply.content = ''
    newReply.parent_id = null
    toast.success('Reply posted successfully!')
  } catch (error) {
    if (error.response?.data?.errors) {
      replyErrors.value = error.response.data.errors
    } else {
      toast.error('Failed to post reply')
    }
  } finally {
    submitting.value = false
  }
}

const likeTopic = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to like topics')
    router.push('/auth/login')
    return
  }

  try {
    const response = await forumAPI.likeStandaloneTopic(topic.value.id)
    if (response.data.status === 'success') {
      // Update the topic's like status and count
      topic.value.is_liked = response.data.data.is_liked
      topic.value.likes_count = response.data.data.likes_count
      toast.success(response.data.message)
    }
  } catch (error) {
    console.error('Error liking topic:', error)
    toast.error('Failed to like topic')
  }
}

const likeReply = async (reply) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to like replies')
    router.push('/auth/login')
    return
  }

  try {
    const response = await forumAPI.likeStandaloneReply(reply.id)
    if (response.data.status === 'success') {
      // Update the reply's like status and count
      reply.is_liked = response.data.data.is_liked
      reply.upvotes = response.data.data.upvotes
      toast.success(response.data.message)
  }
  } catch (error) {
    console.error('Error liking reply:', error)
    toast.error('Failed to like reply')
  }
}

const editTopic = () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to edit topics')
    router.push('/auth/login')
    return
  }
  
  router.push(`/forum/topics/${topic.value.id}/edit`)
}

const deleteTopic = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to delete topics')
    router.push('/auth/login')
    return
  }

  if (!confirm('Are you sure you want to delete this topic?')) return

  try {
    await forumAPI.deleteStandaloneTopic(topic.value.id)
    toast.success('Topic deleted successfully!')
    router.push('/forum')
  } catch (error) {
    console.error('Error deleting topic:', error)
    toast.error('Failed to delete topic')
  }
}

const editReply = (reply) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to edit replies')
    router.push('/auth/login')
    return
  }
  
  // Navigate to edit reply page or open edit modal
  router.push(`/forum/replies/${reply.id}/edit`)
}

const deleteReply = async (replyId) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to delete replies')
    router.push('/auth/login')
    return
  }

  if (!confirm('Are you sure you want to delete this reply?')) return

  try {
    await forumAPI.deleteReply(replyId)
    replies.value = replies.value.filter(reply => reply.id !== replyId)
    toast.success('Reply deleted successfully!')
  } catch (error) {
    console.error('Error deleting reply:', error)
    toast.error('Failed to delete reply')
  }
}

// Modal functions
const openReplyModal = (reply) => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    router.push('/auth/login')
    return
  }

  // Check if topic is locked
  if (topic.value?.is_locked) {
    toast.error('This discussion is locked. You cannot reply to locked topics.')
    return
  }

  selectedReply.value = reply
  modalReply.parent_id = reply.id
  modalReply.content = ''
  modalReplyErrors.value = {}
  showReplyModal.value = true
}

const closeReplyModal = () => {
  showReplyModal.value = false
  selectedReply.value = null
  modalReply.parent_id = null
  modalReply.content = ''
  modalReplyErrors.value = {}
}

const submitModalReply = async () => {
  if (!authStore.isAuthenticated) {
    toast.info('Please log in to reply')
    router.push('/auth/login')
    return
  }

  // Validate content is not empty
  if (!modalReply.content || modalReply.content.trim().length === 0) {
    modalReplyErrors.value = { content: 'Reply cannot be empty' }
    toast.error('Reply cannot be empty')
    return
  }
  
  // Ensure content is trimmed
  modalReply.content = modalReply.content.trim()

  submittingModalReply.value = true
  modalReplyErrors.value = {}

  try {
    console.log('Submitting modal reply:', {
      topicId: topic.value.id,
      replyData: modalReply,
      isAuthenticated: authStore.isAuthenticated,
      user: authStore.user,
      token: localStorage.getItem('auth_token') ? 'Present' : 'Missing'
    })
    
    const response = await forumAPI.createStandaloneReply(topic.value.id, modalReply)
    console.log('Modal reply response:', response)
    
    if (response.data.status === 'success') {
      // Auto-expand the parent reply to show the new nested reply
      if (modalReply.parent_id) {
        expandedNested.value.add(modalReply.parent_id)
      }
      
      // Force refresh the replies to ensure the new reply appears
      await fetchTopic()
      
      closeReplyModal()
      toast.success('Reply posted successfully!')
    } else {
      console.error('Modal reply failed:', response.data)
      toast.error('Failed to post reply')
    }
  } catch (error) {
    console.error('Modal reply error:', error)
    console.error('Error response:', error.response?.data)
    
    if (error.response?.data?.errors) {
      modalReplyErrors.value = error.response.data.errors
      toast.error('Validation error: ' + Object.values(error.response.data.errors).flat().join(', '))
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to post reply: ' + (error.message || 'Unknown error'))
    }
  } finally {
    submittingModalReply.value = false
  }
}

const getRepliesToMessage = (parentId) => {
  return replies.value.filter(reply => reply.parent_id === parentId)
}

const handleReplyAdded = (newReply) => {
  replies.value.push(newReply)
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
const initializeVoteCounts = (topic) => {
  if (topic.poll_options) {
    pollVoteCounts.value[topic.id] = topic.poll_options.map((_, index) => getPollVoteCount(topic, index))
  }
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
  const totalVotes = topic.poll_options?.reduce((sum, _, index) => {
    return sum + getCurrentVoteCount(topic, index)
  }, 0) || 0
  
  if (totalVotes === 0) return 0
  
  const optionVotes = getCurrentVoteCount(topic, optionIndex)
  return Math.round((optionVotes / totalVotes) * 100)
}

// Check if user has voted in this poll
const hasUserVotedInPoll = (topic) => {
  const pollKey = `poll_${topic.id}_voted`
  return localStorage.getItem(pollKey) === 'true'
}

// Check if user has voted for a specific option
const hasUserVotedForOption = (topic, optionIndex) => {
  const optionKey = `poll_${topic.id}_option_${optionIndex}`
  return localStorage.getItem(optionKey) === 'true'
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
    
    // TODO: In a real implementation, you would make an API call to save the vote
    // await forumAPI.votePoll(topic.id, pollId, { option_index: optionIndex })
    
  } catch (error) {
    toast.error('Failed to record vote')
  }
}

// Handle hash fragment for scrolling to specific reply
const handleHashScroll = () => {
  const hash = window.location.hash
  console.log('Checking hash:', hash)
  
  if (hash && hash.startsWith('#reply-')) {
    const replyId = hash.substring(1) // Remove the # symbol
    console.log('Looking for element with ID:', replyId)
    
    // Try multiple times with increasing delays to ensure element is rendered
    const tryScroll = (attempt = 1, maxAttempts = 5) => {
      const element = document.getElementById(replyId)
      console.log(`Attempt ${attempt}: Element found:`, element)
      
      if (element) {
        console.log('Scrolling to element:', element)
        element.scrollIntoView({ behavior: 'smooth', block: 'center' })
        // Add a temporary highlight effect
        element.style.backgroundColor = '#fef3c7'
        element.style.border = '2px solid #f59e0b'
        setTimeout(() => {
          element.style.backgroundColor = ''
          element.style.border = ''
        }, 3000)
      } else if (attempt < maxAttempts) {
        console.log(`Element not found, retrying in ${attempt * 200}ms...`)
        setTimeout(() => tryScroll(attempt + 1, maxAttempts), attempt * 200)
      } else {
        console.log('Element not found after maximum attempts')
      }
    }
    
    // Wait a bit for the page to fully render
    setTimeout(() => {
      tryScroll()
    }, 500)
  }
}

onMounted(async () => {
  await fetchTopic()
  
  // Check for hash after topic is loaded
  handleHashScroll()
  
  // Listen for hash changes
  window.addEventListener('hashchange', handleHashScroll)
})

onUnmounted(() => {
  window.removeEventListener('hashchange', handleHashScroll)
})
</script>
