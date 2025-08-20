<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
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
const userName = computed(() => page.props.auth?.user?.name ?? '');
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

// --- For per-question state:
const userAnswers = ref<(string | null)[]>(Array(mrtQuestions.length).fill(null));
const questionTimes = ref<number[]>(Array(mrtQuestions.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(mrtQuestions.length).fill(null));
const startTime = ref<number | null>(null);

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
    const now = Date.now();
    if (
      qidx >= 0 &&
      qidx < mrtQuestions.length &&
      questionStartTimestamps.value[qidx]
    ) {
      questionTimes.value[qidx] += Math.round(
        (now - (questionStartTimestamps.value[qidx] as number)) / 1000
      );
      questionStartTimestamps.value[qidx] = null;
    }
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
  const now = Date.now();
  const qidx = currentQuestionIndex.value;
  if (
    qidx >= 0 &&
    qidx < mrtQuestions.length &&
    questionStartTimestamps.value[qidx]
  ) {
    questionTimes.value[qidx] += Math.round(
      (now - (questionStartTimestamps.value[qidx] as number)) / 1000
    );
    questionStartTimestamps.value[qidx] = null;
  }
  if (currentQuestionIndex.value < mrtQuestions.length - 1) {
    currentQuestionIndex.value++;
  }
};

const handlePrevClick = () => {
  const now = Date.now();
  const qidx = currentQuestionIndex.value;
  if (
    qidx >= 0 &&
    qidx < mrtQuestions.length &&
    questionStartTimestamps.value[qidx]
  ) {
    questionTimes.value[qidx] += Math.round(
      (now - (questionStartTimestamps.value[qidx] as number)) / 1000
    );
    questionStartTimestamps.value[qidx] = null;
  }
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

const confirmEnd = () => {
  const now = Date.now();
  const qidx = currentQuestionIndex.value;
  if (
    qidx >= 0 &&
    qidx < mrtQuestions.length &&
    questionStartTimestamps.value[qidx]
  ) {
    questionTimes.value[qidx] += Math.round(
      (now - (questionStartTimestamps.value[qidx] as number)) / 1000
    );
    questionStartTimestamps.value[qidx] = null;
  }
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

watch(currentQuestionIndex, async (newIndex, oldIndex) => {
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
  <Head title="Tests" />
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Tests</h1>
    </div>
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20 text-foreground">
      <div class="flex-1 flex flex-col gap-4">
        <!-- Start Test Screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum MRT-A Deutsch-Test</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            Dieser Test besteht aus {{ mrtQuestions.length }} Aufgaben. Wählen Sie jeweils die richtige Schreibweise.
            Die benötigte Zeit pro Aufgabe wird automatisch gemessen.
          </p>
          <Button @click="startTest" class="px-8 py-3 text-lg font-semibold rounded-xl shadow"
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
            <Button v-if="isLastQuestion" @click="finishTest" :disabled="!userAnswers[currentQuestionIndex]" variant="destructive">
              Test beenden
            </Button>
            <Button v-else @click="handleNextClick" :disabled="!userAnswers[currentQuestionIndex]">
              Weiter
            </Button>
          </div>
        </div>
        
        <!-- Test Results -->
        <div v-else-if="isTestComplete && showResults">
          <MrtAResult :results="calculatedResults" />
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