<script setup lang="ts">
import { ref, watch } from 'vue'
import TimeRemainingBanner from '@/components/TimeRemainingBanner.vue'
import { useTimeRemainingWarnings } from '@/composables/useTimeRemainingWarnings'

const props = defineProps<{ timeRemainingSeconds?: number | null }>()

const normalizedSeconds = ref<number | null>(null)

watch(
  () => props.timeRemainingSeconds,
  (value) => {
    normalizedSeconds.value = typeof value === 'number' ? value : null
  },
  { immediate: true },
)

const { showFiveMinuteBanner, showOneMinuteBanner } = useTimeRemainingWarnings(normalizedSeconds)
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-x-0 top-4 z-50 flex justify-center px-4">
      <div class="pointer-events-none w-full max-w-3xl space-y-2">
        <TimeRemainingBanner
          v-if="showOneMinuteBanner"
          tone="warning"
          class="pointer-events-auto"
        >
          Letzte Minute! Bitte schließen Sie den Test ab.
        </TimeRemainingBanner>
        <TimeRemainingBanner
          v-else-if="showFiveMinuteBanner"
          tone="success"
          class="pointer-events-auto"
        >
          Noch 5 Minuten verbleiben.
        </TimeRemainingBanner>
      </div>
    </div>
  </Teleport>
</template>
