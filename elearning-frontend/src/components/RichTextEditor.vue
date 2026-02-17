<template>
  <div class="rich-text-editor" @submit.prevent>
    <div class="toolbar border border-gray-300 rounded-t-md bg-gray-50 p-2 flex flex-wrap gap-1">
      <button type="button" @click.prevent="(e) => execCommand('bold', e)" class="p-2 rounded hover:bg-gray-200" title="Bold">B</button>
      <button type="button" @click.prevent="(e) => execCommand('italic', e)" class="p-2 rounded hover:bg-gray-200" title="Italic">I</button>
      <button type="button" @click.prevent="(e) => execCommand('underline', e)" class="p-2 rounded hover:bg-gray-200" title="Underline">U</button>
      <button type="button" @click.prevent="(e) => execCommand('insertUnorderedList', e)" class="p-2 rounded hover:bg-gray-200" title="List">•</button>
      <button type="button" @click.prevent="(e) => insertLink(e)" class="p-2 rounded hover:bg-gray-200" title="Link">🔗</button>
      <button type="button" @click.prevent="(e) => insertImage(e)" class="p-2 rounded hover:bg-gray-200" title="Image">🖼️</button>
    </div>
    
    <div
      ref="editor"
      class="editor-content border border-t-0 border-gray-300 rounded-b-md p-4 min-h-48 focus:outline-none focus:ring-2 focus:ring-blue-500"
      contenteditable="true"
      @input="onInput"
      @paste="onPaste"
      @keydown="onKeydown"
      @focus="onFocus"
      @blur="onBlur"
      @submit.prevent
      :data-placeholder="props.placeholder || 'Start writing...'"
    ></div>
    
    <div class="text-sm text-gray-500 mt-2 text-right">
      {{ characterCount }} characters
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: String,
  default: ''
})

const emit = defineEmits(['update:modelValue'])
const editor = ref(null)

const characterCount = computed(() => {
  return editor.value ? editor.value.textContent.length : 0
})

const execCommand = (command, event) => {
  // Prevent any form submission
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  
  document.execCommand(command, false, null)
  editor.value.focus()
}

const insertLink = (event) => {
  // Prevent any form submission
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  
  const url = prompt('Enter URL:')
  if (url) {
    execCommand('createLink')
    document.execCommand('createLink', false, url)
  }
}

const insertImage = (event) => {
  // Prevent any form submission
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  
  const url = prompt('Enter image URL:')
  if (url) {
    const img = `<img src="${url}" class="max-w-full h-auto rounded" />`
    document.execCommand('insertHTML', false, img)
  }
}

const onInput = () => {
  if (editor.value) {
    const content = editor.value.innerHTML
    const textContent = editor.value.textContent
    
    console.log('=== Editor Input Event ===')
    console.log('HTML content:', content)
    console.log('Text content:', textContent)
    console.log('Content length:', content.length)
    console.log('Text length:', textContent.length)
    
    // Check if content is corrupted (contains RTL markers)
    if (content.includes('\u200E') || content.includes('\u200F') || content.includes('\u202A')) {
      console.warn('🚨 DETECTED CORRUPTED CONTENT!')
      console.warn('Content contains RTL markers:', {
        '\u200E': content.includes('\u200E'),
        '\u200F': content.includes('\u200F'),
        '\u202A': content.includes('\u202A')
      })
      
      const sanitizedContent = sanitizeContent(content)
      console.log('Sanitized content:', sanitizedContent)
      
      editor.value.innerHTML = sanitizedContent
      forceTextDirection()
    }
    
    // Force text direction
    forceTextDirection()
    
    // Sanitize content before emitting
    const sanitizedContent = sanitizeContent(content)
    emit('update:modelValue', sanitizedContent)
  }
}

const forceTextDirection = () => {
  if (editor.value) {
    // Force all child elements to have proper text direction
    const elements = editor.value.querySelectorAll('*')
    elements.forEach(el => {
      el.style.direction = 'ltr'
      el.style.textAlign = 'left'
      el.style.unicodeBidi = 'normal'
    })
  }
}

const sanitizeContent = (content) => {
  if (!content) return ''
  
  // Remove any RTL or bidirectional text markers
  let sanitized = content
    .replace(/[\u200E\u200F\u202A-\u202E\u2066-\u2069]/g, '') // Remove RTL/LTR markers
    .replace(/dir="rtl"/gi, 'dir="ltr"')
    .replace(/style="[^"]*direction:\s*rtl[^"]*"/gi, 'style="direction: ltr; text-align: left;"')
  
  return sanitized
}

const onPaste = (event) => {
  // Prevent default paste behavior to handle it manually
  event.preventDefault()
  
  // Get plain text from clipboard
  const text = event.clipboardData.getData('text/plain')
  
  // Insert text at cursor position
  document.execCommand('insertText', false, text)
}

const onKeydown = (event) => {
  // Handle tab key
  if (event.key === 'Tab') {
    event.preventDefault()
    document.execCommand('insertHTML', false, '&nbsp;&nbsp;&nbsp;&nbsp;')
  }
  
  // Prevent form submission on Enter key
  if (event.key === 'Enter' && event.ctrlKey) {
    // Allow Ctrl+Enter for new line
    return
  }
  
  // Prevent Enter from submitting the form
  if (event.key === 'Enter') {
    // Let the default behavior happen (new line in contenteditable)
    // but prevent it from bubbling up to the form
    event.stopPropagation()
  }
}

const onFocus = () => {
  if (editor.value) {
    // Ensure proper text direction when focused
    editor.value.style.direction = 'ltr'
    editor.value.style.textAlign = 'left'
    
    // Place cursor at the end if there's content
    if (editor.value.textContent.trim()) {
      const range = document.createRange()
      const selection = window.getSelection()
      range.selectNodeContents(editor.value)
      range.collapse(false)
      selection.removeAllRanges()
      selection.addRange(range)
    }
  }
}

