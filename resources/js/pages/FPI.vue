<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { FPI_QUESTIONS } from '@/Pages/Questions/FPIQuestions';
import { norms_male_16_24 } from '@/Pages/Scores/FPI/norms_male_16_24';
import { norms_male_25_44 } from '@/Pages/Scores/FPI/norms_male_25_44';
import { norms_male_45_59 } from '@/Pages/Scores/FPI/norms_male_45_59';
import { norms_male_60up } from '@/Pages/Scores/FPI/norms_male_60up';
import { norms_female_16_24 } from '@/Pages/Scores/FPI/norms_female_16_24';
import { norms_female_25_44 } from '@/Pages/Scores/FPI/norms_female_25_44';
import { norms_female_45_59 } from '@/Pages/Scores/FPI/norms_female_45_59';
import { norms_female_60up } from '@/Pages/Scores/FPI/norms_female_60up';
import FPIResult from '@/Pages/Scores/FPI/FPIResult.vue'; // adjust the path if FPIResult.vue is not in the same folder



const showDemographics = ref(true);
const sex = ref<'male' | 'female' | null>(null);
const age = ref<number | null>(null);

function getNormTable() {
  if (!sex.value || !age.value) return null;
  if (sex.value === 'male') {
    if (age.value >= 16 && age.value <= 24) return norms_male_16_24;
    if (age.value >= 25 && age.value <= 44) return norms_male_25_44;
    if (age.value >= 45 && age.value <= 59) return norms_male_45_59;
    if (age.value >= 60) return norms_male_60up;
  }
  if (sex.value === 'female') {
    if (age.value >= 16 && age.value <= 24) return norms_female_16_24;
    if (age.value >= 25 && age.value <= 44) return norms_female_25_44;
    if (age.value >= 45 && age.value <= 59) return norms_female_45_59;
    if (age.value >= 60) return norms_female_60up;
  }
  return null;
}

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

