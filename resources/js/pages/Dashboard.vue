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

  <Head title="Übersicht" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 py-6 dark:bg-slate-900">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 lg:px-6">

        <!-- Page header -->
        <header
          class="flex flex-col gap-3 border-b border-slate-200 pb-4 dark:border-slate-700 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
              Prüfungsübersicht
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Verwalten Sie aktuelle Benutzer, neue Prüfungen und laufende Prüfungsdurchläufe.
            </p>
          </div>

          <!-- Small KPI chips -->
          <div class="flex flex-wrap gap-2 text-xs md:text-sm">
            <div
              class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-600">
              <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
              <span class="font-medium text-slate-700 dark:text-slate-200">
                Aktive Prüfungen
              </span>
              <span
                class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                {{ activeExams.length }}
              </span>
            </div>
            <div
              class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-600">
              <span class="inline-block h-2 w-2 rounded-full bg-blue-500"></span>
              <span class="font-medium text-slate-700 dark:text-slate-200">
                Prüfungen gesamt
              </span>
              <span
                class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                {{ props.exams.length }}
              </span>
            </div>
          </div>
        </header>

        <!-- Active exams section -->
        <section v-if="activeExams.length"
          class="space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm dark:border-emerald-800/60 dark:bg-emerald-900/30">
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <span
                class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white">
                LIVE
              </span>
              <div>
                <h2 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                  Laufende Prüfungen
                </h2>
                <p class="text-xs text-emerald-800/80 dark:text-emerald-200/80">
                  Übersicht der aktuell laufenden Prüfungsdurchläufe.
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <div v-for="exam in activeExams" :key="exam.id"
              class="overflow-hidden rounded-xl bg-white/90 ring-1 ring-emerald-100 dark:bg-slate-900 dark:ring-emerald-800/70">
              <LiveExamStatusTable :exam="exam" />
            </div>
          </div>
        </section>

        <!-- Main 3-column layout -->
        <section class="grid gap-4 lg:grid-cols-3">
          <!-- Box 1: Recent Users -->
          <div class="flex flex-col">
            <div
              class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
              <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    Aktuelle Benutzer (letzte 6h)
                  </h2>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    Wählen Sie Teilnehmende für eine neue Prüfung aus.
                  </p>
                </div>
                <span
                  class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                  {{ availableRecentUsers.length }}
                </span>
              </div>
              <div class="flex-1 overflow-auto">
                <table class="w-full text-xs md:text-sm">
                  <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    <tr>
                      <th class="w-10 px-3 py-2 text-center">
                        <input type="checkbox" @change="toggleSelectAllRecent"
                          class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                      </th>
                      <th class="px-3 py-2 text-left font-medium">Name</th>
                      <th class="px-3 py-2 text-left font-medium">Vorname</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in availableRecentUsers" :key="user.id"
                      class="cursor-pointer border-b border-slate-50 text-slate-800 hover:bg-slate-50/80 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-700"
                      @click="toggleRecentUser(user.id)">
                      <td class="px-3 py-1.5 text-center">
                        <input type="checkbox" :checked="selectedRecentUserIds.includes(user.id)"
                          class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                      </td>
                      <td class="px-3 py-1.5">
                        {{ user.name }}
                      </td>
                      <td class="px-3 py-1.5">
                        {{ user.firstname }}
                      </td>
                    </tr>
                    <tr v-if="!availableRecentUsers.length">
                      <td colspan="3" class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                        Keine aktuellen Benutzer gefunden.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div
                class="border-t border-slate-200 px-4 py-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                Tipp: Mehrere Personen markieren, um sie gemeinsam zur Prüfung hinzuzufügen.
              </div>
            </div>
          </div>

          <!-- Box 2: Create Exam -->
          <div class="flex flex-col">
            <div
              class="h-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    Neue Prüfung erstellen
                  </h2>
                  <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Titel festlegen, Teilnehmende hinzufügen und Prüfungsschritte auswählen.
                  </p>
                </div>
                <span v-if="showCreateExamForm"
                  class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700 ring-1 ring-blue-100 dark:bg-blue-900/30 dark:text-blue-100 dark:ring-blue-800/60">
                  Konfiguration
                </span>
              </div>

              <div class="mt-4">
                <div v-if="!showCreateExamForm">
                  <button @click="showCreateExamForm = true"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:ring-offset-slate-800">
                    <span class="text-base leading-none">＋</span>
                    <span>Neue Prüfung erstellen</span>
                  </button>
                  <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Wählen Sie zuerst Teilnehmende in der linken Liste aus.
                  </p>
                </div>

                <div v-else class="space-y-4">
                  <!-- Title -->
                  <div>
                    <label for="exam-title" class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                      Prüfungstitel
                    </label>
                    <input type="text" id="exam-title" v-model="newExamTitle"
                      class="mt-1 block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                      placeholder="z. B. Abschlussprüfung Sommer 2025" />
                  </div>

                  <!-- Participants staging -->
                  <div class="flex items-start gap-3">
                    <button @click="addSelectedUsersToStage" :disabled="!selectedRecentUserIds.length"
                      class="mt-5 inline-flex h-9 w-10 items-center justify-center rounded-xl border border-slate-300 bg-slate-50 text-lg font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                      →
                    </button>
                    <div
                      class="flex-1 rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-3 text-xs dark:border-slate-600 dark:bg-slate-900/40">
                      <div class="mb-2 flex items-center justify-between">
                        <p class="font-medium text-slate-700 dark:text-slate-200">
                          Ausgewählte Teilnehmer:innen
                        </p>
                        <span
                          class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                          {{ stagedUserIds.length }}
                        </span>
                      </div>
                      <ul class="space-y-1.5 max-h-32 overflow-y-auto">
                        <li v-for="userId in stagedUserIds" :key="userId"
                          class="flex items-center justify-between rounded-lg bg-white px-2 py-1.5 text-xs text-slate-800 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-600">
                          <span>
                            {{props.recentUsers.find((u) => u.id === userId)?.name}},
                            {{props.recentUsers.find((u) => u.id === userId)?.firstname}}
                          </span>
                          <button @click="removeStagedUser(userId)"
                            class="text-xs font-bold text-red-500 hover:text-red-600">
                            &times;
                          </button>
                        </li>
                      </ul>
                      <p v-if="!stagedUserIds.length" class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                        Noch keine Teilnehmenden hinzugefügt. Wählen Sie Personen links aus und klicken Sie auf den
                        Pfeil.
                      </p>
                    </div>
                  </div>

                  <!-- Steps -->
                  <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                      Prüfungsschritte
                    </label>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                      <div v-for="test in props.tests" :key="test.id"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs shadow-sm dark:border-slate-600 dark:bg-slate-900">
                        <input type="checkbox" :id="`test-${test.id}`" :value="test.id" v-model="newExamSteps"
                          class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        <label :for="`test-${test.id}`" class="block text-xs text-slate-800 dark:text-slate-100">
                          {{ test.name }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="mt-2 flex items-center justify-end gap-2">
                    <button @click="showCreateExamForm = false"
                      class="text-xs font-medium text-slate-600 underline-offset-2 hover:text-slate-900 hover:underline dark:text-slate-300 dark:hover:text-slate-100">
                      Abbrechen
                    </button>
                    <button @click="saveExam" :disabled="!newExamTitle || !stagedUserIds.length"
                      class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:ring-offset-slate-800">
                      Prüfung speichern
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Box 3: All Exams -->
          <div class="flex flex-col">
            <div
              class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
              <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                <div>
                  <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    Alle Prüfungen
                  </h2>
                  <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Schnellzugriff auf vorhandene Prüfungen und Details.
                  </p>
                </div>
                <span
                  class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                  {{ props.exams.length }}
                </span>
              </div>
              <div class="flex-1 overflow-auto">
                <table class="w-full text-xs md:text-sm">
                  <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                    <tr>
                      <th class="px-3 py-2 text-left font-medium">Name</th>
                      <th class="px-3 py-2 text-left font-medium">Stadt</th>
                      <th class="px-3 py-2 text-left font-medium">Status</th>
                      <th class="px-3 py-2 text-right font-medium"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="exam in props.exams" :key="exam.id"
                      class="border-b border-slate-50 hover:bg-slate-50/80 dark:border-slate-700 dark:hover:bg-slate-700">
                      <td class="px-3 py-2 text-slate-900 dark:text-slate-100">
                        {{ exam.name }}
                      </td>
                      <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                        {{ exam.city.name }}
                      </td>
                      <td class="px-3 py-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium" :class="{
                          'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-100 dark:ring-emerald-800/60':
                            exam.status === 'aktiv',
                          'bg-amber-50 text-amber-700 ring-1 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-100 dark:ring-amber-800/60':
                            exam.status === 'geplant',
                          'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600':
                            exam.status !== 'aktiv' && exam.status !== 'geplant',
                        }">
                          {{ exam.status }}
                        </span>
                      </td>
                      <td class="px-3 py-2 text-right">
                        <button @click="openExamDetailsModal(exam)"
                          class="text-xs font-semibold text-blue-600 underline-offset-2 hover:text-blue-700 hover:underline dark:text-blue-300 dark:hover:text-blue-200">
                          Details
                        </button>
                      </td>
                    </tr>
                    <tr v-if="!props.exams.length">
                      <td colspan="4" class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                        Keine Prüfungen gefunden.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>

      <ExamDetailsModal :exam="selectedExam" :show="showExamDetailsModal" @close="showExamDetailsModal = false"
        @save="saveExamSteps" />
    </div>
  </AppLayout>
</template>
