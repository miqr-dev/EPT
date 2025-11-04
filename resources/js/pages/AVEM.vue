
<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AVEM_QUESTIONS } from '@/pages/Questions/AVEMQuestions'
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

const emit = defineEmits(['complete'])

const showTest = ref(false)
const answers = ref<Record<number, number | null>>({})
const endConfirmOpen = ref(false)

const { isForcedFinish, forcedFinishCountdown, clearForcedFinish } = useTeacherForceFinish({
  isActive: () => showTest.value,
  onStart: () => {
    endConfirmOpen.value = true
    window.dispatchEvent(new Event('start-finish'))
  },
  onCountdownFinished: () => {
    confirmEnd()
  },
  onCancel: () => {
    if (endConfirmOpen.value) {
      window.dispatchEvent(new Event('cancel-finish'))
      endConfirmOpen.value = false
    }
  },
})

// init answers
AVEM_QUESTIONS.forEach((q) => (answers.value[q.number] = null))

const instructions = `Wir bitten Sie, einige Ihrer üblichen Verhaltensweisen, Einstellungen und Gewohnheiten zu beschreiben, wobei vor allem Ihr Arbeitsleben Bezug genommen wird. Dazu finden Sie im Folgenden eine Reihe von Aussagen. Lesen Sie jeden dieser Sätze gründlich und entscheiden Sie, in welchem Maße er auf Sie persönlich zutrifft.`

// Likert order to match the sheet (links = völlig zu, rechts = überhaupt nicht)
const LIKERT_ORDER = [5, 4, 3, 2, 1] as const

const LABELS: Record<number, string> = {
  5: 'trifft völlig zu',
  4: 'trifft überwiegend zu',
  3: 'teils/teils',
  2: 'trifft überwiegend nicht zu',
  1: 'trifft überhaupt nicht zu',
}

const LEGEND_TOP = [
  { val: 5, text: 'völlig zu', heightClass: 'h-6' },
  { val: 4, text: 'überwiegend', heightClass: 'h-10' },
  { val: 3, text: 'teils/teils', heightClass: 'h-14' },
  { val: 2, text: 'überwiegend nicht', heightClass: 'h-20' },
  { val: 1, text: 'trifft überhaupt nicht', heightClass: 'h-24' },
]

function startTest() {
  showTest.value = true
}
function finishTest() {
  window.dispatchEvent(new Event('start-finish'))
  endConfirmOpen.value = true
}
function cancelEnd() {
  if (isForcedFinish.value) {
    return
  }
  endConfirmOpen.value = false
  window.dispatchEvent(new Event('cancel-finish'))
  clearForcedFinish(false)
}
function confirmEnd() {
  clearForcedFinish(false)
  endConfirmOpen.value = false
  const results = {
    answers: AVEM_QUESTIONS.map((q) => ({ number: q.number, answer: answers.value[q.number] })),
  }
  emit('complete', results)
}

// UI helpers for answered/unanswered styling
const isAnswered = (qnum: number) => answers.value[qnum] !== null
const rowBgClass = (qnum: number) =>
  isAnswered(qnum) ? '' : 'bg-slate-50 dark:bg-slate-800/40'
const borderClass = (qnum: number) =>
  isAnswered(qnum)
    ? 'border-slate-100 dark:border-slate-800'
    : 'border-slate-300 dark:border-slate-500'
</script>

