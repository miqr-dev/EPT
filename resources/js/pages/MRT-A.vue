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

// -------------- Utility ---------------
// -------------- DATA ---------------
interface MRTQuestion {
  number: number;
  options: string[];   // ["OptionA", "OptionB", ...]
  correct: string[];   // ["A"] or ["C", "D"]
}
const mrtQuestions = ref<MRTQuestion[]>([
  { number: 1, options: ["Drehohrgel", "Dreohrgel", "Dreorgel", "Drehorgel"], correct: ["D"] },
  { number: 2, options: ["Spülmaschine", "Spühlmaschiene", "Spülmaschiene", "Spühlmaschine"], correct: ["A"] },
  { number: 3, options: ["unwiederstelich", "unwiederstehlich", "unwiderstehlich", "unwiderstelich"], correct: ["C"] },
  { number: 4, options: ["Heerschahr", "Herschar", "Heerschar", "Heerschaar"], correct: ["C"] },
  { number: 5, options: ["ehrerbietig", "ererbietig", "ehrerbitig", "ehrehrbietig"], correct: ["A"] },
  { number: 6, options: ["Musikapelle", "Musikkapelle", "Musikkappelle", "Musikappelle"], correct: ["B"] },
  { number: 7, options: ["Karrussell", "Karrussel", "Karussell", "Karusell"], correct: ["C"] },
  { number: 8, options: ["Apettit", "Appetitt", "Appettit", "Appetit"], correct: ["D"] },
  { number: 9, options: ["Brennesel", "Brennnesel", "Brennessel", "Brennnessel"], correct: ["C", "D"] },
  { number: 10, options: ["Kontrollaparrat", "Kontrollapparrat", "Kontrolapparat", "Kontrollapparat"], correct: ["D"] },
  { number: 11, options: ["Gutmüdigkeit", "Gutmüdichkeit", "Gutmütigkeid", "Gutmütigkeit"], correct: ["D"] },
  { number: 12, options: ["täglisch und vierzehntägig", "täglich und vierzehntägich", "täglich und vierzehntägig", "täglig und vierzehntägig"], correct: ["C"] },
  { number: 13, options: ["Flukzeugwerk", "Flugzeukwerk", "Flugzeugwerg", "Flugzeugwerk"], correct: ["D"] },
  { number: 14, options: ["unendgeldlich", "unendgeltlich", "unentgeldlich", "unentgeltlich"], correct: ["D"] },
  { number: 15, options: ["am bedeutendsden", "am bedeutendsten", "am bedeutentsten", "am bedeudendsten"], correct: ["B"] },
  { number: 16, options: ["Liberalisierung", "Lieberalisirung", "Lieberalisierung", "Liberalisirung"], correct: ["A"] },
  { number: 17, options: ["zwei menschenleere Säle", "zwei menschenlehre Säle", "zwei menschenlehre Sääle", "zwei menschenleere Sääle"], correct: ["A"] },
  { number: 18, options: ["Nämaschiene", "Nähmaschiene", "Nämaschine", "Nähmaschine"], correct: ["D"] },
  { number: 19, options: ["wiederstandsfäig", "widerstandsfähig", "widerstandsfäig", "wiederstandsfähig"], correct: ["B"] },
  { number: 20, options: ["Wahreneinfuhr", "Wareneinfuhr", "Wahreneinfur", "Wareneinfur"], correct: ["B"] },
  { number: 21, options: ["Kanallotse", "Kannallotse", "Kanallotze", "Kannalotse"], correct: ["A"] },
  { number: 22, options: ["aggressiv", "agressiv", "agresiv", "aggresiv"], correct: ["A"] },
  { number: 23, options: ["Komission", "Komision", "Kommission", "Kommision"], correct: ["C"] },
  { number: 24, options: ["Dillemma", "Dilemma", "Dilema", "Dillema"], correct: ["B"] },
  { number: 25, options: ["Rasieraparrat", "Rassierapparat", "Rasierapparat", "Rasieraparat"], correct: ["C"] },
  { number: 26, options: ["Revormforschlag", "Reformforschlag", "Revormvorschlag", "Reformvorschlag"], correct: ["D"] },
  { number: 27, options: ["tugenhaft", "tugenhafd", "tugenthaft", "tugendhaft"], correct: ["D"] },
  { number: 28, options: ["Angeglagter", "Angeklakter", "Angeglakter", "Angeklagter"], correct: ["D"] },
  { number: 29, options: ["Gewandheit", "Gewandtheit", "Gewandtheid", "Gewantheit"], correct: ["B"] },
  { number: 30, options: ["schaudernt", "schauternt", "schaudernd", "schauternd"], correct: ["C"] },
  { number: 31, options: ["Bundespräsidänt", "Bundespresident", "Bundespräsident", "Bundespresidänt"], correct: ["C"] },
  { number: 32, options: ["Sekrätärin", "Sekretärin", "Säkretärin", "Sekräterin"], correct: ["B"] },
  { number: 33, options: ["repräsentativ", "representativ", "repräsäntativ", "räpräsentativ"], correct: ["A"] },
  { number: 34, options: ["annähärnd", "annähernd", "annehärnd", "annehernd"], correct: ["B"] },
  { number: 35, options: ["ärgärlich", "ergerlich", "ergärlich", "ärgerlich"], correct: ["D"] },
  { number: 36, options: ["vom kleinen auf das Große schließen", "vom Kleinen auf das Große schließen", "vom Kleinen auf das große schließen", "vom kleinen auf das große schließen"], correct: ["B"] },
  { number: 37, options: ["im Dunkel der Nacht", "im dunkel der nacht", "im Dunkel der nacht", "im dunkel der Nacht"], correct: ["A"] },
  { number: 38, options: ["sie hat angst und mir ist auch Angst", "sie hat Angst und mir ist auch angst", "sie hat Angst und mir ist auch Angst", "sie hat angst und mir ist auch angst"], correct: ["B"] },
  { number: 39, options: ["wir froren ganze nächtelang vor Kälte", "wir froren ganze Nächtelang vor Kälte", "wir froren ganze nächte lang vor Kälte", "wir froren ganze Nächte lang vor Kälte"], correct: ["D"] },
  { number: 40, options: ["ein einzelnes paar Schuhe", "ein Einzelnes paar Schuhe", "ein Einzelnes Paar Schuhe", "ein einzelnes Paar Schuhe"], correct: ["D"] },
  { number: 41, options: ["Insditution", "Institution", "Instidution", "Insdidution"], correct: ["B"] },
  { number: 42, options: ["generell", "gennerrell", "gennerell", "generrell"], correct: ["A"] },
  { number: 43, options: ["Identifizierung", "Idäntifizierung", "Identifizirung", "Itentifizierung"], correct: ["A"] },
  { number: 44, options: ["Differrenz", "Diferrenz", "Differenz", "Diferenz"], correct: ["C"] },
  { number: 45, options: ["Sattellit", "Satellit", "Satellid", "Satelit"], correct: ["B"] },
  { number: 46, options: ["die Maid ist zartbesaitet", "die Maid ist zartbeseitet", "die Meid ist zartbesaitet", "die Meid ist zartbeseitet"], correct: ["A"] },
  { number: 47, options: ["Laubsegeblätter", "Laubsägeblätter", "Laubsegäblätter", "Laubsägebletter"], correct: ["B"] },
  { number: 48, options: ["Dräschflegel", "Dräschflägel", "Dreschflegel", "Dreschflägel"], correct: ["C"] },
  { number: 49, options: ["unabänderlich", "unabendärlich", "unabändärlich", "unabenderlich"], correct: ["A"] },
  { number: 50, options: ["Salzbräzel", "Salzbräzäl", "Salzbrezäl", "Salzbrezel"], correct: ["D"] },
  { number: 51, options: ["das für und wider", "das für und Wider", "das Für und wider", "das Für und Wider"], correct: ["D"] },
  { number: 52, options: ["über kurz oder Lang", "über kurz oder lang", "über Kurz oder Lang", "über Kurz oder lang"], correct: ["B"] },
  { number: 53, options: ["es sangen junge und alte, klein und groß", "es sangen Junge und Alte, Klein und Groß", "es sangen junge und alte, Klein und Groß", "es sangen Junge und Alte, klein und groß"], correct: ["D"] },
  { number: 54, options: ["er kam gestern mittag", "er kam Gestern Mittag", "er kam gestern Mittag", "er kam Gestern mittag"], correct: ["A", "C"] },
  { number: 55, options: ["sie kann gut maschineschreiben", "sie kann gut Maschineschreiben", "sie kann gut Maschine schreiben", "sie kann gut maschine schreiben"], correct: ["A", "C"] },
  { number: 56, options: ["Allmosen", "Almosen", "Almoosen", "Allmoosen"], correct: ["B"] },
  { number: 57, options: ["debbattieren", "debattiren", "debbatieren", "debattieren"], correct: ["D"] },
  { number: 58, options: ["Witalität", "Vitalidät", "Vitalität", "Vitallität"], correct: ["C"] },
  { number: 59, options: ["Arbeitsmillieu", "Arbeitsmiliö", "Arbeitsmülieu", "Arbeitsmilieu"], correct: ["D"] },
  { number: 60, options: ["Interwiew", "Interviev", "Interwiu", "Interview"], correct: ["D"] },
]);

