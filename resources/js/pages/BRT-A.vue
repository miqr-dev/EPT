<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { deepClone } from '@/lib/deepClone';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';

const emit = defineEmits(['complete']);

interface BrtAProgressState {
  started: boolean;
  currentQuestionIndex: number;
  nextButtonClickCount: number;
  userAnswers: string[];
  questionTimes: number[];
  startTime: number | null;
}

const props = defineProps<{ initialState?: BrtAProgressState | null }>();

interface Question {
  text: string;
  answers: string[];
  image?: string;
}

const questions = ref<Question[]>([
  { text: "619020 – 541600 = ?", answers: ["77420"] },
  { text: "619020 = 174309 + ?", answers: ["444711"] },
  { text: "4 : 80 = ?", answers: ["0,05"] },
  { text: "0,2 · ____ = 0,1", answers: ["0,5", "1/2"] },
  { text: "1/3 : 1/2 = ?", answers: ["2/3"] },
  { text: "Verwandle 0,4 in einen gewöhnlichen Bruch.", answers: ["2/5", "4/10"] },
  { text: "Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 9,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?", answers: ["10500", "10500 g", "10500g", "10,5", "10,5Kg", "10,5kg", "10,5 Kg", "10,5 kg"] },
  { text: "Rechne um: Wie viel g sind 9 kg und 1 g?", answers: ["9001","9001g", "9001 g", "9001 G", "9001G"] },
  { text: "Wie viel Zinsen erbringen 1000 € zu 4 % in einem Jahr?", answers: ["40","40€", "40 €"] },
  { text: "Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 2 % Rabatt. Wie viel muss er bezahlen?", answers: ["1362,2","1362,20","1362,20 €", "1362,2 €", "1362,2€", "1362,20€"] },
  { text: "Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 2 cm dick sind?", answers: ["30"] },
  { text: "Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 15 Bretter aus dem Stamm schneidet?", answers: ["4", "4cm", "4 cm", "4 Cm", "4Cm"] },
  { text: "Berechne die Grundstücksgröße in m².", answers: ["930" , "930 m²", "930m²", "930 m2"], image: "/images/Math/Mathe1.png" },
  { text: "Das Rad hat einen Durchmesser von 0,6 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?", answers: ["188,4","188,4 m","188,4m"], image: "/images/Math/Mathe2.png" },
  { text: "√81 = ?", answers: ["9"] },
  { text: "10³ = ?", answers: ["1000"] },
]);

const showTest = ref(false);
const currentQuestionIndex = ref(0);
const nextButtonClickCount = ref(0);
const userAnswers = ref<string[]>(Array(questions.value.length).fill(''));
const answerInput = ref<InstanceType<typeof Input> | null>(null);
const questionTimes = ref<number[]>(Array(questions.value.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(questions.value.length).fill(null));
const startTime = ref<number | null>(null);
const endConfirmOpen = ref(false);

function formatQuestionMark(text: string): string {
  if (text.endsWith('?')) {
    return (
      text.slice(0, -1) +
      '<span class="font-bold text-red-600 text-lg">?</span>'
    );
  }
  return text;
}

const isTestComplete = computed(() => currentQuestionIndex.value >= questions.value.length);


const currentQuestion = computed(() =>
  currentQuestionIndex.value < questions.value.length
    ? questions.value[currentQuestionIndex.value]
    : null
);

const nextButtonText = computed(() =>
  nextButtonClickCount.value === 1 ? "Weiter (Bestätigen)" : "Weiter"
);
const isLastQuestion = computed(
  () => currentQuestionIndex.value === questions.value.length - 1
);

const commitCurrentQuestionTime = () => {
  const index = currentQuestionIndex.value;
  if (
    index >= 0 &&
    index < questions.value.length &&
    questionStartTimestamps.value[index]
  ) {
    const now = Date.now();
    questionTimes.value[index] += Math.round(
      (now - (questionStartTimestamps.value[index] as number)) / 1000
    );
    questionStartTimestamps.value[index] = null;
  }
};

const jumpToQuestion = (index: number) => {
  commitCurrentQuestionTime();
  currentQuestionIndex.value = index;
  nextButtonClickCount.value = 0;
};

const handleNextClick = () => {
  nextButtonClickCount.value++;
  if (nextButtonClickCount.value >= 2) {
    commitCurrentQuestionTime();
    if (currentQuestionIndex.value < questions.value.length - 1) {
      currentQuestionIndex.value++;
      nextButtonClickCount.value = 0;
    } else {
      // Complete test: move index past last question
      currentQuestionIndex.value = questions.value.length;
      nextButtonClickCount.value = 0;
    }
  }
};

const handlePrevClick = () => {
  commitCurrentQuestionTime();
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
    nextButtonClickCount.value = 0;
  }
};

