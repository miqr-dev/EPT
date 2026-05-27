<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Building2, ClipboardList, Clock3, RefreshCw, UsersRound } from 'lucide-vue-next';

type ExamStatus = 'not_started' | 'in_progress' | 'paused';

type ExamStep = {
  id: number;
  name: string | null;
  code: string | null;
  duration: number;
};

type OverviewExam = {
  id: number;
  name: string;
  status: ExamStatus;
  teacherName: string | null;
  participantsCount: number;
  currentStep: Pick<ExamStep, 'id' | 'name' | 'code'> | null;
  steps: ExamStep[];
  createdAt: string | null;
  startedAt: string | null;
};

type LocationGroup = {
  id: number;
  name: string;
  ongoingExams: OverviewExam[];
  plannedExams: OverviewExam[];
};

const props = defineProps<{
  locations: LocationGroup[];
  summary: {
    locationCount: number;
    ongoingCount: number;
    plannedCount: number;
  };
}>();

const statusMeta: Record<ExamStatus, { label: string; class: string }> = {
  not_started: {
    label: 'Geplant',
    class: 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-900/30 dark:text-blue-100 dark:ring-blue-800/60',
  },
  in_progress: {
    label: 'Laufend',
    class: 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-100 dark:ring-emerald-800/60',
  },
  paused: {
    label: 'Pausiert',
    class: 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-100 dark:ring-amber-800/60',
  },
};

function formatDateTime(value: string | null) {
  if (!value) return '-';

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '-';

  return new Intl.DateTimeFormat('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function stepNames(exam: OverviewExam) {
  return exam.steps
    .map((step) => step.name || step.code)
    .filter(Boolean) as string[];
}

function refreshPage() {
  router.reload({
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Standort-Prüfungen" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 py-6 dark:bg-slate-900">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 lg:px-6">
        <header class="flex flex-col gap-3 border-b border-slate-200 pb-4 dark:border-slate-700 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Admin</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
              Standort-Prüfungen
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Laufende und geplante Prüfungen über alle Standorte hinweg.
            </p>
          </div>

          <button
            type="button"
            class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
            @click="refreshPage"
          >
            <RefreshCw class="h-4 w-4" />
            Aktualisieren
          </button>
        </header>

        <section class="grid gap-3 md:grid-cols-3">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Standorte</span>
              <Building2 class="h-4 w-4 text-slate-400" />
            </div>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ props.summary.locationCount }}</p>
          </div>
          <div class="rounded-lg border border-emerald-100 bg-white p-4 shadow-sm dark:border-emerald-900/70 dark:bg-slate-800">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Laufend</span>
              <Clock3 class="h-4 w-4 text-emerald-500" />
            </div>
            <p class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-200">{{ props.summary.ongoingCount }}</p>
          </div>
          <div class="rounded-lg border border-blue-100 bg-white p-4 shadow-sm dark:border-blue-900/70 dark:bg-slate-800">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Geplant</span>
              <ClipboardList class="h-4 w-4 text-blue-500" />
            </div>
            <p class="mt-2 text-2xl font-semibold text-blue-700 dark:text-blue-200">{{ props.summary.plannedCount }}</p>
          </div>
        </section>

        <section v-if="props.locations.length" class="space-y-8">
          <div v-for="location in props.locations" :key="location.id" class="border-t border-slate-200 pt-6 dark:border-slate-700">
            <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">
                  <Building2 class="h-5 w-5" />
                </span>
                <div>
                  <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">{{ location.name }}</h2>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ location.ongoingExams.length }} laufend, {{ location.plannedExams.length }} geplant
                  </p>
                </div>
              </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-2">
              <div>
                <div class="mb-2 flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Laufende Prüfungen</h3>
                  <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-100 dark:ring-emerald-800/60">
                    {{ location.ongoingExams.length }}
                  </span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                  <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                      <tr>
                        <th class="px-4 py-3 text-left font-medium">Prüfung</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Aktueller Test</th>
                        <th class="px-4 py-3 text-left font-medium">Start</th>
                        <th class="px-4 py-3 text-right font-medium">TN</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                      <tr v-for="exam in location.ongoingExams" :key="exam.id" class="text-slate-800 dark:text-slate-100">
                        <td class="px-4 py-3">
                          <div class="font-medium">{{ exam.name }}</div>
                          <div class="text-xs text-slate-500 dark:text-slate-400">{{ exam.teacherName || 'Keine Lehrkraft' }}</div>
                        </td>
                        <td class="px-4 py-3">
                          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1" :class="statusMeta[exam.status].class">
                            {{ statusMeta[exam.status].label }}
                          </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                          {{ exam.currentStep?.name || exam.currentStep?.code || '-' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ formatDateTime(exam.startedAt) }}</td>
                        <td class="px-4 py-3 text-right">
                          <span class="inline-flex items-center justify-end gap-1 font-medium">
                            <UsersRound class="h-4 w-4 text-slate-400" />
                            {{ exam.participantsCount }}
                          </span>
                        </td>
                      </tr>
                      <tr v-if="!location.ongoingExams.length">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                          Keine laufenden Prüfungen.
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div>
                <div class="mb-2 flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Geplante Prüfungen</h3>
                  <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-100 dark:bg-blue-900/30 dark:text-blue-100 dark:ring-blue-800/60">
                    {{ location.plannedExams.length }}
                  </span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                  <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                      <tr>
                        <th class="px-4 py-3 text-left font-medium">Prüfung</th>
                        <th class="px-4 py-3 text-left font-medium">Schritte</th>
                        <th class="px-4 py-3 text-left font-medium">Erstellt</th>
                        <th class="px-4 py-3 text-right font-medium">TN</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                      <tr v-for="exam in location.plannedExams" :key="exam.id" class="text-slate-800 dark:text-slate-100">
                        <td class="px-4 py-3">
                          <div class="font-medium">{{ exam.name }}</div>
                          <div class="text-xs text-slate-500 dark:text-slate-400">{{ exam.teacherName || 'Keine Lehrkraft' }}</div>
                        </td>
                        <td class="px-4 py-3">
                          <div class="flex max-w-md flex-wrap gap-1">
                            <span
                              v-for="stepName in stepNames(exam).slice(0, 4)"
                              :key="`${exam.id}-${stepName}`"
                              class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-100"
                            >
                              {{ stepName }}
                            </span>
                            <span v-if="stepNames(exam).length > 4" class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                              +{{ stepNames(exam).length - 4 }}
                            </span>
                            <span v-if="!stepNames(exam).length" class="text-slate-400 dark:text-slate-500">-</span>
                          </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ formatDateTime(exam.createdAt) }}</td>
                        <td class="px-4 py-3 text-right">
                          <span class="inline-flex items-center justify-end gap-1 font-medium">
                            <UsersRound class="h-4 w-4 text-slate-400" />
                            {{ exam.participantsCount }}
                          </span>
                        </td>
                      </tr>
                      <tr v-if="!location.plannedExams.length">
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                          Keine geplanten Prüfungen.
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section v-else class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-10 text-center dark:border-slate-700 dark:bg-slate-800">
          <p class="text-sm text-slate-500 dark:text-slate-400">Keine Standorte gefunden.</p>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
