<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, nextTick, onMounted, onUnmounted, ref, shallowRef, watch } from 'vue';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';

// Import test components
import AVEM from '@/pages/AVEM.vue';
import BIT2 from '@/pages/BIT-2.vue';
import BRTA from '@/pages/BRT-A.vue';
import BRTB from '@/pages/BRT-B.vue';
import FPI from '@/pages/FPI-R.vue';
import LMT from '@/pages/LMT.vue';
import LMT2 from '@/pages/LMT2.vue';
import LPS from '@/pages/LPS.vue';
import MRTA from '@/pages/MRT-A.vue';
import MRTB from '@/pages/MRT-B.vue';
import KONZ from '@/pages/Konzentrationstest.vue';

type StepStatus = 'not_started' | 'in_progress' | 'completed' | 'broken' | 'paused';
type ExamStatus = 'not_started' | 'in_progress' | 'paused' | 'completed';
type ExamStepInfo = {
  id: number;
  name: string;
  test: { id: number; name: string };
};
type StepStatusEntry = {
  id: number;
  status: StepStatus;
  force_finish_requested_at?: string | null;
  force_finish_deadline?: string | null;
  time_remaining_seconds?: number | null;
  [key: string]: any;
};

type ForceFinishDetail = {
  stepId: number;
  deadline?: string | null;
  requestedAt?: string | null;
};

const props = defineProps<{
  exam: {
    id: number;
    name: string;
    status: ExamStatus;
    contract_view_enabled?: boolean;
    steps: ExamStepInfo[];
    current_step?: {
      id: number;
      test: { id: number; name: string };
    };
  };
  stepStatuses: Record<number, StepStatusEntry>;
  pausedTestResults?: Record<string, unknown>;
}>();

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);

const activeTestComponent = shallowRef<unknown>(null);
const isTestDialogOpen = ref(false);
const activeStepId = ref<number | null>(null);
const isContractDialogOpen = ref(false);
const contractStatus = ref({
  userEnabled: !!page.props.auth?.user?.contract_view_enabled,
  examEnabled: props.exam.status === 'completed' ? false : !!props.exam.contract_view_enabled,
});
const localPausedResults = ref<Record<string, unknown>>({});
const activeTimeRemainingSeconds = ref<number | null>(null);
const pausedTestResults = computed<Record<string, unknown>>(() => {
  const serverResults = (props.pausedTestResults ?? {}) as Record<string, unknown>;
  return {
    ...localPausedResults.value,
    ...serverResults,
  };
});
const contractSrc = computed(
  () => `${route('my.pdf')}#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&view=FitH`,
);
const isContractAvailable = computed(() => {
  if (props.exam.status === 'completed') {
    return contractStatus.value.userEnabled;
  }

  return contractStatus.value.userEnabled || contractStatus.value.examEnabled;
});

let countdownInterval: ReturnType<typeof setInterval> | null = null;

const testComponents: Record<string, unknown> = {
  'BRT-A': BRTA,
  'BRT-B': BRTB,
  'FPI-R': FPI,
  LMT: LMT,
  'LPS-A': LPS,
  'LPS-B': LPS,
  LPS: LPS,
  'MRT-A': MRTA,
  'MRT-B': MRTB,
  LMT2: LMT2,
  'BIT-2': BIT2,
  AVEM: AVEM,
  Konzentrationstest: KONZ,
  628: KONZ,
};

const stepStatuses = ref<Record<number, StepStatusEntry>>(normalizeStepStatuses(props.stepStatuses));
const testsWithPauseSupport = new Set(['BIT-2', 'FPI-R', 'MRT-A', 'MRT-B', 'LPS', 'LPS-A', 'LPS-B']);
const visibleSteps = computed(() =>
  props.exam.steps.filter((step) => {
    if (props.exam.current_step?.id === step.id) {
      return true;
    }

    const status = stepStatuses.value?.[step.id]?.status;
    return typeof status !== 'undefined' && status !== 'not_started';
  }),
);
const previousStatusByStep = ref<Record<number, StepStatus | undefined>>({});
const previousForceFinishByStep = ref<Record<number, string | null>>({});
const remotelyPausedStepIds = new Set<number>();
const pendingForceFinishRequests = new Map<number, ForceFinishDetail>();

