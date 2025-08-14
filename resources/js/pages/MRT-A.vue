<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { Button } from '@/components/ui/button';
import { Line } from 'vue-chartjs';
import {
  Chart, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title, registerables
} from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';

Chart.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Title)
Chart.register(...registerables, annotationPlugin)



// -------------- Utility ---------------
function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–";
  if (sec < 60) return `${sec} Sekunden`;
  const min = Math.round(sec / 60);
  return `${min} Minuten`;
}

const showDetails = ref(false);
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

const page = usePage<{
  auth: {
    user: {
      participant_profile?: { age?: number }
    }
  }
}>();
const userAge = computed<number | null>(
  () => page.props.auth.user.participant_profile?.age ?? null
);

const showResults = ref(false);

const groupMap = [
  [0, 1, 2, 3, 4, 15, 16, 17, 18, 19],       // U1
  [5, 6, 7, 8, 9, 20, 21, 22, 23, 24],       // U2
  [10, 11, 12, 13, 14, 25, 26, 27, 28, 29],  // U3
  [30, 31, 32, 33, 34, 45, 46, 47, 48, 49],  // U4
  [35, 36, 37, 38, 39, 50, 51, 52, 53, 54],  // U5
  [40, 41, 42, 43, 44, 55, 56, 57, 58, 59],  // U6
];

const SN_WERTE_MATRIX_18_30 = [
  // 0  1  2  3  4  5  6  7  8  9  10  
  // U1
  [1, 1, 1, 1, 2, 3, 3, 4, 5, 6, 9],
  // U2
  [1, 1, 1, 2, 3, 4, 5, 6, 7, 8, 9],
  // U3
  [1, 1, 1, 1, 2, 2, 3, 4, 5, 7, 9],
  // U4
  [1, 1, 1, 1, 1, 1, 2, 3, 4, 6, 9],
  // U5
  [1, 1, 2, 3, 4, 4, 5, 6, 7, 9, 9],
  // U6
  [1, 1, 1, 1, 2, 3, 3, 4, 5, 7, 9],
];

// A 31-50
const SN_WERTE_MATRIX_31_50 = [
  // 0  1  2  3  4  5  6  7  8  9  10  
  // U1
  [1, 1, 1, 2, 2, 3, 4, 5, 5, 7, 9],
  // U2
  [1, 1, 1, 1, 2, 3, 4, 4, 5, 6, 9],
  // U3
  [1, 1, 1, 2, 2, 3, 4, 5, 6, 7, 9],
  // U4
  [1, 1, 1, 1, 1, 2, 2, 3, 4, 6, 9],
  // U5
  [1, 2, 2, 4, 4, 5, 6, 7, 8, 9, 9],
  // U6
  [1, 1, 1, 2, 3, 3, 4, 5, 6, 7, 9],
];



// Stanine value per group (U1–U6)
const groupStanines = computed(() =>
  groupScores.value.map((rw, groupIdx) => {
    // Clamp rw to [0, 10]
    const safeRW = Math.max(0, Math.min(10, rw));
    return selectedMatrix.value[groupIdx][safeRW];
  })
);

function getPR(rwgs) {
  const found = selectedPRTable.value.find(row => row.rwgs === rwgs);
  return found ? found.PR : 0;
}
const prValue = computed(() => getPR(totalScore.value));