const categoryStanines = computed(() => {
  const table = getNormTable();
  if (!table) return {};

  // key order: LEB, SOZ, LEI, GEH, ERR, AGGR, BEAN, KORP, GES, OFF, EXTR, EMOT
  const keys = ['LEB', 'SOZ', 'LEI', 'GEH', 'ERR', 'AGGR', 'BEAN', 'KORP', 'GES', 'OFF', 'EXTR', 'EMOT'];
  const stanines: Record<string, number | null> = {};

  keys.forEach((key, i) => {
    const score = categoryScores.value[categoryKeys[i]];
    stanines[key] = null;
    if (!table[key]) return;
    for (let st = 0; st < 9; ++st) {
      const [min, max] = table[key][st];
      if (score >= min && score <= max) {
        stanines[key] = st + 1; // Stanine 1-9
        break;
      }
    }
  });
  return stanines;
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

function getStanineKey(idx: number) {
  const keys = ['LEB', 'SOZ', 'LEI', 'GEH', 'ERR', 'AGGR', 'BEAN', 'KORP', 'GES', 'OFF', 'EXTR', 'EMOT'];
  return keys[idx];
}

const categoryStaninesArray = computed(() =>
  categoryKeys.map((_, idx) => categoryStanines.value[getStanineKey(idx)] ?? 0)
);

const rohwerteArray = computed(() =>
  categoryKeys.map(key => categoryScores.value[key] ?? 0)
);
// Chart
const chartCategories = [
  { label: "Lebenszufriedenheit", commentL: "lebenszufrieden, gute Laune, zuversichtlich", commentR: "unzufrieden, bedrückt, negative Lebenseinstellung" },
  { label: "Soziale Orientierung", commentL: "sozial verantwortlich, hilfsbereit, mitmenschlich", commentR: "Eigenverantwortung in Notlagen betonend, selbstbezogen unsolidarisch" },
  { label: "Leistungsorientierung", commentL: "leistungsorientiert, aktiv, schnell-handelnd, ehrgeizig-konkurrierend", commentR: "wenig leistungsorientiert oder energisch, wenig ehrgeizig-konkurrierend" },
  { label: "Gehemmtheit", commentL: "gehemmt, unsicher, kontaktscheu", commentR: "ungezwungen, selbstsicher, kontaktbereit" },
  { label: "Erregbarkeit", commentL: "erregbar, empfindlich, unbeherrscht", commentR: "ruhig, gelassen, selbstbeherrscht" },
  { label: "Aggressivität", commentL: "aggressives Verhalten, spontan und reaktiv, sich durchsetzend", commentR: "wenig aggressiv, kontrolliert, zurückhaltend" },
  { label: "Beanspruchung", commentL: "angespannt, überfordert, sich oft 'im Stress' fühlend", commentR: "wenig beansprucht, nicht überfordert, belastbar" },
  { label: "Körperliche Beschwerden", commentL: "viele Beschwerden, psychosomatisch gestört", commentR: "wenig Beschwerden, psychosomatisch nicht gestört" },
  { label: "Gesundheitssorgen", commentL: "furcht vor Erkrankung, gesundheitsbewusst, sich schonend", commentR: "wenig Gesundheitssorgen, gesundheitlich unbekümmert, robust" },
  { label: "Offenheit", commentL: "Offenes Zugeben kleiner Schwächen und alltäglicher Normverletzungen, ungeniert, unkonventionell", commentR: "an Umgangsnormen orientiert, auf guten Eindruck bedacht, mangelnde Selbstkritik, verschlossen (Achtung bei Stanine 1–3)" },
  { label: "Extraversion", commentL: "extravertiert, gesellig, impulsiv, unternehmungslustig", commentR: "introvertiert, zurückhaltend, überlegt, ernst" },
  { label: "Emotionalität", commentL: "emotional, labil, empfindlich, ängstlich, viele Probleme und körperliche Beschwerden", commentR: "emotional stabil, gelassen, selbstvertrauend, lebenszuversichtlich" },
];

const rowHeight = 42;
const stanineWidth = 42;
const svgWidth = stanineWidth * 9;
const svgHeight = rowHeight * 12;

function stanineX(idx: number) {
  return stanineWidth * idx + stanineWidth / 2;
}
function rowY(idx: number) {
  return rowHeight * idx + rowHeight / 2;
}

const staninePoints = computed(() => {
  // List of [x, y] for each category's Stanine
  return chartCategories.map((_, idx) => {
    const st = categoryStanines.value[getStanineKey(idx)];
    if (!st) return null;
    return `${stanineX(st - 1)},${rowY(idx)}`;
  }).filter(Boolean).join(' ');
});

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
              <tr v-for="q in currentBlockQuestions" :key="q.number" :class="{ 'bg-gray-50': !answers[q.number] }">
                <td class="font-mono align-top pt-2 border-b-2 border-gray-200 w-12 text-right">
                  {{ q.number }}.
                </td>
                <td class="align-top pt-2 border-b-2 border-gray-200 pl-2">
                  {{ q.text }}
                </td>
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
          <div class="mb-6 w-full max-w-3xl">
            <!-- SVG Auswertungsbogen -->
            <div class="relative fpi-auswertungsbogen bg-white shadow border mx-auto">
              <FPIResult :stanines="categoryStaninesArray" :rohwerte="rohwerteArray" />
            </div>
          </div>
          <Button @click="restart" class="px-6 py-2 rounded font-bold">Test neu starten</Button>
        </div>


      </div>
    </div>

    <!-- Demographics Popup -->
    <div v-if="showDemographics" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-xs w-full flex flex-col gap-4">
        <h2 class="font-bold text-lg mb-2">Bitte geben Sie Ihr Geschlecht und Alter an</h2>
        <div class="flex flex-row gap-4 justify-center">
          <label class="flex items-center cursor-pointer">
            <input type="radio" v-model="sex" value="male" />
            <span class="ml-2">männlich</span>
          </label>
          <label class="flex items-center cursor-pointer">
            <input type="radio" v-model="sex" value="female" />
            <span class="ml-2">weiblich</span>
          </label>
        </div>
        <input type="number" min="16" max="120" class="border rounded p-2 mt-2" v-model="age"
          placeholder="Alter (z.B. 32)" />
        <Button :disabled="!sex || !age" @click="showDemographics = false" class="w-full mt-4">
          Bestätigen
        </Button>
      </div>
    </div>

  </AppLayout>
</template>
