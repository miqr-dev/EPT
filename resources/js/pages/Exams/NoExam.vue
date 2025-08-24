<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import axios from 'axios'
import { onMounted, onUnmounted } from 'vue'

const handleLogout = () => {
  router.flushAll()
}

let polling: any = null

const checkExamStatus = async () => {
  try {
    const response = await axios.get(route('api.exam-status'))
    if (response.data?.status === 'in_progress') {
      router.get(route('my-exam'))
    }
  } catch (error) {
    console.error('Error checking exam status:', error)
  }
}

onMounted(() => {
  polling = setInterval(checkExamStatus, 5000)
  checkExamStatus()
})

onUnmounted(() => {
  if (polling) {
    clearInterval(polling)
  }
})
</script>



<template>

  <Head title="Keine Prüfung" />
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="absolute top-4 right-4">
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        @click="handleLogout"
        class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
      >
        <LogOut class="h-5 w-5" />
        <span>Abmelden</span>
      </Link>
    </div>
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md text-center">
      <h1 class="text-2xl font-bold text-gray-800">Keine Prüfung</h1>
      <Link :href="route('my-exam')" class="block mt-4">
        <Button class="w-full">Zur Prüfung</Button>
      </Link>
    </div>
  </div>
</template>
