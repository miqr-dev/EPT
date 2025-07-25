<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'

import { LMT_QUESTIONS, LMTQuestion } from '@/pages/Questions/LMTQuestions'

const normTable = {
  L1: [22, 29, 34, 37, 41, 45, 47.5, 50, 54, 58, 62, 66, 70, 76, 81],
  L2: [30, 36, 41, 46, 50, 54, 58, 62, 65, 68, 71, 74, 78],
  "F-": [24, 30, 33, 35, 38, 41, 42, 45, 46, 49, 51, 54, 57, 58, 60, 63, 66, 71, 76],
  "F+": [33, 39, 43, 47, 50, 52.5, 55, 58, 62, 66, 75],
}

const groupScores = computed(() => {
  const totals: Record<'L1' | 'L2' | 'F+' | 'F-', number> = {
    L1: 0,
    L2: 0,
    'F+': 0,
    'F-': 0,
  }

  questions.value.forEach((q, i) => {
    const selectedIdx = userAnswers.value[i]
    if (selectedIdx !== null) {
      const opt = q.options[selectedIdx]
      if (opt.group) {
        totals[opt.group] += opt.points
      }
    }
  })

  return totals
})

function interpretTValue(group: 'L1' | 'L2' | 'F+' | 'F-', tValue: number | null): string {
  if (tValue === null) return '–'

  if (group === 'L1') {
    if (tValue <= 30) return 'nicht motiviert, unwillig'
    if (tValue <= 39) return 'wenig motiviert, kaum leistungsbereit'
    if (tValue <= 60) return 'Ø motiviert, leistungsorientiert'
    if (tValue <= 65) return 'motiviert, leistungsbereit'
    return '(sehr) motiviert, leistungswillig'
  }

  if (group === 'L2') {
    if (tValue <= 39) return 'interessenabhängig'
    if (tValue <= 44) return 'tendenziell interessenabhängig'
    if (tValue <= 55) return 'Ø ehrgeizig & ausdauernd'
    if (tValue <= 60) return 'tendenziell interessenunabhängig'
    return 'interessenunabhängig'
  }

  if (group === 'F-') {
    if (tValue <= 39) return 'keine prüfungsängstliche Neigung'
    if (tValue <= 44) return 'geringe prüfungsängstliche Neigung'
    if (tValue <= 55) return 'Ø'
    if (tValue <= 60) return 'tendenziell prüfungsängstlich'
    return 'Misserfolgsfurcht, Prüfungsangst → führt zu Leistungseinschränkung'
  }

  if (group === 'F+') {
    if (tValue <= 39) return 'keine prüfungsförderliche Strategien'
    if (tValue <= 44) return 'einige prüfungsförderliche Strategien'
    if (tValue <= 55) return 'Ø'
    if (tValue <= 60) return 'tendenziell guter Einsatz vorhandener Strategien'
    return 'prüfungsförderliche Strategien → führt zu Leistungs­steigerung'
  }

  return '–'
}

function getTValue(group: 'L1' | 'L2' | 'F+' | 'F-'): number | null {
  const raw = groupScores.value[group]
  const table = normTable[group]
  return raw < table.length ? table[raw] : null
}

const breadcrumbs = [
  { title: 'Tests', href: '/tests' },
  { title: 'LMT', href: '/tests/lmt' },
]

const questions = ref<LMTQuestion[]>(LMT_QUESTIONS)

const showTest = ref(false)
const currentPage = ref(1)
const questionsPerPage = 15

// Tracks user's selected option index per question
const userAnswers = ref<(number | null)[]>(Array(questions.value.length).fill(null))

// Time tracking
const questionTimes = ref<number[]>(Array(questions.value.length).fill(0))
const questionStartTimestamps = ref<(number | null)[]>(Array(questions.value.length).fill(null))
const startTime = ref<number | null>(null)

const totalPages = computed(() =>
  Math.ceil(questions.value.length / questionsPerPage)
)

const isTestComplete = ref(false)

