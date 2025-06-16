<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { Button } from '@/components/ui/button';

// -------------- Utility Functions ---------------
function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–";
  if (sec < 60) return `${sec} Sekunden`;
  const min = Math.round(sec / 60);
  return `${min} Minuten`;
}

// -------------- DATA ---------------
interface MRTQuestion {
  number: number;
  options: string[];   // ["A", "B", "C", "D"]
  correct: string[];   // ["C"] or ["C", "D"]
}

// Map of number->question/options, fill as shown below.
const mrtQuestions = ref<MRTQuestion[]>([
  { number: 1, options: ["Drehorghel", "Dreohregel", "Dreorgel", "Drehorgel"], correct: ["D"] },
  { number: 2, options: ["Spülmaschine", "Spülmaschiene", "Spülmaschiene", "Spühlmaschine"], correct: ["A"] },
  // ... continue for all 60 questions, each with correct as ["A"], or ["C", "D"] etc.
  // See your answers key for corrects!
  // ...
]);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tests', href: '/tests' },
  { title: 'MRT-A', href: '/tests/mrt-a' },
];

const showTest = ref(false);
const currentQuestionIndex = ref(0);
const nextButtonClickCount = ref(0);
const userAnswers = ref<(string | null)[]>(Array(mrtQuestions.value.length).fill(null));
const questionTimes = ref<number[]>(Array(mrtQuestions.value.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(mrtQuestions.value.length).fill(null));
const startTime = ref<number | null>(null);

// -------------- Computeds and Logic ---------------
const isTestComplete = computed(() => currentQuestionIndex.value >= mrtQuestions.value.length);

const finalScore = computed(() => {
  let correct = 0;
  mrtQuestions.value.forEach((q, i) => {
    if (
      userAnswers.value[i] &&
      q.correct.map(a => a.toUpperCase()).includes(userAnswers.value[i]!.toUpperCase())
    ) {
      correct++;
    }
  });
  return correct;
});

const totalTimeTaken = computed(() =>
  isTestComplete.value
    ? questionTimes.value.reduce((a, b) => a + b, 0)
    : null
);

const currentQuestion = computed(() =>
  currentQuestionIndex.value < mrtQuestions.value.length
    ? mrtQuestions.value[currentQuestionIndex.value]
    : null
);

const nextButtonText = computed(() =>
  nextButtonClickCount.value === 1 ? "Weiter (Bestätigen)" : "Weiter"
);

const jumpToQuestion = (index: number) => {
  const now = Date.now();
  if (
    currentQuestionIndex.value >= 0 &&
    currentQuestionIndex.value < mrtQuestions.value.length &&
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
      currentQuestionIndex.value < mrtQuestions.value.length &&
      questionStartTimestamps.value[currentQuestionIndex.value]
    ) {
      questionTimes.value[currentQuestionIndex.value] += Math.round(
        (now - (questionStartTimestamps.value[currentQuestionIndex.value] as number)) / 1000
      );
      questionStartTimestamps.value[currentQuestionIndex.value] = null;
    }
    if (currentQuestionIndex.value < mrtQuestions.value.length - 1) {
      currentQuestionIndex.value++;
      nextButtonClickCount.value = 0;
    } else {
      // Complete test: move index past last question
      currentQuestionIndex.value = mrtQuestions.value.length;
      nextButtonClickCount.value = 0;
    }
  }
};

