<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import ExamDetailsModal from '@/components/ExamDetailsModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps<{
  participants: any[]
  recentUsers: any[]
  exams: any[]
  tests: any[]
}>()

const selectedIdx = ref(0)
const selectedIds = ref<number[]>([])
const selectedRecentUserIds = ref<number[]>([])

const showCreateExamForm = ref(false)
const newExamTitle = ref('')
const stagedUserIds = ref<number[]>([])

const showExamDetailsModal = ref(false)
const selectedExam = ref<any>(null)

function openExamDetailsModal(exam: any) {
  selectedExam.value = exam
  showExamDetailsModal.value = true
}

function saveExamSteps(steps: any[]) {
  if (!selectedExam.value) return
  router.put(route('exams.updateSteps', selectedExam.value.id), {
    steps: steps.map(s => ({ id: s.id }))
  }, {
    onSuccess: () => {
      showExamDetailsModal.value = false
    }
  })
}

const selectedParticipant = computed(() =>
  props.participants && props.participants.length && selectedIds.value.length === 1
    ? props.participants[selectedIdx.value]
    : null
)

const allSelected = computed(() =>
  props.participants.length > 0 && selectedIds.value.length === props.participants.length
)

function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = props.participants.map(p => p.id)
  }
}
function toggleRow(id: number, idx: number) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter(x => x !== id)
  } else {
    selectedIds.value.push(id)
    selectedIdx.value = idx
  }
}
function singleRowSelect(idx: number, id: number) {
  selectedIdx.value = idx
  selectedIds.value = [id]
}

function toggleRecentUser(id: number) {
  if (selectedRecentUserIds.value.includes(id)) {
    selectedRecentUserIds.value = selectedRecentUserIds.value.filter(x => x !== id)
  } else {
    selectedRecentUserIds.value.push(id)
  }
}

function toggleSelectAllRecent(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.checked) {
    selectedRecentUserIds.value = props.recentUsers.map(u => u.id)
  } else {
    selectedRecentUserIds.value = []
  }
}

function addSelectedUsersToStage() {
  stagedUserIds.value = [...new Set([...stagedUserIds.value, ...selectedRecentUserIds.value])]
  selectedRecentUserIds.value = []
}

function saveExam() {
  router.post(route('exams.storeWithParticipants'), {
    title: newExamTitle.value,
    participant_ids: stagedUserIds.value,
  }, {
    onSuccess: () => {
      showCreateExamForm.value = false
      newExamTitle.value = ''
      stagedUserIds.value = []
    }
  })
}

const assignedTestsForSelected = computed(() => {
  if (selectedIds.value.length === 0) return []
  const all = props.participants.filter(p => selectedIds.value.includes(p.id))
  const testIdSets = all.map(p =>
    new Set((p.test_assignments || []).map((a: any) => a.test_id))
  )
  if (testIdSets.length === 0) return []
  const intersection = [...testIdSets[0]].filter(testId =>
    testIdSets.every(set => set.has(testId))
  )
  return props.tests.filter(t => intersection.includes(t.id))
})

function removeTest(testId: number) {
  if (!selectedIds.value.length) return
  router.post('/remove-tests', {
    participant_ids: selectedIds.value,
    test_ids: [testId],
  }, { preserveScroll: true })
}

const showAddTestSelect = ref(false)
const testsToAdd = ref<number[]>([])
const availableTestsToAdd = computed(() => {
  if (!selectedIds.value.length) return []
  const assignedIds = assignedTestsForSelected.value.map(t => t.id)
  return props.tests.filter(t => !assignedIds.includes(t.id))
})