const hasPausedStep = computed(() =>
  Object.values(stepStatuses.value || {}).some((status) => status?.status === 'paused'),
);

function normalizeStepStatuses(statuses: Record<number, StepStatusEntry> | undefined) {
  if (!statuses) {
    return {} as Record<number, StepStatusEntry>;
  }

  return Object.fromEntries(
    Object.entries(statuses).map(([key, value]) => [Number(key), { ...value }]),
  ) as Record<number, StepStatusEntry>;
}

function getStatusText(status?: StepStatus) {
  const map = {
    not_started: 'Nicht gestartet',
    in_progress: 'In Bearbeitung',
    completed: 'Abgeschlossen',
    broken: 'Abgebrochen',
    paused: 'Pausiert',
  } as const;

  if (!status) {
    return 'Unbekannt';
  }

  return map[status];
}

function displayTestName(name: string) {
  return name === 'Konzentrationstest' ? '628 Test' : name;
}

interface StartTestOptions {
  skipServerStart?: boolean;
}

function startTest(step: ExamStepInfo, options: StartTestOptions = {}) {
  openTestInterface(step, options);
}

function handleStepActionClick(step: ExamStepInfo) {
  const status = stepStatuses.value[step.id]?.status;
  if (status === 'in_progress') {
    startTest(step, { skipServerStart: true });
    return;
  }

  startTest(step);
}

function getStepActionLabel(stepId: number) {
  const status = stepStatuses.value[stepId]?.status;
  if (status === 'in_progress') {
    return 'Test fortsetzen';
  }

  return 'Test starten';
}

const handleContractHotkeys = (event: KeyboardEvent) => {
  const key = event.key.toLowerCase();
  if ((event.ctrlKey || event.metaKey) && (key === 'p' || key === 's')) {
    event.preventDefault();
    event.stopPropagation();
  }
};

function openContract() {
  if (!isContractAvailable.value) return;
  isContractDialogOpen.value = true;
}

function closeContract() {
  isContractDialogOpen.value = false;
}

watch(
  () => props.exam.contract_view_enabled,
  (enabled) => {
    contractStatus.value.examEnabled = props.exam.status === 'completed' ? false : !!enabled;

    if (!isContractAvailable.value) {
      closeContract();
    }
  },
  { immediate: true },
);

watch(
  () => page.props.auth?.user?.contract_view_enabled,
  (enabled) => {
    contractStatus.value.userEnabled = !!enabled;

    if (!isContractAvailable.value) {
      closeContract();
    }
  },
  { immediate: true },
);

const syncContractFlagsFromProps = () => {
  contractStatus.value = {
    userEnabled: !!page.props.auth?.user?.contract_view_enabled,
    examEnabled: props.exam.status === 'completed' ? false : !!props.exam.contract_view_enabled,
  };
};

const refreshContractStatus = async () => {
  try {
    const response = await axios.get(route('api.my-contract-status'), {
      params: { exam_id: props.exam.id },
    });

    const userEnabled = !!response.data?.user_contract_view_enabled;
    const examContractFromResponse = response.data?.exam_contract_view_enabled;
    const examEnabled = props.exam.status === 'completed'
      ? false
      : typeof examContractFromResponse === 'boolean'
        ? !!examContractFromResponse
        : contractStatus.value.examEnabled;

    contractStatus.value = {
      userEnabled,
      examEnabled,
    };

    if (!isContractAvailable.value) {
      closeContract();
    }
  } catch (error) {
    console.error('Fehler beim Aktualisieren des Vertragsstatus', error);
  }
};

function isStepActionDisabled(step: ExamStepInfo) {
  const status = stepStatuses.value[step.id]?.status;
  if (props.exam.status !== 'in_progress') {
    return true;
  }

  if (props.exam.current_step?.id !== step.id) {
    return true;
  }

  if (!status) {
    return true;
  }

  return status === 'completed' || status === 'broken' || status === 'paused';
}

