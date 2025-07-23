<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, watch, nextTick } from 'vue'
import { Button } from '@/components/ui/button'

import { LMT_QUESTIONS, LMTQuestion } from '@/pages/Questions/LMTQuestions'

const breadcrumbs = [
  { title: 'Tests', href: '/tests' },
  { title: 'LMT', href: '/tests/lmt' },
]

const questions = ref<LMTQuestion[]>(LMT_QUESTIONS)

const showTest = ref(false)
const currentQuestionIndex = ref(0)

const userAnswers = ref<(number | null)[]>(Array(questions.value.length).fill(null))
const questionTimes = ref<number[]>(Array(questions.value.length).fill(0))
const questionStartTimestamps = ref<(number | null)[]>(Array(questions.value.length).fill(null))
const startTime = ref<number | null>(null)

const isTestComplete = computed(() => currentQuestionIndex.value >= questions.value.length)

const currentQuestion = computed(() =>
  currentQuestionIndex.value < questions.value.length
    ? questions.value[currentQuestionIndex.value]
    : null
)

function startTest() {
  showTest.value = true
  currentQuestionIndex.value = 0
  userAnswers.value = Array(questions.value.length).fill(null)
  questionTimes.value = Array(questions.value.length).fill(0)
  questionStartTimestamps.value = Array(questions.value.length).fill(null)
  startTime.value = null
}

function handlePrevClick() {
  stopTiming(currentQuestionIndex.value)
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  }
}

function handleNextClick() {
  stopTiming(currentQuestionIndex.value)

  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
  } else {
    currentQuestionIndex.value = questions.value.length
  }
}

function stopTiming(index: number) {
  const start = questionStartTimestamps.value[index]
  if (start !== null) {
    const elapsed = Math.round((Date.now() - start) / 1000)
    questionTimes.value[index] += elapsed
    questionStartTimestamps.value[index] = null
  }
}

function selectAnswer(optionIndex: number) {
  userAnswers.value[currentQuestionIndex.value] = optionIndex
}

function selectAnswerAndAdvance(optionIndex: number) {
  selectAnswer(optionIndex)
  stopTiming(currentQuestionIndex.value)
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
  } else {
    currentQuestionIndex.value = questions.value.length
  }
}

watch(currentQuestionIndex, async (newIndex, oldIndex) => {
  const now = Date.now()
  if (newIndex >= 0 && newIndex < questions.value.length) {
    if (!questionStartTimestamps.value[newIndex]) {
      questionStartTimestamps.value[newIndex] = now
    }
  }
  if (newIndex === 0 && startTime.value === null) {
    startTime.value = now
  }
  if (newIndex !== oldIndex && newIndex < questions.value.length) {
    await nextTick()
  }
}, { immediate: true })

const totalTimeTaken = computed(() => {
  return isTestComplete.value
    ? questionTimes.value.reduce((a, b) => a + b, 0)
    : null
})

function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–"
  if (sec < 60) return `${sec} Sekunden`
  const min = Math.round(sec / 60)
  return `${min} Minuten`
}
</script>

<template>

  <Head title="LMT" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-3xl mx-auto p-6 bg-white border rounded-lg shadow space-y-8">

      <!-- Start Screen -->
      <div v-if="!showTest && !isTestComplete" class="text-center space-y-6">
        <h2 class="text-3xl font-bold text-gray-800">Willkommen zum LMT-Test</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Dieser Test besteht aus {{ questions.length }} Fragen. Es wird jeweils eine Frage angezeigt.
          Die Zeit pro Frage wird gemessen.
          <br>
          <strong>Tipp:</strong> Doppelklick auf eine Option = Auswahl und Weiter.
        </p>
        <Button @click="startTest" class="px-8 py-3 text-lg font-semibold rounded">
          Test starten
        </Button>
      </div>

      <!-- Single-question test screen -->
      <div v-else-if="showTest && !isTestComplete && currentQuestion">
        <div class="flex flex-col space-y-6">
          <div class="text-sm text-gray-500">
            Frage {{ currentQuestionIndex + 1 }} von {{ questions.length }}
          </div>

          <h2 class="text-xl font-bold text-gray-800">
            {{ currentQuestion.text }}
          </h2>

          <div class="space-y-3">
            <button v-for="(opt, optIndex) in currentQuestion.options" :key="optIndex" @click="selectAnswer(optIndex)"
              @dblclick="selectAnswerAndAdvance(optIndex)"
              class="w-full text-left border rounded p-3 transition-all select-none" :class="{
                'bg-blue-600 text-white border-blue-600': userAnswers[currentQuestionIndex] === optIndex,
                'hover:bg-blue-50': userAnswers[currentQuestionIndex] !== optIndex
              }">
              {{ opt.category }}
            </button>
          </div>

          <div class="flex justify-between mt-8">
            <Button @click="handlePrevClick" variant="outline" :disabled="currentQuestionIndex === 0">
              Zurück
            </Button>
            <Button @click="handleNextClick">
              Weiter
            </Button>
          </div>

          <p class="text-xs text-gray-400 mt-1">
            Tipp: Doppelklicken Sie auf eine Option, um sofort weiterzugehen.
          </p>
        </div>
      </div>

      <!-- Results Screen -->
      <div v-else-if="isTestComplete" class="space-y-8">
        <h2 class="text-2xl font-bold text-gray-800">Test abgeschlossen!</h2>
        <p class="text-gray-700">
          Gesamtdauer: <span class="font-semibold" :class="totalTimeTaken > 1800 ? 'text-red-600' : ''">
            {{ formatTime(totalTimeTaken) }}
          </span>
        </p>

        <div class="overflow-x-auto">
          <table class="min-w-full border rounded shadow text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
              <tr>
                <th class="p-2 text-left">#</th>
                <th class="p-2 text-left">Frage</th>
                <th class="p-2 text-left">Antwort</th>
                <th class="p-2 text-left">Bearbeitungszeit</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(q, idx) in questions" :key="idx" class="border-t hover:bg-gray-50">
                <td class="p-2">{{ q.number }}</td>
                <td class="p-2">{{ q.text }}</td>
                <td class="p-2">
                  <span v-if="userAnswers[idx] !== null">
                    {{ q.options[userAnswers[idx]!].category }}
                  </span>
                  <span v-else>–</span>
                </td>
                <td class="p-2">{{ formatTime(questionTimes[idx]) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="text-center">
          <Button @click="startTest" variant="outline" class="mt-6">
            Test erneut starten
          </Button>
        </div>
      </div>

      <div v-else>
        <p class="text-gray-600">Fragen werden geladen...</p>
      </div>
    </div>
  </AppLayout>
</template>
