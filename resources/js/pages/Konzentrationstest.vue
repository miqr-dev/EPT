<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const page = ref(1);

const page1Questions = [
  { seq: '20 . 10 . 18 . 14 . 7 . ?', answer: '11' },
  { seq: '12 . 21 . 29 . 38 . ?', answer: '47' },
  { seq: '19 . 12 . 22 . 18 . 27 . ?', answer: '23' },
];
const page1Responses = ref(Array(page1Questions.length).fill(''));

const page2Answer = ref('');
const page2Correct = 'Example';

const page5Text = 'u n u u n u';
const page5Marks = ref<boolean[]>(page5Text.split(' ').map(() => false));
const page5Sum = ref('');

const page6Rows = [
  '896586569856',
  '658969856985',
  '985689568965',
];
const page6Answers = ref<string[]>(Array(page6Rows.length).fill(''));
const page6Correct = ['3', '2', '4'];

const page7Chars = ['A', '%', 'B', '$', 'C', '&', 'D'];
const page7Marks = ref<boolean[]>(page7Chars.map(() => false));
const page7Total = ref('');

const wrongCount = computed(() => {
  let wrong = 0;
  page1Questions.forEach((q, i) => {
    if (page1Responses.value[i].trim() !== q.answer) wrong++;
  });
  if (page2Answer.value.trim() !== page2Correct) wrong++;
  page6Rows.forEach((row, i) => {
    if (page6Answers.value[i].trim() !== page6Correct[i]) wrong++;
  });
  return wrong;
});

const percentage = computed(() => {
  const wrong = wrongCount.value;
  if (wrong <= 5) return '92 - 100%';
  if (wrong <= 13) return '81 - 91%';
  if (wrong <= 22) return '67 - 80%';
  if (wrong <= 33) return '50 - 66%';
  if (wrong <= 60) return '30 - 49%';
  return '0 - 29%';
});

function nextPage() {
  if (page.value < 7) page.value++;
}
function prevPage() {
  if (page.value > 1) page.value--;
}
function toggleMark(marks: boolean[], index: number) {
  marks[index] = !marks[index];
}
</script>

<template>
  <Head title="Konzentrationstest" />
  <div class="p-4">
    <div v-if="page === 1">
      <h1 class="text-2xl font-bold mb-4">Seite 1</h1>
      <div v-for="(q, i) in page1Questions" :key="i" class="mb-2">
        <span class="mr-2">{{ q.seq }}</span>
        <Input v-model="page1Responses[i]" class="inline w-24" />
      </div>
    </div>

    <div v-else-if="page === 2">
      <h1 class="text-2xl font-bold mb-4">Seite 2</h1>
      <p class="mb-4">Nutzen Sie die Information auf Seite 3 und geben Sie die richtige Antwort ein.</p>
      <Input v-model="page2Answer" placeholder="Antwort" class="w-64" />
    </div>

    <div v-else-if="page === 3">
      <h1 class="text-2xl font-bold mb-4">Seite 3</h1>
      <p>Referenzinformationen für Seite 2.</p>
      <p class="mt-2 text-sm">Beispieldaten: Beispielstraße 1, 12345 Beispielstadt</p>
    </div>

    <div v-else-if="page === 4">
      <h1 class="text-2xl font-bold mb-4">Seite 4</h1>
      <p>Prüfen Sie die Schreibweise und markieren Sie Fehler auf Seite 5.</p>
    </div>

    <div v-else-if="page === 5">
      <h1 class="text-2xl font-bold mb-4">Seite 5</h1>
      <div class="mb-4">
        <span
          v-for="(ch, i) in page5Text.split(' ')"
          :key="i"
          @click="toggleMark(page5Marks, i)"
          :class="{'mark-over': page5Marks[i]}"
          class="cursor-pointer px-1"
        >{{ ch }}</span>
      </div>
      <Input v-model="page5Sum" placeholder="Summe der Fehler" class="w-48" />
    </div>

    <div v-else-if="page === 6">
      <h1 class="text-2xl font-bold mb-4">Seite 6</h1>
      <p class="mb-2">Zählen Sie die Anzahl der 6er in jeder Zeile.</p>
      <div v-for="(row, i) in page6Rows" :key="i" class="mb-2">
        <span class="font-mono mr-2">{{ row }}</span>
        <Input v-model="page6Answers[i]" class="inline w-16" />
      </div>
    </div>

    <div v-else-if="page === 7">
      <h1 class="text-2xl font-bold mb-4">Seite 7</h1>
      <div class="mb-4">
        <span
          v-for="(ch, i) in page7Chars"
          :key="i"
          @click="toggleMark(page7Marks, i)"
          :class="{'underline-double': page7Marks[i]}"
          class="cursor-pointer px-1"
        >{{ ch }}</span>
      </div>
      <Input v-model="page7Total" placeholder="Gefundene Zeichen" class="w-48" />
      <div class="mt-4">
        <p>Falsche Antworten: {{ wrongCount }}</p>
        <p>Prozent: {{ percentage }}</p>
      </div>
    </div>

    <div class="mt-6 flex gap-2">
      <Button @click="prevPage" v-if="page > 1">Zurück</Button>
      <Button @click="nextPage" v-if="page < 7">Weiter</Button>
    </div>
  </div>
</template>

<style scoped>
.mark-over {
  text-decoration: overline red;
}
.underline-double {
  text-decoration: underline red;
  text-decoration-thickness: 2px;
}
</style>

