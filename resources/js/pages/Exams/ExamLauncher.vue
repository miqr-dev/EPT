<script setup lang="ts">
import { onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  examUrl: string
}>()

function launchExam() {
  window.open(props.examUrl, '_blank', 'noopener,noreferrer,fullscreen=yes')
  }

onMounted(() => {
  // Automatically launch the exam window when the component is mounted.
  launchExam()
})
</script>

<template>
  <Head title="Launching Exam..." />
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md text-center">
      <h1 class="text-2xl font-bold text-gray-800">Launching Exam</h1>
      <p class="text-gray-600">Your exam is opening in a new window. If it doesn't open automatically, please click the button below.</p>
      <Button size="lg" @click="launchExam">Launch Exam</Button>
    </div>
  </div>
</template>