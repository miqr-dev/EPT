<script setup lang="ts">
import MrtAResult from '@/components/MrtAResult.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useMrtA } from '@/composables/useMrtA';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const { mrtQuestions, calculateScores } = useMrtA();

type ParticipantPageUser = {
    participant_profile?: {
        age?: number | string | null;
    } | null;
};

const page = usePage();
const profile = computed(() => (page.props.auth?.user as ParticipantPageUser | undefined)?.participant_profile ?? null);
const userAge = computed<number | null>(() => {
    // Case 1: User has a participant profile with an age.
    if (profile.value && typeof profile.value.age === 'number') {
        return profile.value.age;
    }
    if (profile.value && profile.value.age) {
        return Number(profile.value.age);
    }

    // Case 2: User does NOT have a participant profile (is an admin/tester).
    if (!profile.value) {
        // Generate a random age for non-participants.
        return Math.floor(Math.random() * (50 - 20 + 1)) + 20;
    }

    // Case 3: User has a participant profile but no age.
    // In this case, we do not generate a random age, preserving the original behavior for participants.
    return null;
});
const props = defineProps<{
    pausedTestResult?: {
        answers: { user_answer: string | null; time_seconds: number }[];
        currentQuestionIndex?: number;
    };
    timeRemainingSeconds?: number | null;
}>();

const emit = defineEmits(['complete', 'update:answers', 'started']);
const endConfirmOpen = ref(false);

const { isForcedFinish, clearForcedFinish } = useTeacherForceFinish({
    isActive: () => showTest.value && !isTestComplete.value,
    onStart: () => {
        window.dispatchEvent(new Event('start-finish'));
    },
    onCountdownFinished: () => {
        confirmEnd();
    },
    onCancel: () => {
        if (endConfirmOpen.value) {
            window.dispatchEvent(new Event('cancel-finish'));
            endConfirmOpen.value = false;
        }
    },
});

const showResults = ref(false);

const showTest = ref(false);
const currentQuestionIndex = ref(0);
const isLastQuestion = computed(() => currentQuestionIndex.value === mrtQuestions.length - 1);
const isTestComplete = computed(() => currentQuestionIndex.value >= mrtQuestions.length);