function openTestInterface(step: ExamStepInfo, options: StartTestOptions = {}) {
  const component = testComponents[step.test.name];

  if (!component) {
    console.warn(`Kein Test-Component für ${step.test.name} registriert.`);
    return;
  }

  activeStepId.value = step.id;
  activeTestComponent.value = component;
  syncActiveTimeRemaining(step.id);

  const showDialog = () => {
    finishing.value = false;
    isTestDialogOpen.value = true;
    nextTick(() => {
      requestFullscreen();
      window.addEventListener('beforeunload', handleBeforeUnload);
      document.addEventListener('fullscreenchange', handleFullscreenChange);
      window.addEventListener('start-finish', beginFinish as EventListener);
      window.addEventListener('cancel-finish', cancelFinish as EventListener);
      const pending = pendingForceFinishRequests.get(step.id);
      if (pending) {
        setTimeout(() => {
          dispatchForceFinish(pending);
        }, 0);
      }
    });
  };

  if (options.skipServerStart) {
    showDialog();
    return;
  }

  router.post(
    '/my-exam/start-step',
    { exam_step_id: step.id },
    {
      preserveScroll: true,
      onSuccess: showDialog,
    },
  );
}

const activePausedTestResult = computed(() => {
  if (!activeStepId.value) {
    return undefined;
  }

  const status = stepStatuses.value[activeStepId.value]?.status;
  if (!status || !['paused', 'in_progress'].includes(status)) {
    return undefined;
  }

  const key = String(activeStepId.value);
  return pausedTestResults.value[key];
});

const activeComponentProps = computed<Record<string, unknown>>(() => {
  if (!activeStepId.value) {
    return {};
  }

  const step = props.exam.steps.find((candidate) => candidate.id === activeStepId.value);
  if (!step) {
    return {};
  }

  const baseProps: Record<string, unknown> = {
    timeRemainingSeconds: activeTimeRemainingSeconds.value ?? null,
    testName: step.test.name,
  };

  if (testsWithPauseSupport.has(step.test.name) && activePausedTestResult.value) {
    return { ...baseProps, pausedTestResult: activePausedTestResult.value };
  }

  return baseProps;
});

function stopCountdown() {
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
}

function startCountdownIfNeeded() {
  if (!isTestDialogOpen.value || typeof activeStepId.value !== 'number') {
    stopCountdown();
    return;
  }

  const status = stepStatuses.value[activeStepId.value];
  if (!status || status.status !== 'in_progress') {
    stopCountdown();
    return;
  }

  if (countdownInterval) return;

  countdownInterval = setInterval(() => {
    if (activeTimeRemainingSeconds.value === null) return;
    activeTimeRemainingSeconds.value = Math.max(0, activeTimeRemainingSeconds.value - 1);
  }, 1000);
}

function syncActiveTimeRemaining(stepId: number | null) {
  if (stepId === null) {
    activeTimeRemainingSeconds.value = null;
    stopCountdown();
    return;
  }

  const status = stepStatuses.value[stepId];
  activeTimeRemainingSeconds.value = typeof status?.time_remaining_seconds === 'number'
    ? Math.max(0, Math.floor(status.time_remaining_seconds))
    : null;

  startCountdownIfNeeded();
}

watch(
  () => activeStepId.value,
  (stepId) => {
    syncActiveTimeRemaining(typeof stepId === 'number' ? stepId : null);
  },
  { immediate: true },
);

watch(
  () => isTestDialogOpen.value,
  () => startCountdownIfNeeded(),
);

function completeTest(results: any) {
  if (typeof activeStepId.value !== 'number') return;

  const stepId = activeStepId.value;

  router.post(
    '/my-exam/complete-step',
    {
      exam_step_id: activeStepId.value,
      results,
    },
    {
      onSuccess: () => {
        delete localPausedResults.value[String(stepId)];
        closeTestDialog({ resetActive: true });
      },
    },
  );
}

function breakTest() {
  if (!activeStepId.value) return;

  router.post(
    '/my-exam/break-step',
    { exam_step_id: activeStepId.value },
    {
      onSuccess: () => {
        closeTestDialog({ resetActive: true });
      },
    },
  );
}