const onBlur = () => {
  // Clean up any empty content
  if (editor.value && !editor.value.textContent.trim()) {
    editor.value.innerHTML = ''
  }
}

// Initialize editor content
const initializeContent = () => {
  if (editor.value) {
    // Clear any existing content first
    editor.value.innerHTML = ''
    
    // Set the initial content if provided
    if (props.modelValue) {
      const sanitizedValue = sanitizeContent(props.modelValue)
      editor.value.innerHTML = sanitizedValue
    }
    
    // Ensure proper text direction
    editor.value.style.direction = 'ltr'
    editor.value.style.textAlign = 'left'
    
    // Force text direction on all child elements
    forceTextDirection()
  }
}

// Watch for external changes
watch(() => props.modelValue, (newValue, oldValue) => {
  console.log('=== External Content Change ===')
  console.log('Old value:', oldValue)
  console.log('New value:', newValue)
  console.log('Current editor content:', editor.value?.innerHTML)
  
  if (editor.value && newValue !== editor.value.innerHTML) {
    // Only update if the content is actually different
    const sanitizedValue = sanitizeContent(newValue)
    console.log('Sanitized value:', sanitizedValue)
    
    editor.value.innerHTML = sanitizedValue || ''
    // Force text direction after content update
    forceTextDirection()
    
    console.log('Editor content after update:', editor.value.innerHTML)
  }
}, { immediate: true })

// Initialize when component is mounted
import { onMounted } from 'vue'
onMounted(() => {
  initializeContent()
  
  // Additional initialization to ensure proper text direction
  if (editor.value) {
    // Force the editor to have proper text direction
    editor.value.setAttribute('dir', 'ltr')
    editor.value.setAttribute('lang', 'en')
    
    // Add event listeners for additional text direction control
    editor.value.addEventListener('keydown', (e) => {
      // Prevent form submission on Enter
      if (e.key === 'Enter') {
        e.stopPropagation()
        // Force text direction after common text input
        setTimeout(() => {
          forceTextDirection()
        }, 10)
      }
      
      if (e.key === ' ') {
        // Force text direction after space
        setTimeout(() => {
          forceTextDirection()
        }, 10)
      }
    })
    
    // Prevent form submission from editor
    editor.value.addEventListener('submit', (e) => {
      e.preventDefault()
      e.stopPropagation()
      return false
    })
    
    // Add global form submission prevention
    const preventFormSubmit = (e) => {
      if (e.type === 'submit') {
        e.preventDefault()
        e.stopPropagation()
        return false
      }
    }
    
    // Prevent form submission from the entire editor container
    const editorContainer = editor.value.closest('.rich-text-editor')
    if (editorContainer) {
      editorContainer.addEventListener('submit', preventFormSubmit, true)
      editorContainer.addEventListener('click', (e) => {
        // Prevent any button clicks from submitting forms
        if (e.target.tagName === 'BUTTON' && e.target.type !== 'submit') {
          e.preventDefault()
          e.stopPropagation()
        }
      }, true)
    }
    
    // Periodic content integrity check
    setInterval(() => {
      if (editor.value && editor.value.innerHTML) {
        const content = editor.value.innerHTML
        if (content.includes('\u200E') || content.includes('\u200F') || content.includes('\u202A')) {
          console.warn('Periodic check: Found corrupted content, fixing...')
          const sanitizedContent = sanitizeContent(content)
          editor.value.innerHTML = sanitizedContent
          forceTextDirection()
        }
      }
    }, 5000) // Check every 5 seconds
  }
})

// Expose methods for parent component
defineExpose({
  clearContent: () => {
    if (editor.value) {
      editor.value.innerHTML = ''
    }
  },
  getContent: () => {
    return editor.value ? editor.value.innerHTML : ''
  },
  setContent: (content) => {
    if (editor.value) {
      const sanitizedContent = sanitizeContent(content)
      editor.value.innerHTML = sanitizedContent || ''
      // Force refresh the display
      editor.value.style.direction = 'ltr'
      editor.value.style.textAlign = 'left'
      // Force text direction on all child elements
      forceTextDirection()
    }
  },
  refresh: () => {
    if (editor.value) {
      const currentContent = editor.value.innerHTML
      editor.value.innerHTML = ''
      setTimeout(() => {
        editor.value.innerHTML = currentContent
        editor.value.style.direction = 'ltr'
        editor.value.style.textAlign = 'left'
        // Force text direction on all child elements
        forceTextDirection()
      }, 10)
    }
  }
})
</script>

<style scoped>
.editor-content {
  line-height: 1.6;
  direction: ltr;
  text-align: left;
  unicode-bidi: normal;
}

.editor-content img {
  max-width: 100%;
  height: auto;
}

.editor-content:focus {
  outline: none;
}

.editor-content[contenteditable="true"] {
  cursor: text;
}

.editor-content:empty:before {
  content: attr(data-placeholder);
  color: #9ca3af;
  pointer-events: none;
  position: absolute;
}

.editor-content:focus:empty:before {
  display: none;
}

/* Force left-to-right text direction */
.editor-content * {
  direction: ltr !important;
  text-align: left !important;
  unicode-bidi: normal !important;
}

/* Ensure proper text rendering */
.editor-content p,
.editor-content div,
.editor-content span {
  direction: ltr !important;
  text-align: left !important;
}

/* Additional safeguards */
.editor-content {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
  writing-mode: horizontal-tb !important;
  text-orientation: mixed !important;
}

.editor-content * {
  font-family: inherit !important;
  writing-mode: horizontal-tb !important;
  text-orientation: mixed !important;
}
</style>
