<template>
  <div class="content-widget" :class="`widget-${widget.widget_type}`">
    <div class="widget-header">
      <div class="widget-type-badge">
        <i :class="getWidgetIcon(widget.widget_type)"></i>
        {{ getWidgetTypeName(widget.widget_type) }}
      </div>
      
      <div class="widget-actions">
        <button @click="toggleEdit" class="btn-icon" title="Edit">
          <i class="fas fa-edit"></i>
        </button>
        <button @click="deleteWidget" class="btn-icon text-red-500" title="Delete">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    </div>
    
    <!-- Widget Content -->
    <div class="widget-content">
      <!-- Text Widget -->
      <div v-if="widget.widget_type === 'text'" class="text-widget">
        <div v-if="isEditing" class="text-editor">
          <textarea
            v-model="editingData.content"
            class="text-editor-input"
            placeholder="Enter your text content..."
            rows="4"
            @blur="saveWidget"
          ></textarea>
        </div>
        <div v-else class="text-display" @click="toggleEdit">
          <div class="text-content">{{ widget.widget_data.content || 'Click to add text content...' }}</div>
        </div>
      </div>
      
      <!-- Image Widget -->
      <div v-else-if="widget.widget_type === 'image'" class="image-widget">
        <div v-if="isEditing" class="image-editor">
          <input
            v-model="editingData.url"
            type="url"
            class="form-input"
            placeholder="Image URL"
          />
          <button @click="saveWidget" class="btn-primary">Save</button>
        </div>
        <div v-else class="image-display" @click="toggleEdit">
          <img 
            v-if="widget.widget_data.url" 
            :src="widget.widget_data.url" 
            class="widget-image"
          />
          <div v-else class="image-placeholder">
            <i class="fas fa-image"></i>
            <p>Click to add image</p>
          </div>
        </div>
      </div>
      
      <!-- Video Widget -->
      <div v-else-if="widget.widget_type === 'video'" class="video-widget">
        <div v-if="isEditing" class="video-editor">
          <input
            v-model="editingData.url"
            type="url"
            class="form-input"
            placeholder="Video URL"
          />
          <button @click="saveWidget" class="btn-primary">Save</button>
        </div>
        <div v-else class="video-display" @click="toggleEdit">
          <div v-if="widget.widget_data.url" class="video-embed">
            <iframe :src="getEmbedUrl(widget.widget_data.url)" frameborder="0"></iframe>
          </div>
          <div v-else class="video-placeholder">
            <i class="fas fa-video"></i>
            <p>Click to add video</p>
          </div>
        </div>
      </div>
      
      <!-- MCQ Widget -->
      <div v-else-if="widget.widget_type === 'mcq'" class="mcq-widget">
        <div v-if="isEditing" class="mcq-editor">
          <input
            v-model="editingData.question"
            type="text"
            class="form-input"
            placeholder="Question"
          />
          <button @click="saveWidget" class="btn-primary">Save</button>
        </div>
        <div v-else class="mcq-display" @click="toggleEdit">
          <h4 class="question">{{ widget.widget_data.question || 'Click to add question...' }}</h4>
        </div>
      </div>
      
      <!-- Default Display -->
      <div v-else class="default-display" @click="toggleEdit">
        <p>Click to edit {{ getWidgetTypeName(widget.widget_type) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useToast } from 'vue-toastification'
import { courseBuilderAPI } from '@/services/api'

const props = defineProps({
  widget: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update', 'delete'])
const toast = useToast()

const isEditing = ref(false)
const editingData = reactive({})

const toggleEdit = () => {
  if (isEditing.value) {
    saveWidget()
  } else {
    Object.assign(editingData, props.widget.widget_data)
    isEditing.value = true
  }
}

const saveWidget = async () => {
  try {
    const response = await courseBuilderAPI.updateWidget(props.widget.id, {
      widget_data: editingData
    })
    
    if (response.status === 'success') {
      toast.success('Widget updated successfully')
      emit('update', props.widget.id, response.data)
      isEditing.value = false
    }
  } catch (error) {
    toast.error('Failed to update widget')
  }
}

const deleteWidget = async () => {
  if (confirm('Delete this widget?')) {
    try {
      await courseBuilderAPI.deleteWidget(props.widget.id)
      emit('delete', props.widget.id)
      toast.success('Widget deleted successfully')
    } catch (error) {
      toast.error('Failed to delete widget')
    }
  }
}

const getWidgetIcon = (type) => {
  const icons = {
    text: 'fas fa-font',
    image: 'fas fa-image',
    video: 'fas fa-video',
    mcq: 'fas fa-question-circle',
    poll: 'fas fa-poll',
    file: 'fas fa-file',
    code: 'fas fa-code',
    embed: 'fas fa-external-link-alt'
  }
  return icons[type] || 'fas fa-cube'
}

const getWidgetTypeName = (type) => {
  const names = {
    text: 'Text',
    image: 'Image',
    video: 'Video',
    mcq: 'MCQ',
    poll: 'Poll',
    file: 'File',
    code: 'Code',
    embed: 'Embed'
  }
  return names[type] || 'Widget'
}

const getEmbedUrl = (url) => {
  if (url.includes('youtube.com/watch')) {
    const videoId = url.split('v=')[1]?.split('&')[0]
    return videoId ? `https://www.youtube.com/embed/${videoId}` : url
  }
  return url
}
</script>

<style scoped>
.content-widget {
  @apply border border-gray-200 rounded-lg bg-white overflow-hidden;
}

.widget-header {
  @apply flex items-center justify-between p-3 bg-gray-50 border-b border-gray-200;
}

.widget-type-badge {
  @apply flex items-center space-x-2 text-sm text-gray-600;
}

.widget-actions {
  @apply flex items-center space-x-1;
}

.btn-icon {
  @apply p-2 text-gray-400 hover:text-gray-600 transition-colors;
}

.widget-content {
  @apply p-4;
}

.text-editor-input {
  @apply w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500;
}

.text-content {
  @apply min-h-[100px] p-3 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 transition-colors;
}

.widget-image {
  @apply w-full h-48 object-cover rounded-md;
}

.image-placeholder,
.video-placeholder {
  @apply w-full h-48 bg-gray-100 rounded-md flex flex-col items-center justify-center cursor-pointer hover:bg-gray-200 transition-colors;
}

.video-embed iframe {
  @apply w-full h-64 rounded-md;
}

.question {
  @apply text-lg font-medium text-gray-900;
}

.default-display {
  @apply p-4 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 transition-colors;
}

.form-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 mb-3;
}

.btn-primary {
  @apply px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors;
}
</style>
