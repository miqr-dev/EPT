<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps<{
  examUrl: string;
}>();

function launchExam(userInitiated = true) {
  const newWindow = window.open(props.examUrl, '_blank', `noopener,noreferrer,width=${window.screen.width},height=${window.screen.height}`);

  if (!newWindow) {
    alert('Failed to open exam window. Please allow popups.');
    return;
  }

  const handleFocusLoss = () => {
    alert('Exam window must stay focused and in fullscreen.');
    newWindow.close();
  };

  newWindow.addEventListener('blur', handleFocusLoss);

  newWindow.addEventListener('load', () => {
    newWindow.document.addEventListener('contextmenu', (e) => e.preventDefault());

    newWindow.document.addEventListener('keydown', (e) => {
      if (e.key === 'F11' || (e.ctrlKey && e.key.toLowerCase() === 'w')) {
        e.preventDefault();
        alert('This key combination is disabled during the exam.');
      }
    });

    // Note: OS-level controls like Alt+Tab cannot be blocked in standard browsers.

    newWindow.document.addEventListener('visibilitychange', () => {
      if (newWindow.document.hidden) {
        handleFocusLoss();
      }
    });

    newWindow.document.addEventListener('fullscreenchange', () => {
      if (!newWindow.document.fullscreenElement) {
        handleFocusLoss();
      }
    });

    if (userInitiated) {
      newWindow.document.documentElement.requestFullscreen().catch(() => { });
    }
  });
}

onMounted(() => {
  // Automatically launch the exam window when the component is mounted.
  launchExam(false);
});
</script>

<template>

  <Head title="Launching Exam..." />
  <div class="flex min-h-screen items-center justify-center bg-gray-100">
    <div class="w-full max-w-md space-y-6 rounded-lg bg-white p-8 text-center shadow-md">
      <h1 class="text-2xl font-bold text-gray-800">Launching Exam</h1>
      <p class="text-gray-600">Your exam is opening in a new window. If it doesn't open automatically, please click the
        button below.</p>
      <Button size="lg" @click="launchExam">Launch Exam</Button>
    </div>
  </div>
</template>