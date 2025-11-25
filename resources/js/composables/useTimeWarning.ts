import { ref, watch, type Ref } from 'vue';

export function useTimeWarning(timeRemaining: Ref<number | null>) {
    const show5MinWarning = ref(false);
    const show1MinWarning = ref(false);

    watch(timeRemaining, (newTime, oldTime) => {
        if (newTime === null || oldTime === null) return;

        if (newTime <= 300 && oldTime > 300) {
            show5MinWarning.value = true;
            setTimeout(() => (show5MinWarning.value = false), 10000);
        }

        if (newTime <= 60 && oldTime > 60) {
            show1MinWarning.value = true;
            setTimeout(() => (show1MinWarning.value = false), 10000);
        }
    });

    return {
        show5MinWarning,
        show1MinWarning,
    };
}
