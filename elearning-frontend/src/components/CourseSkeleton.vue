<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
    <div class="aspect-w-16 aspect-h-9 bg-gray-200 h-48"></div>
    <div class="p-4">
      <div class="flex items-center justify-between mb-2">
        <div class="h-4 bg-gray-200 rounded w-16"></div>
        <div class="h-4 bg-gray-200 rounded w-20"></div>
      </div>
      <div class="h-5 bg-gray-200 rounded w-full mb-2"></div>
      <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
          <div class="h-4 bg-gray-200 rounded w-24"></div>
        </div>
        <div class="text-right">
          <div class="h-4 bg-gray-200 rounded w-12 mb-1"></div>
          <div class="h-3 bg-gray-200 rounded w-16"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// No props or logic needed for skeleton component
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