const showTest = ref(false);
const currentQuestionIndex = ref(0);
const isLastQuestion = computed(() => currentQuestionIndex.value === mrtQuestions.value.length - 1);

// --- For per-question state:
const userAnswers = ref<(string | null)[]>(Array(mrtQuestions.value.length).fill(null));
const questionTimes = ref<number[]>(Array(mrtQuestions.value.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(mrtQuestions.value.length).fill(null));
const startTime = ref<number | null>(null);

const isTestComplete = computed(() => currentQuestionIndex.value >= mrtQuestions.value.length);


const currentQuestion = computed(() =>
  currentQuestionIndex.value < mrtQuestions.value.length
    ? mrtQuestions.value[currentQuestionIndex.value]
    : null
);

// --- Option selection state per question ---
const tempSelected = ref<(string | null)[]>(Array(mrtQuestions.value.length).fill(null));
const tempClickState = ref<(boolean)[]>(Array(mrtQuestions.value.length).fill(false)); // false = first click, true = need confirm

// -------------- LOGIC ---------------
const handleOptionClick = (optIdx: number) => {
  const qidx = currentQuestionIndex.value;
  const letter = String.fromCharCode(65 + optIdx);
  // if (userAnswers.value[qidx]) return; // already answered

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
      qidx < mrtQuestions.value.length &&
      questionStartTimestamps.value[qidx]
    ) {
      questionTimes.value[qidx] += Math.round(
        (now - (questionStartTimestamps.value[qidx] as number)) / 1000
      );
      questionStartTimestamps.value[qidx] = null;
    }
    if (currentQuestionIndex.value < mrtQuestions.value.length - 1) {
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
    qidx < mrtQuestions.value.length &&
    questionStartTimestamps.value[qidx]
  ) {
    questionTimes.value[qidx] += Math.round(
      (now - (questionStartTimestamps.value[qidx] as number)) / 1000
    );
    questionStartTimestamps.value[qidx] = null;
  }
  if (currentQuestionIndex.value < mrtQuestions.value.length - 1) {
    currentQuestionIndex.value++;
  }
};

