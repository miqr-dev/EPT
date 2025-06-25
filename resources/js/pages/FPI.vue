<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { FPI_QUESTIONS } from '@/Pages/Questions/FPIQuestions';

import { onMounted, onBeforeUnmount, watch } from 'vue';
const sentinel = ref<null | HTMLElement>(null);
let observer: IntersectionObserver | null = null;

function observeSentinel() {
  if (!sentinel.value) return;
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !finished.value && blockIndex.value < totalBlocks.value - 1) {
      handleNextBlock();
    }
  }, { threshold: 1.0 });
  observer.observe(sentinel.value);
}

onMounted(() => {
  watch([showTest, blockIndex, finished], () => {
    // (Re-)attach observer whenever these change
    if (observer) observer.disconnect();
    if (showTest.value && !finished.value && sentinel.value) observeSentinel();
  }, { immediate: true });
});
onBeforeUnmount(() => { if (observer) observer.disconnect(); });

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tests', href: '/tests' },
  { title: 'FPI-R', href: '/tests/fpi-r' },
];

const instructions = `
Sie werden auf den folgenden Seiten eine Reihe von Aussagen über bestimmte Verhaltensweisen, Einstellungen und Gewohnheiten finden. Sie können jede entweder mit „stimmt“ oder mit „stimmt nicht“ beantworten. Setzen Sie bitte ein Kreuz (X) in den dafür vorgesehenen Kreis. Es gibt keine richtigen oder falschen Antworten, weil jeder Mensch das Recht zu eigenen Anschauungen hat.
Antworten Sie bitte so, wie es für Sie zutrifft.
Beachten Sie folgende Punkte:
• Überlegen Sie bitte nicht erst, welche Antwort vielleicht den „besten Eindruck“ machen könnte, sondern antworten Sie so, wie es für Sie persönlich gilt. Manche Fragen kommen Ihnen vielleicht sehr persönlich vor. Bedenken Sie aber, dass Ihre Antworten unbedingt vertraulich behandelt werden.
• Denken Sie nicht lange über einen Satz nach, sondern geben Sie die Antwort, die Ihnen unmittelbar in den Sinn kommt. Natürlich können mit diesen kurzen Fragen nicht alle Besonderheiten berücksichtigt werden. Vielleicht passen deshalb einige nicht gut auf Sie. Kreuzen Sie aber trotzdem immer eine Antwort an, und zwar die, welche noch am ehesten für Sie zutrifft.
`;

// Settings
const QUESTIONS_PER_BLOCK = 24;

// State
const showTest = ref(false);
const consentAnswer = ref<'stimmt' | 'stimmtNicht' | null>(null);
const blockIndex = ref(0);
const answers = ref<Record<number, 'stimmt' | 'stimmtNicht' | null>>({});
const missedQuestions = ref<number[]>([]);
const finished = ref(false);
const totalQuestions = FPI_QUESTIONS.length;
const currentFrom = computed(() => blockIndex.value * QUESTIONS_PER_BLOCK + 1);
const currentTo = computed(() => Math.min((blockIndex.value + 1) * QUESTIONS_PER_BLOCK, totalQuestions));
const currentRangeString = computed(() => `Fragen ${currentFrom.value}–${currentTo.value} von ${totalQuestions}`);



// Init answers as null for each question
FPI_QUESTIONS.forEach(q => {
  answers.value[q.number] = null;
});

// Compute block questions
const totalBlocks = computed(() =>
  Math.ceil(FPI_QUESTIONS.length / QUESTIONS_PER_BLOCK)
);

const currentBlockQuestions = computed(() => {
  const start = blockIndex.value * QUESTIONS_PER_BLOCK;
  return FPI_QUESTIONS.slice(start, start + QUESTIONS_PER_BLOCK);
});

// Sidebar: Show missed questions only after Weiter was clicked
const missedSidebarQuestions = computed(() =>
  missedQuestions.value
    .map(num => FPI_QUESTIONS.find(q => q.number === num))
    .filter(Boolean)
);

// Navigation
function handleNextBlock() {
  // Find unanswered in this block, add to missedQuestions
  currentBlockQuestions.value.forEach(q => {
    if (!answers.value[q.number] && !missedQuestions.value.includes(q.number)) {
      missedQuestions.value.push(q.number);
    }
  });
  if (blockIndex.value < totalBlocks.value - 1) {
    blockIndex.value++;
  } else {
    finished.value = true;
  }
}
function handlePrevBlock() {
  if (blockIndex.value > 0) blockIndex.value--;
}
function jumpToQuestion(num: number) {
  const idx = FPI_QUESTIONS.findIndex(q => q.number === num);
  if (idx !== -1) blockIndex.value = Math.floor(idx / QUESTIONS_PER_BLOCK);
}

// Scoring
const categoryLabels = [
  "Lebenszufriedenheit",        // 1
  "Soziale",                    // 2
  "Leistungsorientierung",      // 3
  "Gehemmtheit",                // 4
  "Erregbarkeit",               // 5
  "Aggressivität",              // 6
  "Beanspruchung",              // 7
  "Körperliche",                // 8
  "Gesundheitssorgen",          // 9
  "Offenheit",                  // 10
  "Extraversion",               // E
  "Emotionalität"               // N
];
const categoryKeys = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'E', 'N'];
const categoryScores = computed(() => {
  const scores: Record<string, number> = {};
  for (const key of categoryKeys) scores[key] = 0;
  FPI_QUESTIONS.forEach(q => {
    const ans = answers.value[q.number];
    if (ans) {
      const arr = q[ans];
      for (const { category, points } of arr) {
        scores[category] = (scores[category] || 0) + points;
      }
    }
  });
  return scores;
});

