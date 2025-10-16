<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { FPI_QUESTIONS } from '@/pages/Questions/FPIQuestions';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';

const emit = defineEmits(['complete']);
// Settings
const QUESTIONS_PER_BLOCK = 24;

// State
const showTest = ref(false);
const consentAnswer = ref<'stimmt' | 'stimmtNicht' | null>(null);
const blockIndex = ref(0);
const answers = ref<Record<number, 'stimmt' | 'stimmtNicht' | null>>({});
const missedQuestions = ref<number[]>([]);
const finished = ref(false);
const endConfirmOpen = ref(false);
const startTime = ref<number | null>(null);
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
// Get the highest-numbered answered question
const lastAnsweredNumber = computed(() => {
  // Get all numbers with an answer
  const answeredNums = Object.keys(answers.value)
    .map(n => Number(n))
    .filter(n => answers.value[n] !== null);
  return answeredNums.length ? Math.max(...answeredNums) : 0;
});

// Show only missed (unanswered) questions BEFORE the last answered
const missedSidebarQuestions = computed(() => {
  if (!lastAnsweredNumber.value) return [];
  return FPI_QUESTIONS.filter(q =>
    !answers.value[q.number] && q.number < lastAnsweredNumber.value
  );
});

// Navigation
function handleNextBlock() {
  currentBlockQuestions.value.forEach(q => {
    if (!answers.value[q.number] && !missedQuestions.value.includes(q.number)) {
      missedQuestions.value.push(q.number);
    }
  });
  if (blockIndex.value < totalBlocks.value - 1) {
    blockIndex.value++;
  }
}
function handlePrevBlock() {
  if (blockIndex.value > 0) blockIndex.value--;
}
function jumpToQuestion(num: number) {
  const idx = FPI_QUESTIONS.findIndex(q => q.number === num);
  if (idx !== -1) blockIndex.value = Math.floor(idx / QUESTIONS_PER_BLOCK);
}
function startTest() {
  startTime.value = Date.now();
  showTest.value = true;
}

// function finishTest() {
//   window.dispatchEvent(new Event('start-finish'))
//   endConfirmOpen.value = true
// }

function confirmEnd() {
  currentBlockQuestions.value.forEach(q => {
    if (!answers.value[q.number] && !missedQuestions.value.includes(q.number)) {
      missedQuestions.value.push(q.number);
    }
  });
  endConfirmOpen.value = false;
  finished.value = true;
  const totalTimeSeconds = startTime.value
    ? Math.round((Date.now() - startTime.value) / 1000)
    : null;
  const results = {
    answers: FPI_QUESTIONS.map(q => ({
      number: q.number,
      answer: answers.value[q.number],
    })),
    total_time_seconds: totalTimeSeconds,
  };
  emit('complete', results);
}

function cancelEnd() {
  window.dispatchEvent(new Event('cancel-finish'))
  endConfirmOpen.value = false
}

// --- additions in <script setup> ---
const isComplete = computed(() =>
  FPI_QUESTIONS.every(q => 
  q.number === 131 || answers.value[q.number] !== null)
)
const remaining = computed(() =>
  FPI_QUESTIONS.filter(q => answers.value[q.number] === null).length
)

function finishTest() {
  // hard guard
  if (!isComplete.value) {
    missedQuestions.value = FPI_QUESTIONS
      .filter(q => answers.value[q.number] === null)
      .map(q => q.number)
    // jump to first missing to help the user
    if (missedQuestions.value.length) jumpToQuestion(missedQuestions.value[0])
    return
  }
  window.dispatchEvent(new Event('start-finish'))
  endConfirmOpen.value = true
}

</script>