function closeTestDialog({ resetActive = false }: { resetActive?: boolean } = {}) {
  if (isTestDialogOpen.value) {
    isTestDialogOpen.value = false;
  }

  cleanupAfterTest();

  stopCountdown();
  activeTimeRemainingSeconds.value = null;

  if (resetActive) {
    const stepId = typeof activeStepId.value === 'number' ? activeStepId.value : null;
    if (typeof activeStepId.value === 'number') {
      remotelyPausedStepIds.delete(activeStepId.value);
    }
    if (stepId !== null) {
      pendingForceFinishRequests.delete(stepId);
    }
    activeStepId.value = null;
    activeTestComponent.value = null;
  }
}

function cleanupAfterTest() {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  document.removeEventListener('fullscreenchange', handleFullscreenChange);
  window.removeEventListener('start-finish', beginFinish as EventListener);
  window.removeEventListener('cancel-finish', cancelFinish as EventListener);
  if (document.fullscreenElement && typeof document.exitFullscreen === 'function') {
    document.exitFullscreen().catch(() => undefined);
  }
  finishing.value = false;
}

const activeTestAnswers = ref<any>(null);

function pauseTest(answers: any) {
  if (typeof activeStepId.value !== 'number') return;

  if (answers) {
    localPausedResults.value[String(activeStepId.value)] = answers;
  }

  router.post(
    '/my-exam/pause-step',
    {
      exam_step_id: activeStepId.value,
      results: answers,
    },
    {
      preserveScroll: true,
    },
  );
}

function handleRemotePause(stepId: number) {
  remotelyPausedStepIds.add(stepId);
  if (activeStepId.value === stepId) {
    pauseTest(activeTestAnswers.value);
    closeTestDialog();
  }
}

function handleRemoteResume(stepId: number, status: StepStatus) {
  if (!remotelyPausedStepIds.has(stepId)) {
    return;
  }

  remotelyPausedStepIds.delete(stepId);

  if (status !== 'in_progress') {
    return;
  }

  if (isTestDialogOpen.value) {
    return;
  }

  const step = props.exam.steps.find((candidate) => candidate.id === stepId);
  if (!step) {
    return;
  }

  startTest(step, { skipServerStart: true });
}

function buildForceFinishDetail(stepId: number, status: StepStatusEntry): ForceFinishDetail {
  return {
    stepId,
    deadline: status.force_finish_deadline ?? status.force_finish_requested_at ?? null,
    requestedAt: status.force_finish_requested_at ?? null,
  };
}

function dispatchForceFinish(detail: ForceFinishDetail) {
  window.dispatchEvent(new CustomEvent('teacher-force-finish', { detail }));
}

function queueForceFinish(stepId: number, status: StepStatusEntry) {
  const detail = buildForceFinishDetail(stepId, status);
  pendingForceFinishRequests.set(stepId, detail);

  if (isTestDialogOpen.value && activeStepId.value === stepId) {
    dispatchForceFinish(detail);
  }
}

function clearQueuedForceFinish(stepId: number) {
  pendingForceFinishRequests.delete(stepId);
}

// --- Fullscreen and Anti-Cheating ---
const finishing = ref(false);
const fullscreenWarningOpen = ref(false);

function requestFullscreen() {
  const elem = document.documentElement;
  if (elem.requestFullscreen) {
    elem.requestFullscreen().catch(() => undefined);
  }
}

function handleFullscreenChange() {
  if (!document.fullscreenElement) {
    if (finishing.value) return;
    fullscreenWarningOpen.value = true;
  }
}

function confirmFullscreenExit() {
  fullscreenWarningOpen.value = false;
  breakTest();
}

function cancelFullscreenExit() {
  fullscreenWarningOpen.value = false;
  requestFullscreen();
}

function beginFinish() {
  finishing.value = true;
}

function cancelFinish() {
  finishing.value = false;
  requestFullscreen();
}

function handleBeforeUnload(event: BeforeUnloadEvent) {
  event.preventDefault();
  event.returnValue = '';
}

// --- Polling ---
let polling: NodeJS.Timeout | null = null;
let contractPolling: number | null = null;
onMounted(() => {
  polling = setInterval(() => {
    router.reload({ only: ['exam', 'stepStatuses'] });
  }, 5000);

  refreshContractStatus();
  contractPolling = window.setInterval(refreshContractStatus, 3000);
});

