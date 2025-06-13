<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

function formatQuestionMark(text: string): string {
  if (text.endsWith('?')) {
    // Replace only the last character (the '?')
    return (
      text.slice(0, -1) +
      '<span class="font-bold text-red-600 text-lg">?</span>'
    );
  }
  return text;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Tests',
    href: '/tests',
  },
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
  // QUESTION 13 WITH IMAGE:
  {
    text: "Berechne die Grundstücksgröße in m².",
    answer: "60",
    image: "/images/Mathe1.png"
  },
  {
    text: "Das Rad hat einen Durchmesser von 0,6 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?",
    answer: "188.5",
    image: "/images/Mathe2.png"
  },
  { text: "√81 = ?", answer: "9" },
  { text: "10³ = ?", answer: "1000" },
]);

const currentQuestionIndex = ref(0);
const nextButtonClickCount = ref(0);
const userAnswers = ref<string[]>(Array(questions.value.length).fill(''));
const answerInput = ref<InstanceType<typeof Input> | null>(null);
const score = ref(0);
const startTime = ref<number | null>(null);
const totalTimeTaken = ref<number | null>(null);

const currentQuestion = computed(() => {
  if (currentQuestionIndex.value < questions.value.length) {
    return questions.value[currentQuestionIndex.value];
  }
  return null;
});

const nextButtonText = computed(() => {
  if (nextButtonClickCount.value === 1) {
    return "Next (Confirm)";
  }
  return "Next";
});

const handleNextClick = () => {
  nextButtonClickCount.value++;
  if (nextButtonClickCount.value >= 2) {
    if (currentQuestionIndex.value < questions.value.length) {
      const userAnswer = userAnswers.value[currentQuestionIndex.value]?.trim().replace(",", ".");
      const correctAnswer = questions.value[currentQuestionIndex.value].answer.trim().replace(",", ".");
      if (userAnswer === correctAnswer) {
        score.value++;
      }
    }

    if (currentQuestionIndex.value < questions.value.length - 1) {
      currentQuestionIndex.value++;
      nextButtonClickCount.value = 0;
    } else {
      currentQuestionIndex.value = questions.value.length;
      nextButtonClickCount.value = 0;
      if (startTime.value) {
        totalTimeTaken.value = Math.round((Date.now() - startTime.value) / 1000);
      }
    }
  }
};

const handlePrevClick = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
    nextButtonClickCount.value = 0; // Reset next logic on going back
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
  if (newIndex === 0 && startTime.value === null) {
    startTime.value = Date.now();
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

</script>

<template>

  <Head title="Tests" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div v-if="!isTestComplete && currentQuestion" class="p-6 bg-background border rounded-lg">
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
              Previous
            </Button>
            <Button @click="handleNextClick">
              {{ nextButtonText }}
            </Button>
          </div>
        </div>
        <p v-if="nextButtonClickCount === 1" class="text-sm text-muted-foreground mt-2">
          Klicken Sie erneut auf "Next (Confirm)", um fortzufahren.
        </p>
      </div>
      <div v-else-if="isTestComplete" class="p-6 bg-background border rounded-lg">
        <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
        <div class="space-y-2">
          <p>Hier sind Ihre Ergebnisse:</p>
          <p>Richtige Antworten: {{ score }} von {{ questions.length }}</p>
          <p>Prozent: {{ percentageScore }}%</p>
          <p v-if="totalTimeTaken !== null">Benötigte Zeit: {{ totalTimeTaken }} Sekunden</p>
          <p v-else>Benötigte Zeit: Wird berechnet...</p>
        </div>
      </div>
      <div v-else>
        <p>Fragen werden geladen...</p>
      </div>
    </div>
  </AppLayout>
</template>