<template>
  <Head title="AVEM" />
  <div class="p-4">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold">AVEM</h1>
    </div>

    <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4">
      <div class="flex flex-1 flex-col gap-4">
        <!-- Intro + legend -->
        <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
          <div
            class="mb-6 w-full max-w-4xl rounded-lg border bg-yellow-50 p-4 text-base whitespace-pre-line text-foreground dark:bg-yellow-900"
          >
            {{ instructions }}
          </div>

          <!-- Exact first-page block -->
          <div class="w-full max-w-4xl">
            <div class="mb-3 text-[17px] leading-none text-gray-900 dark:text-gray-100">
              <strong>Bitte kreuzen Sie das jeweilige Zeichen an:</strong>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <!-- Left note box -->
              <div class="flex items-center border-2 border-gray-700 p-4 dark:border-gray-200">
                <div class="text-[15px] leading-6 text-gray-900 dark:text-gray-100">
                  <strong>Bitte beachten Sie:</strong><br />
                  <strong>Voller Kreis</strong> heißt, dass Sie der Aussage <strong>völlig</strong> zustimmen,<br />
                  <strong>leerer Kreis</strong> heißt, dass Sie <strong>überhaupt nicht</strong> zustimmen.
                </div>
              </div>

              <!-- Right legend with arrows and icons -->
              <div class="flex flex-col">
                <div class="mb-2 text-[15px] text-gray-900 dark:text-gray-100">Die Aussage…</div>

                <div class="grid grid-cols-5 items-end gap-4">
                  <div
                    v-for="item in LEGEND_TOP"
                    :key="item.val"
                    class="flex flex-col items-center"
                  >
                    <!-- label with vertical arrow -->
                    <div class="mb-1 text-[13px] text-gray-800 dark:text-gray-200 text-center">
                      {{ item.text }}
                    </div>
                    <div class="flex flex-col items-center mb-2">
                         <div class="w-px bg-gray-700 dark:bg-gray-200" :class="item.heightClass"></div>

                      <div
                        class="mt-[1px]"
                        style="width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-top:6px solid currentColor;"
                        :class="'text-gray-700 dark:text-gray-200'"
                      ></div>
                    </div>

                    <!-- Icon (same as test options; has title for tooltip) -->
                    <svg class="h-7 w-7 text-gray-900 dark:text-gray-200" viewBox="0 0 24 24" aria-hidden="true" :title="LABELS[item.val]">
                      <defs v-if="item.val === 4">
                        <mask :id="`legend-m75-${item.val}`">
                          <rect x="0" y="0" width="24" height="24" fill="white" />
                          <!-- cut out the top-right quadrant -->
                          <path d="M12 12 L12 3 A9 9 0 0 1 21 12 Z" fill="black" />
                        </mask>
                      </defs>

                      <g v-if="item.val === 5">
                        <circle cx="12" cy="12" r="9" fill="currentColor" />
                      </g>
                      <g v-else-if="item.val === 4">
                        <circle cx="12" cy="12" r="9" fill="currentColor" :mask="`url(#legend-m75-${item.val})`" />
                      </g>
                      <g v-else-if="item.val === 3">
                        <path d="M12 12 L12 3 A9 9 0 0 0 12 21 Z" fill="currentColor" />
                      </g>
                      <g v-else-if="item.val === 2">
                        <path d="M12 12 L12 3 A9 9 0 0 0 3 12 Z" fill="currentColor" />
                      </g>
                      <!-- 0% -> empty -->

                      <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none" />
                    </svg>
                  </div>
                </div>

                <div class="mt-3 text-sm italic text-gray-700 dark:text-gray-300">
                  Hinweis: Sie können den Mauszeiger auf ein Symbol halten, um den entsprechenden Text zu sehen.
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6">
            <Button @click="startTest" class="rounded-xl px-8 py-3 text-lg font-semibold shadow">Test starten</Button>
          </div>
        </div>

        <!-- Test table -->
        <div v-else class="overflow-x-auto rounded-lg border bg-background p-6">
          <table class="w-full border-separate text-base" style="border-spacing: 0">
            <tbody>
              <tr
                v-for="q in AVEM_QUESTIONS"
                :key="q.number"
                :class="rowBgClass(q.number)"
              >
                <td
                  class="w-8 border-b-2 pt-2 pr-2 text-right align-top font-mono"
                  :class="borderClass(q.number)"
                >
                  {{ q.number }}.
                </td>

                <td
                  class="border-b-2 pt-2 pr-4 align-top"
                  :class="borderClass(q.number)"
                >
                  {{ q.text }}
                </td>

                <!-- Options -->
                <td
                  v-for="opt in LIKERT_ORDER"
                  :key="opt"
                  class="border-b-2 px-2 pt-1 text-center align-top"
                  :class="borderClass(q.number)"
                >
                  <label
                    class="relative inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-full transition
                           hover:scale-[1.04] ring-offset-2 ring-offset-background"
                    :class="answers[q.number] === opt
                      ? 'ring-2 ring-blue-500 dark:ring-blue-400'
                      : 'ring-1 ring-gray-300 dark:ring-gray-600'"
                    :title="LABELS[opt]"
                    :aria-label="LABELS[opt]"
                  >
                    <input
                      class="sr-only"
                      type="radio"
                      :name="'q' + q.number"
                      :value="opt"
                      v-model="answers[q.number]"
                    />

                    <!-- Icon (true pie slices) -->
                    <svg
                      class="h-7 w-7"
                      :class="answers[q.number] === opt ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200'"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                    >
                      <!-- unique mask for 75% to avoid ID clashes -->
                      <defs v-if="opt === 4">
                        <mask :id="`m75-${q.number}-${opt}`">
                          <rect x="0" y="0" width="24" height="24" fill="white" />
                          <!-- cut out the top-right quadrant -->
                          <path d="M12 12 L12 3 A9 9 0 0 1 21 12 Z" fill="black" />
                        </mask>
                      </defs>

                      <!-- Fills -->
                      <g v-if="opt === 5">
                        <!-- 100% -->
                        <circle cx="12" cy="12" r="9" fill="currentColor" />
                      </g>
                      <g v-else-if="opt === 4">
                        <!-- 75% -->
                        <circle cx="12" cy="12" r="9" fill="currentColor" :mask="`url(#m75-${q.number}-${opt})`" />
                      </g>
                      <g v-else-if="opt === 3">
                        <!-- 50% (left half) -->
                        <path d="M12 12 L12 3 A9 9 0 0 0 12 21 Z" fill="currentColor" />
                      </g>
                      <g v-else-if="opt === 2">
                        <!-- 25% (top-left quarter) -->
                        <path d="M12 12 L12 3 A9 9 0 0 0 3 12 Z" fill="currentColor" />
                      </g>
                      <!-- 0% -> no fill -->

                      <!-- outline -->
                      <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none" />
                    </svg>
                  </label>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="mt-4 flex justify-end">
            <Button variant="destructive" @click="finishTest">Test beenden</Button>
          </div>
        </div>
      </div>
    </div>

    <Dialog v-model:open="endConfirmOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Test beenden</DialogTitle>
          <DialogDescription v-if="!isForcedFinish">Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück.</DialogDescription>
          <DialogDescription v-else>Der Test wird automatisch in {{ forcedFinishCountdown }} Sekunden beendet.</DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <Button v-if="!isForcedFinish" variant="secondary" @click="cancelEnd">Abbrechen</Button>
          <Button variant="destructive" @click="confirmEnd">{{ isForcedFinish ? 'Jetzt beenden' : 'Ja' }}</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
