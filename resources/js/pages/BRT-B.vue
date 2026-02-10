<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
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
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';

const emit = defineEmits(['complete']);
const props = defineProps<{ timeRemainingSeconds?: number | null }>();

interface Question {
  text: string;
  answers: string[];
  image?: string;
}

const questions = ref<Question[]>([
  { text: "619020 – 540600 = ?", answers: ["78420"] },
  { text: "619020 = 170309 + ?", answers: ["448.711"] },
  { text: "4 : 50 = ?", answers: ["0,08"] },
  { text: "0,4 · ____ = 0,1", answers: ["0,25", "1/4"] },
  { text: "1/8 : 1/3 = ?", answers: ["3/8"] },
  { text: "Verwandle 0,6 in einen gewöhnlichen Bruch.", answers: ["6/10", "3/5"] },
  { text: "Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 6,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?", answers: ["7500", "7500 g", "7500g", "7,5", "7,5Kg", "7,5kg", "7,5 Kg", "7,5 kg"] },
  { text: "Rechne um: Wie viel g sind 5 kg und 1 g?", answers: ["5001","5001g", "5001 g", "5001 G", "5001G"] },
  { text: "Wie viel Zinsen erbringen 1000 € zu 2 % in einem Jahr?", answers: ["20","20€", "20 €"] },
  { text: "Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 3 % Rabatt. Wie viel muss er bezahlen?", answers: ["1348,3","1348,30","1348,30 €", "1348,3 €", "1348,3€", "1348,30€"] },
  { text: "Im Sägewerk können aus einem Baumstamm 30 Bretter von 2 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 3 cm dick sind?", answers: ["20","20 Bretter"] },
  { text: "Im Sägewerk können aus einem Baumstamm 30 Bretter von 2 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 10 Bretter aus dem Stamm schneidet?", answers: ["6", "6cm", "6 cm", "6 Cm", "6Cm"] },
  { text: "Berechne die Grundstücksgröße in m².", answers: ["840" , "840 m²", "840m²", "840 m2"], image: "/images/Math/MatheB1.png" },
  { text: "Das Rad hat einen Durchmesser von 0,3 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?", answers: ["94,2","94,2 m","94,2m"], image: "/images/Math/Mathe2.png" },
  { text: "√49 = ?", answers: ["7"] },
  { text: "10⁴ = ?", answers: ["10000"] },
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

const MAX_ANSWER_LENGTH = 9;
const MAX_DIGITS = 6;
const MAX_NON_DIGITS = 3;

const { isForcedFinish, forcedFinishCountdown, clearForcedFinish } = useTeacherForceFinish({
  isActive: () => showTest.value && !isTestComplete.value,
  onStart: () => {
    endConfirmOpen.value = true;
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

const jumpToQuestion = (index: number) => {
  const now = Date.now();
  if (
    currentQuestionIndex.value >= 0 &&
    currentQuestionIndex.value < questions.value.length &&
    questionStartTimestamps.value[currentQuestionIndex.value]
  ) {
    questionTimes.value[currentQuestionIndex.value] += Math.round(
      (now - (questionStartTimestamps.value[currentQuestionIndex.value] as number)) / 1000
    );
    questionStartTimestamps.value[currentQuestionIndex.value] = null;
  }
  currentQuestionIndex.value = index;
  nextButtonClickCount.value = 0;
};

const handleNextClick = () => {
  nextButtonClickCount.value++;
  if (nextButtonClickCount.value >= 2) {
    const now = Date.now();
    if (
      currentQuestionIndex.value >= 0 &&
      currentQuestionIndex.value < questions.value.length &&
      questionStartTimestamps.value[currentQuestionIndex.value]
    ) {
      questionTimes.value[currentQuestionIndex.value] += Math.round(
        (now - (questionStartTimestamps.value[currentQuestionIndex.value] as number)) / 1000
      );
      questionStartTimestamps.value[currentQuestionIndex.value] = null;
    }
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
  const now = Date.now();
  if (
    currentQuestionIndex.value >= 0 &&
    currentQuestionIndex.value < questions.value.length &&
    questionStartTimestamps.value[currentQuestionIndex.value]
  ) {
    questionTimes.value[currentQuestionIndex.value] += Math.round(
      (now - (questionStartTimestamps.value[currentQuestionIndex.value] as number)) / 1000
    );
    questionStartTimestamps.value[currentQuestionIndex.value] = null;
  }
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
    nextButtonClickCount.value = 0;
  }
};

const finishTest = () => {
  window.dispatchEvent(new Event('start-finish'));
  endConfirmOpen.value = true;
};

const sanitizeAnswer = (value: string): string => {
  let digits = 0;
  let nonDigits = 0;
  let sanitized = '';

  for (const char of value) {
    if (sanitized.length >= MAX_ANSWER_LENGTH) {
      break;
    }

    if (/\d/.test(char)) {
      if (digits >= MAX_DIGITS) {
        continue;
      }
      digits++;
      sanitized += char;
      continue;
    }

    if (nonDigits >= MAX_NON_DIGITS) {
      continue;
    }

    nonDigits++;
    sanitized += char;
  }

  return sanitized;
};

const handleAnswerInput = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const sanitized = sanitizeAnswer(input.value);
  userAnswers.value[currentQuestionIndex.value] = sanitized;
  input.value = sanitized;
};

function confirmEnd() {
  clearForcedFinish(false);
  const now = Date.now();
  if (
    currentQuestionIndex.value >= 0 &&
    currentQuestionIndex.value < questions.value.length &&
    questionStartTimestamps.value[currentQuestionIndex.value]
  ) {
    questionTimes.value[currentQuestionIndex.value] += Math.round(
      (now - (questionStartTimestamps.value[currentQuestionIndex.value] as number)) / 1000
    );
    questionStartTimestamps.value[currentQuestionIndex.value] = null;
  }
  currentQuestionIndex.value = questions.value.length;
  nextButtonClickCount.value = 0;
  endConfirmOpen.value = false;
  const results = {
    answers: [...userAnswers.value],
    question_times: [...questionTimes.value],
  };
  emit('complete', results);
}

const cancelEnd = () => {
  if (isForcedFinish.value) {
    return;
  }
  window.dispatchEvent(new Event('cancel-finish'));
  endConfirmOpen.value = false;
  clearForcedFinish(false);
};

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

  <Head title="Tests" />
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">BRT-B</h1>
    </div>
    <div class="mb-4">
      <TimeRemainingAlerts :time-remaining-seconds="props.timeRemainingSeconds" />
    </div>
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20">
      <!-- Sidebar Navigation: Only visible during the test -->
      <aside v-if="showTest" class="w-64 flex-shrink-0 flex flex-col items-start space-y-2  py-4 h-fit sticky top-8">
        <h3 class="font-bold mb-2 text-sm text-muted-foreground pl-4">Fragen</h3>
        <div class="flex flex-col space-y-1 w-full items-start">
          <template v-for="(q, idx) in questions" :key="idx">
            <button class="w-full flex items-center space-x-2 py-1 px-2 rounded-lg font-medium border transition
               hover:bg-blue-50 focus:outline-none text-base" :class="{
                'bg-blue-600 text-white border-blue-600': idx === currentQuestionIndex,
                'hover:bg-blue-500': idx === currentQuestionIndex,
                'bg-gray-300 border-gray-400 text-gray-900': userAnswers[idx] && idx !== currentQuestionIndex,
                'bg-gray-100 border-gray-300 text-gray-900': !userAnswers[idx] && idx !== currentQuestionIndex,
              }" @click="jumpToQuestion(idx)" :disabled="isTestComplete || !showTest">
              <span class="w-8 h-8 flex items-center justify-center rounded-full border mr-2" :class="{
                'bg-blue-600 text-white border-blue-600': idx === currentQuestionIndex,
                'bg-gray-400 text-white border-gray-400': userAnswers[idx] && idx !== currentQuestionIndex,
                'bg-gray-300 text-gray-600 border-gray-400': !userAnswers[idx] && idx !== currentQuestionIndex,
              }">
                {{ idx + 1 }}
              </span>
              <span class="truncate max-w-[130px] text-left text-xs" :title="q.text">
                {{ q.text.length > 30 ? q.text.slice(0, 30) + '…' : q.text }}
              </span>
            </button>
          </template>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col gap-4">
        <!-- Start Test Screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum Berufsbezogenen Rechentest</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            In diesem Verfahren finden Sie insgesamt {{ questions.length }} Rechenaufgaben, die zu lösen sind. Hierfür haben Sie 35 Minuten Zeit. Halten Sie sich nicht zu lange an einer Aufgabe auf, wenn Sie sie nicht lösen können. Gehen Sie zur nächsten weiter.</p>
            <p> Wir wollen wissen, auf welcher Ebene Sie mit Ihren Kenntnissen stehen und wo wir Sie individuell fördern können. </p>
            <br>
            <p>
            Für Nebenrechnungen haben Sie einen zusätzlichen Block.</p>
            <p>Bitte notieren Sie vor Abgabe Ihres Blattes Ihren Namen und das heutige Datum darauf.
            </p>
            
          <Button @click="startTest" class="px-8 py-3 text-lg mt-6 font-semibold rounded-xl shadow">
            Test starten
          </Button>
        </div>

        <!-- Test Content -->
        <div v-else-if="!isTestComplete && currentQuestion" class="p-6 bg-background border rounded-lg">
          <h2 class="text-xl font-semibold mb-4">Frage {{ currentQuestionIndex + 1 }}:</h2>
          <p class="text-lg mb-6" v-html="formatQuestionMark(currentQuestion.text)"></p>
          <div v-if="currentQuestion.image" class="mb-4">
            <img :src="currentQuestion.image" alt="Fragebild" class="max-w-xs border rounded shadow" />
          </div>
          <div class="w-full md:w-1/2">
            <Input ref="answerInput" type="text" v-model="userAnswers[currentQuestionIndex]" placeholder="Ihre Antwort"
              maxlength="9"
              @input="handleAnswerInput"
              class="mb-2 w-full" />

            <div class="flex flex-row justify-between mt-2">
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
          <p v-if="nextButtonClickCount === 1" class="text-sm text-muted-foreground mt-2">
            Klicken Sie erneut auf "Weiter (Bestätigen)", um fortzufahren.
          </p>
        </div>
        <!-- Test Results -->
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
          <DialogDescription v-if="!isForcedFinish">
            Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück.
          </DialogDescription>
          <DialogDescription v-else>
            Der Test wird automatisch in {{ forcedFinishCountdown }} Sekunden beendet.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <Button v-if="!isForcedFinish" variant="secondary" @click="cancelEnd">Abbrechen</Button>
          <Button variant="destructive" @click="confirmEnd">
            {{ isForcedFinish ? 'Jetzt beenden' : 'Ja' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
</div>
</template>
