<!-- resources/js/Pages/Konzentrationstest.vue -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps<{
  pauseEnabled?: boolean;
}>();
const emit = defineEmits(['complete', 'pause']);

// ---------- NAV ----------
const page = ref(1)
const nextPage = () => { if (page.value < 7) page.value++ }
const prevPage = () => { if (page.value > 1) page.value-- }
const toggleMark = (marks: boolean[], i: number) => (marks[i] = !marks[i])

/* =====================================================================================
 * PAGE 1
 * ===================================================================================== */
const page1Series: string[][] = [
  ['20','10','16','8','14','7'],
  ['2','7','8','13','14','19','20','25'],
  ['7','9','13','21','37','69'],
  ['12','11','21','20','30','29'],
  ['8','24','17','51','44','132'],
  ['17','20','24','29','35','42'],
  ['30','10','60','20','120','40','240','80'],
  ['5','15','10','30','25','75','70'],
  ['30','15','20','10','15','7,5','12,5','6,25'],
  ['25','28','56','59','118','121'],
]
const page1Inputs = ref<string[]>(Array(page1Series.length).fill(''))
const page1Correct = ['13','26','133','39','125','50','480','210','11,25','242']

/* =====================================================================================
 * PAGE 2  (image with overlayed inputs pinned to FAR RIGHT)
 * ===================================================================================== */
import page2Png from '@/assets/konz/page2tall.png'

// Never upscale beyond the asset
const page2NaturalWidth = ref<number>(0)
const onPage2ImgLoad = (e: Event) => {
  const img = e.target as HTMLImageElement
  page2NaturalWidth.value = img.naturalWidth || 760
}
// container width: min(image width, 820px)
const containerWidth = computed(() => Math.min(page2NaturalWidth.value || 760, 820))

const page2Answers = ref<string[]>(Array(10).fill(''))

// ---- Vertical positions (centers) as % of image height ----
// Tweak rowStart to nudge all inputs up/down; tweak rowGap for spacing.
const rowStart = 4.7
const rowGap   = 10
const rowTopOffsets = Array.from({ length: 10 }, (_, i) =>
  +(rowStart + i * rowGap).toFixed(2)
)

// ---- Horizontal/right pinning ----
// Smaller = further right. Use negative to go a hair OUTSIDE the image if needed.
const answerRightPercent = -15  // <= pushed to the far right
const boxWidthPercent  = 8.8     // a bit wider to fill the right box nicely
const boxHeightPercent = 8.8

// Optional debug overlay to help alignment
const debugAlign = ref(false)

// (Optional) fill to enable auto-scoring for page 2
const page2Correct = Array(10).fill('')

/* =====================================================================================
 * PAGE 3 (answers sheet image)
 * ===================================================================================== */
import page3Png from '@/assets/konz/page3tall.png'

/* =====================================================================================
 * PAGE 5–7 placeholders
 * ===================================================================================== */
const page2Answer = ref('')
const page2CorrectPlaceholder = 'Example'

const page5Text = 'u n u u n u'
const page5Marks = ref<boolean[]>(page5Text.split(' ').map(() => false))
const page5Sum = ref('')

const page6Rows = ['896586569856','658969856985','985689568965']
const page6Answers = ref<string[]>(Array(page6Rows.length).fill(''))
const page6Correct = ['3','2','4']

const page7Chars = ['A','%','B','$','C','&','D']
const page7Marks = ref<boolean[]>(page7Chars.map(() => false))
const page7Total = ref('')

/* =====================================================================================
 * SCORING (Page 1 + optional Page 2 + Page 6)
 * ===================================================================================== */
const toNum = (s: string) => {
  if (!s) return null
  const n = parseFloat(s.replace(',', '.').trim())
  return Number.isFinite(n) ? n : null
}

const wrongCount = computed(() => {
  let wrong = 0

  // Page 1
  page1Correct.forEach((ans, i) => {
    const exp = toNum(ans)!
    const got = toNum(page1Inputs.value[i] || '')
    if (got === null || got !== exp) wrong++
  })

  // Page 2 (only if keys provided)
  page2Correct.forEach((ans, i) => {
    if (!ans) return
    if ((page2Answers.value[i] || '').trim() !== ans.trim()) wrong++
  })

  // Page 6
  page6Rows.forEach((_, i) => {
    if ((page6Answers.value[i] || '').trim() !== page6Correct[i]) wrong++
  })

  return wrong
})