// --- For per-question state:
const userAnswers = ref<(string | null)[]>(Array(mrtQuestions.length).fill(null));
const questionTimes = ref<number[]>(Array(mrtQuestions.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(mrtQuestions.length).fill(null));
const startTime = ref<number | null>(null);

function stopTimingQuestion(index: number) {
    if (index < 0 || index >= mrtQuestions.length) {
        return;
    }

    const start = questionStartTimestamps.value[index];
    if (!start) {
        return;
    }

    questionTimes.value[index] += Math.round((Date.now() - start) / 1000);
    questionStartTimestamps.value[index] = null;
}

function startTimingQuestion(index: number) {
    if (!showTest.value || isTestComplete.value || index < 0 || index >= mrtQuestions.length) {
        return;
    }

    if (!questionStartTimestamps.value[index]) {
        questionStartTimestamps.value[index] = Date.now();
    }
}

function currentQuestionTimes() {
    const times = [...questionTimes.value];
    const index = currentQuestionIndex.value;
    const start = questionStartTimestamps.value[index];

    if (showTest.value && !isTestComplete.value && index >= 0 && index < mrtQuestions.length && start) {
        times[index] += Math.round((Date.now() - start) / 1000);
    }

    return times;
}

function buildProgressPayload() {
    const liveQuestionTimes = currentQuestionTimes();

    return {
        answers: mrtQuestions.map((q, i) => ({
            user_answer: userAnswers.value[i],
            time_seconds: liveQuestionTimes[i],
        })),
        currentQuestionIndex: currentQuestionIndex.value,
        total_time_seconds: liveQuestionTimes.reduce((total, seconds) => total + seconds, 0),
    };
}

function emitProgress() {
    if (!showTest.value || isTestComplete.value) {
        return;
    }

    emit('update:answers', buildProgressPayload());
}

if (props.pausedTestResult) {
    if (props.pausedTestResult.answers) {
        props.pausedTestResult.answers.forEach((a, i) => {
            userAnswers.value[i] = a.user_answer;
            questionTimes.value[i] = Number(a.time_seconds ?? 0);
        });
    }
    if (typeof props.pausedTestResult.currentQuestionIndex === 'number') {
        currentQuestionIndex.value = Math.min(Math.max(props.pausedTestResult.currentQuestionIndex, 0), mrtQuestions.length - 1);
    }
    showTest.value = true;
    startTimingQuestion(currentQuestionIndex.value);
}

watch([userAnswers, questionTimes, currentQuestionIndex], () => emitProgress(), { deep: true, immediate: true });

let progressEmitInterval: number | null = null;

onMounted(() => {
    progressEmitInterval = window.setInterval(() => {
        emitProgress();
    }, 1000);
});

onUnmounted(() => {
    if (progressEmitInterval !== null) {
        window.clearInterval(progressEmitInterval);
    }
});

const totalTimeTaken = computed(() => (isTestComplete.value ? questionTimes.value.reduce((a, b) => a + b, 0) : null));
const currentQuestion = computed(() => (currentQuestionIndex.value < mrtQuestions.length ? mrtQuestions[currentQuestionIndex.value] : null));

// --- Option selection state per question ---
const tempSelected = ref<(string | null)[]>(Array(mrtQuestions.length).fill(null));
const tempClickState = ref<boolean[]>(Array(mrtQuestions.length).fill(false)); // false = first click, true = need confirm

// -------------- LOGIC ---------------
const handleOptionClick = (optIdx: number) => {
    const qidx = currentQuestionIndex.value;
    const letter = String.fromCharCode(65 + optIdx);

    if (!tempClickState.value[qidx]) {
        // First click: mark as temp, wait for confirm
        tempSelected.value[qidx] = letter;
        tempClickState.value[qidx] = true;
    } else if (tempSelected.value[qidx] === letter) {
        // Confirm: set as answer and auto-next!
        userAnswers.value[qidx] = letter;
        tempClickState.value[qidx] = false;
        tempSelected.value[qidx] = null;

        // --- After confirming, record time and auto-jump ---
        stopTimingQuestion(qidx);
        if (currentQuestionIndex.value < mrtQuestions.length - 1) {
            currentQuestionIndex.value++;
        }
    } else {
        // Clicked a different option, reset temp selection
        tempSelected.value[qidx] = letter;
        tempClickState.value[qidx] = true;
    }
};

const handleNextClick = () => {
    stopTimingQuestion(currentQuestionIndex.value);
    if (currentQuestionIndex.value < mrtQuestions.length - 1) {
        currentQuestionIndex.value++;
    }
};

const handlePrevClick = () => {
    stopTimingQuestion(currentQuestionIndex.value);
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
};

const finishTest = () => {
    window.dispatchEvent(new Event('start-finish'));
    endConfirmOpen.value = true;
};

const cancelEnd = () => {
    window.dispatchEvent(new Event('cancel-finish'));
    endConfirmOpen.value = false;
    clearForcedFinish(false);
};

function confirmEnd() {
    clearForcedFinish(false);
    stopTimingQuestion(currentQuestionIndex.value);
    currentQuestionIndex.value = mrtQuestions.length;
    endConfirmOpen.value = false;
    showResults.value = true;
    emit('complete', calculatedResults.value);
}

const calculatedResults = computed(() => {
    if (!isTestComplete.value) return null;

    const scoreData = calculateScores(
        userAnswers.value.map((ans) => ({ user_answer: ans })),
        userAge.value,
    );

    return {
        ...scoreData,
        total_time_seconds: totalTimeTaken.value,
        answers: scoreData.answers.map((ans, i) => ({
            ...ans,
            time_seconds: questionTimes.value[i],
        })),
    };
});

watch(
    currentQuestionIndex,
    async (newIndex, oldIndex) => {
        const now = Date.now();
        startTimingQuestion(newIndex);
        if (showTest.value && newIndex === 0 && startTime.value === null) {
            startTime.value = now;
        }
        // Reset temp selection for new question
        if (newIndex >= 0 && newIndex < mrtQuestions.length) {
            tempSelected.value[newIndex] = null;
            tempClickState.value[newIndex] = false;
        }
    },
    { immediate: true },
);

const startTest = () => {
    emit('started');
    showTest.value = true;
    currentQuestionIndex.value = 0;
    userAnswers.value = Array(mrtQuestions.length).fill(null);
    tempSelected.value = Array(mrtQuestions.length).fill(null);
    tempClickState.value = Array(mrtQuestions.length).fill(false);
    questionTimes.value = Array(mrtQuestions.length).fill(0);
    questionStartTimestamps.value = Array(mrtQuestions.length).fill(null);
    startTime.value = Date.now();
    startTimingQuestion(0);
    emitProgress();
};
</script>

<template>
    <Head title="Tests" />
    <div class="p-4">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">MRT-A</h1>
        </div>
        <div class="mb-4"></div>
        <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4 text-foreground">
            <div class="flex flex-1 flex-col gap-4">
                <!-- Start Test Screen -->
                <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
                    <h2 class="mb-4 text-2xl font-bold">Willkommen zum Mannheimer Rechtschreibtest</h2>
                    <p class="mb-6 max-w-xl text-center text-base">
                        Mit diesem Verfahren wollen wir wissen, wie Ihre Rechtschreibkenntnisse sind. Jede Aufgabe enthält vier Wörter. Wählen Sie das
                        richtig geschriebene Wort aus.
                    </p>
                    <p class="font-semibold">Beispielaufgabe:</p>
                    <!-- Images column -->
                    <div class="my-6 flex flex-col items-center gap-6">
                        <img src="/images/MRT/woerter_empty.PNG" alt="Beispiel falsch" class="h-[150px] w-[600px] rounded object-contain shadow" />
                        <img
                            src="/images/MRT/woerter_selectedPNG.PNG"
                            alt="Beispiel richtig"
                            class="h-[150px] w-[600px] rounded object-contain shadow"
                        />
                    </div>
                    <p>Dieses klicken Sie an und bestätigen es mit einem weiteren Klick. Es gilt die alte und neue Rechtschreibung.</p>
                    <Button @click="startTest" class="mt-6 rounded-xl px-8 py-3 text-lg font-semibold shadow" :disabled="!userAge">
                        Test starten
                    </Button>
                </div>

                <!-- Test Content -->
                <div v-else-if="!isTestComplete && currentQuestion" class="rounded-lg border bg-background p-6">
                    <h2 class="mb-4 text-xl font-semibold">Frage {{ currentQuestionIndex + 1 }}:</h2>
                    <div class="mb-6 flex flex-row gap-4">
                        <div v-for="(option, oidx) in currentQuestion.options" :key="oidx" class="flex flex-col items-center">
                            <button
                                class="min-w-[120px] rounded-xl border px-4 py-3 font-sans text-base font-semibold transition"
                                :class="[
                                    userAnswers[currentQuestionIndex] === String.fromCharCode(65 + oidx)
                                        ? 'border-blue-700 bg-blue-300 text-blue-900 dark:bg-blue-900 dark:text-blue-100'
                                        : tempSelected[currentQuestionIndex] === String.fromCharCode(65 + oidx)
                                          ? 'border-blue-500 bg-blue-100 text-blue-900 dark:border-blue-600 dark:bg-blue-800 dark:text-blue-100'
                                          : 'border-gray-300 bg-white hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-blue-900',
                                    'focus:outline-none',
                                    userAnswers[currentQuestionIndex] ? 'cursor-default opacity-60' : 'cursor-pointer',
                                ]"
                                @click="handleOptionClick(oidx)"
                                :disabled="false"
                            >
                                <!-- *** NO LETTERS, JUST OPTION *** -->
                                <span class="font-sans select-none">{{ option }}</span>
                            </button>
                            <span
                                v-if="
                                    tempSelected[currentQuestionIndex] === String.fromCharCode(65 + oidx) &&
                                    tempClickState[currentQuestionIndex] &&
                                    !userAnswers[currentQuestionIndex]
                                "
                                class="mt-2 text-xs font-semibold text-blue-700"
                            >
                                Klicken Sie erneut zum Bestätigen
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-row justify-between">
                        <Button @click="handlePrevClick" :disabled="currentQuestionIndex === 0" variant="outline"> Zurück </Button>
                        <Button v-if="isLastQuestion" @click="finishTest" :disabled="!userAnswers[currentQuestionIndex]" variant="destructive">
                            Test beenden
                        </Button>
                        <Button v-else @click="handleNextClick" :disabled="!userAnswers[currentQuestionIndex]"> Weiter </Button>
                    </div>
                </div>

                <!-- Test Results -->
                <div v-else-if="isTestComplete && showResults">
                    <MrtAResult class="invisible" :results="calculatedResults" />
                </div>

                <div v-else></div>
            </div>
        </div>
        <Dialog v-model:open="endConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Test beenden</DialogTitle>
                    <DialogDescription> Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück. </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="secondary" @click="cancelEnd">Abbrechen</Button>
                    <Button variant="destructive" @click="confirmEnd">Ja</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
