import { computed, ref, watch, onScopeDispose } from 'vue'

const FIVE_MINUTES = 300
const ONE_MINUTE = 60

export function useTimeRemainingWarnings(remainingSecondsRef: { value: number | null | undefined }, options: { displayDurationMs?: number } = {}) {
  const displayDuration = options.displayDurationMs ?? 10000

  const showFiveMinuteBanner = ref(false)
  const showOneMinuteBanner = ref(false)

  const hasShownFiveMinute = ref(false)
  const hasShownOneMinute = ref(false)

  let fiveMinuteTimeout: ReturnType<typeof setTimeout> | null = null
  let oneMinuteTimeout: ReturnType<typeof setTimeout> | null = null

  const clearTimers = () => {
    if (fiveMinuteTimeout) {
      clearTimeout(fiveMinuteTimeout)
      fiveMinuteTimeout = null
    }
    if (oneMinuteTimeout) {
      clearTimeout(oneMinuteTimeout)
      oneMinuteTimeout = null
    }
  }

  const triggerBanner = (type: 'five' | 'one') => {
    if (type === 'five') {
      showFiveMinuteBanner.value = true
      fiveMinuteTimeout = setTimeout(() => {
        showFiveMinuteBanner.value = false
      }, displayDuration)
    }
    if (type === 'one') {
      showOneMinuteBanner.value = true
      oneMinuteTimeout = setTimeout(() => {
        showOneMinuteBanner.value = false
      }, displayDuration)
    }
  }

  watch(
    () => remainingSecondsRef.value,
    (newValue, oldValue) => {
      const current = typeof newValue === 'number' ? newValue : null
      const previous = typeof oldValue === 'number' ? oldValue : null

      if (current === null) return

      if (current <= FIVE_MINUTES && current > ONE_MINUTE && !hasShownFiveMinute.value) {
        triggerBanner('five')
        hasShownFiveMinute.value = true
      }

      if (current <= ONE_MINUTE && (!hasShownOneMinute.value && (previous === null || previous > ONE_MINUTE))) {
        triggerBanner('one')
        hasShownOneMinute.value = true
      }
    },
    { immediate: true },
  )

  onScopeDispose(() => {
    clearTimers()
  })

  return {
    showFiveMinuteBanner: computed(() => showFiveMinuteBanner.value),
    showOneMinuteBanner: computed(() => showOneMinuteBanner.value),
  }
}
