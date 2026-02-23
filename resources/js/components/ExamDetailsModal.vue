<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import draggable from 'vuedraggable'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  exam: any
  show: boolean
  participants: any[]
  tests: any[]
  recentUsers: any[]
}>()

const emit = defineEmits(['close', 'save'])

const localSteps = ref<any[]>([])
const localParticipants = ref<any[]>([])
const participantSearch = ref('')
const testSearch = ref('')

const signedInUserIds = computed(() => new Set(props.recentUsers.map((u) => u.id)));

watch(() => props.exam, (newExam) => {
  if (newExam) {
    localSteps.value = newExam.steps ? [...newExam.steps].sort((a, b) => a.step_order - b.step_order) : []
    localParticipants.value = newExam.participants ? newExam.participants.map((p: any) => p.user) : []
  } else {
    localSteps.value = []
    localParticipants.value = []
  }
}, { immediate: true })

const canEdit = computed(() => props.exam && props.exam.status === 'not_started')

function closeModal() {
  emit('close')
}

function removeStep(index: number) {
  if (!canEdit.value) return
  localSteps.value.splice(index, 1)
}

function removeParticipant(userId: number) {
  if (!canEdit.value) return
  localParticipants.value = localParticipants.value.filter(p => p.id !== userId)
}

function saveChanges() {
  if (!canEdit.value) return
  emit('save', {
    steps: localSteps.value.map(s => s.test || s),
    participants: localParticipants.value
  })
}

function startExam() {
  if (!props.exam) return
  router.post(`/exams/${props.exam.id}/start`, {}, {
    onSuccess: () => {
      closeModal()
    }
  })
}

const filteredParticipants = computed(() => {
  if (!participantSearch.value) return []
  const search = participantSearch.value.toLowerCase()
  const existingIds = new Set(localParticipants.value.map(p => p.id))
  return props.participants.filter(p =>
    !existingIds.has(p.id) &&
    (p.name.toLowerCase().includes(search) || p.firstname.toLowerCase().includes(search) || p.username.toLowerCase().includes(search))
  ).slice(0, 5)
})

const filteredTests = computed(() => {
  if (!testSearch.value) return []
  const search = testSearch.value.toLowerCase()
  return props.tests.filter(t =>
    t.name.toLowerCase().includes(search)
  ).slice(0, 5)
})

function addParticipant(user: any) {
  localParticipants.value.push(user)
  participantSearch.value = ''
}

function addTest(test: any) {
  localSteps.value.push({ test: test, id: Math.random() })
  testSearch.value = ''
}

function getUserColorClass(user: any) {
  const isSignedIn = signedInUserIds.value.has(user.id);
  const isImported = !!user.id;

  if (isSignedIn && isImported) return 'text-green-600 dark:text-green-400 font-bold';
  if (isSignedIn && !isImported) return 'text-blue-600 dark:text-blue-400 font-bold';
  if (isImported && !isSignedIn) return 'text-red-600 dark:text-red-400 font-bold';
  return '';
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-2xl border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4 text-gray-800 dark:text-gray-100">
        <h2 class="text-xl font-bold">{{ exam.name }}</h2>
        <button @click="closeModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 text-2xl">&times;</button>
      </div>

      <!-- Participants Section -->
      <div class="mb-6">
        <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Teilnehmer:innen</h3>

        <div v-if="canEdit" class="relative mb-3">
          <input v-model="participantSearch" type="text" placeholder="Teilnehmer suchen..."
            class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          <div v-if="filteredParticipants.length" class="absolute z-10 w-full bg-white dark:bg-gray-700 border rounded shadow-lg mt-1">
            <div v-for="p in filteredParticipants" :key="p.id" @click="addParticipant(p)"
              class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm dark:text-white">
              {{ p.name }}, {{ p.firstname }}
            </div>
          </div>
        </div>

        <ul class="space-y-1">
          <li v-for="p in localParticipants" :key="p.id" class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 px-3 py-1.5 rounded border dark:border-gray-600">
            <span :class="getUserColorClass(p)">{{ p.name }}, {{ p.firstname }}</span>
            <button v-if="canEdit" @click="removeParticipant(p.id)" class="text-red-500 hover:text-red-700 font-bold px-2">&times;</button>
          </li>
        </ul>
        <p v-if="!localParticipants.length" class="text-gray-500 text-sm italic">Keine Teilnehmer zugeordnet.</p>
      </div>

      <!-- Steps Section -->
      <div class="mb-6">
        <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Prüfungsschritte (zum Neuordnen ziehen)</h3>

        <div v-if="canEdit" class="relative mb-3">
          <input v-model="testSearch" type="text" placeholder="Test hinzufügen..."
            class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          <div v-if="filteredTests.length" class="absolute z-10 w-full bg-white dark:bg-gray-700 border rounded shadow-lg mt-1">
            <div v-for="t in filteredTests" :key="t.id" @click="addTest(t)"
              class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm dark:text-white">
              {{ t.name }}
            </div>
          </div>
        </div>

        <draggable v-model="localSteps" item-key="id" class="space-y-2" :disabled="!canEdit">
          <template #item="{ element, index }">
            <div class="flex items-center justify-between p-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700"
              :class="canEdit ? 'cursor-move' : ''">
              <span class="text-gray-800 dark:text-gray-200">{{ element.test ? element.test.name : element.name }}</span>
              <button v-if="canEdit" @click="removeStep(index)" class="text-red-500 hover:text-red-700 font-bold px-2">&times;</button>
            </div>
          </template>
        </draggable>
        <p v-if="!localSteps.length" class="text-gray-500 text-sm italic">Keine Schritte definiert.</p>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex flex-wrap justify-end gap-2">
        <button @click="closeModal" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition text-sm font-medium">
          Schließen
        </button>
        <button v-if="canEdit" @click="saveChanges" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-medium">
          Änderungen speichern
        </button>
        <button v-if="exam && exam.status === 'not_started'" @click="startExam" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-sm font-medium">
          Prüfung starten
        </button>
      </div>
    </div>
  </div>
</template>
