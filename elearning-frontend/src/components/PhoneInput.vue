<template>
  <div class="phone-input-container">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <!-- Country Selector Button -->
      <button
        type="button"
        @click="toggleDropdown"
        :class="[
          'absolute left-0 top-0 h-full px-3 py-2 border border-r-0 border-gray-300 rounded-l-md bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 flex items-center space-x-2 min-w-[120px]',
          error ? 'border-red-500' : ''
        ]"
      >
        <span class="text-lg">{{ selectedCountry.flag }}</span>
        <span class="text-sm font-medium text-gray-700">{{ selectedCountry.dialCode }}</span>
        <svg class="w-4 h-4 text-gray-400" :class="{ 'rotate-180': isDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>

      <!-- Phone Number Input -->
      <input
        :id="inputId"
        :name="inputId"
        v-model="phoneNumber"
        type="tel"
        autocomplete="tel"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="[
          'block w-full pl-32 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm',
          error ? 'border-red-500' : ''
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Dropdown -->
      <div
        v-if="isDropdownOpen"
        class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
      >
        <!-- Search Input -->
        <div class="p-2 border-b border-gray-200">
          <label for="country-search" class="sr-only">Search countries</label>
          <input
            id="country-search"
            name="country-search"
            v-model="searchQuery"
            type="text"
            placeholder="Search countries..."
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
            @click.stop
          />
        </div>

        <!-- Country List -->
        <div class="max-h-48 overflow-auto">
          <button
            v-for="country in filteredCountries"
            :key="country.code"
            type="button"
            @click="selectCountry(country)"
            :class="[
              'w-full px-3 py-2 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 flex items-center space-x-3',
              selectedCountry.code === country.code ? 'bg-primary-50 text-primary-700' : 'text-gray-900'
            ]"
          >
            <span class="text-lg">{{ country.flag }}</span>
            <span class="flex-1 text-sm font-medium">{{ country.name }}</span>
            <span class="text-sm text-gray-500">{{ country.dialCode }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="helpText" class="mt-1 text-sm text-gray-500">{{ helpText }}</p>

    <!-- Formatted Number Display -->
    <div v-if="showFormatted && formattedNumber" class="mt-1 text-xs text-gray-500">
      Formatted: {{ formattedNumber }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { countries, defaultCountry, formatPhoneNumber } from '@/data/countries'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ countryCode: '', phoneNumber: '' })
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Enter phone number'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  showFormatted: {
    type: Boolean,
    default: false
  },
  defaultCountryCode: {
    type: String,
    default: 'US'
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus'])

const inputId = `phone-input-${Math.random().toString(36).substr(2, 9)}`
const isDropdownOpen = ref(false)
const searchQuery = ref('')
const phoneNumber = ref('')
const selectedCountry = ref(defaultCountry)

// Initialize with default country or prop value
onMounted(() => {
  if (props.modelValue.countryCode) {
    const country = countries.find(c => c.code === props.modelValue.countryCode)
    if (country) {
      selectedCountry.value = country
    }
  } else if (props.defaultCountryCode) {
    const country = countries.find(c => c.code === props.defaultCountryCode)
    if (country) {
      selectedCountry.value = country
    }
  }
  
  phoneNumber.value = props.modelValue.phoneNumber || ''
})

// Filter countries based on search query
const filteredCountries = computed(() => {
  if (!searchQuery.value) return countries
  
  const query = searchQuery.value.toLowerCase()
  return countries.filter(country => 
    country.name.toLowerCase().includes(query) ||
    country.dialCode.includes(query) ||
    country.code.toLowerCase().includes(query)
  )
})

// Formatted phone number
const formattedNumber = computed(() => {
  if (!phoneNumber.value || !selectedCountry.value) return ''
  return formatPhoneNumber(selectedCountry.value.code, phoneNumber.value)
})

// Watch for changes and emit updates
watch([phoneNumber, selectedCountry], () => {
  const value = {
    countryCode: selectedCountry.value.code,
    phoneNumber: phoneNumber.value,
    dialCode: selectedCountry.value.dialCode,
    formatted: formattedNumber.value
  }
  
  emit('update:modelValue', value)
  emit('change', value)
}, { deep: true })

// Methods
const toggleDropdown = () => {
  if (props.disabled) return
  isDropdownOpen.value = !isDropdownOpen.value
  if (isDropdownOpen.value) {
    searchQuery.value = ''
  }
}

const selectCountry = (country) => {
  selectedCountry.value = country
  isDropdownOpen.value = false
  searchQuery.value = ''
}

const handleInput = (event) => {
  // Remove any non-digit characters except + at the beginning
  let value = event.target.value
  
  // If user types a country code, try to detect it
  if (value.startsWith('+')) {
    const dialCode = value.split(' ')[0]
    const country = countries.find(c => c.dialCode === dialCode)
    if (country) {
      selectedCountry.value = country
      phoneNumber.value = value.replace(dialCode, '').trim()
      return
    }
  }
  
  // Remove non-digit characters
  phoneNumber.value = value.replace(/\D/g, '')
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleFocus = (event) => {
  emit('focus', event)
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.phone-input-container')) {
    isDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.phone-input-container {
  position: relative;
}

/* Custom scrollbar for dropdown */
.overflow-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