onUnmounted(() => {
  if (polling) clearInterval(polling);
  if (contractPolling) window.clearInterval(contractPolling);
  cleanupAfterTest();
  stopCountdown();
  window.removeEventListener('keydown', handleContractHotkeys, true);
});

let hasSyncedInitialStatuses = false;
watch(
  () => props.stepStatuses,
  (newStatuses) => {
    const normalized = normalizeStepStatuses(newStatuses);
    const previous = stepStatuses.value;

    stepStatuses.value = normalized;

    if (typeof activeStepId.value === 'number') {
      syncActiveTimeRemaining(activeStepId.value);
    }

    const ids = new Set([
      ...Object.keys(previous),
      ...Object.keys(normalized),
    ]);

    ids.forEach((key) => {
      const id = Number(key);
      const prevStatus = previousStatusByStep.value[id];
      const currentStatus = normalized[id]?.status;
      const currentForceFinish = normalized[id]?.force_finish_requested_at ?? null;
      const prevForceFinish = previousForceFinishByStep.value[id];

      if (hasSyncedInitialStatuses) {
        if (currentStatus === 'paused' && prevStatus !== 'paused') {
          handleRemotePause(id);
        } else if (prevStatus === 'paused' && currentStatus && currentStatus !== 'paused') {
          handleRemoteResume(id, currentStatus);
        }
        if (normalized[id]) {
          if (currentForceFinish && currentForceFinish !== prevForceFinish) {
            queueForceFinish(id, normalized[id]!);
          } else if (!currentForceFinish && prevForceFinish) {
            clearQueuedForceFinish(id);
          }
        }
      }

      if (typeof currentStatus === 'undefined') {
        delete previousStatusByStep.value[id];
        remotelyPausedStepIds.delete(id);
      } else {
        previousStatusByStep.value[id] = currentStatus;
      }

      if (!currentStatus || !['paused', 'in_progress'].includes(currentStatus)) {
        delete localPausedResults.value[String(id)];
      }

      if (typeof currentForceFinish === 'undefined') {
        delete previousForceFinishByStep.value[id];
      } else {
        previousForceFinishByStep.value[id] = currentForceFinish;
      }
    });

    hasSyncedInitialStatuses = true;
  },
  { deep: true, immediate: true },
);

let previousExamStatus: ExamStatus | null = null;
watch(
  () => props.exam.status,
  (newStatus) => {
    if (previousExamStatus && newStatus !== previousExamStatus) {
      if (newStatus === 'paused') {
        closeTestDialog();
      } else if (newStatus === 'completed' || newStatus === 'not_started') {
        closeTestDialog({ resetActive: true });
      }
    }

    previousExamStatus = newStatus;
  },
  { immediate: true },
);

watch(isContractDialogOpen, (isOpen) => {
  if (isOpen) {
    window.addEventListener('keydown', handleContractHotkeys, true);
  } else {
    window.removeEventListener('keydown', handleContractHotkeys, true);
  }
});

watch(
  isContractAvailable,
  (enabled) => {
    if (!enabled) {
      isContractDialogOpen.value = false;
    }
  },
  { immediate: true },
);
</script>

