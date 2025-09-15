<script setup lang="ts">
import { computed } from 'vue'
import { AVEM_QUESTIONS } from '@/pages/Questions/AVEMQuestions'

const props = defineProps<{ results: any }>()

const LABELS: Record<number, string> = {
  5: 'trifft völlig zu',
  4: 'trifft überwiegend zu',
  3: 'teils/teils',
  2: 'trifft überwiegend nicht zu',
  1: 'trifft überhaupt nicht zu',
}

const questionMap = AVEM_QUESTIONS.reduce((acc, q) => {
  acc[q.number] = q.text
  return acc
}, {} as Record<number, string>)

const rows = computed(() => {
  return (props.results?.answers ?? []).map((ans: any, idx: number) => ({
    number: ans.number ?? idx + 1,
    question: questionMap[ans.number ?? idx + 1] ?? '',
    answer:
      ans.answer == null ? '–' : LABELS[Number(ans.answer)] ?? String(ans.answer),
  }))
})
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full border text-sm">
      <thead class="bg-muted/40 dark:bg-gray-700">
        <tr>
          <th class="px-2 py-1 text-left font-semibold">#</th>
          <th class="px-2 py-1 text-left font-semibold">Frage</th>
          <th class="px-2 py-1 text-left font-semibold">Antwort</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in rows" :key="row.number">
          <td class="px-2 py-1">{{ row.number }}</td>
          <td class="px-2 py-1">{{ row.question }}</td>
          <td class="px-2 py-1">{{ row.answer }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
