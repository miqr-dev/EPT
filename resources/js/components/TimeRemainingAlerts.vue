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
  <div class="space-y-2">
    <TimeRemainingBanner v-if="showFiveMinuteBanner" tone="success">
      Noch 5 Minuten verbleiben.
    </TimeRemainingBanner>
    <TimeRemainingBanner v-if="showOneMinuteBanner" tone="warning">
      Letzte Minute! Bitte schließen Sie den Test ab.
    </TimeRemainingBanner>
  </div>
</template>
