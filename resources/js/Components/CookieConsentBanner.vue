<script setup lang="ts">
import { ref, onMounted } from 'vue'

const isVisible = ref(false)

// Helper to get cookie value
const getCookie = (name: string) => {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) return parts.pop()?.split(';').shift()
  return null
}

onMounted(() => {
  // Check if consent has already been given or denied using document cookies and localstorage fallback
  const consent = getCookie('buildflow_cookie_consent') || localStorage.getItem('buildflow_cookie_consent')
  if (!consent) {
    isVisible.value = true
  }
})

const setConsentCookie = (value: string) => {
  const d = new Date()
  d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000)) // 1 year
  document.cookie = `buildflow_cookie_consent=${value};expires=${d.toUTCString()};path=/;SameSite=Strict`
  localStorage.setItem('buildflow_cookie_consent', value) // Backup
}

const accept = () => {
  setConsentCookie('accepted')
  isVisible.value = false
  window.dispatchEvent(new Event('cookie_consent_accepted'))
}

const decline = () => {
  setConsentCookie('declined')
  isVisible.value = false
}
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <div 
      v-if="isVisible" 
      class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 px-2 sm:px-5 z-50 pointer-events-none"
    >
      <div class="pointer-events-auto bg-white border border-gray-200 shadow-xl rounded-xl p-4 sm:p-5 mx-auto max-w-4xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        
        <div class="flex-1">
          <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            🍪 We value your privacy
          </h3>
          <p class="mt-1 text-sm text-gray-600">
            We use strictly necessary cookies to make our site work. We'd also like to use optional analytics cookies to help us improve the platform by collecting usage data. We won't set optional cookies unless you enable them.
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">
          <button 
            @click="decline"
            class="flex-1 sm:flex-none justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors"
          >
            Decline All
          </button>
          <button 
            @click="accept"
            class="flex-1 sm:flex-none justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors"
          >
            Accept Analytics
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>
