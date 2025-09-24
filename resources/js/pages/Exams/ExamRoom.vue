<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, shallowRef, watch } from 'vue';

// Import test components
import AVEM from '@/pages/AVEM.vue';
import BIT2 from '@/pages/BIT-2.vue';
import BRTA from '@/pages/BRT-A.vue';
import BRTB from '@/pages/BRT-B.vue';
import FPI from '@/pages/FPI-R.vue';
import LMT from '@/pages/LMT.vue';
import LMT2 from '@/pages/LMT2.vue';
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
    [key: string]: any;
};

const props = defineProps<{
    exam: {
        id: number;
        name: string;
        status: ExamStatus;
        steps: ExamStepInfo[];
        current_step?: {
            id: number;
            test: { id: number; name: string };
        };
    };
    stepStatuses: Record<number, StepStatusEntry>;
}>();

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);

const activeTestComponent = shallowRef<unknown>(null);
const activeTestComponentRef = ref<any>(null);
const isTestDialogOpen = ref(false);
const isPausePending = ref(false);
const activeStepId = ref<number | null>(null);

const testComponents: Record<string, unknown> = {
    'BRT-A': BRTA,
    'BRT-B': BRTB,
    'FPI-R': FPI,
    LMT: LMT,
    'MRT-A': MRTA,
    'MRT-B': MRTB,
    LMT2: LMT2,
    'BIT-2': BIT2,
    AVEM: AVEM,
    Konzentrationstest: KONZ,
};

const stepStatuses = ref<Record<number, StepStatusEntry>>(normalizeStepStatuses(props.stepStatuses));
const previousStatusByStep = ref<Record<number, StepStatus | undefined>>({});
const remotelyPausedStepIds = new Set<number>();

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

interface StartTestOptions {
    skipServerStart?: boolean;
}

function startTest(step: ExamStepInfo, options: StartTestOptions = {}) {
    openTestInterface(step, options);
}

function handleStepActionClick(step: ExamStepInfo) {
    const status = stepStatuses.value[step.id]?.status;
    if (status === 'in_progress' || status === 'paused') {
        startTest(step, { skipServerStart: true });
        return;
    }

    startTest(step);
}

function getStepActionLabel(stepId: number) {
    const status = stepStatuses.value[stepId]?.status;
    if (status === 'in_progress' || status === 'paused') {
        return 'Test fortsetzen';
    }

    return 'Test starten';
}

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

    return status === 'completed' || status === 'broken';
}

