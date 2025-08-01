<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ExamCreateForm from '@/components/Exams/ExamCreateForm.vue'

const props = defineProps<{
  exams: Array<any>
  cities: Array<any>
  teachers: Array<any>
  tests: Array<any>
  participants: Array<any>
}>()

const selectedExam = ref<any>(null)

const availableParticipants = computed(() => {
    if (!selectedExam.value) {
        return props.participants;
    }
    const existingParticipantIds = selectedExam.value.participants.map(p => p.participant_id);
    return props.participants.filter(p => p.city_id === selectedExam.value.city_id && !existingParticipantIds.includes(p.id));
});

function viewExam(exam: any) {
  selectedExam.value = exam
}

function addParticipants() {
    const selectedParticipantIds = props.participants
        .filter(p => p.selected)
        .map(p => p.id);

    if (selectedExam.value && selectedParticipantIds.length > 0) {
        router.post(`/exams/${selectedExam.value.id}/participants`, {
            participant_ids: selectedParticipantIds,
        }, {
            onSuccess: () => {
                props.participants.forEach(p => p.selected = false);
                selectedExam.value = null;
            }
        });
    }
}

function addExtraTime(statusId) {
  const minutes = prompt('Wie viele Minuten extra?', '5')
  if (minutes) {
    router.post(`/exam-step-status/${statusId}/add-time`, { minutes: parseInt(minutes) })
  }
}

let polling = null
import { onMounted, onUnmounted } from 'vue'
onMounted(() => {
  polling = setInterval(() => {
    if (selectedExam.value) {
        router.reload({ only: ['exams'] })
    }
  }, 5000)
})

onUnmounted(() => {
  if (polling) {
    clearInterval(polling)
  }
})
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Column 1: Recent Participants -->
        <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
          <h2 class="text-xl font-bold mb-4">Available Participants</h2>
          <table class="w-full text-sm">
            <thead>
              <tr>
                <th class="px-2 py-2 font-medium text-left">Name</th>
                <th class="px-2 py-2 font-medium text-left">City</th>
                <th class="px-2 py-2 font-medium text-left"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in availableParticipants" :key="p.id">
                <td class="px-2 py-1">{{ p.name }}</td>
                <td class="px-2 py-1">{{ p.city.name }}</td>
                <td class="px-2 py-1">
                  <input type="checkbox" v-model="p.selected" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Column 2: All Exams -->
        <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
          <h2 class="text-xl font-bold mb-4">Exams</h2>
          <table class="w-full text-sm">
            <thead>
              <tr>
                <th class="px-2 py-2 font-medium text-left">Name</th>
                <th class="px-2 py-2 font-medium text-left">Status</th>
                <th class="px-2 py-2 font-medium text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exam in exams" :key="exam.id" :class="{ 'bg-blue-100': selectedExam && selectedExam.id === exam.id }">
                <td class="px-2 py-1">{{ exam.name }}</td>
                <td class="px-2 py-1">{{ exam.status }}</td>
                <td class="px-2 py-1">
                  <button @click="viewExam(exam)" class="text-blue-600 hover:underline">
                    Select
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="selectedExam" class="mt-4">
            <h3 class="text-lg font-bold">Selected Exam: {{ selectedExam.name }}</h3>
            <div class="mt-2">
              <h4 class="font-bold">Current Participants:</h4>
              <ul>
                <li v-for="p in selectedExam.participants" :key="p.id">
                  {{ p.user.name }}
                </li>
              </ul>
            </div>
            <button @click="addParticipants" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition" :disabled="!participants.some(p => p.selected)">
              Add Selected Participants
            </button>
            <div v-if="selectedExam" class="mt-6">
              <div class="text-lg font-medium text-gray-700 mb-1">Ablauf / Schritte</div>
              <ol class="list-decimal pl-6 space-y-1">
                <li v-for="step in selectedExam.steps" :key="step.id">
                  {{ step.step_order }}. {{ step.test.name }}
                </li>
                <li v-if="!selectedExam.steps.length" class="text-gray-400">Keine Schritte definiert.</li>
              </ol>
              <div class="mt-4 flex gap-3">
                <button v-if="selectedExam.status === 'not_started'" @click="router.post(`/exams/${selectedExam.id}/start`)" class="bg-green-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-700 transition">
                  Prüfung starten
                </button>
                <button v-if="selectedExam.status === 'in_progress' || selectedExam.status === 'paused'" @click="router.post(`/exams/${selectedExam.id}/next-step`)" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-indigo-700 transition">
                  Nächster Schritt
                </button>
                <button v-if="selectedExam.status === 'in_progress'" @click="router.post(`/exams/${selectedExam.id}/set-status`, { status: 'paused' })" class="bg-yellow-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-yellow-600 transition">
                  Pausieren
                </button>
                <button v-if="selectedExam.status === 'paused'" @click="router.post(`/exams/${selectedExam.id}/set-status`, { status: 'in_progress' })" class="bg-green-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-600 transition">
                  Fortsetzen
                </button>
              </div>
            </div>
            <div v-if="selectedExam && selectedExam.currentStep" class="mt-6">
              <h3 class="text-lg font-medium text-gray-700 mb-2">
                Aktueller Schritt: {{ selectedExam.currentStep.test.name }} (Status)
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
                    <tr v-for="p in selectedExam.participants" :key="p.id">
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
          </div>
        </div>

        <!-- Column 3: Create Exam -->
        <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
          <ExamCreateForm :cities="cities" :teachers="teachers" :tests="tests" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