const prTable_18_30 = [{ rwgs: 9, PR: 0 }, { rwgs: 10, PR: 0 }, { rwgs: 11, PR: 0 }, { rwgs: 12, PR: 0 }, { rwgs: 13, PR: 0 }, { rwgs: 14, PR: 0 }, { rwgs: 15, PR: 0 }, { rwgs: 16, PR: 0 }, { rwgs: 17, PR: 0 }, { rwgs: 18, PR: 1 }, { rwgs: 19, PR: 1 }, { rwgs: 20, PR: 1 }, { rwgs: 21, PR: 2 }, { rwgs: 22, PR: 2 }, { rwgs: 23, PR: 3 }, { rwgs: 24, PR: 3 }, { rwgs: 25, PR: 3 }, { rwgs: 26, PR: 3 }, { rwgs: 27, PR: 3 }, { rwgs: 28, PR: 4 }, { rwgs: 29, PR: 5 }, { rwgs: 30, PR: 7 }, { rwgs: 31, PR: 8 }, { rwgs: 32, PR: 10 }, { rwgs: 33, PR: 12 }, { rwgs: 34, PR: 13 }, { rwgs: 35, PR: 16 }, { rwgs: 36, PR: 18 }, { rwgs: 37, PR: 18 }, { rwgs: 38, PR: 21 }, { rwgs: 39, PR: 24 }, { rwgs: 40, PR: 27 }, { rwgs: 41, PR: 31 }, { rwgs: 42, PR: 34 }, { rwgs: 43, PR: 38 }, { rwgs: 44, PR: 42 }, { rwgs: 45, PR: 46 }, { rwgs: 46, PR: 50 }, { rwgs: 47, PR: 54 }, { rwgs: 48, PR: 58 }, { rwgs: 49, PR: 62 }, { rwgs: 50, PR: 69 }, { rwgs: 51, PR: 73 }, { rwgs: 52, PR: 79 }, { rwgs: 53, PR: 84 }, { rwgs: 54, PR: 88 }, { rwgs: 55, PR: 93 }, { rwgs: 56, PR: 96 }, { rwgs: 57, PR: 98 }, { rwgs: 58, PR: 99 }, { rwgs: 59, PR: 100 }, { rwgs: 60, PR: 100 }]

const prTable_31_50 = [{ rwgs: 9, PR: 0 }, { rwgs: 10, PR: 0 }, { rwgs: 11, PR: 0 }, { rwgs: 12, PR: 0 }, { rwgs: 13, PR: 0 }, { rwgs: 14, PR: 0 }, { rwgs: 15, PR: 1 }, { rwgs: 16, PR: 1 }, { rwgs: 17, PR: 1 }, { rwgs: 18, PR: 1 }, { rwgs: 19, PR: 3 }, { rwgs: 20, PR: 3 }, { rwgs: 21, PR: 3 }, { rwgs: 22, PR: 3 }, { rwgs: 23, PR: 4 }, { rwgs: 24, PR: 5 }, { rwgs: 25, PR: 5 }, { rwgs: 26, PR: 7 }, { rwgs: 27, PR: 8 }, { rwgs: 28, PR: 10 }, { rwgs: 29, PR: 10 }, { rwgs: 30, PR: 12 }, { rwgs: 31, PR: 13 }, { rwgs: 32, PR: 13 }, { rwgs: 33, PR: 16 }, { rwgs: 34, PR: 18 }, { rwgs: 35, PR: 21 }, { rwgs: 36, PR: 21 }, { rwgs: 37, PR: 24 }, { rwgs: 38, PR: 27 }, { rwgs: 39, PR: 31 }, { rwgs: 40, PR: 34 }, { rwgs: 41, PR: 38 }, { rwgs: 42, PR: 42 }, { rwgs: 43, PR: 42 }, { rwgs: 44, PR: 46 }, { rwgs: 45, PR: 50 }, { rwgs: 46, PR: 58 }, { rwgs: 47, PR: 62 }, { rwgs: 48, PR: 62 }, { rwgs: 49, PR: 66 }, { rwgs: 50, PR: 79 }, { rwgs: 51, PR: 79 }, { rwgs: 52, PR: 82 }, { rwgs: 53, PR: 84 }, { rwgs: 54, PR: 86 }, { rwgs: 55, PR: 90 }, { rwgs: 56, PR: 95 }, { rwgs: 57, PR: 98 }, { rwgs: 58, PR: 99 }, { rwgs: 59, PR: 100 }, { rwgs: 60, PR: 100 }]