const finishTest = () => {
  window.dispatchEvent(new Event('start-finish'));
  endConfirmOpen.value = true;
};

const confirmEnd = () => {
  commitCurrentQuestionTime();
  currentQuestionIndex.value = questions.value.length;
  nextButtonClickCount.value = 0;
  endConfirmOpen.value = false;

  const results = {
    answers: [...userAnswers.value],
    question_times: [...questionTimes.value],
  };
  emit('complete', results);
};

const cancelEnd = () => {
  window.dispatchEvent(new Event('cancel-finish'));
  endConfirmOpen.value = false;
};

function loadProgress(state?: BrtAProgressState | null) {
  if (!state) return;

  showTest.value = !!state.started;

  const totalQuestions = questions.value.length;

  const restoredAnswers = Array(totalQuestions).fill('');
  if (Array.isArray(state.userAnswers)) {
    state.userAnswers.slice(0, totalQuestions).forEach((answer, index) => {
      restoredAnswers[index] = answer ?? '';
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

  nextButtonClickCount.value = state.nextButtonClickCount ?? 0;
  startTime.value = typeof state.startTime === 'number' ? state.startTime : null;

  const index = typeof state.currentQuestionIndex === 'number'
    ? Math.min(Math.max(state.currentQuestionIndex, 0), totalQuestions)
    : 0;
  currentQuestionIndex.value = index;
}

function getProgress(): BrtAProgressState {
  commitCurrentQuestionTime();

  return {
    started: showTest.value,
    currentQuestionIndex: currentQuestionIndex.value,
    nextButtonClickCount: nextButtonClickCount.value,
    userAnswers: deepClone(userAnswers.value),
    questionTimes: deepClone(questionTimes.value),
    startTime: startTime.value,
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

// Per-question timer starter
watch(currentQuestionIndex, async (newIndex, oldIndex) => {
  const now = Date.now();
  if (
    typeof newIndex === 'number' &&
    newIndex >= 0 &&
    newIndex < questions.value.length
  ) {
    if (!questionStartTimestamps.value[newIndex]) {
      questionStartTimestamps.value[newIndex] = now;
    }
  }
  if (newIndex === 0 && startTime.value === null) {
    startTime.value = now;
  }
  if (newIndex !== oldIndex && newIndex < questions.value.length) {
    await nextTick();
    if (answerInput.value) {
      if (typeof (answerInput.value as any)?.focus === 'function') {
        (answerInput.value as any).focus();
      } else if ((answerInput.value as any)?.$el && typeof (answerInput.value as any)?.$el.focus === 'function') {
        (answerInput.value as any).$el.focus();
      }
    }
  }
}, { immediate: true });

const startTest = () => {
  showTest.value = true;
  currentQuestionIndex.value = 0;
  nextButtonClickCount.value = 0;
  userAnswers.value = Array(questions.value.length).fill('');
  questionTimes.value = Array(questions.value.length).fill(0);
  questionStartTimestamps.value = Array(questions.value.length).fill(null);
  startTime.value = null;
};

</script>

<template>
  <div v-bind="$attrs" class="p-4">
    <Head title="Tests" />
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold">BRT-A</h1>
    </div>
    <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4">
      <aside
        v-if="showTest"
        class="sticky top-8 flex h-fit w-64 flex-shrink-0 flex-col items-start space-y-2 py-4"
      >
        <h3 class="mb-2 pl-4 text-sm font-bold text-muted-foreground">Fragen</h3>
        <div class="flex w-full flex-col items-start space-y-1">
          <template v-for="(q, idx) in questions" :key="idx">
            <button
              class="flex w-full items-center space-x-2 rounded-lg border px-2 py-1 font-medium transition hover:bg-blue-50 focus:outline-none text-base"
              :class="{
                'bg-blue-600 text-white border-blue-600 hover:bg-blue-500': idx === currentQuestionIndex,
                'bg-gray-300 border-gray-400 text-gray-900': userAnswers[idx] && idx !== currentQuestionIndex,
                'bg-gray-100 border-gray-300 text-gray-900': !userAnswers[idx] && idx !== currentQuestionIndex,
              }"
              @click="jumpToQuestion(idx)"
              :disabled="isTestComplete || !showTest"
            >
              <span
                class="mr-2 flex h-8 w-8 items-center justify-center rounded-full border"
                :class="{
                  'bg-blue-600 text-white border-blue-600': idx === currentQuestionIndex,
                  'bg-gray-400 text-white border-gray-400': userAnswers[idx] && idx !== currentQuestionIndex,
                  'bg-gray-300 text-gray-600 border-gray-400': !userAnswers[idx] && idx !== currentQuestionIndex,
                }"
              >
                {{ idx + 1 }}
              </span>
              <span class="max-w-[130px] truncate text-left text-xs" :title="q.text">
                {{ q.text.length > 30 ? q.text.slice(0, 30) + '…' : q.text }}
              </span>
            </button>
          </template>
        </div>
      </aside>

      <div class="flex flex-1 flex-col gap-4">
        <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
          <h2 class="mb-4 text-2xl font-bold">Willkommen zum Berufsbezogenen Rechentest</h2>
          <p class="mb-6 max-w-xl text-center text-base">
            In diesem Verfahren finden Sie insgesamt {{ questions.length }} Rechenaufgaben, die zu lösen sind. Hierfür haben Sie
            35 Minuten Zeit. Halten Sie sich nicht zu lange an einer Aufgabe auf, wenn Sie sie nicht lösen können. Gehen Sie zur
            nächsten weiter.
          </p>
          <p>
            Wir wollen wissen, auf welcher Ebene Sie mit Ihren Kenntnissen stehen und wo wir Sie individuell fördern können.
          </p>
          <br />
          <p>Für Nebenrechnungen haben Sie einen zusätzlichen Block.</p>
          <p>Bitte notieren Sie vor Abgabe Ihres Blattes Ihren Namen und das heutige Datum darauf.</p>

          <Button @click="startTest" class="mt-6 rounded-xl px-8 py-3 text-lg font-semibold shadow">
            Test starten
          </Button>
        </div>

        <div v-else-if="!isTestComplete && currentQuestion" class="rounded-lg border bg-background p-6">
          <h2 class="mb-4 text-xl font-semibold">Frage {{ currentQuestionIndex + 1 }}:</h2>
          <p class="mb-6 text-lg" v-html="formatQuestionMark(currentQuestion.text)"></p>
          <div v-if="currentQuestion.image" class="mb-4">
            <img :src="currentQuestion.image" alt="Fragebild" class="max-w-xs rounded border shadow" />
          </div>
          <div class="w-full md:w-1/2">
            <Input
              ref="answerInput"
              type="text"
              v-model="userAnswers[currentQuestionIndex]"
              placeholder="Ihre Antwort"
              class="mb-2 w-full"
            />

            <div class="mt-2 flex flex-row justify-between">
              <Button @click="handlePrevClick" :disabled="currentQuestionIndex === 0" variant="outline">
                Zurück
              </Button>
              <Button v-if="isLastQuestion" @click="finishTest" variant="destructive">
                Test beenden
              </Button>
              <Button v-else @click="handleNextClick">
                {{ nextButtonText }}
              </Button>
            </div>
          </div>
          <p v-if="nextButtonClickCount === 1" class="mt-2 text-sm text-muted-foreground">
            Klicken Sie erneut auf "Weiter (Bestätigen)", um fortzufahren.
          </p>
        </div>

        <div v-else-if="isTestComplete"></div>

        <div v-else>
          <p>Fragen werden geladen...</p>
        </div>
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
