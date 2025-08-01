<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps<{
  exam: {
    id: number
    name: string
    city?: { name: string }
    status?: string
    participants: Array<{ id: number; name: string; firstname: string }>
    steps: Array<{ id: number; name: string; order: number }>
  },
  availableParticipants: Array<{ id: number; name: string; firstname: string }>
}>()

// For adding participants (later you can upgrade to multi-select or modal)
const newParticipantId = ref('')
function addParticipant() {
  if (!newParticipantId.value) return
  router.post(`/exams/${props.exam.id}/participants`, {
    participant_ids: [newParticipantId.value] // Fix: send as array
  }, {
    onSuccess: () => {
      newParticipantId.value = ''
    }
  })
}
</script>

<template>
  <Head :title="`Prüfung: ${props.exam.name}`" />
  <AppLayout>
    <div class="max-w-5xl mx-auto mt-8 px-4">
      <div class="mb-6 flex items-center gap-3">
        <Link href="/exams" class="text-blue-600 hover:underline">&larr; Zurück</Link>
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
        <div>
          <div class="text-lg font-medium text-gray-700 mb-1">Teilnehmer:innen</div>
          <ul class="list-disc pl-6 space-y-1">
            <li v-for="p in props.exam.participants" :key="p.id" class="text-gray-800">
              {{ p.firstname }} {{ p.name }}
            </li>
            <li v-if="!props.exam.participants.length" class="text-gray-400">Keine Teilnehmer:innen zugewiesen.</li>
          </ul>
          <form @submit.prevent="addParticipant" class="mt-4 pt-4 border-t border-gray-200">
            <label for="participant-select" class="block text-sm font-medium text-gray-600 mb-1">Teilnehmer:in hinzufügen</label>
            <div class="flex items-center gap-2">
              <select
                id="participant-select"
                v-model="newParticipantId"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="" disabled>Bitte auswählen...</option>
                <option v-for="p in props.availableParticipants" :key="p.id" :value="p.id">
                  {{ p.firstname }} {{ p.name }}
                </option>
              </select>
              <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                :disabled="!newParticipantId"
              >
                Hinzufügen
              </button>
            </div>
          </form>
        </div>
        <div>
          <div class="text-lg font-medium text-gray-700 mb-1">Ablauf / Schritte</div>
          <ol class="list-decimal pl-6 space-y-1">
            <li v-for="step in props.exam.steps" :key="step.id">
              {{ step.order }}. {{ step.name }}
            </li>
            <li v-if="!props.exam.steps.length" class="text-gray-400">Keine Schritte definiert.</li>
          </ol>
          <div class="mt-4 flex gap-3">
            <Link :href="`/exams/${props.exam.id}/manage`" class="bg-blue-100 text-blue-700 px-3 py-1 rounded font-semibold hover:bg-blue-200 transition">
              Ablauf/Flow verwalten
            </Link>
            <!-- Other session control buttons could go here -->
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
