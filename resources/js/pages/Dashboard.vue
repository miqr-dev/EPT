<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' }
]

const props = defineProps<{
  participants: any[]
  tests: any[]
}>()

// Selected participant index
const selectedIdx = ref(0)
const selectedParticipant = computed(() =>
  props.participants && props.participants.length
    ? props.participants[selectedIdx.value]
    : null
)

// --------------- Remove a test ---------------
function removeTest(testId: number) {
  if (!selectedParticipant.value) return
  router.post('/remove-tests', {
    participant_ids: [selectedParticipant.value.id],
    test_ids: [testId],
  }, { preserveScroll: true })
}

// --------------- Add test selection ---------------
const showAddTestSelect = ref(false)
const testToAdd = ref<number | null>(null)
const availableTestsToAdd = computed(() => {
  if (!selectedParticipant.value) return []
  const assignedIds = selectedParticipant.value.test_assignments.map((a: any) => a.test_id)
  return props.tests.filter(t => !assignedIds.includes(t.id))
})

function addTest() {
  if (!selectedParticipant.value || !testToAdd.value) return
  router.post('/assign-tests', {
    participant_ids: [selectedParticipant.value.id],
    test_ids: [testToAdd.value],
  }, { preserveScroll: true })
  testToAdd.value = null
  showAddTestSelect.value = false
}
</script>


<template>

  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 flex-row gap-8 p-6">
      <!-- Left: Participants Table -->
      <div class="w-[400px] min-w-[320px]">
        <h2 class="font-semibold mb-2">Teilnehmer:innen in deiner Stadt</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm border rounded shadow bg-white">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-2 py-1">Name</th>
                <th class="px-2 py-1">Vorname</th>
                <th class="px-2 py-1">E-Mail</th>
                <th class="px-2 py-1">Stadt</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, idx) in props.participants" :key="p.id"
                :class="idx === selectedIdx ? 'bg-blue-100 font-bold cursor-pointer' : 'hover:bg-blue-50 cursor-pointer'"
                @click="selectedIdx = idx">
                <td>{{ p.name }}</td>
                <td>{{ p.firstname }}</td>
                <td>{{ p.email }}</td>
                <td>{{ p.city?.name }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right: Assigned Tests List -->
      <div class="flex-1 max-w-xl">
        <div v-if="selectedParticipant">
          <h2 class="font-semibold mb-2">
            Tests für {{ selectedParticipant.firstname }} {{ selectedParticipant.name }}
            <button v-if="availableTestsToAdd.length" @click="showAddTestSelect = !showAddTestSelect"
              class="ml-2 px-2 py-1 rounded bg-green-600 text-white font-bold text-lg" title="Test hinzufügen">
              +
            </button>
          </h2>

          <!-- Add test select -->
          <div v-if="showAddTestSelect" class="flex flex-row items-center gap-2 mb-2">
            <select v-model="testToAdd" class="border rounded px-2 py-1">
              <option :value="null" disabled>Wähle einen Test</option>
              <option v-for="t in availableTestsToAdd" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
            <button class="bg-blue-600 text-white px-3 py-1 rounded" :disabled="!testToAdd" @click="addTest">
              Hinzufügen
            </button>
            <button class="text-gray-500 hover:text-black" @click="showAddTestSelect = false">
              Abbrechen
            </button>
          </div>

          <ul class="pl-4 mt-2 list-disc">
            <li v-for="assign in selectedParticipant.test_assignments" :key="assign.id" class="flex items-center gap-2">
              {{ assign.test?.name || assign.test_id }}
              <span class="text-xs text-gray-600">({{ assign.status }})</span>
              <button @click="removeTest(assign.test_id)" class="ml-2 text-red-600 hover:text-red-800"
                title="Test entfernen">
                🗑
              </button>
            </li>
            <li v-if="!selectedParticipant.test_assignments.length" class="text-gray-500">
              Keine Tests zugewiesen.
            </li>
          </ul>
        </div>
        <div v-else class="text-gray-500 mt-8">Bitte einen Teilnehmer auswählen.</div>
      </div>
    </div>
  </AppLayout>
</template>