function startTest() {
  showTest.value = true;
}

function restart() {
  showTest.value = false;
  consentAnswer.value = null;
  blockIndex.value = 0;
  finished.value = false;
  missedQuestions.value = [];
  FPI_QUESTIONS.forEach(q => {
    answers.value[q.number] = null;
  });
}
</script>

<template>

  <Head title="FPI-R Test" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20">

      <!-- Sidebar: Only missed (unanswered after Weiter) -->
      <aside v-if="showTest && !finished && missedSidebarQuestions.length"
        class="w-64 flex-shrink-0 flex flex-col items-start space-y-2 py-4 h-fit sticky top-8">
        <h3 class="font-bold mb-2 text-sm text-muted-foreground pl-4">Offene Fragen</h3>
        <div class="flex flex-col space-y-1 w-full items-start">
          <template v-for="q in missedSidebarQuestions" :key="q?.number">
            <button class="w-full flex items-center py-1 px-2 rounded-lg border transition text-base hover:bg-blue-50"
              @click="jumpToQuestion(q.number)">
              <span
                class="w-8 h-8 flex items-center justify-center rounded-full border mr-2 bg-yellow-100 text-black font-bold">
                {{ q.number }}
              </span>
              <span class="truncate max-w-[130px] text-left text-xs" :title="q.text">
                {{ q.text.length > 30 ? q.text.slice(0, 30) + '…' : q.text }}
              </span>
            </button>
          </template>
        </div>
        <div class="w-full mt-6">
          <div class="h-2 rounded bg-gray-200 overflow-hidden">
            <div class="h-full bg-blue-500 transition-all duration-300"
              :style="{ width: Math.round((blockIndex + 1) / totalBlocks * 100) + '%' }"></div>
          </div>
          <div class="text-xs text-gray-600 text-center mt-1">
            {{ blockIndex + 1 }}/{{ totalBlocks }} Seiten
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col gap-4">

        <!-- Consent screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum FPI-R-Test</h2>
          <div
            class="mb-6 whitespace-pre-line text-base text-gray-900 border p-4 rounded-lg shadow-sm bg-yellow-50 w-full max-w-2xl">
            {{ instructions }}</div>
          <div class="mb-8 mt-4 p-4 bg-blue-50 rounded-lg border font-semibold w-full max-w-xl">
            <div class="mb-3">Ich habe die Anleitung gelesen und bin bereit, jeden Satz offen zu beantworten.</div>
            <div class="flex flex-row gap-8">
              <label class="flex items-center cursor-pointer">
                <input type="radio" v-model="consentAnswer" value="stimmt" />
                <span class="ml-2">stimmt</span>
              </label>
              <label class="flex items-center cursor-pointer">
                <input type="radio" v-model="consentAnswer" value="stimmtNicht" />
                <span class="ml-2">stimmt nicht</span>
              </label>
            </div>
          </div>
          <Button :disabled="consentAnswer !== 'stimmt'" @click="startTest"
            class="px-8 py-3 text-lg font-semibold rounded-xl shadow">
            Test starten
          </Button>
        </div>

        <!-- Questions Block Table -->
        <div v-else-if="!finished" class="p-6 bg-background border rounded-lg">
          <table class="w-full text-base mb-6 border-separate" style="border-spacing: 0;">
            <thead>
              <tr>
                <th class="w-48">{{ currentRangeString }}</th>
                <th class="text-left"></th>
                <th class="w-36 text-center">stimmt</th>
                <th class="w-36 text-center">stimmt nicht</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="q in currentBlockQuestions" :key="q.number" :class="{ 'bg-gray-50': !answers[q.number] }">
                <td class="pr-2 font-mono align-top pt-2 border-b-2 border-gray-200">{{ q.number }}.</td>
                <td class="pr-4 align-top pt-2 border-b-2 border-gray-200">{{ q.text }}</td>
                <td class="text-center align-top pt-2 border-b-2 border-gray-200">
                  <input type="radio" :name="'q' + q.number" v-model="answers[q.number]" value="stimmt" />
                </td>
                <td class="text-center align-top pt-2 border-b-2 border-gray-200">
                  <input type="radio" :name="'q' + q.number" v-model="answers[q.number]" value="stimmtNicht" />
                </td>
              </tr>

            </tbody>
          </table>
          <div class="flex flex-row justify-between">
            <Button @click="handlePrevBlock" :disabled="blockIndex === 0" variant="outline">
              Zurück
            </Button>
            <Button @click="handleNextBlock">
              Weiter
            </Button>
          </div>
        </div>

        <!-- Results -->
        <div v-else class="p-6 bg-background border rounded-lg">
          <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
          <div class="mb-6 w-full max-w-md">
            <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
              <thead>
                <tr class="bg-muted/40">
                  <th class="text-left py-2 px-4">Kategorie</th>
                  <th class="text-left py-2 px-4">Punkte</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cat in categoryKeys" :key="cat">
                  <td class="py-2 px-4">{{ categoryLabels[typeof cat === 'number' ? cat - 1 : (cat === 'E' ? 10 : 11)]
                  }}
                  </td>
                  <td class="py-2 px-4">{{ categoryScores[cat] }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <Button @click="restart" class="px-6 py-2 rounded font-bold">Test neu starten</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
