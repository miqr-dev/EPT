<!-- resources/js/Pages/Konzentrationstest.vue -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

/* =========================================================
   NAV
========================================================= */
const page = ref(1)
const MAX_PAGE = 5

const nextPage = () => {
  if (page.value < MAX_PAGE) page.value++
}
const prevPage = () => {
  if (page.value > 1) page.value--
}
const toggleMark = (marks: boolean[], i: number) => (marks[i] = !marks[i])

/* =========================================================
   PAGE 1
========================================================= */
const page1Series: string[][] = [
  ['20', '10', '16', '8', '14', '7'],
  ['2', '7', '8', '13', '14', '19', '20', '25'],
  ['7', '9', '13', '21', '37', '69'],
  ['12', '11', '21', '20', '30', '29'],
  ['8', '24', '17', '51', '44', '132'],
  ['17', '20', '24', '29', '35', '42'],
  ['30', '10', '60', '20', '120', '40', '240', '80'],
  ['5', '15', '10', '30', '25', '75', '70'],
  ['30', '15', '20', '10', '15', '7,5', '12,5', '6,25'],
  ['25', '28', '56', '59', '118', '121'],
]
const page1Inputs = ref<string[]>(Array(page1Series.length).fill(''))
const page1Correct = ['13', '26', '133', '39', '125', '50', '480', '210', '11,25', '242']

/* =========================================================
   PAGE 2 (merged: left image+inputs, right answers image)
========================================================= */
import page2Png from '@/assets/konz/page2tall.png'
import page3Png from '@/assets/konz/page3tall.png'

const page2NaturalWidth = ref<number>(0)
const onPage2ImgLoad = (e: Event) => {
  const img = e.target as HTMLImageElement
  page2NaturalWidth.value = img.naturalWidth || 760
}
const containerWidth = computed(() => Math.min(page2NaturalWidth.value || 760, 1100))

const page2Answers = ref<string[]>(Array(10).fill(''))
const rowStart = 4.7
const rowGap = 10
const rowTopOffsets = Array.from({ length: 10 }, (_, i) => +(rowStart + i * rowGap).toFixed(2))

// pin to far right (negative moves slightly outside)
const answerRightPercent = -15
const boxWidthPercent = 8.8
const boxHeightPercent = 8.8
const debugAlign = ref(false)

// optional scoring keys
const page2Correct = Array(10).fill('')

/* =========================================================
   PAGE 3 (Original LEFT, Abschrift RIGHT)  [was old page 4]
========================================================= */
type RowParts = [string, string, string, string]

