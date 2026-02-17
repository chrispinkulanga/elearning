<template>
  <div class="poll-creator">
    <div class="border border-gray-300 rounded-lg p-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Add a Poll</h3>
        <button
          type="button"
          @click="togglePoll"
          class="text-sm text-gray-600 hover:text-gray-800"
        >
          {{ showPoll ? 'Remove Poll' : 'Add Poll' }}
        </button>
      </div>
      
      <div v-if="showPoll" class="space-y-4">
        <!-- Poll Question -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Poll Question *
          </label>
          <input
            v-model="pollData.question"
            type="text"
            placeholder="What would you like to ask?"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            maxlength="500"
          />
          <p class="mt-1 text-xs text-gray-500">{{ pollData.question.length }}/500</p>
        </div>
        
        <!-- Poll Options -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Poll Options *
          </label>
          <div class="space-y-2">
            <div
              v-for="(option, index) in pollData.options"
              :key="index"
              class="flex items-center space-x-2"
            >
              <input
                v-model="pollData.options[index]"
                type="text"
                :placeholder="`Option ${index + 1}`"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                maxlength="200"
              />
              <button
                type="button"
                v-if="pollData.options.length > 2"
                @click="removeOption(index)"
                class="p-2 text-red-500 hover:text-red-700"
                title="Remove option"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
          
          <button
            type="button"
            v-if="pollData.options.length < 10"
            @click="addOption"
            class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium"
          >
            + Add Option
          </button>
        </div>
        
        <!-- Poll Settings -->
        <div class="space-y-3">
          <div class="flex items-center">
            <input
              v-model="pollData.is_multiple_choice"
              type="checkbox"
              id="multiple-choice"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label for="multiple-choice" class="ml-2 block text-sm text-gray-900">
              Allow multiple choice voting
            </label>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Poll Expiry (Optional)
            </label>
            <input
              v-model="pollData.expires_at"
              type="datetime-local"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
            <p class="mt-1 text-xs text-gray-500">
              Leave empty for no expiry
            </p>
          </div>
        </div>
        
        <!-- Validation Errors -->
        <div v-if="errors.length" class="text-sm text-red-600">
          <ul class="list-disc list-inside space-y-1">
            <li v-for="error in errors" :key="error">{{ error }}</li>
          </ul>
        </div>
        
        <!-- Poll Save Button -->
        <div class="pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="savePoll"
            :disabled="errors.length > 0 || !pollData.question.trim()"
            class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
          >
            {{ pollData.question.trim() ? 'Save Poll' : 'Complete Poll to Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      question: '',
      options: ['', ''],
      is_multiple_choice: false,
      expires_at: ''
    })
  }
})

const emit = defineEmits(['update:modelValue', 'poll-validated'])

const showPoll = ref(false)
const pollData = ref({
  question: '',
  options: ['', ''],
  is_multiple_choice: false,
  expires_at: ''
})

const errors = ref([])

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    pollData.value = { ...newValue }
    showPoll.value = !!newValue.question
  }
}, { immediate: true })

// Watch for internal changes - but don't emit immediately when opening poll interface
watch(pollData, (newValue, oldValue) => {
  // Only emit if there's actual meaningful content, not just when opening the interface
  if (newValue.question || newValue.options.some(opt => opt.trim().length > 0)) {
    emit('update:modelValue', newValue)
    validatePoll()
  }
}, { deep: true })

const togglePoll = () => {
  showPoll.value = !showPoll.value
  if (!showPoll.value) {
    pollData.value = {
      question: '',
      options: ['', ''],
      is_multiple_choice: false,
      expires_at: ''
    }
    errors.value = []
  }
}

const addOption = () => {
  if (pollData.value.options.length < 10) {
    pollData.value.options.push('')
  }
}

const removeOption = (index) => {
  if (pollData.value.options.length > 2) {
    pollData.value.options.splice(index, 1)
  }
}

const validatePoll = () => {
  errors.value = []
  
  if (!showPoll.value) {
    emit('poll-validated', { isValid: true, data: null })
    return
  }
  
  if (!pollData.value.question.trim()) {
    errors.value.push('Poll question is required')
  }
  
  if (pollData.value.question.length > 500) {
    errors.value.push('Poll question must be 500 characters or less')
  }
  
  const validOptions = pollData.value.options.filter(option => option.trim().length > 0)
  if (validOptions.length < 2) {
    errors.value.push('At least 2 poll options are required')
  }
  
  if (validOptions.length > 10) {
    errors.value.push('Maximum 10 poll options allowed')
  }
  
  // Check for duplicate options
  const uniqueOptions = new Set(validOptions.map(option => option.trim().toLowerCase()))
  if (uniqueOptions.size !== validOptions.length) {
    errors.value.push('Poll options must be unique')
  }
  
  // Check option length
  for (let i = 0; i < pollData.value.options.length; i++) {
    const option = pollData.value.options[i]
    if (option.trim() && option.length > 200) {
      errors.value.push(`Option ${i + 1} must be 200 characters or less`)
    }
  }
  
  // Check expiry date
  if (pollData.value.expires_at && pollData.value.expires_at.trim()) {
    const expiryDate = new Date(pollData.value.expires_at)
    const now = new Date()
    if (isNaN(expiryDate.getTime())) {
      errors.value.push('Poll expiry date is invalid')
    } else if (expiryDate <= now) {
      errors.value.push('Poll expiry must be in the future')
    }
  }
  
  const isValid = errors.value.length === 0
  emit('poll-validated', { 
    isValid, 
    data: isValid ? pollData.value : null 
  })
}

const savePoll = () => {
  validatePoll()
  if (errors.value.length === 0) {
    // Explicitly emit the poll data when user clicks save
    emit('update:modelValue', pollData.value)
    emit('poll-validated', { isValid: true, data: pollData.value })
    // Add visual feedback that poll was saved
    const event = new CustomEvent('poll-saved', { 
      detail: { message: 'Poll saved successfully!' }
    })
    window.dispatchEvent(event)
  }
}

// Expose methods for parent component
defineExpose({
  showPoll: computed(() => showPoll.value),
  pollData: computed(() => pollData.value),
  validatePoll
})
</script>

<style scoped>
.poll-creator {
  @apply w-full;
}
</style>
