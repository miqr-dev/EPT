<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'

const handleLogout = () => {
  router.flushAll()
}

const examAvailable = ref(false)
let intervalId: number | undefined
const page = usePage<{ auth: { user: { id: number } } }>()
const userId = page.props.auth.user.id

const checkExam = async () => {
  try {
    const { data } = await axios.get(route('api.active-exam'))
    examAvailable.value = !!data && data.participants?.some((p: any) => p.participant_id === userId)
  } catch {
    examAvailable.value = false
  }
}

onMounted(() => {
  checkExam()
  intervalId = window.setInterval(checkExam, 5000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>



<template>

  <Head title="Warten auf Prüfung" />
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
      <h1 class="text-2xl font-bold text-gray-800">
        Bitte warten Sie, bis die Prüfung beginnt.
      </h1>
      <div v-if="examAvailable" class="mt-4">
        <Link :href="route('my-exam')" class="block">
          <Button class="w-full">Zur Prüfung</Button>
        </Link>
      </div>
    </div>
  </div>
</template>