const originalRows: Array<{ letter: string; parts: RowParts }> = [
  { letter: 'a', parts: ['Friedrich Berlenbeck', '445527 Hattingen', 'An der Hunsebeck 27', 'Tel. 0421 455773'] },
  { letter: 'b', parts: ['Friedel Feschner', '80768 Maienheim', 'Heissenstr. 4', 'Tel. 0943 557834'] },
  { letter: 'c', parts: ['Kordula Karlsferch', '47605 Kittenberg', 'Rellinghauser Str. 248', 'Tel. 0213 329823'] },
  { letter: 'd', parts: ['Dr. Mara Müller-Meerfeld', '20657 Bammenbach', 'Meracher Weg 3', 'Tel. 02592 2578-43'] },
  { letter: 'e', parts: ['Sybilla Vautenloh', '10678 Ickten', 'Banneken 4', 'Tel. 0553 365667'] },
  { letter: 'f', parts: ['Grazyna Rzymski', '76342 Waldfeldt', 'Mendelsche Gasse 3', 'Tel. 02435 450391'] },
  { letter: 'g', parts: ['Peter Waidtmanns', '98423 Schüttdorf', 'Karla-Wegemann-Str. 5', 'Tel. 07753 8942'] },
  { letter: 'h', parts: ['Maschinen Bortenbycks', '84932 Sahlfeeden', 'Bergerbeek 5-12', 'Tel. 0432 567409-0'] },
  { letter: 'i', parts: ['Dr. Sybille Schyrzyk', '87645 Mühlhausen', 'Bergerfeldstr. 5', 'Tel. 0876 786543'] },
  { letter: 'j', parts: ['Christoph Pehrmann', '56478 Esslingen', 'Mausegattstr. 5', 'Tel. 045 7874547'] },
  { letter: 'k', parts: ['Alicia Jablonski', '545632 Velbert', 'Johänntegesbrucher 8b', 'Tel. 0212 3840421'] },
  { letter: 'l', parts: ['Patrizia Iserloh-Konegen', '37273 Lethmate', 'Hacketäuerstr. 4', 'Tel. 0245 2248799'] },
  { letter: 'm', parts: ['Turkovic Elektro Fachhandel', '76321 Schöppingen', 'Obenrüdener Kotten 4', 'Tel. 03234 43973-3'] },
  { letter: 'n', parts: ['Slavica Kumarasamy', '54689 Meirhofen', 'Ernst-Woltmann-Str. 8', 'Tel. 0879 345345'] },
  { letter: 'o', parts: ['Hellmuth Spicker GmbH', '74839 Seeberg', 'Westerweider Str. 99', 'Tel. 02342 7734-3'] },
  { letter: 'p', parts: ['Kriskofski Umzüge', '93432 Strehlow', 'Richard-Wagner-Str. 3', 'Tel. 0217 8989-20'] },
  { letter: 'q', parts: ['Ruddeck Betonwaren', '80796 München', 'Entenpfhul 5', 'Tel. 0234 887745'] },
  { letter: 'r', parts: ['Kartonagenfabrik Großpietsch', '45363 Essen', 'Merowingerstr. 7', 'Tel. 0456 45638'] },
  { letter: 's', parts: ['Hermann Große-Naust', '13245 Rostock', 'Melanchthonstr. 4', 'Tel. 06534 73625'] },
  { letter: 't', parts: ['Autohaus Ayibogan', '33245 Duisburg', 'Am Kistner 3', 'Tel. 04567 43556'] },
  { letter: 'u', parts: ['Bauklempnerei Frickartz', '45637 Wesel', 'Am Röhr 40', 'Tel. 0443 876523'] },
  { letter: 'v', parts: ['Foto Forobosko', '23456 Lüneburg', 'Agnesstr. 14', 'Tel. 0442 275839'] },
]

