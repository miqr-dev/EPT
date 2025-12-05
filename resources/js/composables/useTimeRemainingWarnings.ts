import { computed, onScopeDispose, ref, watch } from 'vue';

/**
 * @module useTimeRemainingWarnings
 * @description A Vue composable for showing time remaining warnings.
 */

const FIVE_MINUTES = 300;
const ONE_MINUTE = 60;

/**
 * A composable that provides warnings when the remaining time is low.
 *
 * @param {{ value: number | null | undefined }} remainingSecondsRef - A ref containing the remaining seconds.
 * @param {{ displayDurationMs?: number }} [options={}] - Options for the composable.
 * @returns {{
 *   showFiveMinuteBanner: import('vue').ComputedRef<boolean>,
 *   showOneMinuteBanner: import('vue').ComputedRef<boolean>,
 * }}
 */
export function useTimeRemainingWarnings(remainingSecondsRef: { value: number | null | undefined }, options: { displayDurationMs?: number } = {}) {
    const displayDuration = options.displayDurationMs ?? 10000;

    const showFiveMinuteBanner = ref(false);
    const showOneMinuteBanner = ref(false);

    const hasShownFiveMinute = ref(false);
    const hasShownOneMinute = ref(false);

    let fiveMinuteTimeout: ReturnType<typeof setTimeout> | null = null;
    let oneMinuteTimeout: ReturnType<typeof setTimeout> | null = null;

    /**
     * Clears any active timers.
     */
    const clearTimers = () => {
        if (fiveMinuteTimeout) {
            clearTimeout(fiveMinuteTimeout);
            fiveMinuteTimeout = null;
        }
        if (oneMinuteTimeout) {
            clearTimeout(oneMinuteTimeout);
            oneMinuteTimeout = null;
        }
    };

    /**
     * Triggers a banner to be shown for a limited time.
     * @param {'five' | 'one'} type - The type of banner to show.
     */
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

            const hasReset =
                (current !== null && previous === null) || (current !== null && previous !== null && current > previous + 5)

            if (hasReset) {
                clearTimers()
                showFiveMinuteBanner.value = false
                showOneMinuteBanner.value = false
                hasShownFiveMinute.value = false
                hasShownOneMinute.value = false
            }

            if (current === null) {
                clearTimers()
                showFiveMinuteBanner.value = false
                showOneMinuteBanner.value = false
                return
            }

            if (current <= FIVE_MINUTES && current > ONE_MINUTE && !hasShownFiveMinute.value) {
                triggerBanner('five')
                hasShownFiveMinute.value = true
            }

            if (current <= ONE_MINUTE && !hasShownOneMinute.value) {
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
