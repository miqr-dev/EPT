import { onMounted, onUnmounted, ref } from 'vue';

type ForceFinishDetail = {
  stepId?: number;
  deadline?: string | number | null;
  requestedAt?: string | number | null;
};

interface UseTeacherForceFinishOptions {
  isActive: () => boolean;
  onStart: (detail: ForceFinishDetail) => void;
  onCountdownFinished: () => void;
  onCancel?: () => void;
  countdownSeconds?: number;
}

export function useTeacherForceFinish(options: UseTeacherForceFinishOptions) {
  const defaultSeconds = options.countdownSeconds ?? 10;
  const isForcedFinish = ref(false);
  const forcedFinishCountdown = ref(defaultSeconds);
  let deadlineMs: number | null = null;
  let timer: ReturnType<typeof setInterval> | null = null;

  const clearTimer = () => {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  };

  const toMillis = (value: ForceFinishDetail['deadline']) => {
    if (typeof value === 'number') {
      return value;
    }
    if (typeof value === 'string') {
      const parsed = Date.parse(value);
      if (!Number.isNaN(parsed)) {
        return parsed;
      }
    }
    return null;
  };

  const updateCountdown = () => {
    if (deadlineMs === null) {
      return;
    }
    const diffSeconds = Math.ceil((deadlineMs - Date.now()) / 1000);
    forcedFinishCountdown.value = Math.max(0, diffSeconds);
    if (diffSeconds <= 0) {
      clearTimer();
      deadlineMs = null;
      options.onCountdownFinished();
    }
  };

  const startForcedFinish = (detail: ForceFinishDetail) => {
    const explicitDeadline = toMillis(detail.deadline ?? null);
    const requestedAt = toMillis(detail.requestedAt ?? null);
    const fallbackDeadline = Date.now() + defaultSeconds * 1000;

    deadlineMs = explicitDeadline ?? (requestedAt !== null ? requestedAt + defaultSeconds * 1000 : fallbackDeadline);
    forcedFinishCountdown.value = Math.max(0, Math.ceil((deadlineMs - Date.now()) / 1000));
    isForcedFinish.value = true;
    options.onStart(detail);

    clearTimer();
    timer = setInterval(updateCountdown, 1000);
    updateCountdown();
  };

  const clearForcedFinish = (notifyCancel = true) => {
    clearTimer();
    deadlineMs = null;
    isForcedFinish.value = false;
    forcedFinishCountdown.value = defaultSeconds;
    if (notifyCancel) {
      options.onCancel?.();
    }
  };

  const handleEvent = (event: Event) => {
    const detail = (event as CustomEvent<ForceFinishDetail>).detail ?? {};
    if (!options.isActive()) {
      return;
    }
    startForcedFinish(detail);
  };

  onMounted(() => {
    window.addEventListener('teacher-force-finish', handleEvent as EventListener);
  });

  onUnmounted(() => {
    window.removeEventListener('teacher-force-finish', handleEvent as EventListener);
    clearForcedFinish(false);
  });

  return {
    isForcedFinish,
    forcedFinishCountdown,
    clearForcedFinish,
  };
}
