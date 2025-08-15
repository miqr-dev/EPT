<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  exam: any
}>()

const formatTime = (seconds?: number) => {
  if (typeof seconds !== 'number' || seconds < 0) return '00:00'
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

// We need a local copy of the exam to update the timer
const localExam = ref(JSON.parse(JSON.stringify(props.exam)))

const getParticipantStatusFromExam = (exam: any, participant: any) => {
  if (!exam.current_step) {
    return null
  }
  const statuses = participant.stepStatuses || participant.step_statuses
  if (!statuses) {
    return null
  }
  return statuses.find((s: any) => s.exam_step_id === exam.current_exam_step_id)
}

const getParticipantStatus = (participant: any) =>
  getParticipantStatusFromExam(localExam.value, participant)

let timerInterval: any = null

onMounted(() => {
  timerInterval = setInterval(() => {
    if (localExam.value && localExam.value.participants) {
      localExam.value.participants.forEach((participant: any) => {
        const status = getParticipantStatus(participant)
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

watch(
  () => props.exam,
  (newExam) => {
    const updatedExam = JSON.parse(JSON.stringify(newExam))
    if (localExam.value?.participants) {
      updatedExam.participants = updatedExam.participants.map((participant: any) => {
        const existing = localExam.value.participants.find(
          (p: any) => p.id === participant.id,
        )
        if (existing) {
          const updatedStatus = getParticipantStatusFromExam(updatedExam, participant)
          const existingStatus = getParticipantStatusFromExam(localExam.value, existing)
          if (updatedStatus && existingStatus) {
            if (typeof existingStatus.time_remaining === 'number') {
              const newTime =
                typeof updatedStatus.time_remaining === 'number'
                  ? updatedStatus.time_remaining
                  : existingStatus.time_remaining
              updatedStatus.time_remaining = Math.min(
                newTime,
                existingStatus.time_remaining,
              )
            }
          }
        }
        return participant
      })
    }
    localExam.value = updatedExam
  },
  { deep: true },
)

const startExam = () => {
  router.post(route('exams.start', { exam: props.exam.id }), {}, {
    preserveScroll: true,
  })
}

const setStep = (stepId: number) => {
  router.post(route('exams.set-step', { exam: props.exam.id }), { step_id: stepId }, {
    preserveScroll: true,
  })
}

const setStatus = (status: string) => {
  router.post(route('exams.set-status', { exam: props.exam.id }), { status }, {
    preserveScroll: true,
  })
}

</script>

<template>
  <div class="mt-8">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ exam.name }}</h2>
      <div class="flex space-x-2">
        <Button v-if="exam.status === 'not_started'" @click="startExam">
          Start Exam
        </Button>
        <template v-if="exam.status === 'in_progress'">
          <Button v-for="step in exam.steps" :key="step.id" @click="setStep(step.id)"
            :disabled="exam.current_exam_step_id === step.id">
            Start {{ step.test.name }}
          </Button>
        </template>
        <Button v-if="exam.status === 'in_progress'" @click="setStatus('paused')">
          Pause Exam
        </Button>
        <Button v-else-if="exam.status === 'paused'" @click="setStatus('in_progress')">
          Resume Exam
        </Button>
        <Button v-if="exam.status !== 'completed'" @click="setStatus('completed')">
          End Exam
        </Button>
      </div>
    </div>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
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
              {{ participant.user.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              {{ localExam.current_step?.test.name || 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              <span v-if="getParticipantStatus(participant)"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                  'bg-green-100 text-green-800': getParticipantStatus(participant)?.status === 'completed',
                  'bg-yellow-100 text-yellow-800': getParticipantStatus(participant)?.status === 'in_progress',
                  'bg-blue-100 text-blue-800': getParticipantStatus(participant)?.status === 'not_started',
                }">
                {{ getParticipantStatus(participant)?.status?.replace('_', ' ') }}
              </span>
              <span v-else
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                N/A
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              {{ formatTime(getParticipantStatus(participant)?.time_remaining) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
