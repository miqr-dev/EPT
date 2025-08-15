<script setup lang="ts">
import ExamDetailsModal from '@/components/ExamDetailsModal.vue';
import LiveExamStatusTable from '@/components/LiveExamStatusTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps<{
  participants: any[];
  recentUsers: any[];
  exams: any[];
  tests: any[];
}>();

const activeExams = ref<any[]>([]);
let polling: any = null;

const fetchActiveExams = async () => {
  try {
    const response = await axios.get(route('api.active-exams'));
    activeExams.value = response.data || [];
  } catch (error) {
    console.error("Error fetching active exams:", error);
  }
};

onMounted(() => {
  fetchActiveExams();
  polling = setInterval(fetchActiveExams, 5000);
});

onUnmounted(() => {
  if (polling) {
    clearInterval(polling);
  }
});

const selectedIdx = ref(0);
const selectedIds = ref<number[]>([]);
const selectedRecentUserIds = ref<number[]>([]);

const showCreateExamForm = ref(false);
const newExamTitle = ref('');
const stagedUserIds = ref<number[]>([]);
const newExamSteps = ref<number[]>([]);

const defaultSteps = ['BRT-A', 'MRT-A', 'FPI-R'];

watch(
  () => props.tests,
  (newTests) => {
    nextTick(() => {
      if (newTests.length > 0) {
        const defaultTestIds = newTests.filter((test) => defaultSteps.includes(test.code)).map((test) => test.id);
        newExamSteps.value = defaultTestIds;
      }
    });
  },
  { immediate: true },
);

const showExamDetailsModal = ref(false);
const selectedExam = ref<any>(null);

function openExamDetailsModal(exam: any) {
  selectedExam.value = exam;
  showExamDetailsModal.value = true;
}

function saveExamSteps(steps: any[]) {
  if (!selectedExam.value) return;
  router.put(
    route('exams.updateSteps', selectedExam.value.id),
    {
      steps: steps.map((s) => ({ id: s.id })),
    },
    {
      onSuccess: () => {
        showExamDetailsModal.value = false;
      },
    },
  );
}

const selectedParticipant = computed(() =>
  props.participants && props.participants.length && selectedIds.value.length === 1 ? props.participants[selectedIdx.value] : null,
);

const allSelected = computed(() => props.participants.length > 0 && selectedIds.value.length === props.participants.length);

function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = props.participants.map((p) => p.id);
  }
}
function toggleRow(id: number, idx: number) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id);
  } else {
    selectedIds.value.push(id);
    selectedIdx.value = idx;
  }
}
const availableRecentUsers = computed(() => props.recentUsers.filter((u) => !stagedUserIds.value.includes(u.id)));

function singleRowSelect(idx: number, id: number) {
  selectedIdx.value = idx;
  selectedIds.value = [id];
}

function toggleRecentUser(id: number) {
  if (selectedRecentUserIds.value.includes(id)) {
    selectedRecentUserIds.value = selectedRecentUserIds.value.filter((x) => x !== id);
  } else {
    selectedRecentUserIds.value.push(id);
  }
}

function toggleSelectAllRecent(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.checked) {
    selectedRecentUserIds.value = availableRecentUsers.value.map((u) => u.id);
  } else {
    selectedRecentUserIds.value = [];
  }
}

function addSelectedUsersToStage() {
  stagedUserIds.value = [...new Set([...stagedUserIds.value, ...selectedRecentUserIds.value])];
  selectedRecentUserIds.value = [];
}

function removeStagedUser(userId: number) {
  stagedUserIds.value = stagedUserIds.value.filter((id) => id !== userId);
}

function saveExam() {
  router.post(
    route('exams.storeWithParticipants'),
    {
      title: newExamTitle.value,
      participant_ids: stagedUserIds.value,
      steps: newExamSteps.value,
    },
    {
      onSuccess: () => {
        showCreateExamForm.value = false;
        newExamTitle.value = '';
        stagedUserIds.value = [];
      },
    },
  );
}

const assignedTestsForSelected = computed(() => {
  if (selectedIds.value.length === 0) return [];
  const all = props.participants.filter((p) => selectedIds.value.includes(p.id));
  const testIdSets = all.map((p) => new Set((p.test_assignments || []).map((a: any) => a.test_id)));
  if (testIdSets.length === 0) return [];
  const intersection = [...testIdSets[0]].filter((testId) => testIdSets.every((set) => set.has(testId)));
  return props.tests.filter((t) => intersection.includes(t.id));
});

function removeTest(testId: number) {
  if (!selectedIds.value.length) return;
  router.post(
    '/remove-tests',
    {
      participant_ids: selectedIds.value,
      test_ids: [testId],
    },
    { preserveScroll: true },
  );
}

const showAddTestSelect = ref(false);
const testsToAdd = ref<number[]>([]);
const availableTestsToAdd = computed(() => {
  if (!selectedIds.value.length) return [];
  const assignedIds = assignedTestsForSelected.value.map((t) => t.id);
  return props.tests.filter((t) => !assignedIds.includes(t.id));
});

function addTests() {
  if (!selectedIds.value.length || !testsToAdd.value.length) return;
  router.post(
    '/assign-tests',
    {
      participant_ids: selectedIds.value,
      test_ids: testsToAdd.value,
    },
    { preserveScroll: true },
  );
  testsToAdd.value = [];
  showAddTestSelect.value = false;
}
</script>

