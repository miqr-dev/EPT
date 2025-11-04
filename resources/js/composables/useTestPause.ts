import { ref, onMounted, onUnmounted } from 'vue';
import Echo from 'laravel-echo';

export function useTestPause(participantId: number, onPause: () => void) {
  const isPaused = ref(false);
  const pauseCountdown = ref(0);
  let countdownTimer: any = null;

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
