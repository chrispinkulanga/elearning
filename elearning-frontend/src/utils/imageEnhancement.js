/**
 * Automatic Image Enhancement Utility
 * Automatically enhances images for better visibility and clarity
 */

/**
 * Automatically enhances an image using Canvas API
 * @param {File} imageFile - The image file to enhance
 * @param {Object} options - Enhancement options
 * @returns {Promise<Blob>} - Enhanced image as Blob
 */
export async function autoEnhanceImage(imageFile, options = {}) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')
    
    img.onload = () => {
      try {
        // Set canvas dimensions
        canvas.width = img.width
        canvas.height = img.height
        
        // Draw original image
        ctx.drawImage(img, 0, 0)
        
        // Get image data
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
        const data = imageData.data
        
        // Apply automatic enhancements
        const enhancedData = applyAutoEnhancements(data, options)
        
        // Put enhanced data back
        imageData.data.set(enhancedData)
        ctx.putImageData(imageData, 0, 0)
        
        // Convert to blob
        canvas.toBlob((blob) => {
          if (blob) {
            resolve(blob)
          } else {
            reject(new Error('Failed to create enhanced image blob'))
          }
        }, imageFile.type, 0.95) // 95% quality
        
      } catch (error) {
        reject(error)
      }
    }
    
    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }
    
    // Load the image
    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.readAsDataURL(imageFile)
  })
}

/**
 * Apply automatic enhancements to image data
 * @param {Uint8ClampedArray} data - Image pixel data
 * @param {Object} options - Enhancement options
 * @returns {Uint8ClampedArray} - Enhanced pixel data
 */
function applyAutoEnhancements(data, options = {}) {
  const {
    brightness = 1.1,        // Slightly brighten
    contrast = 1.2,          // Increase contrast
    saturation = 1.15,       // Boost saturation
    sharpness = 0.3,         // Add sharpness
    gamma = 0.9,             // Adjust gamma
    noiseReduction = true,   // Reduce noise
    autoLevels = true        // Auto-adjust levels
  } = options
  
  const enhancedData = new Uint8ClampedArray(data)
  const length = data.length
  
  // First pass: Auto-levels adjustment
  if (autoLevels) {
    const { min, max } = getMinMaxValues(data)
    const range = max - min
    
    for (let i = 0; i < length; i += 4) {
      // Normalize each channel
      enhancedData[i] = Math.max(0, Math.min(255, ((data[i] - min) / range) * 255))     // R
      enhancedData[i + 1] = Math.max(0, Math.min(255, ((data[i + 1] - min) / range) * 255)) // G
      enhancedData[i + 2] = Math.max(0, Math.min(255, ((data[i + 2] - min) / range) * 255)) // B
      // Alpha channel remains unchanged
    }
  }
  
  // Second pass: Apply enhancements
  for (let i = 0; i < length; i += 4) {
    let r = enhancedData[i]
    let g = enhancedData[i + 1]
    let b = enhancedData[i + 2]
    
    // Apply gamma correction
    r = Math.pow(r / 255, gamma) * 255
    g = Math.pow(g / 255, gamma) * 255
    b = Math.pow(b / 255, gamma) * 255
    
    // Apply brightness
    r = r * brightness
    g = g * brightness
    b = b * brightness
    
    // Apply contrast
    r = ((r - 128) * contrast) + 128
    g = ((g - 128) * contrast) + 128
    b = ((b - 128) * contrast) + 128
    
    // Apply saturation
    const gray = 0.299 * r + 0.587 * g + 0.114 * b
    r = gray + saturation * (r - gray)
    g = gray + saturation * (g - gray)
    b = gray + saturation * (b - gray)
    
    // Clamp values
    enhancedData[i] = Math.max(0, Math.min(255, r))
    enhancedData[i + 1] = Math.max(0, Math.min(255, g))
    enhancedData[i + 2] = Math.max(0, Math.min(255, b))
  }
  
  // Third pass: Apply sharpening
  if (sharpness > 0) {
    applySharpening(enhancedData, canvas.width, canvas.height, sharpness)
  }
  
  // Fourth pass: Noise reduction
  if (noiseReduction) {
    applyNoiseReduction(enhancedData, canvas.width, canvas.height)
  }
  
  return enhancedData
}