function addTests() {
  if (!selectedIds.value.length || !testsToAdd.value.length) return
  router.post('/assign-tests', {
    participant_ids: selectedIds.value,
    test_ids: testsToAdd.value,
  }, { preserveScroll: true })
  testsToAdd.value = []
  showAddTestSelect.value = false
}
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="min-h-screen bg-[#f6f7f9] flex flex-col items-center py-4">
      <div class="w-full flex flex-col md:flex-row gap-4 max-w-7xl">
        <!-- Participants Table -->
        <div class="flex-1 flex flex-col">
          <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden flex flex-col h-full">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center">
              <h2 class="text-base font-semibold flex-1 text-gray-800">Teilnehmer:innen</h2>
              <span class="text-xs text-gray-500">{{ props.participants.length }}</span>
            </div>
            <div class="flex-1 overflow-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                  <tr>
                    <th class="w-8 px-2 py-2 text-center">
                      <input type="checkbox" :checked="allSelected" @change="toggleSelectAll"
                        class="accent-blue-600 w-4 h-4" title="Alle auswählen" />
                    </th>
                    <th class="px-2 py-2 font-medium text-left">Name</th>
                    <th class="px-2 py-2 font-medium text-left">Vorname</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(p, idx) in props.participants" :key="p.id" :class="[
                    selectedIds.includes(p.id) ? 'bg-blue-50 font-semibold ring-2 ring-blue-300' : '',
                    'hover:bg-gray-100 cursor-pointer transition'
                  ]" @click="singleRowSelect(idx, p.id)">
                    <td class="px-2 py-1 text-center">
                      <input type="checkbox" :checked="selectedIds.includes(p.id)" @click.stop="toggleRow(p.id, idx)"
                        class="accent-blue-600 w-4 h-4" />
                    </td>
                    <td class="px-2 py-1">{{ p.name }}</td>
                    <td class="px-2 py-1">{{ p.firstname }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Assigned Tests Panel -->
        <div class="flex-1 flex flex-col">
          <div
            class="bg-white rounded-xl shadow border border-gray-100 px-6 py-5 flex flex-col h-full min-h-[340px] relative">
            <div class="flex items-center mb-3 border-b border-gray-100 pb-2">
              <h2 class="text-base font-semibold flex-1 text-gray-800 truncate">
                <template v-if="selectedIds.length === 1 && selectedParticipant">
                  Tests für {{ selectedParticipant.firstname }} {{ selectedParticipant.name }}
                </template>
                <template v-else-if="selectedIds.length > 1">
                  Tests für <b>{{ selectedIds.length }}</b> Teilnehmer:innen
                </template>
                <template v-else>
                  Bitte Teilnehmer:in auswählen
                </template>
              </h2>
              <button v-if="selectedIds.length && availableTestsToAdd.length"
                @click="showAddTestSelect = !showAddTestSelect"
                class="ml-auto px-3 py-0.5 rounded-full bg-green-600 text-white font-bold text-lg shadow hover:bg-green-700 transition absolute right-6"
                style="top: 16px" title="Test hinzufügen">
                +
              </button>
            </div>
            <div v-if="showAddTestSelect" class="flex flex-row items-center gap-2 mb-4 justify-end">
              <div
                class="rounded-lg border border-gray-200 bg-gray-50 shadow-sm px-3 py-2 max-h-36 overflow-y-auto min-w-[140px]">
                <div v-for="t in availableTestsToAdd" :key="t.id" class="flex items-center gap-2 py-0.5">
                  <input type="checkbox" class="accent-blue-600 w-4 h-4" :id="'add_test_' + t.id" :value="t.id"
                    v-model="testsToAdd" />
                  <label :for="'add_test_' + t.id" class="text-gray-800 cursor-pointer select-none text-sm">
                    {{ t.name }}
                  </label>
                </div>
                <div v-if="!availableTestsToAdd.length" class="text-gray-400 px-1 py-1 text-xs">
                  Keine weiteren Tests verfügbar.
                </div>
              </div>
              <button
                class="bg-blue-600 text-white px-3 py-1 rounded-lg shadow-sm font-medium hover:bg-blue-700 transition text-sm"
                :disabled="!testsToAdd.length" @click="addTests">
                Hinzufügen
              </button>
              <button class="text-gray-500 hover:text-black px-2 py-1 font-medium rounded-lg transition text-sm"
                @click="showAddTestSelect = false">
                Abbrechen
              </button>
            </div>

            <ul class="pl-0 mt-1 space-y-2 flex-1 overflow-y-auto">
              <li v-for="t in assignedTestsForSelected" :key="t.id"
                class="flex items-center gap-3 group px-2 py-1 rounded hover:bg-gray-50 border border-gray-100 last:border-b-0">
                <span class="font-medium text-gray-800 text-sm">{{ t.name }}</span>
                <button @click="removeTest(t.id)"
                  class="opacity-70 group-hover:opacity-100 transition text-gray-400 hover:text-gray-600 p-0.5 ml-auto"
                  title="Test entfernen">
                  <!-- Gray trash bin -->
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.4" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 10v10a2 2 0 002 2h8a2 2 0 002-2V10M19 4h-1.5A2.5 2.5 0 0015 1.5h-6A2.5 2.5 0 006.5 4H5m0 0v2h14V4m-3 0V3a2 2 0 00-2-2h-2a2 2 0 00-2 2v1" />
                  </svg>
                </button>
              </li>
              <li v-if="!assignedTestsForSelected.length" class="text-gray-400 pt-5 pl-2 text-sm">
                Keine Tests zugewiesen.
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- New Exam Management Section -->
      <div class="w-full flex flex-col md:flex-row gap-4 max-w-7xl mt-6">
        <!-- Box 1: Recent Users -->
        <div class="flex-1 flex flex-col">
          <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden flex flex-col h-full">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center">
              <h2 class="text-base font-semibold flex-1 text-gray-800">Recent Users (6h)</h2>
              <span class="text-xs text-gray-500">{{ props.recentUsers.length }}</span>
            </div>
            <div class="flex-1 overflow-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                  <tr>
                    <th class="w-8 px-2 py-2 text-center">
                      <input type="checkbox" @change="toggleSelectAllRecent" class="accent-blue-600 w-4 h-4" />
                    </th>
                    <th class="px-2 py-2 font-medium text-left">Name</th>
                    <th class="px-2 py-2 font-medium text-left">Vorname</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in props.recentUsers" :key="user.id" class="hover:bg-gray-50 cursor-pointer"
                    @click="toggleRecentUser(user.id)">
                    <td class="px-2 py-1 text-center">
                      <input type="checkbox" :checked="selectedRecentUserIds.includes(user.id)"
                        class="accent-blue-600 w-4 h-4" />
                    </td>
                    <td class="px-2 py-1">{{ user.name }}</td>
                    <td class="px-2 py-1">{{ user.firstname }}</td>
                  </tr>
                  <tr v-if="!props.recentUsers.length">
                    <td colspan="3" class="text-center py-4 text-gray-400">No recent users found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Box 2: Create Exam -->
        <div class="flex-1 flex flex-col">
          <div class="bg-white rounded-xl shadow border border-gray-100 p-4 h-full">
            <h2 class="text-base font-semibold text-gray-800 mb-3">Create New Exam</h2>
            <div v-if="!showCreateExamForm">
              <button @click="showCreateExamForm = true"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700">
                Create New Exam
              </button>
            </div>
            <div v-else>
              <div class="mb-4">
                <label for="exam-title" class="block text-sm font-medium text-gray-700">Exam Title</label>
                <input type="text" id="exam-title" v-model="newExamTitle"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="e.g. Final Exam Summer 2025" />
              </div>
              <div class="flex items-center gap-4">
                <button @click="addSelectedUsersToStage" :disabled="!selectedRecentUserIds.length"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded disabled:opacity-50">
                  &rarr;
                </button>
                <div class="flex-1 border rounded-md p-2 h-24 overflow-y-auto">
                  <p class="text-sm text-gray-500 mb-2">Staged Participants:</p>
                  <ul>
                    <li v-for="userId in stagedUserIds" :key="userId" class="text-sm">
                      {{ props.recentUsers.find(u => u.id === userId)?.name }},
                      {{ props.recentUsers.find(u => u.id === userId)?.firstname }}
                    </li>
                  </ul>
                  <p v-if="!stagedUserIds.length" class="text-xs text-gray-400">No users staged.</p>
                </div>
              </div>
              <div class="mt-4 flex justify-end gap-2">
                <button @click="showCreateExamForm = false" class="text-gray-600 hover:text-gray-900">Cancel</button>
                <button @click="saveExam" :disabled="!newExamTitle || !stagedUserIds.length"
                  class="bg-green-600 text-white px-4 py-2 rounded font-semibold hover:bg-green-700 disabled:opacity-50">
                  Save Exam
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Box 3: All Exams -->
        <div class="flex-1 flex flex-col">
          <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden flex flex-col h-full">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center">
              <h2 class="text-base font-semibold flex-1 text-gray-800">All Exams</h2>
              <span class="text-xs text-gray-500">{{ props.exams.length }}</span>
            </div>
            <div class="flex-1 overflow-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                  <tr>
                    <th class="px-2 py-2 font-medium text-left">Name</th>
                    <th class="px-2 py-2 font-medium text-left">City</th>
                    <th class="px-2 py-2 font-medium text-left">Status</th>
                    <th class="px-2 py-2"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="exam in props.exams" :key="exam.id" class="hover:bg-gray-50">
                    <td class="px-2 py-1">{{ exam.name }}</td>
                    <td class="px-2 py-1">{{ exam.city.name }}</td>
                    <td class="px-2 py-1">{{ exam.status }}</td>
                    <td class="px-2 py-1 text-right">
                      <button @click="openExamDetailsModal(exam)" class="text-blue-600 font-semibold hover:underline">Details</button>
                    </td>
                  </tr>
                  <tr v-if="!props.exams.length">
                    <td colspan="4" class="text-center py-4 text-gray-400">No exams found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ExamDetailsModal :exam="selectedExam" :show="showExamDetailsModal" @close="showExamDetailsModal = false" @save="saveExamSteps" />
  </AppLayout>
</template>