<template>

  <Head title="Dashboard" />
  <AppLayout>
    <div
      class="flex min-h-screen flex-col items-center bg-[#f6f7f9] py-4 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
      <!-- Live Exam Status -->
      <div v-for="exam in activeExams" :key="exam.id" class="w-full max-w-7xl">
        <LiveExamStatusTable :exam="exam" />
      </div>
      <!-- New Exam Management Section -->
      <div class="mt-6 flex w-full max-w-7xl flex-col gap-4 md:flex-row">
        <!-- Box 1: Recent Users -->
        <div class="flex flex-1 flex-col">
          <div
            class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center border-b border-gray-100 px-4 py-3 dark:border-gray-700">
              <h2 class="flex-1 text-base font-semibold text-gray-800 dark:text-gray-200">Recent Users (6h)</h2>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ availableRecentUsers.length }}</span>
            </div>
            <div class="flex-1 overflow-auto">
              <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="w-8 px-2 py-2 text-center">
                      <input type="checkbox" @change="toggleSelectAllRecent" class="h-4 w-4 accent-blue-600" />
                    </th>
                    <th class="px-2 py-2 text-left font-medium">Name</th>
                    <th class="px-2 py-2 text-left font-medium">Vorname</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in availableRecentUsers" :key="user.id"
                    class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" @click="toggleRecentUser(user.id)">
                    <td class="px-2 py-1 text-center">
                      <input type="checkbox" :checked="selectedRecentUserIds.includes(user.id)"
                        class="h-4 w-4 accent-blue-600" />
                    </td>
                    <td class="px-2 py-1">{{ user.name }}</td>
                    <td class="px-2 py-1">{{ user.firstname }}</td>
                  </tr>
                  <tr v-if="!availableRecentUsers.length">
                    <td colspan="3" class="py-4 text-center text-gray-400 dark:text-gray-500">No recent users found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Box 2: Create Exam -->
        <div class="flex flex-1 flex-col">
          <div
            class="h-full rounded-xl border border-gray-100 bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 text-base font-semibold text-gray-800 dark:text-gray-200">Create New Exam</h2>
            <div v-if="!showCreateExamForm">
              <button @click="showCreateExamForm = true"
                class="w-full rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                Create New Exam
              </button>
            </div>
            <div v-else>
              <div class="mb-4">
                <label for="exam-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Exam
                  Title</label>
                <input type="text" id="exam-title" v-model="newExamTitle"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                  placeholder="e.g. Final Exam Summer 2025" />
              </div>
              <div class="flex items-center gap-4">
                <button @click="addSelectedUsersToStage" :disabled="!selectedRecentUserIds.length"
                  class="rounded bg-gray-200 px-4 py-2 font-bold text-gray-800 hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                  &rarr;
                </button>
                <div class="h-24 flex-1 overflow-y-auto rounded-md border p-2 dark:border-gray-600">
                  <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">Staged Participants:</p>
                  <ul>
                    <li v-for="userId in stagedUserIds" :key="userId" class="flex items-center justify-between text-sm">
                      <span>
                        {{props.recentUsers.find((u) => u.id === userId)?.name}},
                        {{props.recentUsers.find((u) => u.id === userId)?.firstname}}
                      </span>
                      <button @click="removeStagedUser(userId)" class="text-red-500 hover:text-red-700">&times;</button>
                    </li>
                  </ul>
                  <p v-if="!stagedUserIds.length" class="text-xs text-gray-400 dark:text-gray-500">No users staged.</p>
                </div>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Exam Steps</label>
                <div class="mt-2 grid grid-cols-2 gap-2">
                  <div v-for="test in props.tests" :key="test.id" class="flex items-center">
                    <input type="checkbox" :id="`test-${test.id}`" :value="test.id" v-model="newExamSteps"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <label :for="`test-${test.id}`" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">{{
                      test.name
                      }}</label>
                  </div>
                </div>
              </div>
              <div class="mt-4 flex justify-end gap-2">
                <button @click="showCreateExamForm = false"
                  class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                  Cancel
                </button>
                <button @click="saveExam" :disabled="!newExamTitle || !stagedUserIds.length"
                  class="rounded bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700 disabled:opacity-50">
                  Save Exam
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Box 3: All Exams -->
        <div class="flex flex-1 flex-col">
          <div
            class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center border-b border-gray-100 px-4 py-3 dark:border-gray-700">
              <h2 class="flex-1 text-base font-semibold text-gray-800 dark:text-gray-200">All Exams</h2>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ props.exams.length }}</span>
            </div>
            <div class="flex-1 overflow-auto">
              <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-2 py-2 text-left font-medium">Name</th>
                    <th class="px-2 py-2 text-left font-medium">City</th>
                    <th class="px-2 py-2 text-left font-medium">Status</th>
                    <th class="px-2 py-2"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="exam in props.exams" :key="exam.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-2 py-1">{{ exam.name }}</td>
                    <td class="px-2 py-1">{{ exam.city.name }}</td>
                    <td class="px-2 py-1">{{ exam.status }}</td>
                    <td class="px-2 py-1 text-right">
                      <button @click="openExamDetailsModal(exam)" class="font-semibold text-blue-600 hover:underline">
                        Details
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!props.exams.length">
                    <td colspan="4" class="py-4 text-center text-gray-400 dark:text-gray-500">No exams found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ExamDetailsModal :exam="selectedExam" :show="showExamDetailsModal" @close="showExamDetailsModal = false"
      @save="saveExamSteps" />
  </AppLayout>
</template>
