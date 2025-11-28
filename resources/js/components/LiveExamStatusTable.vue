<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  exam: any
}>()

const MAX_EXTRA_MINUTES = 30

const formatTime = (seconds?: number) => {
  if (typeof seconds !== 'number' || seconds < 0) return '00:00'
  const whole = Math.floor(seconds)
  const minutes = Math.floor(whole / 60)
  const secs = whole % 60
  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

// We need a local copy of the exam to update the timer
const localExam = ref(JSON.parse(JSON.stringify(props.exam)))

const stepRows = computed(() => {
  const steps = Array.isArray(localExam.value?.steps) ? localExam.value.steps : []
  const rows: any[][] = []
  for (let i = 0; i < steps.length; i += 5) {
    rows.push(steps.slice(i, i + 5))
  }
  return rows
})

const firstStepRow = computed(() => stepRows.value[0] ?? [])
const additionalStepRows = computed(() => stepRows.value.slice(1))

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

const toggleContractVisibility = (enabled: boolean) => {
  router.post(
    route('exams.set-contract-visibility', { exam: props.exam.id }),
    { enabled },
    { preserveScroll: true },
  )
}

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

const getParticipantUserId = (participant: any) =>
  participant.participant_id ?? participant.user?.id

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

const getExtraTimeState = (participantId: number) => extraTimeState.value[participantId]

const canAddExtraTime = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  return !['completed', 'paused', 'broken'].includes(status.status)
}

const toggleExtraTime = (participant: any) => {
  const participantId = getParticipantUserId(participant)
  if (!participantId) return

  const current = getExtraTimeState(participantId) ?? { expanded: false, minutes: '', error: null, loading: false }
  extraTimeState.value = {
    ...extraTimeState.value,
    [participantId]: { ...current, expanded: !current.expanded, error: null },
  }
}

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

const canPauseParticipant = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  return !['paused', 'completed', 'broken'].includes(status.status)
}

const canResumeParticipant = (participant: any) => {
  const status = getParticipantStatus(participant)
  if (!status) return false
  if (localExam.value?.status !== 'in_progress') return false
  return status.status === 'paused'
}

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
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ exam.name }}</h2>
      <div class="flex flex-col gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <Button v-if="exam.status === 'not_started'" @click="startExam">
            Prüfung starten
          </Button>
          <Button v-if="exam.status === 'in_progress'" @click="setStatus('paused')">
            Prüfung pausieren
          </Button>
          <Button v-else-if="exam.status === 'paused'" @click="setStatus('in_progress')">
            Prüfung fortsetzen
          </Button>
          <Button v-if="exam.status !== 'completed'" @click="setStatus('completed')">
            Prüfung beenden
          </Button>
          <Button
            v-if="exam.status !== 'completed'"
            :variant="localExam?.contract_view_enabled ? 'destructive' : 'outline'"
            @click="toggleContractVisibility(!localExam?.contract_view_enabled)"
          >
            {{ localExam?.contract_view_enabled ? 'Vertragsansicht sperren' : 'Vertrag freigeben' }}
          </Button>
          <template v-if="exam.status === 'in_progress'">
            <Button v-for="step in firstStepRow" :key="step.id" @click="setStep(step.id)"
              :disabled="exam.current_exam_step_id === step.id">
              {{ step.test.name }} starten
            </Button>
          </template>
        </div>
        <template v-if="exam.status === 'in_progress'">
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