const questionsOnPage = computed(() => {
  const start = (currentPage.value - 1) * questionsPerPage
  const end = start + questionsPerPage
  return questions.value.slice(start, end)
})

function startTest() {
  showTest.value = true
  currentPage.value = 1
  isTestComplete.value = false
  userAnswers.value = Array(questions.value.length).fill(null)
  questionTimes.value = Array(questions.value.length).fill(0)
  questionStartTimestamps.value = Array(questions.value.length).fill(null)
  startTime.value = Date.now()

  // Start timing for visible questions
  questionsOnPage.value.forEach(q => {
    questionStartTimestamps.value[q.number - 1] = Date.now()
  })
}

function handleNextPage() {
  stopTimingCurrentPage()

  if (currentPage.value < totalPages.value) {
    currentPage.value++
    startTimingCurrentPage()
  } else {
    completeTest()
  }
}

function handlePrevPage() {
  stopTimingCurrentPage()

  if (currentPage.value > 1) {
    currentPage.value--
    startTimingCurrentPage()
  }
}

function startTimingCurrentPage() {
  const now = Date.now()
  questionsOnPage.value.forEach(q => {
    const index = q.number - 1
    if (!questionStartTimestamps.value[index]) {
      questionStartTimestamps.value[index] = now
    }
  })
}

function stopTimingCurrentPage() {
  const now = Date.now()
  questionsOnPage.value.forEach(q => {
    const index = q.number - 1
    const start = questionStartTimestamps.value[index]
    if (start) {
      const elapsed = Math.round((now - start) / 1000)
      questionTimes.value[index] += elapsed
      questionStartTimestamps.value[index] = null
    }
  })
}

function completeTest() {
  stopTimingCurrentPage()
  isTestComplete.value = true
  showTest.value = false
}

function selectAnswer(questionIndex: number, optionIndex: number) {
  userAnswers.value[questionIndex] = optionIndex
}

function formatTime(sec: number | null): string {
  if (sec === null || isNaN(sec)) return "–"
  if (sec < 60) return `${sec} Sekunden`
  const min = Math.round(sec / 60)
  return `${min} Minuten`
}

const totalTimeTaken = computed(() => {
  return isTestComplete.value
    ? questionTimes.value.reduce((a, b) => a + b, 0)
    : null
})
</script>