const copyRows: Array<{ letter: string; parts: RowParts }> = [
  { letter: 'a', parts: ['Friedrich Berllenbeck', '45527 Hattingen', 'An der Hunsebeek 27', 'Tel. 0421 455773'] },
  { letter: 'b', parts: ['Friedel Fechner', '80768 Meienheim', 'Heisssenstr. 4', 'Tel. 0943 567834'] },
  { letter: 'c', parts: ['Kordula Kalsferch', '47605 Kittenberg', 'Relllinghauser Str. 248', 'Tel. 0213 32982'] },
  { letter: 'd', parts: ['Dr. Mara Müller-Mehrfeld', '20657 Bammenbach', 'Meracher Weg 3', 'Tel. 02592 2578-43'] },
  { letter: 'e', parts: ['Sibylla Vautenloh', '10678 Ickten', 'Bannecken 4', 'Tel. 0553 365667'] },
  { letter: 'f', parts: ['Gracyna Rzymski', '76342 Waldfeld', 'Mendelsche Gasse 3', 'Tel. 024355 450391'] },
  { letter: 'g', parts: ['Peter Waidtmanns', '98423 Schüttdorf', 'Karla-Wegemann-Str. 5', 'Tel. 07753 8942'] },
  { letter: 'h', parts: ['Maschienen Bortenbycks', '84932 Sahlfeeden', 'Bergerbek 5-12', 'Tel. 0432 567409-0'] },
  { letter: 'i', parts: ['Dr. Sybille Schyrzik', '87645 Mülhausen', 'Bergerfeldstr. 5', 'Tel. 0876 786543'] },
  { letter: 'j', parts: ['Christoph Peermann', '56478 Esßlingen', 'Mausegatstr. 5', 'Tel. 045 7875447'] },
  { letter: 'k', parts: ['Alizia Jablonski', '45633 Velbert', 'Johänntegesbrucher 8b', 'Tel. 0212 3840421'] },
  { letter: 'l', parts: ['Patrizia Iserloh-Kohnegen', '37273 Letmate', 'Hacketeuerstr. 4', 'Tel. 0245 2248799'] },
  { letter: 'm', parts: ['Turkovic Electro Fachhandel', '76321 Schöppingen', 'Oberrüdener Kotten 4', 'Tel. 03234 43973-3'] },
  { letter: 'n', parts: ['Slaviza Kumarasamy', '54689 Mairhofen', 'Ernst-Woltmann-Str. 8', 'Tel. 0879 345345'] },
  { letter: 'o', parts: ['Helmuth Spicker GmbH', '74839 Seeberg', 'Westerwaider Str. 99', 'Tel. 02341 7724-3'] },
  { letter: 'p', parts: ['Kriskofsky Umzüge', '93432 Strelow', 'Richard-Wagner-Str. 3', 'Tel. 0217 8999-20'] },
  { letter: 'q', parts: ['Ruddeck Betonwaren', '80796 München', 'Entenfuhl 5', 'Tel. 0234 887745'] },
  { letter: 'r', parts: ['Katonagenfabrik Großpietsch', '45363 Essen', 'Merowingerstr. 7', 'Tel. 0456 45638'] },
  { letter: 's', parts: ['Hermann Große-Naust', '13245 Rostock', 'Melanchthonstr. 4', 'Tel. 06543 73626'] },
  { letter: 't', parts: ['Autohaus Ayibogan', '33245 Dusiburg', 'Am Kistner 3', 'Tel. 04567 43566'] },
  { letter: 'u', parts: ['Bauklempnerei Frickartz', '45637 Wesel', 'Am Röhr 40', 'Tel. 0443 876523'] },
  { letter: 'v', parts: ['Foto Forrobosko', '23456 Lünenburg', 'Agnesstr. 14', 'Tel. 0442 275839'] },
]

// click-to-strike matrix & per-row error counts
const copyMarks = ref(copyRows.map(r => r.parts.map(p => Array.from(p).map(() => false))))
const copyCounts = ref<string[]>(Array(copyRows.length).fill(''))

const toggleCopyChar = (r: number, p: number, c: number) => {
  copyMarks.value[r][p][c] = !copyMarks.value[r][p][c]
}
const getChars = (r: number, p: number) => Array.from(copyRows[r].parts[p])

/* =========================================================
   PAGES 4–5 + scoring  [were old 6–7]
========================================================= */
const page5Text = 'u n u u n u'
const page5Marks = ref<boolean[]>(page5Text.split(' ').map(() => false))
const page5Sum = ref('')

const page4Rows = [
  '8985689565966559886565689695865958655689565',
  '5986956896866596589668586896859858695895895',
  '6859598586965868656968568668695855689585565',
  '5689865898589658959589595866569895655668956',
  '5689658985685996859658995689586596556699965',
  '6898658695865965995895969565958958695899586',
  '6859686869588686665596956698695956598695866',
  '6898869665858586965589696568968958695865696',
  '8686986998566585869968959998659685966659659',
  '8699868959685965858968596886955565998695586',
  '6589686958656666698568958665968595986596859',
  '5689665898589868995659896666566596659658586',
  '6986988865988595556886656869565995568558566',
  '8695685588685585668989586566985869568956686'
]
const page4Answers = ref<string[]>(Array(page4Rows.length).fill(''))
const page4Correct = ['3', '2', '4']

const page5Chars = ['A', '%', 'B', '$', 'C', '&', 'D']
const page5Marks2 = ref<boolean[]>(page5Chars.map(() => false))
const page5Total = ref('')