/**
 * Get minimum and maximum values from image data
 */
function getMinMaxValues(data) {
  let min = 255
  let max = 0
  
  for (let i = 0; i < data.length; i += 4) {
    const r = data[i]
    const g = data[i + 1]
    const b = data[i + 2]
    const avg = (r + g + b) / 3
    
    min = Math.min(min, avg)
    max = Math.max(max, avg)
  }
  
  return { min, max }
}

/**
 * Apply sharpening filter
 */
function applySharpening(data, width, height, strength) {
  const kernel = [
    0, -strength, 0,
    -strength, 1 + 4 * strength, -strength,
    0, -strength, 0
  ]
  
  const tempData = new Uint8ClampedArray(data)
  
  for (let y = 1; y < height - 1; y++) {
    for (let x = 1; x < width - 1; x++) {
      const idx = (y * width + x) * 4
      
      for (let c = 0; c < 3; c++) { // RGB channels only
        let sum = 0
        
        for (let ky = -1; ky <= 1; ky++) {
          for (let kx = -1; kx <= 1; kx++) {
            const pixelIdx = ((y + ky) * width + (x + kx)) * 4 + c
            const kernelIdx = (ky + 1) * 3 + (kx + 1)
            sum += data[pixelIdx] * kernel[kernelIdx]
          }
        }
        
        tempData[idx + c] = Math.max(0, Math.min(255, sum))
      }
    }
  }
  
  // Copy back
  for (let i = 0; i < data.length; i++) {
    data[i] = tempData[i]
  }
}

/**
 * Apply noise reduction using simple blur
 */
function applyNoiseReduction(data, width, height) {
  const tempData = new Uint8ClampedArray(data)
  const radius = 1
  
  for (let y = radius; y < height - radius; y++) {
    for (let x = radius; x < width - radius; x++) {
      const idx = (y * width + x) * 4
      
      for (let c = 0; c < 3; c++) { // RGB channels only
        let sum = 0
        let count = 0
        
        for (let dy = -radius; dy <= radius; dy++) {
          for (let dx = -radius; dx <= radius; dx++) {
            const pixelIdx = ((y + dy) * width + (x + dx)) * 4 + c
            sum += data[pixelIdx]
            count++
          }
        }
        
        tempData[idx + c] = sum / count
      }
    }
  }
  
  // Copy back
  for (let i = 0; i < data.length; i++) {
    data[i] = tempData[i]
  }
}

/**
 * Create enhanced image URL for display
 * @param {File} imageFile - The image file to enhance
 * @returns {Promise<string>} - Data URL of enhanced image
 */
export async function createEnhancedImageUrl(imageFile) {
  try {
    const enhancedBlob = await autoEnhanceImage(imageFile)
    return URL.createObjectURL(enhancedBlob)
  } catch (error) {
    console.warn('Image enhancement failed, using original:', error)
    return URL.createObjectURL(imageFile)
  }
}

/**
 * Check if image needs enhancement based on file size and dimensions
 * @param {File} imageFile - The image file to check
 * @returns {boolean} - Whether the image should be enhanced
 */
export function shouldEnhanceImage(imageFile) {
  // Only enhance images
  if (!imageFile.type.startsWith('image/')) {
    return false
  }
  
  // Don't enhance very small images (likely icons)
  if (imageFile.size < 10240) { // 10KB
    return false
  }
  
  // Don't enhance very large images (might be high quality already)
  if (imageFile.size > 5 * 1024 * 1024) { // 5MB
    return false
  }
  
  return true
}

/**
 * Get enhancement options based on image characteristics
 * @param {File} imageFile - The image file
 * @returns {Object} - Enhancement options
 */
export function getEnhancementOptions(imageFile) {
  const size = imageFile.size
  const isSmall = size < 500 * 1024 // 500KB
  const isLarge = size > 2 * 1024 * 1024 // 2MB
  
  return {
    brightness: isSmall ? 1.15 : 1.1,
    contrast: isSmall ? 1.3 : 1.2,
    saturation: isSmall ? 1.2 : 1.15,
    sharpness: isSmall ? 0.4 : 0.3,
    gamma: isSmall ? 0.85 : 0.9,
    noiseReduction: isSmall,
    autoLevels: true
  }
}