const percentage = computed(() => {
  const wrong = wrongCount.value
  if (wrong <= 5) return '92 - 100%'
  if (wrong <= 13) return '81 - 91%'
  if (wrong <= 22) return '67 - 80%'
  if (wrong <= 33) return '50 - 66%'
  if (wrong <= 60) return '30 - 49%'
  return '0 - 29%'
})
</script>

<template>
  <Head title="Konzentrationstest" />
  <div class="p-4 md:p-8 max-w-[900px] mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Konzentrationstest</h1>
        <Button
            :disabled="!pauseEnabled"
            @click="emit('pause')"
            variant="secondary"
        >
            Pause
        </Button>
    </div>
    <!-- ======================== PAGE 1 ======================== -->
    <div v-if="page === 1">
      <div class="flex items-baseline gap-2">
        <span class="text-2xl font-semibold">1</span>
        <span class="text-sm">(je 1 Fehlerpunkt)</span>
      </div>
      <div class="h-[3px] bg-black my-2"></div>

      <p class="text-[14px] leading-snug">
        Die folgenden Zahlenreihen sind nach einer bestimmten Gesetzmäßigkeit aufgebaut.
      </p>
      <p class="text-[14px] leading-snug">
        Durch welche Zahl wird die jeweilige Zahlenreihe logisch fortgesetzt?
      </p>
      <p class="text-[14px] leading-snug mb-5">
        Tragen Sie bitte die entsprechende Zahl in das jeweils dafür vorgesehene Kästchen ein.
      </p>

      <ul class="list-disc pl-6 space-y-6">
        <li
          v-for="(row, i) in page1Series"
          :key="i"
          class="grid grid-cols-[1fr_auto] items-center gap-4"
        >
          <div class="flex flex-wrap items-center">
            <span class="whitespace-pre text-[15px]">{{ row.join(' . ') }} . ?</span>
          </div>
          <input v-model="page1Inputs[i]" class="answer-box justify-self-end" inputmode="numeric" />
        </li>
      </ul>
    </div>

    <!-- ======================== PAGE 2 ======================== -->
    <div v-else-if="page === 2">
      <div class="flex items-baseline gap-2">
        <span class="text-2xl font-semibold">2</span>
        <span class="text-sm">(je 1 Fehlerpunkt)</span>
        <Button class="ml-3" variant="outline" @click="debugAlign = !debugAlign">Align helper</Button>
      </div>
      <div class="h-[3px] bg-black my-2"></div>

      <p class="text-[14px] leading-snug">
        Aus den untenstehenden Bilderreihen lassen sich bestimmte Folgerungen und
        Gesetzmäßigkeiten ableiten. Wählen Sie auf der folgenden Seite das Bild,
        das die Bilderreihe sinnvoll vervollständigt, und tragen Sie die entsprechende
        Zahl <b>[1] bis [4]</b> in das Kästchen ein.
      </p>

      <!-- Image + overlay inputs -->
      <div class="relative mx-auto mt-5" :style="{ width: containerWidth + 'px' }">
        <img
          :src="page2Png"
          alt="Bilderreihen (Fragen)"
          class="w-full h-auto block select-none test-img"
          @load="onPage2ImgLoad"
        />

        <!-- 10 overlay inputs pinned to the FAR-RIGHT column -->
        <template v-for="(top, i) in rowTopOffsets" :key="i">
          <input
            v-model="page2Answers[i]"
            class="answer-box absolute"
            inputmode="numeric"
            maxlength="1"
            :style="{
              top: top + '%',
              right: answerRightPercent + '%',
              width: boxWidthPercent + '%',
              height: boxHeightPercent + '%',
              transform: 'translateY(-50%)'
            }"
          />
          <!-- optional visual guide of target area -->
          <div
            v-if="debugAlign"
            class="absolute pointer-events-none ring-2 ring-fuchsia-500/60"
            :style="{
              top: top + '%',
              right: (answerRightPercent) + '%',
              width: boxWidthPercent + '%',
              height: boxHeightPercent + '%',
              transform: 'translateY(-50%)'
            }"
          />
        </template>
      </div>

      <p class="text-xs text-gray-600 mt-3">
        Tipp: Auf Seite 3 finden Sie die vier Spalten mit Antwort-Bildern, jeweils mit [1]–[4] beschriftet.
        Tragen Sie hier die Zahl ein, nicht das Bild.
      </p>
    </div>

    <!-- ======================== PAGE 3 ======================== -->
    <div v-else-if="page === 3">
      <div class="flex items-baseline gap-2">
        <span class="text-2xl font-semibold">3</span>
      </div>
      <div class="h-[3px] bg-black my-2"></div>
      <p class="text-[14px]">Antworttafeln [1]–[4]:</p>
      <div class="mt-4 mx-auto max-w-[820px]">
        <img :src="page3Png" alt="Antwortspalten [1]–[4]" class="w-full h-auto block select-none" />
      </div>
    </div>

    <!-- ======================== PAGE 4 ======================== -->
    <div v-else-if="page === 4">
      <h1 class="text-2xl font-bold mb-4">Seite 4</h1>
      <p>Prüfen Sie die Schreibweise und markieren Sie Fehler auf Seite 5.</p>
    </div>

    <!-- ======================== PAGE 5 ======================== -->
    <div v-else-if="page === 5">
      <h1 class="text-2xl font-bold mb-4">Seite 5</h1>
      <div class="mb-4">
        <span
          v-for="(ch, i) in page5Text.split(' ')"
          :key="i"
          @click="toggleMark(page5Marks, i)"
          :class="{ 'mark-over': page5Marks[i] }"
          class="cursor-pointer px-1 select-none"
        >
          {{ ch }}
        </span>
      </div>
      <Input v-model="page5Sum" placeholder="Summe der Fehler" class="w-48" />
    </div>

    <!-- ======================== PAGE 6 ======================== -->
    <div v-else-if="page === 6">
      <h1 class="text-2xl font-bold mb-4">Seite 6</h1>
      <p class="mb-2">Zählen Sie die Anzahl der 6er in jeder Zeile.</p>
      <div v-for="(row, i) in page6Rows" :key="i" class="mb-2">
        <span class="font-mono mr-2 tracking-wider">{{ row }}</span>
        <Input v-model="page6Answers[i]" class="inline w-16" />
      </div>
    </div>

    <!-- ======================== PAGE 7 ======================== -->
    <div v-else-if="page === 7">
      <h1 class="text-2xl font-bold mb-4">Seite 7</h1>
      <div class="mb-4">
        <span
          v-for="(ch, i) in page7Chars"
          :key="i"
          @click="toggleMark(page7Marks, i)"
          :class="{ 'underline-double': page7Marks[i] }"
          class="cursor-pointer px-1 select-none"
        >
          {{ ch }}
        </span>
      </div>
      <Input v-model="page7Total" placeholder="Gefundene Zeichen" class="w-48" />

      <div class="mt-6 rounded-lg border p-4 bg-white/70">
        <div class="font-medium">Zwischenergebnis</div>
        <div class="text-sm mt-1">Falsche Antworten: <b>{{ wrongCount }}</b></div>
        <div class="text-sm">Prozent: <b>{{ percentage }}</b></div>
      </div>
    </div>

    <!-- NAV -->
    <div class="mt-8 flex gap-2">
      <Button @click="prevPage" v-if="page > 1">Zurück</Button>
      <Button @click="nextPage" v-if="page < 7">Weiter</Button>
    </div>
  </div>
</template>

<style scoped>
.test-img { image-rendering: crisp-edges; }

/* Small square inputs */
.answer-box {
  border: 1px solid #000;
  border-radius: 0;
  padding: 0;
  text-align: center;
  line-height: 1.5rem;
  font-size: 0.95rem;
  background: #fff;
  box-sizing: border-box; /* precise % sizing */
}

/* Page 1 fixed size */
ul .answer-box {
  width: 2rem;
  height: 1.5rem;
}

/* Page 5 marking */
.mark-over { text-decoration: overline; text-decoration-color: red; }

/* Page 7 double underline */
.underline-double {
  text-decoration-line: underline;
  text-decoration-style: double;
  text-decoration-color: red;
}
</style>
