<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

function formatQuestionMark(text: string): string {
  if (text.endsWith('?')) {
    return (
      text.slice(0, -1) +
      '<span class="font-bold text-red-600 text-lg">?</span>'
    );
  }
  return text;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tests', href: '/tests' },
];

interface Question {
  text: string;
  answer: string;
  image?: string;
}

const questions = ref<Question[]>([
  { text: "619020 – 541600 = ?", answer: "77420" },
  { text: "619020 = 174309 + ?", answer: "444711" },
  { text: "4 : 80 = ?", answer: "0.05" },
  { text: "0,2 · ____ = 0,1", answer: "0.5" },
  { text: "1/3 : 1/2 = ?", answer: "2/3" },
  { text: "Verwandle 0,4 in einen gewöhnlichen Bruch.", answer: "2/5" },
  { text: "Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 9,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?", answer: "10500" },
  { text: "Rechne um: Wie viel g sind 9 kg und 1 g?", answer: "9001" },
  { text: "Wie viel Zinsen erbringen 1000 € zu 4 % in einem Jahr?", answer: "40" },
  { text: "Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 2 % Rabatt. Wie viel muss er bezahlen?", answer: "1362.2" },
  { text: "Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 2 cm dick sind?", answer: "30" },
  { text: "Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 15 Bretter aus dem Stamm schneidet?", answer: "4" },
  {
    text: "Berechne die Grundstücksgröße in m².",
    answer: "60",
    image: "/images/Math/Mathe1.png"
  },
  {
    text: "Das Rad hat einen Durchmesser von 0,6 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?",
    answer: "188.5",
    image: "/images/Math/Mathe2.png"
  },
  { text: "√81 = ?", answer: "9" },
  { text: "10³ = ?", answer: "1000" },
]);

const showTest = ref(false); // Controls when to show the test (after clicking Start)

const currentQuestionIndex = ref(0);
const nextButtonClickCount = ref(0);
const userAnswers = ref<string[]>(Array(questions.value.length).fill(''));
const answerInput = ref<InstanceType<typeof Input> | null>(null);
const score = ref(0);
const startTime = ref<number | null>(null);
const totalTimeTaken = ref<number | null>(null);

// Track per-question time (seconds) and when timing started for each
const questionTimes = ref<number[]>(Array(questions.value.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(questions.value.length).fill(null));

const currentQuestion = computed(() => {
  if (currentQuestionIndex.value < questions.value.length) {
    return questions.value[currentQuestionIndex.value];
  }
  return null;
});

const nextButtonText = computed(() => {
  if (nextButtonClickCount.value === 1) {
    return "Weiter (Bestätigen)";
  }
  return "Weiter";
});

// --- Navigation logic ---
const jumpToQuestion = (index: number) => {
  // Stop timing the current question before leaving it
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
// ---

const handleNextClick = () => {
  nextButtonClickCount.value++;
  if (nextButtonClickCount.value >= 2) {
    // Check answer before moving on
    if (currentQuestionIndex.value < questions.value.length) {
      const userAnswer = userAnswers.value[currentQuestionIndex.value]?.trim().replace(",", ".");
      const correctAnswer = questions.value[currentQuestionIndex.value].answer.trim().replace(",", ".");
      if (userAnswer === correctAnswer) {
        score.value++;
      }
    }

    // Stop timing the current question
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
      // Test completed!
      currentQuestionIndex.value = questions.value.length;
      nextButtonClickCount.value = 0;
      if (startTime.value) {
        totalTimeTaken.value = Math.round((now - startTime.value) / 1000);
      }
    }
  }
};

const handlePrevClick = () => {
  // Stop timing the current question before leaving it
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

const isTestComplete = computed(() => {
  return currentQuestionIndex.value >= questions.value.length;
});

const percentageScore = computed(() => {
  if (questions.value.length === 0) return 0;
  return Math.round((score.value / questions.value.length) * 100);
});

watch(currentQuestionIndex, async (newIndex, oldIndex) => {
  const now = Date.now();

  // Start timing the new question
  if (
    typeof newIndex === 'number' &&
    newIndex >= 0 &&
    newIndex < questions.value.length
  ) {
    // Only start if not already timing
    if (!questionStartTimestamps.value[newIndex]) {
      questionStartTimestamps.value[newIndex] = now;
    }
  }

  // Start main timer if it's the first question
  if (newIndex === 0 && startTime.value === null) {
    startTime.value = now;
  }

  // Focus logic (unchanged)
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

// Reset state if the test is started again (optional, e.g. for dev/testing)
const startTest = () => {
  showTest.value = true;
  currentQuestionIndex.value = 0;
  nextButtonClickCount.value = 0;
  userAnswers.value = Array(questions.value.length).fill('');
  score.value = 0;
  startTime.value = null;
  totalTimeTaken.value = null;
  questionTimes.value = Array(questions.value.length).fill(0);
  questionStartTimestamps.value = Array(questions.value.length).fill(null);
};

</script>

<template>

  <Head title="Tests" />
  <AppLayout :breadcrumbs="breadcrumbs">
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
            Fragen
            navigieren.
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
              <Button @click="handleNextClick">
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
          <div class="space-y-2">
            <p>Hier sind Ihre Ergebnisse:</p>
            <p>Richtige Antworten: {{ score }} von {{ questions.length }}</p>
            <p>Prozent: {{ percentageScore }}%</p>
            <p v-if="totalTimeTaken !== null">Benötigte Zeit: {{ totalTimeTaken }} Sekunden</p>
            <p v-else>Benötigte Zeit: Wird berechnet...</p>
            <div class="mt-4">
              <h3>Bearbeitungszeit pro Frage:</h3>
              <ul>
                <li v-for="(t, idx) in questionTimes" :key="idx">
                  Frage {{ idx + 1 }}: {{ t }} Sekunden
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div v-else>
          <p>Fragen werden geladen...</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>