const selectedMatrix = computed(() => {
  if (userAge.value >= 31) return SN_WERTE_MATRIX_31_50;
  else return SN_WERTE_MATRIX_18_30;
});
const selectedPRTable = computed(() => {
  if (userAge.value >= 31) return prTable_31_50;
  else return prTable_18_30;
});

const groupLabels = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6']
const chartData = computed(() => ({
  labels: groupLabels,
  datasets: [
    {
      label: 'SN',
      data: groupStanines.value, // [1,2,3,4,5,6] as an example
      borderColor: '#1d4ed8',
      backgroundColor: '#1d4ed8',
      tension: 0,   // 0 = stepline, 0.4 = curve
      pointRadius: 6,
      pointBackgroundColor: '#1d4ed8',
      fill: false
    }
  ]
}))

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { display: false },
    title: { display: false },
    annotation: {
      annotations: {
        rangeBox: {
          type: 'box',
          xMin: 4,
          xMax: 6,
          backgroundColor: 'rgba(144,238,144,0.3)', // light green
          borderWidth: 0,
        }
      }
    }
  },
  indexAxis: 'y',
  scales: {
    x: {
      min: 1,
      max: 9,
      ticks: { stepSize: 1 }
    }
  }
};

const groupScores = computed(() =>
  groupMap.map(indices =>
    indices.reduce((sum, idx) => {
      const ans = userAnswers.value[idx];
      if (!ans) return sum;
      const q = mrtQuestions.value[idx];
      return sum + (q.correct.map(x => x.toUpperCase()).includes(ans.toUpperCase()) ? 1 : 0);
    }, 0)
  )
);

const totalScore = computed(() => groupScores.value.reduce((a, b) => a + b, 0));


//! remove code 
// function getNormByTotal(total: number) {
//   const found = normtable_18_30.find(row => row.total === total);
//   return found ? { SN: found.SN, PR: found.PR } : { SN: null, PR: null };
// }
// const normResult = computed(() => getNormByTotal(totalScore.value));

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tests', href: '/tests' },
  { title: 'MRT-A', href: '/tests/mrt-a' },
];

const showTest = ref(false);
const currentQuestionIndex = ref(0);

// --- For per-question state:
const userAnswers = ref<(string | null)[]>(Array(mrtQuestions.value.length).fill(null));
const questionTimes = ref<number[]>(Array(mrtQuestions.value.length).fill(0));
const questionStartTimestamps = ref<(number | null)[]>(Array(mrtQuestions.value.length).fill(null));
const startTime = ref<number | null>(null);