const handlePrevClick = () => {
  const now = Date.now();
  const qidx = currentQuestionIndex.value;
  if (
    qidx >= 0 &&
    qidx < mrtQuestions.value.length &&
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
    qidx < mrtQuestions.value.length &&
    questionStartTimestamps.value[qidx]
  ) {
    questionTimes.value[qidx] += Math.round(
      (now - (questionStartTimestamps.value[qidx] as number)) / 1000
    );
    questionStartTimestamps.value[qidx] = null;
  }
  currentQuestionIndex.value = mrtQuestions.value.length;
  endConfirmOpen.value = false;
  const results = {
    answers: [...userAnswers.value],
    question_times: [...questionTimes.value],
  };
  emit('complete', results);
};

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
  // Reset temp selection for new question
  tempSelected.value[newIndex] = null;
  tempClickState.value[newIndex] = false;
}, { immediate: true });

const startTest = () => {
  showTest.value = true;
  currentQuestionIndex.value = 0;
  userAnswers.value = Array(mrtQuestions.value.length).fill(null);
  tempSelected.value = Array(mrtQuestions.value.length).fill(null);
  tempClickState.value = Array(mrtQuestions.value.length).fill(false);
  questionTimes.value = Array(mrtQuestions.value.length).fill(0);
  questionStartTimestamps.value = Array(mrtQuestions.value.length).fill(null);
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
