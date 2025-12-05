<script setup lang="ts">
/**
 * @file LiveExamStatusTable.vue - Component for displaying and managing the live status of an exam.
 */
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

/**
 * @props {any} exam - The exam object.
 */
const props = defineProps<{
  exam: any
}>()

const MAX_EXTRA_MINUTES = 30

/**
 * Formats a number of seconds into a mm:ss string.
 * @param {number} [seconds] - The number of seconds to format.
 * @returns {string} The formatted time string.
 */
const formatTime = (seconds?: number) => {
  if (typeof seconds !== 'number' || seconds < 0) return '00:00'
  const whole = Math.floor(seconds)
  const minutes = Math.floor(whole / 60)
  const secs = whole % 60
  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

// We need a local copy of the exam to update the timer
const localExam = ref(JSON.parse(JSON.stringify(props.exam)))

/**
 * A computed property that splits the exam steps into rows of 5.
 * @returns {any[][]}
 */
const stepRows = computed(() => {
  const steps = Array.isArray(localExam.value?.steps) ? localExam.value.steps : []
  const rows: any[][] = []
  for (let i = 0; i < steps.length; i += 5) {
    rows.push(steps.slice(i, i + 5))
  }
  return rows
})

/**
 * A computed property for the first row of steps.
 * @returns {any[]}
 */
const firstStepRow = computed(() => stepRows.value[0] ?? [])
/**
 * A computed property for the additional rows of steps.
 * @returns {any[][]}
 */
const additionalStepRows = computed(() => stepRows.value.slice(1))

/**
 * Gets the status of a participant for the current exam step.
 * @param {any} exam - The exam object.
 * @param {any} participant - The participant object.
 * @returns {any | null}
 */
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

/**
 * Gets the status of a participant from the local exam state.
 * @param {any} participant - The participant object.
 * @returns {any | null}
 */
const getParticipantStatus = (participant: any) =>
  getParticipantStatusFromExam(localExam.value, participant)

/**
 * Toggles the visibility of the contract for the exam.
 * @param {boolean} enabled - Whether the contract should be visible.
 */
const toggleContractVisibility = (enabled: boolean) => {
  router.post(
    route('exams.set-contract-visibility', { exam: props.exam.id }),
    { enabled },
    { preserveScroll: true },
  )
}

/**
 * State for the extra time input fields.
 * @type {import('vue').Ref<Record<number, { expanded: boolean; minutes: string; error: string | null; loading: boolean }>>}
 */
const extraTimeState = ref<Record<number, { expanded: boolean; minutes: string; error: string | null; loading: boolean }>>({})

let timerInterval: any = null

onMounted(() => {
  timerInterval = setInterval(() => {
    if (localExam.value && localExam.value.participants) {
      localExam.value.participants.forEach((participant: any) => {
        const status = getParticipantStatus(participant)
        if (status) {
          if (status.status === 'in_progress' && status.time_remaining > 0) {
            status.time_remaining = Math.max(0, Math.floor(status.time_remaining) - 1)
          } else if (status.status === 'paused') {
            status.time_remaining = Math.max(0, Math.floor(status.time_remaining ?? 0))
          } else if (status.status !== 'in_progress') {
            status.time_remaining = 0
          }
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
    if (
      localExam.value &&
      updatedExam.current_exam_step_id === localExam.value.current_exam_step_id &&
      localExam.value.participants
    ) {
      updatedExam.participants = updatedExam.participants.map((participant: any) => {
        const existing = localExam.value!.participants.find(
          (p: any) => p.id === participant.id,
        )
        if (existing) {
          const updatedStatus = getParticipantStatusFromExam(updatedExam, participant)
          const existingStatus = getParticipantStatusFromExam(localExam.value!, existing)
          if (updatedStatus && existingStatus) {
            if (typeof existingStatus.time_remaining === 'number') {
              const newTime =
                typeof updatedStatus.time_remaining === 'number'
                  ? Math.floor(updatedStatus.time_remaining)
                  : existingStatus.time_remaining
              updatedStatus.time_remaining =
                existingStatus.time_remaining > 0
                  ? Math.max(newTime, existingStatus.time_remaining)
                  : newTime
            }
            if (updatedStatus.status === 'paused') {
              updatedStatus.time_remaining =
                typeof updatedStatus.time_remaining === 'number'
                  ? Math.max(0, Math.floor(updatedStatus.time_remaining))
                  : existingStatus.time_remaining
            } else if (updatedStatus.status !== 'in_progress') {
              updatedStatus.time_remaining = 0
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

/**
 * Starts the exam.
 */
const startExam = () => {
  router.post(route('exams.start', { exam: props.exam.id }), {}, {
    preserveScroll: true,
  })
}

/**
 * Sets the current step of the exam.
 * @param {number} stepId - The ID of the step to set.
 */
const setStep = (stepId: number) => {
  router.post(route('exams.set-step', { exam: props.exam.id }), { step_id: stepId }, {
    preserveScroll: true,
  })
}

/**
 * Sets the status of the exam.
 * @param {string} status - The status to set.
 */
const setStatus = (status: string) => {
  router.post(route('exams.set-status', { exam: props.exam.id }), { status }, {
    preserveScroll: true,
  })
}

/**
 * Gets the user ID of a participant.
 * @param {any} participant - The participant object.
 * @returns {number | undefined}
 */
const getParticipantUserId = (participant: any) =>
  participant.participant_id ?? participant.user?.id

/**
 * Sets an action for a participant.
 * @param {any} participant - The participant object.
 * @param {'pause' | 'resume' | 'finish'} action - The action to perform.
 */
const setParticipantAction = (participant: any, action: 'pause' | 'resume' | 'finish') => {
  const participantId = getParticipantUserId(participant)
  if (!participantId) return
  router.post(
    route('exams.participants.set-step-status', {
      exam: props.exam.id,
      participant: participantId,
    }),
    { action },
    { preserveScroll: true, preserveState: true },
  )
}

/**
 * Gets the state of the extra time input for a participant.
 * @param {number} participantId - The ID of the participant.
 * @returns {{ expanded: boolean; minutes: string; error: string | null; loading: boolean } | undefined}
 */
const getExtraTimeState = (participantId: number) => extraTimeState.value[participantId]

/**
 * Checks if extra time can be added for a participant.
 * @param {any} participant - The participant object.
 * @returns {boolean}
 */
const canAddExtraTime = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  return !['completed', 'paused', 'broken'].includes(status.status)
}

/**
 * Toggles the extra time input for a participant.
 * @param {any} participant - The participant object.
 */
const toggleExtraTime = (participant: any) => {
  const participantId = getParticipantUserId(participant)
  if (!participantId) return

  const current = getExtraTimeState(participantId) ?? { expanded: false, minutes: '', error: null, loading: false }
  extraTimeState.value = {
    ...extraTimeState.value,
    [participantId]: { ...current, expanded: !current.expanded, error: null },
  }
}

/**
 * Handles input for the extra time minutes.
 * @param {any} participant - The participant object.
 * @param {Event} event - The input event.
 */
const onExtraTimeMinutesInput = (participant: any, event: Event) => {
  const participantId = getParticipantUserId(participant)
  if (!participantId) return

  const value = (event.target as HTMLInputElement).value
  const current = getExtraTimeState(participantId) ?? { expanded: true, minutes: '', error: null, loading: false }

  extraTimeState.value = {
    ...extraTimeState.value,
    [participantId]: {
      ...current,
      minutes: value,
    },
  }
}

/**
 * Submits the extra time for a participant.
 * @param {any} participant - The participant object.
 */
const submitExtraTime = (participant: any) => {
  const participantId = getParticipantUserId(participant)
  const status = getParticipantStatus(participant)

  if (!participantId || !status?.id) return

  const current = getExtraTimeState(participantId) ?? { expanded: true, minutes: '', error: null, loading: false }
  const minutes = parseInt(current.minutes, 10)

  if (Number.isNaN(minutes) || minutes <= 0) {
    extraTimeState.value[participantId] = { ...current, error: 'Bitte geben Sie eine gültige Minutenanzahl ein.' }
    return
  }

  if (minutes > MAX_EXTRA_MINUTES) {
    extraTimeState.value[participantId] = {
      ...current,
      error: `Es können maximal ${MAX_EXTRA_MINUTES} Minuten hinzugefügt werden.`,
    }
    return
  }

  extraTimeState.value[participantId] = { ...current, error: null, loading: true }

  router.post(
    route('exam-step-status.add-time', { status: status.id }),
    { minutes },
    {
      preserveScroll: true,
      onSuccess: () => {
        const updatedStatus = getParticipantStatus(participant)
        if (updatedStatus && typeof updatedStatus.time_remaining === 'number') {
          updatedStatus.time_remaining += minutes * 60
        }

        extraTimeState.value[participantId] = { expanded: false, minutes: '', error: null, loading: false }
      },
      onError: (errors) => {
        const errorMessage = (errors && (errors as Record<string, string>).minutes) || null
        extraTimeState.value[participantId] = {
          ...current,
          loading: false,
          error: errorMessage ?? 'Zeit konnte nicht hinzugefügt werden.',
        }
      },
      onFinish: () => {
        const existing = getExtraTimeState(participantId)
        if (existing) {
          extraTimeState.value[participantId] = { ...existing, loading: false }
        }
      },
    },
  )
}

/**
 * Checks if a participant can be paused.
 * @param {any} participant - The participant object.
 * @returns {boolean}
 */
const canPauseParticipant = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  return !['paused', 'completed', 'broken'].includes(status.status)
}

/**
 * Checks if a participant can be resumed.
 * @param {any} participant - The participant object.
 * @returns {boolean}
 */
const canResumeParticipant = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  return status.status === 'paused'
}

/**
 * Checks if a participant can be force-finished.
 * @param {any} participant - The participant object.
 * @returns {boolean}
 */
const canForceFinishParticipant = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  if (status.status !== 'in_progress') return false
  return !status.force_finish_requested_at
}

</script>

<template>
  <div class="mt-8">
    <div class="flex flex-col gap-3 mb-4">
      <div class="flex items-start justify-between gap-4">
        <h2
          class="text-xl font-bold text-gray-900 dark:text-gray-100 px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-900 mb-1"
        >
          {{ exam.name }}
        </h2>
      </div>
      <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2">
          <Button
            v-if="exam.status !== 'completed'"
            size="sm"
            :class="localExam?.contract_view_enabled
              ? 'bg-red-600 hover:bg-red-700 text-white'
              : 'bg-green-600 hover:bg-green-700 text-white'"
            variant="outline"
            @click="toggleContractVisibility(!localExam?.contract_view_enabled)"
          >
            {{ localExam?.contract_view_enabled ? 'Vertragsansicht sperren' : 'Vertrag freigeben' }}
          </Button>
          <div class="flex items-center gap-2 ml-auto">
            <Button v-if="exam.status === 'not_started'" size="sm" @click="startExam">
              Prüfung starten
            </Button>
            <Button v-if="exam.status === 'in_progress'" size="sm" @click="setStatus('paused')">
              Prüfung pausieren
            </Button>
            <Button v-else-if="exam.status === 'paused'" size="sm" @click="setStatus('in_progress')">
              Prüfung fortsetzen
            </Button>
            <Button v-if="exam.status !== 'completed'" size="sm" @click="setStatus('completed')">
              Prüfung beenden
            </Button>
          </div>
        </div>
        <template v-if="exam.status === 'in_progress'">
          <div class="flex flex-wrap items-center gap-2">
            <Button v-for="step in firstStepRow" :key="step.id" @click="setStep(step.id)"
              :disabled="exam.current_exam_step_id === step.id">
              {{ step.test.name }} starten
            </Button>
          </div>
          <div v-for="(row, rowIndex) in additionalStepRows" :key="`step-row-${rowIndex}`"
            class="flex flex-wrap items-center gap-2">
            <Button v-for="step in row" :key="step.id" @click="setStep(step.id)"
              :disabled="exam.current_exam_step_id === step.id">
              {{ step.test.name }} starten
            </Button>
          </div>
        </template>
      </div>
    </div>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Teilnehmer:in
            </th>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Aktueller Test
            </th>
            <th scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Status
            </th>
          <th scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Verbleibende Zeit
          </th>
          <th scope="col"
            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Aktion
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
              {{ localExam.current_step?.test.name || '–' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              <span v-if="getParticipantStatus(participant)"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                  'bg-green-100 text-green-800': getParticipantStatus(participant)?.status === 'completed',
                  'bg-yellow-100 text-yellow-800': getParticipantStatus(participant)?.status === 'in_progress',
                  'bg-blue-100 text-blue-800': getParticipantStatus(participant)?.status === 'not_started',
                  'bg-purple-100 text-purple-800': getParticipantStatus(participant)?.status === 'paused',
                  'bg-red-100 text-red-800': getParticipantStatus(participant)?.status === 'broken',
                }">
                {{ getParticipantStatus(participant)?.status?.replace('_', ' ') }}
              </span>
              <span v-else
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                –
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              {{ formatTime(getParticipantStatus(participant)?.time_remaining) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
              <div class="flex flex-col items-end gap-2">
                <div class="flex justify-end gap-2">
                  <Button v-if="canPauseParticipant(participant)"
                    variant="secondary" size="sm" @click="setParticipantAction(participant, 'pause')">
                    Pausieren
                  </Button>
                  <Button v-if="canResumeParticipant(participant)"
                    size="sm" @click="setParticipantAction(participant, 'resume')">
                    Fortsetzen
                  </Button>
                  <Button v-if="canForceFinishParticipant(participant)"
                    variant="destructive" size="sm" @click="setParticipantAction(participant, 'finish')">
                    Test beenden
                  </Button>
                </div>
                <div v-if="canAddExtraTime(participant)" class="flex flex-col items-end gap-1">
                  <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="toggleExtraTime(participant)">
                      + Zeit
                    </Button>
                    <div v-if="getExtraTimeState(getParticipantUserId(participant))?.expanded"
                      class="flex items-center gap-2">
                      <input type="number" min="1" :max="MAX_EXTRA_MINUTES"
                        class="w-20 rounded-md border border-slate-300 px-2 py-1 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                        :value="getExtraTimeState(getParticipantUserId(participant))?.minutes ?? ''"
                        @input="(event) => onExtraTimeMinutesInput(participant, event)" />
                      <Button size="sm"
                        :disabled="getExtraTimeState(getParticipantUserId(participant))?.loading"
                        @click="submitExtraTime(participant)">
                        Hinzufügen
                      </Button>
                    </div>
                  </div>
                  <p v-if="getExtraTimeState(getParticipantUserId(participant))?.error"
                    class="text-xs text-red-600 dark:text-red-400">
                    {{ getExtraTimeState(getParticipantUserId(participant))?.error }}
                  </p>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
