<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  exam: any
  availableParticipants: Array<any>
}>()

const emit = defineEmits(['back'])

const newParticipantId = ref(null)

function addParticipant() {
  if (!newParticipantId.value) return
  router.post(`/exams/${props.exam.id}/participants`, {
    participant_ids: [newParticipantId.value]
  }, {
    onSuccess: () => {
      newParticipantId.value = null
    }
  })
}

function addExtraTime(statusId) {
  const minutes = prompt('Wie viele Minuten extra?', '5')
  if (minutes) {
    router.post(`/exam-step-status/${statusId}/add-time`, { minutes: parseInt(minutes) })
  }
}

let polling = null
onMounted(() => {
  polling = setInterval(() => {
    router.reload({ only: ['exam'] })
  }, 5000)
})

onUnmounted(() => {
  if (polling) {
    clearInterval(polling)
  }
})
</script>

<template>
  <div>
    <div class="mb-6 flex items-center gap-3">
      <button @click="emit('back')" class="text-blue-600 hover:underline">&larr; Back to List</button>
      <h1 class="text-2xl font-bold text-gray-800 flex-1">{{ props.exam.name }}</h1>
    </div>
    <div class="bg-white shadow rounded-xl border border-gray-100 p-6 flex flex-col gap-6">
      <div>
        <div class="text-lg font-medium text-gray-700 mb-1">Allgemeine Infos</div>
        <div class="grid grid-cols-2 gap-x-10">
          <div>
            <span class="text-gray-500">Stadt:</span>
            <span class="ml-2 text-gray-800 font-semibold">{{ props.exam.city?.name ?? '-' }}</span>
          </div>
          <div>
            <span class="text-gray-500">Status:</span>
            <span class="ml-2 text-gray-800 font-semibold capitalize">{{ props.exam.status ?? 'Neu' }}</span>
          </div>
        </div>
      </div>
      <div v-if="exam.current_step" class="mt-6">
        <h3 class="text-lg font-medium text-gray-700 mb-2">
          Aktueller Schritt: {{ exam.current_step.test.name }} (Status)
        </h3>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Teilnehmer</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aktionen</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="p in exam.participants" :key="p.id">
                <td class="whitespace-nowrap px-6 py-4">{{ p.user.firstname }} {{ p.user.name }}</td>
                <td class="whitespace-nowrap px-6 py-4">
                  <span :class="{
                    'bg-gray-200 text-gray-800': !p.stepStatuses[0]?.status,
                    'bg-blue-100 text-blue-800': p.stepStatuses[0]?.status === 'in_progress',
                    'bg-green-100 text-green-800': p.stepStatuses[0]?.status === 'completed',
                  }" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                    {{ p.stepStatuses[0]?.status || 'nicht gestartet' }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                  <button v-if="p.stepStatuses[0]" @click="addExtraTime(p.stepStatuses[0].id)" class="text-blue-600 hover:underline">
                    Zeit gutschreiben
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div>
        <div class="text-lg font-medium text-gray-700 mb-1">Teilnehmer:innen</div>
        <ul class="list-disc pl-6 space-y-1">
          <li v-for="p in props.exam.participants" :key="p.id" class="text-gray-800">
            {{ p.user.firstname }} {{ p.user.name }}
          </li>
          <li v-if="!props.exam.participants.length" class="text-gray-400">Keine Teilnehmer:innen zugewiesen.</li>
        </ul>
        <form @submit.prevent="addParticipant" class="mt-4 pt-4 border-t border-gray-200">
          <label for="participant-select" class="block text-sm font-medium text-gray-600 mb-1">Teilnehmer:in hinzufügen</label>
          <div class="flex items-center gap-2">
            <select id="participant-select" v-model="newParticipantId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
              <option value="" disabled>Bitte auswählen...</option>
              <option v-for="p in props.availableParticipants" :key="p.id" :value="p.id">
                {{ p.firstname }} {{ p.name }}
              </option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition disabled:opacity-50" :disabled="!newParticipantId">
              Hinzufügen
            </button>
          </div>
        </form>
      </div>
      <div>
        <div class="text-lg font-medium text-gray-700 mb-1">Ablauf / Schritte</div>
        <ol class="list-decimal pl-6 space-y-1">
          <li v-for="step in props.exam.steps" :key="step.id">
            {{ step.step_order }}. {{ step.test.name }}
          </li>
          <li v-if="!props.exam.steps.length" class="text-gray-400">Keine Schritte definiert.</li>
        </ol>
        <div class="mt-4 flex gap-3">
          <button v-if="props.exam.status === 'not_started'" @click="router.post(`/exams/${props.exam.id}/start`)" class="bg-green-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-700 transition">
            Prüfung starten
          </button>
          <button v-if="props.exam.status === 'in_progress' || props.exam.status === 'paused'" @click="router.post(`/exams/${props.exam.id}/next-step`)" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-indigo-700 transition">
            Nächster Schritt
          </button>
          <button v-if="props.exam.status === 'in_progress'" @click="router.post(`/exams/${props.exam.id}/set-status`, { status: 'paused' })" class="bg-yellow-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-yellow-600 transition">
            Pausieren
          </button>
          <button v-if="props.exam.status === 'paused'" @click="router.post(`/exams/${props.exam.id}/set-status`, { status: 'in_progress' })" class="bg-green-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-600 transition">
            Fortsetzen
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