function openTestInterface(step: ExamStepInfo, options: StartTestOptions = {}) {
    const component = testComponents[step.test.name];

    if (!component) {
        console.warn(`Kein Test-Component für ${step.test.name} registriert.`);
        return;
    }

    activeStepId.value = step.id;
    activeTestComponent.value = component;

    const showDialog = () => {
        finishing.value = false;
        isTestDialogOpen.value = true;
        nextTick(() => {
            requestFullscreen();
            window.addEventListener('beforeunload', handleBeforeUnload);
            document.addEventListener('fullscreenchange', handleFullscreenChange);
            window.addEventListener('start-finish', beginFinish as EventListener);
            window.addEventListener('cancel-finish', cancelFinish as EventListener);
            if (autosaveInterval) clearInterval(autosaveInterval);
            autosaveInterval = setInterval(autosave, 15000);
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

async function pauseTest() {
    if (!activeStepId.value) return;

    const results = activeTestComponentRef.value?.getResults
        ? await activeTestComponentRef.value.getResults()
        : null;

    router.post(
        '/my-exam/pause-step',
        {
            exam_step_id: activeStepId.value,
            results,
        },
        {
            onSuccess: () => {
                closeTestDialog({ resetActive: true });
            },
        },
    );
}

function completeTest(results: any) {
    if (!activeStepId.value) return;

    router.post(
        '/my-exam/complete-step',
        {
            exam_step_id: activeStepId.value,
            results,
        },
        {
            onSuccess: () => {
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

    if (resetActive) {
        if (typeof activeStepId.value === 'number') {
            remotelyPausedStepIds.delete(activeStepId.value);
        }
        activeStepId.value = null;
        activeTestComponent.value = null;
    }
}

function cleanupAfterTest() {
    if (autosaveInterval) {
        clearInterval(autosaveInterval);
        autosaveInterval = null;
    }
    window.removeEventListener('beforeunload', handleBeforeUnload);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
    window.removeEventListener('start-finish', beginFinish as EventListener);
    window.removeEventListener('cancel-finish', cancelFinish as EventListener);
    if (document.fullscreenElement && typeof document.exitFullscreen === 'function') {
        document.exitFullscreen().catch(() => undefined);
    }
    finishing.value = false;
}

function handleRemotePause(stepId: number) {
    remotelyPausedStepIds.add(stepId);
    if (activeStepId.value === stepId) {
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

// --- Autosave ---
let autosaveInterval: NodeJS.Timeout | null = null;

async function autosave() {
    if (!activeStepId.value || !activeTestComponentRef.value) return Promise.resolve();

    const results = activeTestComponentRef.value?.getResults
        ? await activeTestComponentRef.value.getResults()
        : null;

    if (results) {
        return router.post(
            '/my-exam/autosave-progress',
            {
                exam_step_id: activeStepId.value,
                results,
            },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }
    return Promise.resolve();
}

// --- Polling ---
let polling: NodeJS.Timeout | null = null;
onMounted(() => {
    polling = setInterval(() => {
        router.reload({ only: ['exam', 'stepStatuses'] });
    }, 5000);
});

onUnmounted(() => {
    if (polling) clearInterval(polling);
    cleanupAfterTest();
});

let hasSyncedInitialStatuses = false;
const previousPauseRequestAt = ref<Record<number, string | null>>({});

async function handlePauseRequest(stepId: number) {
    isPausePending.value = true;
    await autosave();
    router.post('/my-exam/acknowledge-pause', { exam_step_id: stepId }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isPausePending.value = false;
        },
        onError: () => {
            // Also reset on error to avoid getting stuck
            isPausePending.value = false;
        }
    });
}

watch(
    () => props.stepStatuses,
    (newStatuses) => {
        const normalized = normalizeStepStatuses(newStatuses);
        const previous = stepStatuses.value;

        stepStatuses.value = normalized;

        const ids = new Set([
            ...Object.keys(previous),
            ...Object.keys(normalized),
        ]);

        ids.forEach((key) => {
            const id = Number(key);
            const prevStatus = previousStatusByStep.value[id];
            const currentStatus = normalized[id]?.status;

            if (hasSyncedInitialStatuses) {
                const pauseRequestedAt = normalized[id]?.pause_requested_at;
                if (pauseRequestedAt && previousPauseRequestAt.value[id] !== pauseRequestedAt) {
                    handlePauseRequest(id);
                }

                if (currentStatus === 'paused' && prevStatus !== 'paused') {
                    handleRemotePause(id);
                } else if (prevStatus === 'paused' && currentStatus && currentStatus !== 'paused') {
                    handleRemoteResume(id, currentStatus);
                }
            }

            if (typeof currentStatus === 'undefined') {
                delete previousStatusByStep.value[id];
                delete previousPauseRequestAt.value[id];
                remotelyPausedStepIds.delete(id);
            } else {
                previousStatusByStep.value[id] = currentStatus;
                previousPauseRequestAt.value[id] = normalized[id]?.pause_requested_at;
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
</script>

<template>
    <Head title="My Exam" />
    <div class="flex min-h-screen items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="w-full max-w-2xl space-y-6 rounded-lg bg-white p-8 shadow-md dark:bg-gray-800">
            <h1 class="text-center text-2xl font-bold text-gray-800 dark:text-gray-100">{{ exam.name }}</h1>

            <!-- General Status Messages -->
            <div v-if="exam.status === 'not_started'" class="text-center">
                <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung hat noch nicht begonnen. Bitte warten Sie...</p>
            </div>
            <div v-else-if="exam.status === 'paused'" class="text-center">
                <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist pausiert. Bitte warten Sie, bis der Prüfer sie fortsetzt.</p>
            </div>
            <div v-else-if="exam.status === 'completed'" class="text-center">
                <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist abgeschlossen. Vielen Dank für Ihre Teilnahme.</p>
            </div>

            <!-- Exam Steps Table -->
            <div v-else class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Testübersicht</h2>
                <div
                    v-if="hasPausedStep"
                    class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:border-yellow-400/40 dark:bg-yellow-950/40 dark:text-yellow-100"
                >
                    Der Prüfer hat Ihren aktuellen Test pausiert. Bitte warten Sie auf weitere Anweisungen.
                </div>
                <div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Test
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Status
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Aktion</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="step in exam.steps" :key="step.id">
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">{{ step.test.name }}</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <Badge
                                        :class="
                                            cn(
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
                                        "
                                    >
                                        {{ getStatusText(stepStatuses[step.id]?.status) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <Dialog v-model:open="isTestDialogOpen">
                                        <DialogTrigger as-child>
                                            <Button
                                                size="sm"
                                                :disabled="isStepActionDisabled(step)"
                                                @click="handleStepActionClick(step)"
                                            >
                                                {{ getStepActionLabel(step.id) }}
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent
                                            class="inset-0 top-0 left-0 h-screen w-screen max-w-none translate-x-0 translate-y-0 overflow-auto rounded-none border-none bg-white p-0 text-black sm:max-w-none dark:bg-gray-900 dark:text-white"
                                        >
                                            <div v-if="isPausePending" class="absolute inset-x-0 top-0 z-20 bg-red-600 text-white text-center p-2 font-bold animate-pulse">
                                                Pause requested by teacher, saving final data...
                                            </div>
                                            <template #top-right>
                                                <div class="absolute top-4 right-4 z-10 flex items-center gap-4">
                                                    <Button
                                                        v-if="exam.pause_allowed"
                                                        variant="secondary"
                                                        size="sm"
                                                        @click="pauseTest"
                                                    >
                                                        Pause
                                                    </Button>
                                                    <div class="font-semibold">{{ userName }}</div>
                                                </div>
                                            </template>
                                            <KeepAlive>
                                                <component
                                                    v-if="activeTestComponent"
                                                    ref="activeTestComponentRef"
                                                    :is="activeTestComponent"
                                                    :key="activeStepId ?? 'inactive'"
                                                    :initial-results="stepStatuses[activeStepId!]?.partial_results"
                                                    class="h-full w-full"
                                                    @complete="completeTest"
                                                />
                                            </KeepAlive>
                                        </DialogContent>
                                    </Dialog>
                                </td>
                            </tr>
                            <tr v-if="!exam.steps.length">
                                <td colspan="3" class="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    Für diese Prüfung sind keine Tests definiert.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Dialog :open="fullscreenWarningOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Vollbildmodus verlassen</DialogTitle>
                    <DialogDescription>
                        Sie haben den Vollbildmodus verlassen. Der Test wird jetzt beendet. Klicken Sie auf „Ja“, um den Test zu beenden, oder auf
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
