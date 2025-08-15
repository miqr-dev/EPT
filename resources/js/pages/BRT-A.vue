<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
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

const page = usePage<{ auth: { user: { name: string } } }>();
const userName = computed(() => page.props.auth?.user?.name ?? '');

const emit = defineEmits(['complete']);

function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–";
  if (sec < 60) return `${sec} Sekunden`;
  const min = Math.round(sec / 60);
  return `${min} Minuten`;
}


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

// Normtables
const rohwertToPR: Record<number, number> = {
  1: 0, 2: 0, 3: 2.5, 4: 2.5, 5: 5, 6: 8.5, 7: 16,
  8: 27, 9: 34, 10: 53, 11: 62, 12: 75, 13: 85, 14: 95, 15: 99, 16: 100,
};
const prToTwert = [
  { pr: 0, t: 30 }, { pr: 2, t: 30 }, { pr: 5, t: 34 }, { pr: 7, t: 35 }, { pr: 8, t: 36 },
  { pr: 16, t: 40 }, { pr: 27, t: 44 }, { pr: 34, t: 46 }, { pr: 53, t: 50 },
  { pr: 62, t: 53 }, { pr: 75, t: 56 }, { pr: 85, t: 60 }, { pr: 95, t: 66 },
  { pr: 99, t: 73 }, { pr: 100, t: 80 },
];
function getPRFromRohwert(rohwert: number): number {
  return rohwertToPR[rohwert] ?? 0;
}
function getTwertFromPR(pr: number): number {
  let best = prToTwert[0];
  for (const entry of prToTwert) {
    if (pr >= entry.pr) best = entry;
  }
  return best.t;
}

const isTestComplete = computed(() => currentQuestionIndex.value >= questions.value.length);

const finalScore = computed(() => {
  let correct = 0;
  questions.value.forEach((q, i) => {
    const user = (userAnswers.value[i] ?? "").trim().replace(",", ".").toLowerCase();
    const validAnswers = q.answers.map(a => a.trim().replace(",", ".").toLowerCase());
    if (validAnswers.includes(user)) {
      correct++;
    }
  });
  return correct;
});

const userPR = computed(() => getPRFromRohwert(finalScore.value));
const userTwert = computed(() => getTwertFromPR(userPR.value));
const totalTimeTaken = computed(() =>
  isTestComplete.value
    ? questionTimes.value.reduce((a, b) => a + b, 0)
    : null
);

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

const confirmEnd = () => {
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
  const payload = {
    assignment_id: (page.props as any)?.exam?.current_step?.id,
    answers: userAnswers.value,
    timings: questionTimes.value,
    raw_score: finalScore.value,
    pr: userPR.value,
    t_score: userTwert.value,
  };
  router.post('/tests/brt-a/results', payload, {
    onSuccess: () => emit('complete'),
  });
};