const toNum = (s: string) => {
  if (!s) return null
  const n = parseFloat(s.replace(',', '.').trim())
  return Number.isFinite(n) ? n : null
}

const wrongCount = computed(() => {
  let wrong = 0
  page1Correct.forEach((ans, i) => {
    const exp = toNum(ans)!
    const got = toNum(page1Inputs.value[i] || '')
    if (got === null || got !== exp) wrong++
  })
  page2Correct.forEach((ans, i) => {
    if (!ans) return
    if ((page2Answers.value[i] || '').trim() !== ans.trim()) wrong++
  })
  page4Rows.forEach((_, i) => {
    if ((page4Answers.value[i] || '').trim() !== page4Correct[i]) wrong++
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

  <!-- FULL WIDTH PAGE WRAPPER -->
  <div class="w-full max-w-none px-3 md:px-8">

    <!-- ======================== PAGE 1 ======================== -->
    <div v-if="page === 1">
      <div class="flex items-baseline gap-3">
        <span class="text-3xl font-semibold">1</span>
        <span class="text-base">(je 1 Fehlerpunkt)</span>
      </div>
      <div class="h-[3px] bg-black my-2"></div>

      <p class="text-[18px] leading-snug">
        Die folgenden Zahlenreihen sind nach einer bestimmten Gesetzmäßigkeit aufgebaut.
      </p>
      <p class="text-[18px] leading-snug">
        Durch welche Zahl wird die jeweilige Zahlenreihe logisch fortgesetzt?
      </p>
      <p class="text-[18px] leading-snug mb-5">
        Tragen Sie bitte die entsprechende Zahl in das jeweils dafür vorgesehene Kästchen ein.
      </p>

      <ul class="list-disc pl-8 space-y-6">
        <li v-for="(row, i) in page1Series" :key="i" class="grid grid-cols-[1fr_auto] items-center gap-4">
          <div class="flex flex-wrap items-center">
            <span class="whitespace-pre text-[18px]">{{ row.join(' . ') }} . ?</span>
          </div>
          <input v-model="page1Inputs[i]" class="answer-box justify-self-end w-12 h-8 text-[18px]"
            inputmode="numeric" />
        </li>
      </ul>
    </div>

    <!-- ======================== PAGE 2 (merged) ======================== -->
    <div v-else-if="page === 2">
      <div class="flex items-baseline gap-3">
        <span class="text-3xl font-semibold">2</span>
        <span class="text-base">(je 1 Fehlerpunkt)</span>
      </div>
      <div class="h-[3px] bg-black my-2"></div>

      <p class="text-[18px] leading-snug">
        Aus den untenstehenden Bilderreihen lassen sich bestimmte Folgerungen und Gesetzmäßigkeiten ableiten. Wählen Sie
        <b>rechts</b> das Bild [1]–[4], das die Reihe sinnvoll vervollständigt, und tragen Sie links die Zahl in das
        Kästchen ein.
      </p>

      <div class="relative mt-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- middle divider -->
        <div class="hidden lg:block absolute inset-y-0 left-1/2 w-[2px] bg-black/60 pointer-events-none"></div>

        <!-- LEFT: Bilderreihen (+ overlay inputs) -->
        <section class="pr-4">
          <div class="text-red-600 font-semibold text-2xl mb-3">➔ Bilderreihen</div>
          <div class="relative mx-auto" :style="{ width: containerWidth + 'px' }">
            <img :src="page2Png" alt="Bilderreihen (Fragen)" class="w-full h-auto block select-none test-img"
              @load="onPage2ImgLoad" />
            <template v-for="(top, i) in rowTopOffsets" :key="i">
              <input v-model="page2Answers[i]" class="answer-box absolute text-[18px]" inputmode="numeric" maxlength="1"
                :style="{
                  top: top + '%',
                  right: answerRightPercent + '%',
                  width: boxWidthPercent + '%',
                  height: boxHeightPercent + '%',
                  transform: 'translateY(-50%)'
                }" />
              <div v-if="debugAlign" class="absolute pointer-events-none ring-2 ring-fuchsia-500/60" :style="{
                top: top + '%',
                right: (answerRightPercent) + '%',
                width: boxWidthPercent + '%',
                height: boxHeightPercent + '%',
                transform: 'translateY(-50%)'
              }" />
            </template>
          </div>
        </section>

        <!-- RIGHT: Antworttafeln -->
        <section class="pl-4">
          <div class="text-red-600 font-semibold text-2xl mb-3">➔ Antworttafeln [1]–[4]</div>
          <div class="mt-1 mx-auto max-w-[1100px]">
            <img :src="page3Png" alt="Antwortspalten [1]–[4]" class="w-full h-auto block select-none" />
          </div>
        </section>
      </div>
    </div>

    <!-- ======================== PAGE 3 (Original | Abschrift) ======================== -->
    <div v-else-if="page === 3">
      <div class="flex items-baseline gap-3">
        <span class="text-3xl font-semibold">3</span>
        <span class="text-base">(je 1 Fehlerpunkt)</span>
      </div>
      <div class="h-[3px] bg-black my-2"></div>

      <p class="text-[18px] leading-snug">
        Links: Original – Rechts: Abschrift. Jede Position steht in <b>zwei Zeilen</b>; markieren Sie Fehler in der
        Abschrift und tragen Sie die Anzahl rechts ein.
      </p>

      <div class="relative mt-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="hidden lg:block absolute inset-y-0 left-1/2 w-[2px] bg-black/60 pointer-events-none"></div>

        <!-- LEFT: Original -->
        <section class="pr-4">
          <div class="text-red-600 font-semibold text-2xl mb-3">➔ Original</div>
          <div class="text-[18px] font-mono">
            <div v-for="(row, i) in originalRows" :key="'orig-' + row.letter" class="py-2 border-b">
              <div
                class="grid grid-cols-[22px_minmax(0,1fr)_minmax(0,1fr)] grid-rows-2 gap-x-4 items-start whitespace-nowrap leading-[1.55]">
                <div class="text-right text-gray-700 font-semibold pr-1 row-span-2">
                  {{ String.fromCharCode(97 + i) }})
                </div>
                <div>{{ row.parts[0] }}</div>
                <div>{{ row.parts[1] }}</div>
                <div class="col-start-2 mt-1">{{ row.parts[2] }}</div>
                <div class="col-start-3 mt-1">{{ row.parts[3] }}</div>
              </div>
            </div>
          </div>
        </section>

        <!-- RIGHT: Abschrift -->
        <section class="pl-4">
          <div class="text-red-600 font-semibold text-2xl mb-3">➔ Abschrift</div>
          <div class="text-[18px] font-mono">
            <div v-for="(row, rIdx) in copyRows" :key="'copy-' + row.letter" class="py-2 border-b">
              <div
                class="grid grid-cols-[22px_minmax(0,1fr)_minmax(0,1fr)_auto] grid-rows-2 gap-x-4 items-start whitespace-nowrap leading-[1.55]">
                <div class="text-right text-gray-700 font-semibold pr-1 row-span-2">
                  {{ String.fromCharCode(97 + rIdx) }})
                </div>

                <!-- Row 1 -->
                <div>
                  <span v-for="(ch, cIdx) in getChars(rIdx, 0)" :key="'r' + rIdx + 'p0c' + cIdx"
                    @click="toggleCopyChar(rIdx, 0, cIdx)" :class="['letter', { marked: copyMarks[rIdx][0][cIdx] }]">
                    {{ ch }}
                  </span>
                </div>
                <div>
                  <span v-for="(ch, cIdx) in getChars(rIdx, 1)" :key="'r' + rIdx + 'p1c' + cIdx"
                    @click="toggleCopyChar(rIdx, 1, cIdx)" :class="['letter', { marked: copyMarks[rIdx][1][cIdx] }]">
                    {{ ch }}
                  </span>
                </div>

                <!-- Count -->
                <input v-model="copyCounts[rIdx]"
                  class="answer-box count-box row-span-2 self-center justify-self-center" inputmode="numeric"
                  maxlength="2" />

                <!-- Row 2 -->
                <div class="col-start-2 mt-1">
                  <span v-for="(ch, cIdx) in getChars(rIdx, 2)" :key="'r' + rIdx + 'p2c' + cIdx"
                    @click="toggleCopyChar(rIdx, 2, cIdx)" :class="['letter', { marked: copyMarks[rIdx][2][cIdx] }]">
                    {{ ch }}
                  </span>
                </div>
                <div class="col-start-3 mt-1">
                  <span v-for="(ch, cIdx) in getChars(rIdx, 3)" :key="'r' + rIdx + 'p3c' + cIdx"
                    @click="toggleCopyChar(rIdx, 3, cIdx)" :class="['letter', { marked: copyMarks[rIdx][3][cIdx] }]">
                    {{ ch }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- ======================== PAGE 4 (6er zählen) ======================== -->
    <div v-else-if="page === 4">
      <h1 class="text-3xl font-bold mb-4">4</h1>
      <p class="mb-3 text-[18px]">Zählen Sie die Anzahl der 6er in jeder Zeile.</p>
      <div v-for="(row, i) in page4Rows" :key="i" class="mb-3">
        <span class="font-mono mr-2 tracking-wider text-[18px] whitespace-nowrap">{{ row }}</span>
        <Input v-model="page4Answers[i]" class="inline w-16 h-9 text-[18px]" />
      </div>
    </div>

    <!-- ======================== PAGE 5 (Zeichen zählen) ======================== -->
    <div v-else-if="page === 5">
      <h1 class="text-3xl font-bold mb-4">5</h1>
      <div class="mb-4 text-[20px]">
        <span v-for="(ch, i) in page5Chars" :key="i" @click="toggleMark(page5Marks2, i)"
          :class="{ 'underline-double': page5Marks2[i] }" class="cursor-pointer px-1 select-none">
          {{ ch }}
        </span>
      </div>
      <Input v-model="page5Total" placeholder="Gefundene Zeichen" class="w-56 h-10 text-[18px]" />

      <div class="mt-6 rounded-lg border p-4 bg-white/70">
        <div class="font-medium text-[18px]">Zwischenergebnis</div>
        <div class="text-[16px] mt-1">Falsche Antworten: <b>{{ wrongCount }}</b></div>
        <div class="text-[16px]">Prozent: <b>{{ percentage }}</b></div>
      </div>
    </div>

    <!-- NAV -->
    <div class="mt-8 flex gap-3">
      <Button @click="prevPage" v-if="page > 1">Zurück</Button>
      <Button @click="nextPage" v-if="page < MAX_PAGE">Weiter</Button>
    </div>
  </div>
</template>

<style scoped>
/* keep page-2 images crisp */
.test-img {
  image-rendering: crisp-edges;
}

/* small square inputs */
.answer-box {
  border: 1px solid #000;
  border-radius: 0;
  padding: 0;
  text-align: center;
  background: #fff;
  box-sizing: border-box;
}

/* page-1 inputs fixed size if used inside lists */
ul .answer-box {
  width: 2.5rem;
  height: 2rem;
}

/* Abschrift clickable letters */
.letter {
  cursor: pointer;
  padding: 0 0.25px;
}

.marked {
  color: #dc2626;
  text-decoration: line-through;
  text-decoration-thickness: 2px;
}

.count-box {
  width: 2.6ch;
  height: 1.6em;
  line-height: 1.6em;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* double underline used on page 5 */
.underline-double {
  text-decoration-line: underline;
  text-decoration-style: double;
  text-decoration-color: red;
}
</style>
