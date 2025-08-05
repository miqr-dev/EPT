<script setup lang="ts">
import { defineProps, ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps<{
  exam: any
}>()

const formatTime = (seconds: number) => {
  if (seconds < 0) return '00:00'
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

// We need a local copy of the exam to update the timer
const localExam = ref(JSON.parse(JSON.stringify(props.exam)))

let timerInterval: any = null

onMounted(() => {
  timerInterval = setInterval(() => {
    if (localExam.value && localExam.value.participants) {
      localExam.value.participants.forEach((participant: any) => {
        const status = participant.stepStatuses?.[0]
        if (status && status.time_remaining > 0) {
          status.time_remaining--
        }
      })
    }
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})

watch(() => props.exam, (newExam) => {
  localExam.value = JSON.parse(JSON.stringify(newExam))
}, { deep: true })

</script>

<template>
  <div class="mt-8">
    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Live Exam Status</h2>
    <div class="mt-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Participant
            </th>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Current Test
            </th>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Status
            </th>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Time Remaining
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="localExam && localExam.participants" v-for="participant in localExam.participants"
            :key="participant.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ participant.user.name }}, {{ participant.user.firstname }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              {{ localExam.currentStep?.test.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                'bg-green-100 text-green-800': participant.stepStatuses?.[0]?.status === 'completed',
                'bg-yellow-100 text-yellow-800': participant.stepStatuses?.[0]?.status === 'in_progress',
                'bg-blue-100 text-blue-800': participant.stepStatuses?.[0]?.status === 'not_started',
                'bg-red-100 text-red-800': !participant.stepStatuses?.[0]?.status
              }">
                {{ participant.stepStatuses?.[0]?.status?.replace('_', ' ') || 'N/A' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              {{ formatTime(participant.stepStatuses?.[0]?.time_remaining) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