<template>

  <Head title="My Exam" />
  <div class="flex min-h-screen items-center justify-center bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-2xl space-y-6 rounded-lg bg-white p-8 shadow-md dark:bg-gray-800">
      <h1 class="text-center text-2xl font-bold text-gray-800 dark:text-gray-100">{{ exam.name }}</h1>
      <div v-if="isContractAvailable" class="flex justify-end">
        <Button variant="outline" @click="openContract">Vertrag anzeigen</Button>
      </div>
      <!-- General Status Messages -->
      <div v-if="exam.status === 'not_started'" class="text-center">
        <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung hat noch nicht begonnen. Bitte warten Sie...</p>
      </div>
      <div v-else-if="exam.status === 'paused'" class="text-center">
        <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist pausiert. Bitte warten Sie, bis der Prüfer
          sie fortsetzt.</p>
      </div>
      <div v-else-if="exam.status === 'completed'" class="text-center">
        <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist abgeschlossen. Vielen Dank für Ihre
          Teilnahme.</p>
      </div>

      <!-- Exam Steps Table -->
      <div v-else class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Testübersicht</h2>
        <div v-if="hasPausedStep"
          class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:border-yellow-400/40 dark:bg-yellow-950/40 dark:text-yellow-100">
          Der Prüfer hat Ihren aktuellen Test pausiert. Bitte warten Sie auf weitere Anweisungen.
        </div>
        <div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                  Test
                </th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                  Status
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Aktion</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
              <tr v-for="step in visibleSteps" :key="step.id">
                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">{{
                  displayTestName(step.test.name) }}</td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  <Badge :class="cn(
                    stepStatuses[step.id]?.status === 'completed' &&
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    stepStatuses[step.id]?.status === 'in_progress' &&
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    stepStatuses[step.id]?.status === 'not_started' &&
                    'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200',
                    stepStatuses[step.id]?.status === 'paused' &&
                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                    stepStatuses[step.id]?.status === 'broken' &&
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                  )
                    ">
                    {{ getStatusText(stepStatuses[step.id]?.status) }}
                  </Badge>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                  <Button size="sm" :disabled="isStepActionDisabled(step)" @click="handleStepActionClick(step)">
                    {{ getStepActionLabel(step.id) }}
                  </Button>
                </td>
              </tr>
              <tr v-if="!visibleSteps.length">
                <td colspan="3"
                  class="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                  Momentan sind keine Tests verfügbar. Bitte warten Sie, bis Ihre Prüfung startet oder der Prüfer den
                  nächsten Test freigibt.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <Dialog v-model:open="isContractDialogOpen">
      <DialogContent
        class="inset-0 top-0 left-0 h-screen w-screen max-w-none translate-x-0 translate-y-0 overflow-hidden rounded-none border-none bg-white p-0 text-black sm:max-w-none dark:bg-gray-900 dark:text-white"
        @contextmenu.prevent
      >
        <DialogHeader class="sr-only">
          <DialogTitle>Vertragsansicht</DialogTitle>
          <DialogDescription>Vertragsdetails im Vollbild anzeigen.</DialogDescription>
        </DialogHeader>
        <div class="absolute top-4 left-4 flex items-center gap-2">
          <Button variant="ghost" size="sm" @click="closeContract">✕</Button>
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Vertrag</span>
        </div>
        <div class="h-full w-full pt-12">
          <iframe
            :src="contractSrc"
            class="h-full w-full border-0"
            allow="fullscreen"
            allowfullscreen
          />
        </div>
      </DialogContent>
    </Dialog>
    <Dialog v-model:open="isTestDialogOpen">
      <DialogContent
        class="inset-0 top-0 left-0 h-screen w-screen max-w-none translate-x-0 translate-y-0 overflow-auto rounded-none border-none bg-white p-0 text-black sm:max-w-none dark:bg-gray-900 dark:text-white">
        <DialogHeader class="sr-only">
          <DialogTitle>Testoberfläche</DialogTitle>
          <DialogDescription>Hier bearbeiten Sie den ausgewählten Test.</DialogDescription>
        </DialogHeader>
        <template #top-right>
          <div class="absolute top-4 right-4 font-semibold">{{ userName }}</div>
        </template>
        <div class="h-full w-full flex flex-col gap-4">
          <div class="px-4 pt-4 sm:px-6">
            <TimeRemainingAlerts :time-remaining-seconds="activeTimeRemainingSeconds" />
          </div>
          <div class="flex-1">
            <KeepAlive>
              <component v-if="activeTestComponent" :is="activeTestComponent" :key="activeStepId ?? 'inactive'"
                v-bind="activeComponentProps" @complete="completeTest" @update:answers="activeTestAnswers = $event" />
            </KeepAlive>
          </div>
        </div>
      </DialogContent>
    </Dialog>
    <Dialog :open="fullscreenWarningOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Vollbildmodus verlassen</DialogTitle>
          <DialogDescription>
            Sie haben den Vollbildmodus verlassen. Der Test wird jetzt beendet. Klicken Sie auf „Ja“, um den Test zu
            beenden, oder auf
            „Abbrechen“, um fortzufahren.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <Button variant="secondary" @click="cancelFullscreenExit">Abbrechen</Button>
          <Button variant="destructive" @click="confirmFullscreenExit">Ja</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