<template>

  <Head title="LMT" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-5xl mx-auto p-6 bg-white border rounded shadow space-y-8">

      <!-- Start Screen -->
      <div v-if="!showTest && !isTestComplete" class="text-center space-y-6">
        <h2 class="text-3xl font-bold text-gray-800">Willkommen zum LMT-Test</h2>
        <p class="text-gray-600 max-w-3xl mx-auto">
          Dieser Test besteht aus {{ questions.length }} Fragen, aufgeteilt auf {{ totalPages }} Seiten.
        </p>
        <Button @click="startTest" class="px-8 py-3 text-lg font-semibold rounded">
          Test starten
        </Button>
      </div>

      <!-- Test Pages -->
      <div v-else-if="showTest && !isTestComplete">
        <h2 class="text-xl font-bold text-gray-800 mb-6">
          Seite {{ currentPage }} von {{ totalPages }}
        </h2>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm table-fixed border-separate border-spacing-y-0">
            <tbody>
              <tr v-for="q in questionsOnPage" :key="q.number" class="border-t border-gray-300">
                <td class="w-1/2 pr-4 font-medium text-gray-800 whitespace-normal align-top py-4">
                  {{ q.number }}. {{ q.text }}
                </td>
                <td class="w-1/2 align-top py-4">
                  <div class="flex flex-col gap-2 w-full">
                    <label v-for="(opt, idx) in q.options" :key="idx"
                      class="flex justify-between items-center border rounded px-3 py-2 cursor-pointer hover:bg-gray-50 transition w-full"
                      :class="{
                        'bg-blue-50 border-blue-300': userAnswers[q.number - 1] === idx
                      }">
                      <span class="text-sm text-gray-700">
                        {{ opt.category }}
                      </span>
                      <input type="radio" :name="'question-' + q.number"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        :checked="userAnswers[q.number - 1] === idx" @change="selectAnswer(q.number - 1, idx)" />
                    </label>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex justify-between mt-8">
          <Button @click="handlePrevPage" variant="outline" :disabled="currentPage === 1">
            Zurück
          </Button>

          <Button @click="handleNextPage">
            {{ currentPage === totalPages ? 'Test abschließen' : 'Weiter' }}
          </Button>
        </div>
      </div>

      <!-- Results Screen -->
      <div v-else-if="isTestComplete" class="space-y-8">
        <h2 class="text-2xl font-bold text-gray-800">Test abgeschlossen!</h2>
        <p class="text-gray-700">
          Gesamtdauer:
          <span class="font-semibold" :class="totalTimeTaken > 1800 ? 'text-red-600' : ''">
            {{ formatTime(totalTimeTaken) }}
          </span>
        </p>

        <ul class="grid grid-cols-2 gap-x-6 text-sm text-gray-700">
          <li>L1 – Rohwert: <strong>{{ groupScores.L1 }}</strong></li>
          <li>L2 – Rohwert: <strong>{{ groupScores.L2 }}</strong></li>
          <li>F⁺ – Rohwert: <strong>{{ groupScores['F+'] }}</strong></li>
          <li>F⁻ – Rohwert: <strong>{{ groupScores['F-'] }}</strong></li>
        </ul>

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
          <div class="space-y-4 mt-6">
            <h3 class="text-lg font-semibold text-gray-800">LMT – Leistungs-Motivations-Fragebogen</h3>
            <table class="min-w-full text-sm border rounded shadow">
              <thead class="bg-gray-100">
                <tr>
                  <th class="p-2 text-left">Skala</th>
                  <th class="p-2 text-left">Beschreibung</th>
                  <th class="p-2 text-left">T-Wert</th>
                  <th class="p-2 text-left">Interpretation der Ergebnisse in Worten</th>
                </tr>
              </thead>
              <tbody class="text-gray-800">
                <tr class="border-t">
                  <td class="p-2 font-semibold">L1</td>
                  <td class="p-2">Leistungsstreben: Motivation/-wille</td>
                  <td class="p-2">{{ getTValue('L1') ?? '–' }}</td>
                  <td class="p-2">{{ interpretTValue('L1', getTValue('L1')) }}</td>
                </tr>
                <tr class="border-t">
                  <td class="p-2 font-semibold">L2</td>
                  <td class="p-2">Ausdauer/Fleiß</td>
                  <td class="p-2">{{ getTValue('L2') ?? '–' }}</td>
                  <td class="p-2">{{ interpretTValue('L2', getTValue('L2')) }}</td>
                </tr>
                <tr class="border-t">
                  <td class="p-2 font-semibold">F−</td>
                  <td class="p-2">Misserfolgsfurcht: leistungsshemmend</td>
                  <td class="p-2">{{ getTValue('F-') ?? '–' }}</td>
                  <td class="p-2">{{ interpretTValue('F-', getTValue('F-')) }}</td>
                </tr>
                <tr class="border-t">
                  <td class="p-2 font-semibold">F⁺</td>
                  <td class="p-2">Misserfolgsfurcht: leistungsfördernd</td>
                  <td class="p-2">{{ getTValue('F+') ?? '–' }}</td>
                  <td class="p-2">{{ interpretTValue('F+', getTValue('F+')) }}</td>
                </tr>
              </tbody>
            </table>
          </div>



        </div>

        <div class="text-center">
          <Button @click="startTest" variant="outline" class="mt-6">
            Test erneut starten
          </Button>
        </div>
      </div>

      <!-- Loading -->
      <div v-else>
        <p class="text-gray-600">Fragen werden geladen...</p>
      </div>

    </div>
  </AppLayout>
</template>
