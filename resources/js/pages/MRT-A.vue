<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import MrtAResult from '@/components/MrtAResult.vue';
import { useMrtA } from '@/composables/useMrtA';
import { deepClone } from '@/lib/deepClone';

const { mrtQuestions, calculateScores } = useMrtA();

const page = usePage<{
  auth: {
    user: {
      name: string;
      participant_profile?: {
        age?: number;
      };
    };
  };
}>();
const profile = computed(() => page.props.auth?.user?.participant_profile);
const userAge = computed<number | null>(() => {
  const age = profile.value?.age;
  return typeof age === 'number' ? age : age ? Number(age) : null;
});
const emit = defineEmits(['complete']);
const endConfirmOpen = ref(false);

const showResults = ref(false);

const showTest = ref(false);
const currentQuestionIndex = ref(0);
const isLastQuestion = computed(() => currentQuestionIndex.value === mrtQuestions.length - 1);

interface MrtAProgressState {
  started: boolean;
  currentQuestionIndex: number;
  userAnswers: (string | null)[];
  questionTimes: number[];
  tempSelected: (string | null)[];
  tempClickState: boolean[];
  startTime: number | null;
  showResults: boolean;
}

const props = defineProps<{ initialState?: MrtAProgressState | null }>();

// --- For per-question state:
const userAnswers = ref<(string | null)[]>(Array(mrtQuestions.length).fill(null));
const questionTimes = ref<number[]>(Array(mrtQuestions.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(mrtQuestions.length).fill(null));
const startTime = ref<number | null>(null);

const commitQuestionTime = (index: number) => {
  if (
    index >= 0 &&
    index < mrtQuestions.length &&
    questionStartTimestamps.value[index]
  ) {
    const now = Date.now();
    questionTimes.value[index] += Math.round(
      (now - (questionStartTimestamps.value[index] as number)) / 1000
    );
    questionStartTimestamps.value[index] = null;
  }
};

const isTestComplete = computed(() => currentQuestionIndex.value >= mrtQuestions.length);


const totalTimeTaken = computed(() =>
  isTestComplete.value
    ? questionTimes.value.reduce((a, b) => a + b, 0)
    : null
);
const currentQuestion = computed(() =>
  currentQuestionIndex.value < mrtQuestions.length
    ? mrtQuestions[currentQuestionIndex.value]
    : null
);

// --- Option selection state per question ---
const tempSelected = ref<(string | null)[]>(Array(mrtQuestions.length).fill(null));
const tempClickState = ref<(boolean)[]>(Array(mrtQuestions.length).fill(false)); // false = first click, true = need confirm

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
    commitQuestionTime(qidx);
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
  const qidx = currentQuestionIndex.value;
  commitQuestionTime(qidx);
  if (currentQuestionIndex.value < mrtQuestions.length - 1) {
    currentQuestionIndex.value++;
  }
};

const handlePrevClick = () => {
  const qidx = currentQuestionIndex.value;
  commitQuestionTime(qidx);
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
};

function loadProgress(state?: MrtAProgressState | null) {
  if (!state) return;

  const totalQuestions = mrtQuestions.length;

  const restoredAnswers = Array(totalQuestions).fill(null);
  if (Array.isArray(state.userAnswers)) {
    state.userAnswers.slice(0, totalQuestions).forEach((answer, index) => {
      restoredAnswers[index] = typeof answer === 'string' ? answer : null;
    });
  }
  userAnswers.value = restoredAnswers;

  const restoredTimes = Array(totalQuestions).fill(0);
  if (Array.isArray(state.questionTimes)) {
    state.questionTimes.slice(0, totalQuestions).forEach((time, index) => {
      restoredTimes[index] = typeof time === 'number' ? time : 0;
    });
  }
  questionTimes.value = restoredTimes;
  questionStartTimestamps.value = Array(totalQuestions).fill(null);

  const restoredTempSelected = Array(totalQuestions).fill(null);
  if (Array.isArray(state.tempSelected)) {
    state.tempSelected.slice(0, totalQuestions).forEach((value, index) => {
      restoredTempSelected[index] = typeof value === 'string' ? value : null;
    });
  }
  tempSelected.value = restoredTempSelected;

  const restoredTempClickState = Array(totalQuestions).fill(false);
  if (Array.isArray(state.tempClickState)) {
    state.tempClickState.slice(0, totalQuestions).forEach((value, index) => {
      restoredTempClickState[index] = !!value;
    });
  }
  tempClickState.value = restoredTempClickState;

  startTime.value = typeof state.startTime === 'number' ? state.startTime : null;

  showResults.value = !!state.showResults;
  showTest.value = !!state.started && !showResults.value;

  const index = typeof state.currentQuestionIndex === 'number'
    ? Math.min(Math.max(state.currentQuestionIndex, 0), totalQuestions)
    : 0;
  currentQuestionIndex.value = index;
}

function getProgress(): MrtAProgressState {
  commitQuestionTime(currentQuestionIndex.value);

  return {
    started: showTest.value,
    currentQuestionIndex: currentQuestionIndex.value,
    userAnswers: deepClone(userAnswers.value),
    questionTimes: deepClone(questionTimes.value),
    tempSelected: deepClone(tempSelected.value),
    tempClickState: deepClone(tempClickState.value),
    startTime: startTime.value,
    showResults: showResults.value,
  };
}

watch(
  () => props.initialState,
  (state) => {
    loadProgress(state ?? null);
  },
  { immediate: true, deep: true },
);