const cancelEnd = () => {
  window.dispatchEvent(new Event('cancel-finish'));
  endConfirmOpen.value = false;
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

function normalizeAnswer(answer: string): string {
  return answer
    .trim()
    .replace(",", ".")
    .replace(/[€%$]/g, "")
    .replace(/\s+/g, "")
    .toLowerCase();
}

function isCorrectAnswer(userAnswer: string | undefined, validAnswers: string[]): boolean {
  if (!userAnswer) return false;

  const normalizedUser = normalizeAnswer(userAnswer);
  const normalizedCorrectAnswers = validAnswers.map(normalizeAnswer);

  const userAsNumber = parseFloat(normalizedUser);
  const isNumeric = !isNaN(userAsNumber);

  return normalizedCorrectAnswers.some(correct => {
    const correctAsNumber = parseFloat(correct);
    if (isNumeric && !isNaN(correctAsNumber)) {
      // Numeric comparison with tolerance
      return Math.abs(userAsNumber - correctAsNumber) < 0.001;
    }
    // Fallback to string compare
    return normalizedUser === correct;
  });
}
</script>

<template>

  <Head title="Tests" />
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">BRT-A</h1>
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
                'bg-green-200 border-green-400 text-green-900': userAnswers[idx] && idx !== currentQuestionIndex,
                'bg-gray-100 border-gray-300 text-gray-900': !userAnswers[idx] && idx !== currentQuestionIndex,
              }" @click="jumpToQuestion(idx)" :disabled="isTestComplete || !showTest">
              <span class="w-8 h-8 flex items-center justify-center rounded-full border mr-2" :class="{
                'bg-blue-600 text-white border-blue-600': idx === currentQuestionIndex,
                'bg-green-400 text-white border-green-400': userAnswers[idx] && idx !== currentQuestionIndex,
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
          <h2 class="text-2xl font-bold mb-4">Willkommen zum Mathematik-Test</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            Dieser Test besteht aus {{ questions.length }} Fragen. Sie können während des Tests jederzeit zwischen den
            Fragen navigieren.
            Die benötigte Zeit pro Frage wird automatisch gemessen.
          </p>
          <Button @click="startTest" class="px-8 py-3 text-lg font-semibold rounded-xl shadow">
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
        <div v-else-if="isTestComplete" class="p-6 bg-background border rounded-lg">
          <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
          <div class="mb-6 w-full max-w-md">
            <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
              <tbody>
                <tr class="bg-muted/40">
                  <td class="font-semibold px-3 py-2 w-1/2">Rohwert</td>
                  <td class="px-3 py-2">{{ finalScore }} von {{ questions.length }}</td>
                </tr>
                <tr>
                  <td class="font-semibold px-3 py-2">Prozentrang (PR)</td>
                  <td class="px-3 py-2">{{ userPR }}</td>
                </tr>
                <tr class="bg-muted/40">
                  <td class="font-semibold px-3 py-2">T-Wert (Normwert)</td>
                  <td class="px-3 py-2">{{ userTwert }}</td>
                </tr>
                <tr>
                  <td class="font-semibold px-3 py-2">Benötigte Zeit</td>
                  <td class="px-3 py-2">
                    <span v-if="totalTimeTaken !== null" :class="totalTimeTaken > 1800 ? 'text-red-600 font-bold' : ''">
                      {{ formatTime(totalTimeTaken) }}
                    </span>
                    <span v-else>–</span>
                  </td>

                </tr>
              </tbody>
            </table>
          </div>

          <div>
            <h3 class="font-bold mb-2">Antwort- und Bearbeitungszeit je Frage</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm border rounded-lg shadow">
                <thead class="bg-muted/40">
                  <tr>
                    <th class="px-2 py-1 text-left font-semibold">#</th>
                    <th class="px-2 py-1 text-left font-semibold">Frage</th>
                    <th class="px-2 py-1 text-left font-semibold">Ihre Antwort</th>
                    <th class="px-2 py-1 text-left font-semibold">Richtige Antwort</th>
                    <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(q, idx) in questions" :key="idx"
                    :class="isCorrectAnswer(userAnswers[idx], q.answers) ? 'bg-green-50' : 'bg-red-50'">
                    <td class="px-2 py-1 font-medium text-muted-foreground">{{ idx + 1 }}</td>
                    <td class="px-2 py-1 align-top">
                      <span v-html="formatQuestionMark(q.text)"></span>
                      <div v-if="q.image" class="mt-1">
                        <img :src="q.image" alt="Fragebild" class="max-w-[90px] border rounded shadow" />
                      </div>
                    </td>
                    <td class="px-2 py-1">
                      <span class="font-mono">{{ userAnswers[idx] || '–' }}</span>
                    </td>
                    <td class="px-2 py-1">
                      <span class="font-mono">{{ q.answers.join(', ') }}</span>
                    </td>
                    <td class="px-2 py-1 text-right text-gray-500 font-mono min-w-[60px]">
                      {{ formatTime(questionTimes[idx]) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>

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
