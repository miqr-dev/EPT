<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import draggable from 'vuedraggable'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  exam: any
  show: boolean
  tests: any[]
  importedUsers: any[]
  recentUsers: any[]
}>()

const emit = defineEmits(['close', 'save'])

const localSteps = ref<any[]>([])
const localParticipantIds = ref<number[]>([])
const participantSearch = ref('')
const testSearch = ref('')

const isLocked = computed(() => props.exam && props.exam.status !== 'not_started')

watch(() => props.exam, (newExam) => {
  if (newExam && newExam.steps) {
    localSteps.value = [...newExam.steps].sort((a, b) => a.step_order - b.step_order)
  } else {
    localSteps.value = []
  }

  if (newExam && newExam.participants) {
    localParticipantIds.value = newExam.participants.map((p: any) => p.participant_id)
  } else {
    localParticipantIds.value = []
  }

  participantSearch.value = ''
  testSearch.value = ''
}, { immediate: true })

const importedIds = computed(() => new Set(props.importedUsers.map((u: any) => u.id)))
const signedInIds = computed(() => new Set(props.recentUsers.map((u: any) => u.id)))

const selectedParticipants = computed(() => {
  const all = props.importedUsers
  const map = new Map(all.map((u: any) => [u.id, u]))
  return localParticipantIds.value
    .map((id) => map.get(id))
    .filter(Boolean)
})

const availableParticipants = computed(() => {
  const selected = new Set(localParticipantIds.value)
  const q = participantSearch.value.trim().toLowerCase()
  return props.importedUsers.filter((u: any) => {
    if (selected.has(u.id)) return false
    if (!q) return true
    const full = `${u.name ?? ''} ${u.firstname ?? ''} ${u.username ?? ''}`.toLowerCase()
    return full.includes(q)
  })
})

const availableTests = computed(() => {
  const selectedTestIds = new Set(localSteps.value.map((s: any) => s.test_id))
  const q = testSearch.value.trim().toLowerCase()
  return props.tests.filter((t: any) => {
    if (selectedTestIds.has(t.id)) return false
    if (!q) return true
    return `${t.name ?? ''} ${t.code ?? ''}`.toLowerCase().includes(q)
  })
})

function participantColorClass(userId: number) {
  const imported = importedIds.value.has(userId)
  const signed = signedInIds.value.has(userId)

  if (signed && imported) return 'text-emerald-600 dark:text-emerald-300'
  if (signed && !imported) return 'text-blue-600 dark:text-blue-300'
  if (imported && !signed) return 'text-red-600 dark:text-red-300'
  return 'text-gray-700 dark:text-gray-300'
}

function closeModal() {
  emit('close')
}

function removeStep(stepId: number) {
  if (isLocked.value) return
  localSteps.value = localSteps.value.filter((step) => step.id !== stepId)
}

function addTest(test: any) {
  if (isLocked.value) return
  localSteps.value.push({
    id: `new-${test.id}-${Date.now()}`,
    test_id: test.id,
    step_order: localSteps.value.length + 1,
    test,
  })
}

function addParticipant(userId: number) {
  if (isLocked.value) return
  if (!localParticipantIds.value.includes(userId)) {
    localParticipantIds.value.push(userId)
  }
}

function removeParticipant(userId: number) {
  if (isLocked.value) return
  localParticipantIds.value = localParticipantIds.value.filter((id) => id !== userId)
}

function saveChanges() {
  emit('save', {
    steps: localSteps.value,
    participantIds: localParticipantIds.value,
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
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-4xl border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ exam.name }}</h2>
        <button @click="closeModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&times;</button>
      </div>

      <p v-if="isLocked" class="mb-4 rounded border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        Teilnehmer:innen und Testschritte können nur vor Prüfungsstart geändert werden.
      </p>

      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Teilnehmer:innen (zugewiesen)</h3>
          <ul class="space-y-1 text-sm">
            <li v-for="u in selectedParticipants" :key="u.id" class="flex items-center justify-between rounded border border-gray-200 px-2 py-1">
              <span :class="participantColorClass(u.id)">{{ u.name }}, {{ u.firstname }}</span>
              <button v-if="!isLocked" @click="removeParticipant(u.id)" class="text-red-600 font-bold">&times;</button>
            </li>
            <li v-if="!selectedParticipants.length" class="text-gray-400">Keine Teilnehmer:innen zugewiesen.</li>
          </ul>

          <div v-if="!isLocked" class="mt-3">
            <input v-model="participantSearch" type="search" placeholder="Teilnehmer suchen..." class="w-full rounded border border-gray-300 px-2 py-1 text-sm" />
            <ul class="mt-2 max-h-40 overflow-y-auto space-y-1">
              <li v-for="u in availableParticipants" :key="`avail-${u.id}`" class="flex items-center justify-between rounded border border-gray-200 px-2 py-1 text-sm">
                <span>{{ u.name }}, {{ u.firstname }}</span>
                <button @click="addParticipant(u.id)" class="text-blue-600 font-semibold">＋</button>
              </li>
              <li v-if="!availableParticipants.length" class="text-xs text-gray-400">Keine passenden Teilnehmer:innen.</li>
            </ul>
          </div>
        </div>

        <div>
          <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Schritte (zum Neuordnen ziehen)</h3>
          <draggable v-model="localSteps" item-key="id" class="space-y-2" :disabled="isLocked">
            <template #item="{ element }">
              <div class="flex items-center justify-between p-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700 cursor-move text-gray-800 dark:text-gray-200">
                <span>{{ element.test.name }}</span>
                <button v-if="!isLocked" @click="removeStep(element.id)" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
              </div>
            </template>
          </draggable>

          <div v-if="!isLocked" class="mt-3">
            <input v-model="testSearch" type="search" placeholder="Tests suchen..." class="w-full rounded border border-gray-300 px-2 py-1 text-sm" />
            <ul class="mt-2 max-h-40 overflow-y-auto space-y-1">
              <li v-for="test in availableTests" :key="`test-${test.id}`" class="flex items-center justify-between rounded border border-gray-200 px-2 py-1 text-sm">
                <span>{{ test.name }}</span>
                <button @click="addTest(test)" class="text-blue-600 font-semibold">＋</button>
              </li>
              <li v-if="!availableTests.length" class="text-xs text-gray-400">Keine passenden Tests.</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button @click="closeModal" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500">Schließen</button>
        <button v-if="!isLocked" @click="saveChanges" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Änderungen speichern</button>
        <button v-if="exam && exam.status === 'not_started'" @click="startExam" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Prüfung starten
        </button>
      </div>
    </div>
  </div>
</template>