defineExpose({
  getProgress,
  loadProgress,
});

const confirmEnd = () => {
  const qidx = currentQuestionIndex.value;
  commitQuestionTime(qidx);
  currentQuestionIndex.value = mrtQuestions.length;
  endConfirmOpen.value = false;
  showResults.value = true;
  emit('complete', calculatedResults.value);
};

const calculatedResults = computed(() => {
  if (!isTestComplete.value) return null;

  const scoreData = calculateScores(
    userAnswers.value.map(ans => ({ user_answer: ans })),
    userAge.value
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

watch(currentQuestionIndex, async (newIndex) => {
  const now = Date.now();
  if (
    typeof newIndex === 'number' &&
    newIndex >= 0 &&
    newIndex < mrtQuestions.length
  ) {
    if (!questionStartTimestamps.value[newIndex]) {
      questionStartTimestamps.value[newIndex] = now;
    }
  }
  if (newIndex === 0 && startTime.value === null) {
    startTime.value = now;
  }
  // Reset temp selection for new question
  tempSelected.value[newIndex] = null;
  tempClickState.value[newIndex] = false;
}, { immediate: true });

const startTest = () => {
  showTest.value = true;
  currentQuestionIndex.value = 0;
  userAnswers.value = Array(mrtQuestions.length).fill(null);
  tempSelected.value = Array(mrtQuestions.length).fill(null);
  tempClickState.value = Array(mrtQuestions.length).fill(false);
  questionTimes.value = Array(mrtQuestions.length).fill(0);
  questionStartTimestamps.value = Array(mrtQuestions.length).fill(null);
  startTime.value = null;
};

</script>

<template>
  <div v-bind="$attrs" class="p-4">
    <Head title="Tests" />
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">MRT-A</h1>
    </div>
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20 text-foreground">
      <div class="flex-1 flex flex-col gap-4">
        <!-- Start Test Screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum Mannheimer Rechtschreibtest</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            Mit diesem Verfahren wollen wir wissen, wie Ihre Rechtschreibkenntnisse sind. Jede Aufgabe enthält 4 Wörter.
            Wählen Sie das richtig geschriebene Wort aus.
          </p>
          <p class="font-semibold">Beispielaufgabe:</p>
          <!-- Images column -->
          <div class="flex flex-col items-center gap-6 my-6">
            <img src="/images/MRT/woerter_empty.PNG" alt="Beispiel falsch"
              class="w-[600px] h-[150px] object-contain rounded shadow" />
            <img src="/images/MRT/woerter_selectedPNG.PNG" alt="Beispiel richtig"
              class="w-[600px] h-[150px] object-contain rounded shadow" />
          </div>
          <p>Dieses klicken Sie an und bestätigen es mit einem weiteren Klick. Es gilt die alte und neue Rechtschreibung.
          </p>
          <Button @click="startTest" class="px-8 py-3 mt-6 text-lg font-semibold rounded-xl shadow"
            :disabled="!userAge">
            Test starten
          </Button>
        </div>

        <!-- Test Content -->
        <div v-else-if="!isTestComplete && currentQuestion" class="p-6 bg-background border rounded-lg">
          <h2 class="text-xl font-semibold mb-4">Frage {{ currentQuestionIndex + 1 }}:</h2>
          <div class="flex flex-row gap-4 mb-6">
            <div v-for="(option, oidx) in currentQuestion.options" :key="oidx" class="flex flex-col items-center">
              <button class="px-4 py-3 rounded-xl border transition text-base font-sans font-semibold min-w-[120px]"
                :class="[
                  userAnswers[currentQuestionIndex] === String.fromCharCode(65 + oidx)
                    ? 'bg-blue-300 border-blue-700 text-blue-900 dark:bg-blue-900 dark:text-blue-100'
                    : tempSelected[currentQuestionIndex] === String.fromCharCode(65 + oidx)
                      ? 'bg-blue-100 border-blue-500 text-blue-900 dark:bg-blue-800 dark:border-blue-600 dark:text-blue-100'
                      : 'bg-white border-gray-300 hover:bg-blue-50 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-blue-900',
                  'focus:outline-none',
                  userAnswers[currentQuestionIndex] ? 'cursor-default opacity-60' : 'cursor-pointer'
                ]" @click="handleOptionClick(oidx)" :disabled="false">
                <!-- *** NO LETTERS, JUST OPTION *** -->
                <span class="font-sans select-none">{{ option }}</span>
              </button>
              <span
                v-if="tempSelected[currentQuestionIndex] === String.fromCharCode(65 + oidx) && tempClickState[currentQuestionIndex] && !userAnswers[currentQuestionIndex]"
                class="mt-2 text-xs text-blue-700 font-semibold">
                Klicken Sie erneut zum Bestätigen
              </span>
            </div>
          </div>

          <div class="flex flex-row justify-between mt-4">
            <Button @click="handlePrevClick" :disabled="currentQuestionIndex === 0" variant="outline">
              Zurück
            </Button>
            <Button v-if="isLastQuestion" @click="finishTest" :disabled="!userAnswers[currentQuestionIndex]"
              variant="destructive">
              Test beenden
            </Button>
            <Button v-else @click="handleNextClick" :disabled="!userAnswers[currentQuestionIndex]">
              Weiter
            </Button>
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
          <DialogDescription>
            Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <Button variant="secondary" @click="cancelEnd">Abbrechen</Button>
          <Button variant="destructive" @click="confirmEnd">Ja</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>