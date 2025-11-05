import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Echo from 'laravel-echo';

export function useTestPause(participantId: number, examStepId: number, getAnswers: () => any) {
  const isPaused = ref(false);
  const pauseCountdown = ref(0);
  let countdownTimer: any = null;

  function onPause() {
    router.post(route('my-exam.pause-step'), {
      exam_step_id: examStepId,
      results: getAnswers(),
    }, {
      preserveState: true,
    });
  }

  onMounted(() => {
    window.Echo.private(`participant.${participantId}`)
      .listen('TestPausing', (e: any) => {
        isPaused.value = true;
        pauseCountdown.value = e.delay;
        countdownTimer = setInterval(() => {
          pauseCountdown.value--;
          if (pauseCountdown.value <= 0) {
            clearInterval(countdownTimer);
            onPause();
          }
        }, 1000);
      })
      .listen('TestResumed', () => {
        isPaused.value = false;
        clearInterval(countdownTimer);
        pauseCountdown.value = 0;
      });
  });

  onUnmounted(() => {
    clearInterval(countdownTimer);
  });

  return { isPaused, pauseCountdown };
}