<template>

  <Head title="FPI-R" />
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">FPI-R</h1>
    </div>
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20 text-foreground">

      <!-- Sidebar: Only missed (unanswered after Weiter) -->
      <aside v-if="showTest && !finished && missedSidebarQuestions.length"
        class="w-64 flex-shrink-0 flex flex-col items-start space-y-2 py-4 h-fit sticky top-8">
        <h3 class="font-bold mb-2 text-sm text-muted-foreground pl-4">Offene Fragen</h3>
        <div class="flex flex-col space-y-1 w-full items-start">
          <template v-for="q in missedSidebarQuestions" :key="q?.number">
            <button
              class="w-full flex items-center py-1 px-2 rounded-lg border transition text-base hover:bg-blue-50 dark:hover:bg-blue-900"
              @click="jumpToQuestion(q.number)">
              <span
                class="w-8 h-8 flex items-center justify-center rounded-full border mr-2 bg-yellow-100 text-black font-bold dark:bg-yellow-900 dark:text-yellow-100">
                {{ q.number }}
              </span>
              <span class="truncate max-w-[130px] text-left text-xs" :title="q.text">
                {{ q.text.length > 30 ? q.text.slice(0, 30) + '…' : q.text }}
              </span>
            </button>
          </template>
        </div>
        <div class="w-full mt-6">
          <div class="h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
            <div class="h-full bg-blue-500 transition-all duration-300"
              :style="{ width: Math.round((blockIndex + 1) / totalBlocks * 100) + '%' }"></div>
          </div>
          <div class="text-xs text-gray-600 dark:text-gray-400 text-center mt-1">
            {{ blockIndex + 1 }}/{{ totalBlocks }} Seiten
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col gap-4 mx-auto w-full max-w-5xl">

        <!-- Consent screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum FPI-R-Fragebogen</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            In diesem Fragebogen geht es um eine Reihe von Aussagen über bestimmte
            Verhaltensweisen, Einstellungen und Gewohnheiten, die auch für den Berufskontext wichtig sind.
          </p>
          <p class="mb-6 text-base text-center max-w-xl">
            Bitte beantworten Sie jede Aussage mit <strong>„stimmt“</strong> oder <strong>„stimmt nicht“</strong>.
            Kreuzen Sie an, was auf Sie zutrifft. Es gibt keine richtigen und falschen Antworten.
          </p>
          <p class="mb-6 text-base text-center max-w-xl">
            Überlegen Sie bitte nicht erst, welche Antwort vielleicht den „besten Eindruck“ machen könnte,
            sondern antworten Sie so, wie es für Sie persönlich gilt.
          </p>
          <div
            class="mb-8 mt-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg border dark:border-blue-700 font-semibold w-full max-w-xl">
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
                <th colspan="4" class="p-0">
                  <div class="flex items-center justify-between w-full text-base font-medium pt-1 pb-2">
                    <span class="w-1/3 text-left">{{ currentRangeString }}</span>
                    <span class="w-1/3 text-center text-sm text-muted-foreground font-semibold">
                      Seite {{ blockIndex + 1 }}/{{ totalBlocks }}
                    </span>
                    <span class="w-1/3"></span>
                  </div>
                </th>
              </tr>
              <tr>
                <th class="w-12"></th>
                <th class="text-left"></th>
                <th class="w-36 text-center">stimmt</th>
                <th class="w-36 text-center">stimmt nicht</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="q in currentBlockQuestions" :key="q.number"
                :class="{ 'bg-gray-50 dark:bg-gray-700': !answers[q.number] }">
                <td class="font-mono align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700 w-12 text-right">
                  {{ q.number }}.
                </td>
                <td class="align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700 pl-2">
                  {{ q.text }}
                  <div v-if="q.number === 131" class="text-xs text-gray-500 italic">
                    gegebenenfalls freilassen
                  </div>
                </td>
                <td class="text-center align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700">
                  <input type="radio" :name="'q' + q.number" v-model="answers[q.number]" value="stimmt" />
                </td>
                <td class="text-center align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700">
                  <input type="radio" :name="'q' + q.number" v-model="answers[q.number]" value="stimmtNicht" />
                </td>
              </tr>
            </tbody>
          </table>
          <div class="flex flex-row justify-between">
            <Button @click="handlePrevBlock" :disabled="blockIndex === 0" variant="outline">
              Zurück
            </Button>
            <Button v-if="blockIndex < totalBlocks - 1" @click="handleNextBlock">
              Weiter
            </Button>
            <Button v-else @click="finishTest" variant="destructive" :disabled="!isComplete"
              :title="!isComplete ? `Bitte alle Fragen beantworten (${remaining} offen)` : ''">
              Test beenden
            </Button>
          </div>
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
