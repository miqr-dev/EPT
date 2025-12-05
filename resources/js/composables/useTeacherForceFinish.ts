import { onMounted, onUnmounted, ref } from 'vue';

/**
 * @fileoverview Composable for handling a teacher-initiated forced finish of a test.
 */

/**
 * Type definition for the detail object of the 'teacher-force-finish' event.
 * @typedef {Object} ForceFinishDetail
 * @property {number} [stepId] - The ID of the exam step being finished.
 * @property {string|number|null} [deadline] - The timestamp of the deadline.
 * @property {string|number|null} [requestedAt] - The timestamp when the finish was requested.
 */
type ForceFinishDetail = {
  stepId?: number;
  deadline?: string | number | null;
  requestedAt?: string | number | null;
};

/**
 * Options for the useTeacherForceFinish composable.
 * @interface UseTeacherForceFinishOptions
 * @property {() => boolean} isActive - A function that returns true if the composable should be active.
 * @property {(detail: ForceFinishDetail) => void} onStart - Callback function when the forced finish starts.
 * @property {() => void} onCountdownFinished - Callback function when the countdown finishes.
 * @property {() => void} [onCancel] - Optional callback function when the forced finish is cancelled.
 * @property {number} [countdownSeconds] - Optional duration of the countdown in seconds.
 */
interface UseTeacherForceFinishOptions {
  isActive: () => boolean;
  onStart: (detail: ForceFinishDetail) => void;
  onCountdownFinished: () => void;
  onCancel?: () => void;
  countdownSeconds?: number;
}

/**
 * A composable for managing a teacher-initiated forced finish countdown.
 *
 * @param {UseTeacherForceFinishOptions} options - The options for the composable.
 * @returns {{
 *   isForcedFinish: import('vue').Ref<boolean>,
 *   forcedFinishCountdown: import('vue').Ref<number>,
 *   clearForcedFinish: (notifyCancel?: boolean) => void
 * }}
 */
export function useTeacherForceFinish(options: UseTeacherForceFinishOptions) {
  const defaultSeconds = options.countdownSeconds ?? 10;
  const isForcedFinish = ref(false);
  const forcedFinishCountdown = ref(defaultSeconds);
  let deadlineMs: number | null = null;
  let timer: ReturnType<typeof setInterval> | null = null;

  /** Clears the countdown timer. */
  const clearTimer = () => {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  };

  /**
   * Converts a deadline value to milliseconds.
   * @param {ForceFinishDetail['deadline']} value - The deadline value to convert.
   * @returns {number | null} The deadline in milliseconds, or null if invalid.
   */
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

  /** Updates the countdown timer. */
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

  /**
   * Starts the forced finish countdown.
   * @param {ForceFinishDetail} detail - The detail object from the event.
   */
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

  /**
   * Clears the forced finish state.
   * @param {boolean} [notifyCancel=true] - Whether to notify the cancel callback.
   */
  const clearForcedFinish = (notifyCancel = true) => {
    clearTimer();
    deadlineMs = null;
    isForcedFinish.value = false;
    forcedFinishCountdown.value = defaultSeconds;
    if (notifyCancel) {
      options.onCancel?.();
    }
  };

  /**
   * Handles the 'teacher-force-finish' event.
   * @param {Event} event - The event object.
   */
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