const handlePrevClick = () => {
  const now = Date.now();
  if (
    currentQuestionIndex.value >= 0 &&
    currentQuestionIndex.value < mrtQuestions.value.length &&
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

// Per-question timer starter
watch(currentQuestionIndex, async (newIndex, oldIndex) => {
  const now = Date.now();
  if (
    typeof newIndex === 'number' &&
    newIndex >= 0 &&
    newIndex < mrtQuestions.value.length
  ) {
    if (!questionStartTimestamps.value[newIndex]) {
      questionStartTimestamps.value[newIndex] = now;
    }
  }
  if (newIndex === 0 && startTime.value === null) {
    startTime.value = now;
  }
}, { immediate: true });

const startTest = () => {
  showTest.value = true;
  currentQuestionIndex.value = 0;
  nextButtonClickCount.value = 0;
  userAnswers.value = Array(mrtQuestions.value.length).fill(null);
  questionTimes.value = Array(mrtQuestions.value.length).fill(0);
  questionStartTimestamps.value = Array(mrtQuestions.value.length).fill(null);
  startTime.value = null;
};
</script>

<template>
  <Head title="MRT-A Test" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20">
      <!-- Sidebar Navigation -->
      <aside v-if="showTest" class="w-64 flex-shrink-0 flex flex-col items-start space-y-2  py-4 h-fit sticky top-8">
        <h3 class="font-bold mb-2 text-sm text-muted-foreground pl-4">Fragen</h3>
        <div class="flex flex-col space-y-1 w-full items-start">
          <template v-for="(q, idx) in mrtQuestions" :key="idx">
            <button class="w-full flex items-center space-x-2 py-1 px-2 rounded-lg font-medium border transition
               hover:bg-blue-50 focus:outline-none text-base" :class="{
                'bg-blue-600 text-white border-blue-600': idx === currentQuestionIndex,
                'hover:bg-blue-500': idx === currentQuestionIndex,
                'bg-green-200 border-green-400 text-green-900': userAnswers[idx] && idx !== currentQuestionIndex && q.correct.map(a => a.toUpperCase()).includes(userAnswers[idx]?.toUpperCase()),
                'bg-red-200 border-red-400 text-red-900': userAnswers[idx] && idx !== currentQuestionIndex && !q.correct.map(a => a.toUpperCase()).includes(userAnswers[idx]?.toUpperCase()),
                'bg-gray-100 border-gray-300 text-gray-900': !userAnswers[idx] && idx !== currentQuestionIndex,
              }" @click="jumpToQuestion(idx)" :disabled="isTestComplete || !showTest">
              <span class="w-8 h-8 flex items-center justify-center rounded-full border mr-2" :class="{
                'bg-blue-600 text-white border-blue-600': idx === currentQuestionIndex,
                'bg-green-400 text-white border-green-400': userAnswers[idx] && q.correct.map(a => a.toUpperCase()).includes(userAnswers[idx]?.toUpperCase()),
                'bg-red-400 text-white border-red-400': userAnswers[idx] && !q.correct.map(a => a.toUpperCase()).includes(userAnswers[idx]?.toUpperCase()),
                'bg-gray-300 text-gray-600 border-gray-400': !userAnswers[idx],
              }">
                {{ idx + 1 }}
              </span>
              <span class="truncate max-w-[130px] text-left text-xs" :title="q.options.join(' | ')">
                Frage {{ q.number }}
              </span>
            </button>
          </template>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col gap-4">
        <!-- Start Test Screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum MRT-A Deutsch-Test</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            Dieser Test besteht aus {{ mrtQuestions.length }} Aufgaben. Wählen Sie jeweils die richtige Schreibweise.
            Die benötigte Zeit pro Aufgabe wird automatisch gemessen.
          </p>
          <Button @click="startTest" class="px-8 py-3 text-lg font-semibold rounded-xl shadow">
            Test starten
          </Button>
        </div>

        <!-- Test Content -->
        <div v-else-if="!isTestComplete && currentQuestion" class="p-6 bg-background border rounded-lg">
          <h2 class="text-xl font-semibold mb-4">Frage {{ currentQuestionIndex + 1 }}:</h2>
          <div class="mb-6 text-base">
            <ul>
              <li v-for="(option, oidx) in currentQuestion.options" :key="oidx" class="mb-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    class="accent-blue-600"
                    :name="'question-' + currentQuestionIndex"
                    :value="String.fromCharCode(65 + oidx)"
                    v-model="userAnswers[currentQuestionIndex]"
                    :disabled="isTestComplete"
                  />
                  <span class="font-mono font-semibold mr-1">{{ String.fromCharCode(65 + oidx) }})</span>
                  <span>{{ option }}</span>
                </label>
              </li>
            </ul>
          </div>
          <div class="flex flex-row justify-between mt-4">
            <Button @click="handlePrevClick" :disabled="currentQuestionIndex === 0" variant="outline">
              Zurück
            </Button>
            <Button @click="handleNextClick" :disabled="userAnswers[currentQuestionIndex] === null">
              {{ nextButtonText }}
            </Button>
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
                  <td class="px-3 py-2">{{ finalScore }} von {{ mrtQuestions.length }}</td>
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
            <h3 class="font-bold mb-2">Antwort- und Bearbeitungszeit je Aufgabe</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm border rounded-lg shadow">
                <thead class="bg-muted/40">
                  <tr>
                    <th class="px-2 py-1 text-left font-semibold">#</th>
                    <th class="px-2 py-1 text-left font-semibold">Ihre Auswahl</th>
                    <th class="px-2 py-1 text-left font-semibold">Richtige Antwort(en)</th>
                    <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(q, idx) in mrtQuestions" :key="idx"
                    :class="userAnswers[idx] && q.correct.map(a => a.toUpperCase()).includes(userAnswers[idx]?.toUpperCase())
                      ? 'bg-green-50'
                      : 'bg-red-50'">
                    <td class="px-2 py-1 font-medium text-muted-foreground">{{ idx + 1 }}</td>
                    <td class="px-2 py-1">
                      <span class="font-mono">{{ userAnswers[idx] || '–' }}</span>
                    </td>
                    <td class="px-2 py-1">
                      <span class="font-mono">{{ q.correct.join(', ') }}</span>
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
  </AppLayout>
</template>
