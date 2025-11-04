import { onMounted, onUnmounted, ref } from 'vue';

type ForceFinishDetail = {
  stepId?: number;
};

interface UseTeacherForceFinishOptions {
  isActive: () => boolean;
  onStart: (detail: ForceFinishDetail) => void;
  onCancel?: () => void;
}

export function useTeacherForceFinish(options: UseTeacherForceFinishOptions) {
  const isForcedFinish = ref(false);

  const startForcedFinish = (detail: ForceFinishDetail) => {
    isForcedFinish.value = true;
    options.onStart(detail);
  };

  const clearForcedFinish = (notifyCancel = true) => {
    const wasForced = isForcedFinish.value;
    isForcedFinish.value = false;
    if (notifyCancel && wasForced) {
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
    clearForcedFinish,
  };
}
