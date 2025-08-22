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
  <Head title="Prüfung wird gestartet..." />
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md text-center">
      <h1 class="text-2xl font-bold text-gray-800">Prüfung wird gestartet</h1>
      <p class="text-gray-600">Ihre Prüfung öffnet sich in einem neuen Fenster. Wenn sie sich nicht automatisch öffnet, klicken Sie bitte auf den untenstehenden Button.</p>
      <Button size="lg" @click="launchExam">Prüfung starten</Button>
    </div>
  </div>
</template>