const isTestComplete = computed(() => currentQuestionIndex.value >= mrtQuestions.value.length);


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

    // --- Now, auto jump to next (or finish test) ---
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
    } else {
      currentQuestionIndex.value = mrtQuestions.value.length;
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
  } else {
    currentQuestionIndex.value = mrtQuestions.value.length;
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

  <Head title="MRT-A Test" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 min-h-[600px] gap-4 rounded-xl p-4 bg-muted/20 text-foreground">
      <div class="flex-1 flex flex-col gap-4">
        <!-- Start Test Screen -->
        <div v-if="!showTest" class="flex flex-col items-center justify-center h-full">
          <h2 class="text-2xl font-bold mb-4">Willkommen zum MRT-A Deutsch-Test</h2>
          <p class="mb-6 text-base text-center max-w-xl">
            Dieser Test besteht aus {{ mrtQuestions.length }} Aufgaben. Wählen Sie jeweils die richtige Schreibweise.
            Die benötigte Zeit pro Aufgabe wird automatisch gemessen.
          </p>
          <p v-if="userAge" class="mb-6 text-base">Ihr Alter: {{ userAge }}</p>
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
            <Button @click="handleNextClick" :disabled="!userAnswers[currentQuestionIndex]">
              Weiter
            </Button>
          </div>
        </div>

        <!-- Test Results -->
        <div v-else-if="isTestComplete" class="p-6 bg-background border rounded-lg">
          <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
          <div class="mb-6 w-full max-w-md">
            <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
              <tbody>
                <tr class="bg-muted/40">
                  <td class="font-semibold px-3 py-2 w-1/2">Rohwert</td>
                  <td class="px-3 py-2">{{ totalScore }} von {{ mrtQuestions.length }}</td>
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

          <Button v-if="!showDetails" @click="showDetails = true" class="mb-4 px-4 py-2 rounded-lg font-semibold">
            Antwort- und Bearbeitungszeit je Aufgabe anzeigen
          </Button>
          <Button v-else @click="showDetails = false" class="mb-4 px-4 py-2 rounded-lg font-semibold">
            Antwort- und Bearbeitungszeit je Aufgabe verbergen
          </Button>
          <div v-if="showDetails">
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
                  <tr v-for="(q, idx) in mrtQuestions" :key="idx" :class="userAnswers[idx] && q.correct.map(a => a.toUpperCase()).includes(userAnswers[idx]?.toUpperCase())
                    ? 'bg-green-50 dark:bg-green-900/50'
                    : 'bg-red-50 dark:bg-red-900/50'">
                    <td class="px-2 py-1 font-medium text-muted-foreground">{{ idx + 1 }}</td>
                    <td class="px-2 py-1">
                      <span class="font-mono">
                        {{ userAnswers[idx] ? userAnswers[idx] : '–' }}
                      </span>
                    </td>
                    <td class="px-2 py-1">
                      <span class="font-mono">
                        {{ q.correct.join(', ') }}
                      </span>
                    </td>
                    <td class="px-2 py-1 text-right text-gray-500 dark:text-gray-400 font-mono min-w-[60px]">
                      {{ formatTime(questionTimes[idx]) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>


          <div v-if="isTestComplete">
            <div class="flex flex-col items-center justify-center my-10 w-full">
              <!-- RW Boxes Row -->
              <div class="flex flex-row items-center gap-3 mb-8">
                <!-- RW label -->
                <span class="font-bold text-base mr-3">RW</span>
                <template v-for="(score, i) in groupScores" :key="'rwbox' + i">
                  <div class="flex flex-col items-center">
                    <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold">
                      {{ score }}
                    </div>
                    <span class="text-xs text-gray-700 dark:text-gray-300 mt-1">U{{ i + 1 }}</span>
                  </div>
                </template>
                <!-- RW Total -->
                <div class="flex flex-col items-center ml-6">
                  <div
                    class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-blue-50 dark:bg-blue-900">
                    {{ totalScore }}
                  </div>
                  <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">RW GS</span>
                </div>
                <!-- PR -->
                <div class="flex flex-col items-center ml-6">
                  <div
                    class="w-10 h-10 border-2 border-black flex items-center justify-center text-base font-bold bg-yellow-50 dark:bg-yellow-900">
                    {{ prValue }}
                  </div>
                  <span class="text-xs text-gray-700 dark:text-gray-300 mt-1 font-bold">PR</span>
                </div>
              </div>
              <!-- Stepline Chart Centered -->
              <div style="width: 480px; height: 320px;">
                <Line :data="chartData" :options="chartOptions" />
              </div>
              <!-- PR Bar Chart -->
              <div class="w-full flex justify-center mt-6">
                <div class="w-[400px] h-8 rounded-full bg-gray-200 dark:bg-gray-700 relative overflow-hidden shadow-inner">
                  <!-- The filled part -->
                  <div class="h-full bg-red-600 transition-all duration-700 flex items-center justify-center"
                    :style="{ width: (prValue || 0) + '%' }">
                    <!-- Empty span just for flex alignment, no text here -->
                    <span class="opacity-0">.</span>
                  </div>
                  <!-- The text is absolutely centered on top of the bar -->
                  <span class="absolute left-0 w-full h-full flex items-center justify-center text-black font-bold"
                    style="top: 0;" v-if="prValue !== null">
                    {{ prValue }}%
                  </span>
                </div>
              </div>
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