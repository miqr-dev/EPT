<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Restriction = 'F' | 'S' | null;

type Apprentice = {
  id: number;
  name: string;
  restriction: Restriction;
};

type DragPayload = {
  name: string;
  from: 'pool' | 'cell';
  key?: string;
};

const days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag'];
const earlySlots = [1, 2];
const lateSlots = [1, 2, 3];

const apprentices = ref<Apprentice[]>([
  { id: 1, name: 'Angermann', restriction: null },
  { id: 2, name: 'Basten', restriction: 'S' },
  { id: 3, name: 'Bauer', restriction: 'S' },
  { id: 4, name: 'Berkelhahn', restriction: null },
  { id: 5, name: 'Bertram', restriction: 'S' },
  { id: 6, name: 'Bolte', restriction: 'S' },
  { id: 7, name: 'Diesterweg', restriction: null },
  { id: 8, name: 'Eschweiler', restriction: null },
  { id: 9, name: 'Fuchs', restriction: 'F' },
  { id: 10, name: 'Hamburger', restriction: null },
  { id: 11, name: 'Hamelring', restriction: null },
  { id: 12, name: 'Hans', restriction: 'F' },
  { id: 13, name: 'Kleininger', restriction: null },
  { id: 14, name: 'Krämer', restriction: 'S' },
  { id: 15, name: 'Kühne', restriction: 'S' },
  { id: 16, name: 'Kurz', restriction: 'F' },
  { id: 17, name: 'Mann', restriction: 'F' },
  { id: 18, name: 'Müller', restriction: 'S' },
  { id: 19, name: 'Pahl', restriction: 'F' },
  { id: 20, name: 'Paul', restriction: 'F' },
  { id: 21, name: 'Pees', restriction: 'F' },
  { id: 22, name: 'Petersen', restriction: null },
  { id: 23, name: 'Roth', restriction: 'F' },
  { id: 24, name: 'Schneider', restriction: 'S' },
  { id: 25, name: 'Schuster', restriction: 'S' },
]);

const assignments = ref<Record<string, string | null>>(
  Object.fromEntries(
    days.flatMap((day) => [
      ...earlySlots.map((slot) => [`early-${slot}-${day}`, null]),
      ...lateSlots.map((slot) => [`late-${slot}-${day}`, null]),
    ]),
  ),
);

const assignedNames = computed(() => new Set(Object.values(assignments.value).filter(Boolean)));
const leftNames = computed(() => apprentices.value.slice(0, 13));
const rightNames = computed(() => apprentices.value.slice(13));
const maxPage = 4;
const page = ref(1);
const cashDenominations = [
  { label: '100€', key: '100' },
  { label: '50€', key: '50' },
  { label: '20€', key: '20' },
  { label: '10€', key: '10' },
  { label: '5€', key: '5' },
  { label: '2€', key: '2' },
  { label: '1€', key: '1' },
  { label: '0,50€', key: '0.5' },
];
const cashAnswers = ref<Record<string, string>>(
  Object.fromEntries(cashDenominations.map((denomination) => [denomination.key, ''])),
);
const folderAnswers = ref<Record<number, string>>(
  Object.fromEntries(Array.from({ length: 10 }, (_, index) => [index + 1, ''])),
);

function buildCellKey(shift: 'early' | 'late', slot: number, day: string) {
  return `${shift}-${slot}-${day}`;
}

function clearNameFromAssignments(name: string) {
  Object.keys(assignments.value).forEach((key) => {
    if (assignments.value[key] === name) {
      assignments.value[key] = null;
    }
  });
}

function handleDragStart(event: DragEvent, payload: DragPayload) {
  event.dataTransfer?.setData('text/plain', JSON.stringify(payload));
  event.dataTransfer?.setDragImage((event.target as HTMLElement) ?? document.body, 0, 0);
}

function handleDropOnCell(event: DragEvent, key: string) {
  event.preventDefault();
  const payloadText = event.dataTransfer?.getData('text/plain');
  if (!payloadText) return;

  const payload = JSON.parse(payloadText) as DragPayload;
  if (!payload?.name) return;

  if (payload.from === 'cell' && payload.key) {
    assignments.value[payload.key] = null;
  }

  clearNameFromAssignments(payload.name);
  assignments.value[key] = payload.name;
}

function handleDropOnPool(event: DragEvent) {
  event.preventDefault();
  const payloadText = event.dataTransfer?.getData('text/plain');
  if (!payloadText) return;

  const payload = JSON.parse(payloadText) as DragPayload;
  if (!payload?.name) return;

  clearNameFromAssignments(payload.name);
}

function allowDrop(event: DragEvent) {
  event.preventDefault();
}

function clearCell(key: string) {
  assignments.value[key] = null;
}

function isAssigned(name: string) {
  return assignedNames.value.has(name);
}

function handleCashInput(key: string, event: Event) {
  const target = event.target as HTMLInputElement;
  const cleaned = target.value.replace(/\D/g, '').slice(0, 2);
  cashAnswers.value[key] = cleaned;
  target.value = cleaned;
}

function handleFolderInput(index: number, event: Event) {
  const target = event.target as HTMLInputElement;
  const cleaned = target.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 5);
  folderAnswers.value[index] = cleaned;
  target.value = cleaned;
}

function nextPage() {
  if (page.value < maxPage) {
    page.value += 1;
  }
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1;
  }
}
</script>

<template>
  <Head title="BT" />
  <div class="relative h-screen overflow-hidden bg-background text-black">
    <div class="absolute right-6 top-4 flex items-center gap-2">
      <Button variant="outline" @click="prevPage" :disabled="page === 1">Zurück</Button>
      <Button @click="nextPage" :disabled="page === maxPage">Weiter</Button>
    </div>
    <div v-if="page === 1" class="flex h-full flex-col">
      <div class="flex-1 overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 1</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-base leading-relaxed">
                <p>
                  Unser Betrieb beschäftigt 25 Lehrlinge. Von diesen sollen jeweils zwei Lehrlinge für den
                  Post-Frühdienst und drei Lehrlinge für den Post-Spätdienst eingeteilt werden, so dass jeder
                  Woche jeder Lehrling einmal Postdienst hat. Aus verkehrstechnischen Gründen können acht
                  Lehrlinge keinen Frühdienst und neun Lehrlinge keinen Spätdienst machen.
                </p>
                <p class="text-base">(Siehe Vermerke in der Liste)</p>
                <p>
                  Stellen Sie bitte einen Wochenplan auf (Montag bis Freitag), in dem Sie alle Namen der Lehrlinge
                  für den Postdienst (Früh oder Spät) auf das Lösungsblatt übertragen.
                </p>
              </div>
              <div class="mt-4 text-center text-base">_____</div>
            </div>

            <div class="flex-1 border border-black/20 px-4 py-2">
              <div class="flex h-full items-start justify-center gap-8 overflow-hidden">
                <div
                  class="border-2 border-black px-4 py-2 text-base leading-tight w-[420px] flex-none"
                  @dragover="allowDrop"
                  @drop="handleDropOnPool"
                >
                  <div class="grid grid-cols-2 gap-x-8">
                    <div class="space-y-0">
                      <div
                        v-for="apprentice in leftNames"
                        :key="apprentice.id"
                        class="flex items-center gap-2"
                      >
                        <span class="w-6 text-right flex-none">{{ apprentice.id }}</span>
                        <span
                          class="flex-1 text-left"
                          :class="{ 'line-through text-gray-500': isAssigned(apprentice.name) }"
                          :draggable="!isAssigned(apprentice.name)"
                          @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })"
                        >
                          {{ apprentice.name }}
                        </span>
                        <span class="w-4 text-center flex-none">{{ apprentice.restriction ?? '' }}</span>
                      </div>
                    </div>
                    <div class="space-y-0">
                      <div
                        v-for="apprentice in rightNames"
                        :key="apprentice.id"
                        class="flex items-center gap-2"
                      >
                        <span class="w-6 text-right flex-none">{{ apprentice.id }}</span>
                        <span
                          class="flex-1 text-left"
                          :class="{ 'line-through text-gray-500': isAssigned(apprentice.name) }"
                          :draggable="!isAssigned(apprentice.name)"
                          @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })"
                        >
                          {{ apprentice.name }}
                        </span>
                        <span class="w-4 text-center flex-none">{{ apprentice.restriction ?? '' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-6 text-base text-left leading-tight min-w-[200px]">
                  <div class="text-center">
                    <p class="text-xl mb-1" style="letter-spacing: 0.4em;">L i s t e</p>
                    <p>der Namen</p>
                  </div>
                  <div class="space-y-0">
                    <p>F = <span class="ml-1" style="letter-spacing: 0.2em;">k a n n   k e i n e n</span></p>
                    <p class="pl-8">Frühdienst</p>
                    <p class="pl-8">machen</p>
                  </div>
                  <div class="space-y-0">
                    <p>S = <span class="ml-1" style="letter-spacing: 0.2em;">k a n n   k e i n e n</span></p>
                    <p class="pl-8">Spätdienst</p>
                    <p class="pl-8">machen</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <table class="mt-3 w-full table-fixed border border-black text-base">
            <thead>
              <tr>
                <th class="w-24 border border-black p-1 text-left">Aufgabe 1</th>
                <th v-for="day in days" :key="day" class="border border-black p-1 text-center">
                  {{ day }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="border border-black p-1 text-left" rowspan="2">Frühdienst</th>
                <td
                  v-for="day in days"
                  :key="`early-1-${day}`"
                  class="border border-black p-1 align-middle"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('early', 1, day))"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-4 text-right">1.</span>
                    <span
                      v-if="assignments[buildCellKey('early', 1, day)]"
                      class="flex-1 min-w-0 cursor-move"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 1, day)] ?? '', from: 'cell', key: buildCellKey('early', 1, day) })"
                    >
                      {{ assignments[buildCellKey('early', 1, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('early', 1, day)]"
                      type="button"
                      class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                      aria-label="Remove assignment"
                      @click.stop="clearCell(buildCellKey('early', 1, day))"
                    >
                      ×
                    </button>
                    <span v-else class="flex-1">&nbsp;</span>
                  </div>
                </td>
              </tr>
              <tr>
                <td
                  v-for="day in days"
                  :key="`early-2-${day}`"
                  class="border border-black p-1 align-middle"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('early', 2, day))"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-4 text-right">2.</span>
                    <span
                      v-if="assignments[buildCellKey('early', 2, day)]"
                      class="flex-1 min-w-0 cursor-move"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 2, day)] ?? '', from: 'cell', key: buildCellKey('early', 2, day) })"
                    >
                      {{ assignments[buildCellKey('early', 2, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('early', 2, day)]"
                      type="button"
                      class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                      aria-label="Remove assignment"
                      @click.stop="clearCell(buildCellKey('early', 2, day))"
                    >
                      ×
                    </button>
                    <span v-else class="flex-1">&nbsp;</span>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="border border-black p-1 text-left" rowspan="3">Spätdienst</th>
                <td
                  v-for="day in days"
                  :key="`late-1-${day}`"
                  class="border border-black p-1 align-middle"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('late', 1, day))"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-4 text-right">1.</span>
                    <span
                      v-if="assignments[buildCellKey('late', 1, day)]"
                      class="flex-1 min-w-0 cursor-move"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 1, day)] ?? '', from: 'cell', key: buildCellKey('late', 1, day) })"
                    >
                      {{ assignments[buildCellKey('late', 1, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('late', 1, day)]"
                      type="button"
                      class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                      aria-label="Remove assignment"
                      @click.stop="clearCell(buildCellKey('late', 1, day))"
                    >
                      ×
                    </button>
                    <span v-else class="flex-1">&nbsp;</span>
                  </div>
                </td>
              </tr>
              <tr>
                <td
                  v-for="day in days"
                  :key="`late-2-${day}`"
                  class="border border-black p-1 align-middle"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('late', 2, day))"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-4 text-right">2.</span>
                    <span
                      v-if="assignments[buildCellKey('late', 2, day)]"
                      class="flex-1 min-w-0 cursor-move"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 2, day)] ?? '', from: 'cell', key: buildCellKey('late', 2, day) })"
                    >
                      {{ assignments[buildCellKey('late', 2, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('late', 2, day)]"
                      type="button"
                      class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                      aria-label="Remove assignment"
                      @click.stop="clearCell(buildCellKey('late', 2, day))"
                    >
                      ×
                    </button>
                    <span v-else class="flex-1">&nbsp;</span>
                  </div>
                </td>
              </tr>
              <tr>
                <td
                  v-for="day in days"
                  :key="`late-3-${day}`"
                  class="border border-black p-1 align-middle"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('late', 3, day))"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-4 text-right">3.</span>
                    <span
                      v-if="assignments[buildCellKey('late', 3, day)]"
                      class="flex-1 min-w-0 cursor-move"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 3, day)] ?? '', from: 'cell', key: buildCellKey('late', 3, day) })"
                    >
                      {{ assignments[buildCellKey('late', 3, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('late', 3, day)]"
                      type="button"
                      class="flex h-4 w-4 items-center justify-center rounded-full border border-black text-[10px] leading-none"
                      aria-label="Remove assignment"
                      @click.stop="clearCell(buildCellKey('late', 3, day))"
                    >
                      ×
                    </button>
                    <span v-else class="flex-1">&nbsp;</span>
                  </div>
                </td>
              </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="page === 2" class="flex h-full flex-col px-12 py-8 font-serif text-base">
      <div class="text-center">
        <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 2</h1>
      </div>
      <div class="mt-10 flex flex-1 flex-col items-center">
        <div class="max-w-2xl text-center space-y-6 leading-relaxed">
          <p>
            Sie erhalten ungeordnet 220 Kopien von Rechnungen aus den Orten Essen, Düsseldorf und Köln.
          </p>
          <p>Sie sollen möglichst schnell feststellen:</p>
          <div class="text-center">_____</div>
          <p class="text-base">In diesem Heft bitte keine Notizen machen!</p>
        </div>
        <div class="mt-10 w-full max-w-2xl space-y-4 text-left">
          <p>a) wie viele dieser Rechnungen aus jedem der drei Orte kommen</p>
          <div class="space-y-2">
            <p>b) welche bereits bezahlt und welche noch unbezahlt sind</p>
            <div class="flex flex-wrap items-center gap-4 pl-6">
              <p class="text-base">Bezahlte Rechnungen haben entsprechenden Stempelaufdruck</p>
              <div class="border border-black px-4 py-1 text-sm tracking-[0.5em]">BEZAHLT</div>
            </div>
          </div>
          <p>c) welche Höhe die unbezahlten Rechnungen insgesamt haben.</p>
        </div>
        <p class="mt-10 max-w-2xl text-center">
          Geben Sie bitte auf dem Lösungsblatt in Stichworten an, wie Sie diese Aufgabe anpacken würden.
        </p>
      </div>
    </div>
    <div v-else-if="page === 3" class="flex h-full flex-col">
      <div class="flex-1 overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 3</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-base leading-relaxed">
                <p>
                  Sie sollen jeden der acht Beträge auf nebenstehender Liste gegen Quittung in bar auszahlen.
                </p>
                <p>
                  Rechnen Sie bitte aus, welche Geldsorten Sie benötigen. Sie sollen in keinem Falle wechseln
                  und möglichst wenig Geldstücke bzw. Scheine brauchen.
                </p>
                <p>
                  Schreiben Sie bitte auf das Lösungsblatt, welche Anzahl Sie hierzu von jeder Geldsorte
                  benötigen!
                </p>
              </div>
              <div class="mt-4 text-center text-base">_____</div>
              <p class="mt-6 text-center text-base">In diesem Heft bitte keine Notizen machen!</p>
            </div>

            <div class="flex-1 border border-black/20 px-4 py-2">
              <div class="flex h-full items-stretch justify-center">
                <div class="h-full w-[240px] border-2 border-black px-10 py-6 text-base">
                  <table class="h-full w-full border-collapse">
                    <tbody>
                      <tr>
                        <td class="w-8 align-middle text-left">€</td>
                        <td class="align-middle text-right">2 854,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">26,00</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">144,00</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">211,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">86,00</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">3 221,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">175,50</td>
                      </tr>
                      <tr>
                        <td class="align-middle text-left">“</td>
                        <td class="align-middle text-right">11,00</td>
                      </tr>
                      <tr class="border-t border-black">
                        <td class="pt-2 align-middle text-left">€</td>
                        <td class="pt-2 align-middle text-right">6 730,00</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <table class="mt-3 w-full table-fixed border border-black text-base">
              <thead>
                <tr>
                  <th class="w-28 border border-black p-1 text-left">Geldsorten</th>
                  <th
                    v-for="denomination in cashDenominations"
                    :key="denomination.key"
                    class="border border-black p-1 text-center"
                  >
                    {{ denomination.label }}
                  </th>
                  <th class="w-10 border border-black p-1" />
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th class="border border-black p-1 text-left">Anzahl</th>
                  <td
                    v-for="denomination in cashDenominations"
                    :key="`input-${denomination.key}`"
                    class="border border-black p-1 text-center"
                  >
                    <input
                      :value="cashAnswers[denomination.key]"
                      type="text"
                      inputmode="numeric"
                      maxlength="2"
                      class="h-6 w-10 border border-black text-center text-base"
                      @input="(event) => handleCashInput(denomination.key, event)"
                    />
                  </td>
                  <td class="border border-black p-1" />
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="flex h-full flex-col">
      <div class="flex-1 overflow-hidden px-6 pt-3 font-serif text-base">
        <div class="flex h-full flex-col gap-4">
          <div class="px-4 py-2 text-center">
            <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 4</h1>
          </div>
          <div class="flex flex-1 gap-4">
            <div class="flex-1 border border-black/20 px-6 py-4">
              <div class="space-y-4 text-center text-base leading-relaxed">
                <p>
                  Sie erhalten 10 Ordner und eine Menge Briefe. Diese sollen nach den Anfangsbuchstaben der
                  Absender geordnet in den Ordnern abgelegt werden. Beim Sortieren der Briefe haben Sie
                  festgestellt, dass die einzelnen Buchstaben des Alphabets prozentual unterschiedlich oft
                  vorkommen (vgl. nebenstehende Zusammenstellung). Zum Schluss soll jeder Ordner die gleiche
                  Menge Briefe enthalten.
                </p>
                <div class="text-center">Wie müssen die 25 Buchstaben des Alphabets</div>
                <div class="mx-auto w-fit border border-black px-4 py-1 text-base tracking-[0.35em]">
                  A B C D E F G H J K L M N O P Q R S T U V W X Y Z
                </div>
                <div class="text-center">auf die 10 Ordner aufgeteilt werden?</div>
                <p>
                  Schreiben Sie bitte die betreffenden Buchstaben unter die jeweilige Ordner-Nr. auf das
                  Lösungsblatt!
                </p>
                <p>In dieses Heft bitte keine Notizen machen!</p>
              </div>
              <div class="mt-4 text-center text-base">_____</div>
            </div>
            <div class="flex-1 border border-black/20">
              <div class="flex h-full w-full items-center justify-center border-2 border-black p-8">
                <table class="w-full border-collapse text-base leading-tight">
                  <tbody>
                    <tr>
                      <td colspan="7" class="pb-8 text-center text-xl">Von allen Absendern beginnen</td>
                    </tr>
                    <tr>
                      <td class="w-[12%] py-1 text-left">jeweils</td>
                      <td class="w-[8%] py-1 text-right">2,5</td>
                      <td class="w-[4%] py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td class="w-[15%] py-1"></td>
                      <td class="w-[4%] py-1"></td>
                      <td class="w-[25%] py-1"></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left tracking-[0.35em]">A F W B G J</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left tracking-[0.35em]">X Y C H Z D</td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">30 % aller Briefe</td>
                    </tr>
                    <tr class="h-4">
                      <td colspan="7"></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-left">jeweils</td>
                      <td class="py-1 text-right">3,33</td>
                      <td class="py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left"><span class="tracking-[0.35em]">R P</span> und <span class="tracking-[0.35em]">Q</span></td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">10 % aller Briefe</td>
                    </tr>
                    <tr class="h-4">
                      <td colspan="7"></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-left">jeweils</td>
                      <td class="py-1 text-right">5,0</td>
                      <td class="py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left tracking-[0.35em]">N K S V T U</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-8 text-left"><span class="tracking-[0.35em]">O</span> und <span class="tracking-[0.35em]">L</span></td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">40 % aller Briefe</td>
                    </tr>
                    <tr class="h-4">
                      <td colspan="7"></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-left">jeweils</td>
                      <td class="py-1 text-right">10,0</td>
                      <td class="py-1 text-center">%</td>
                      <td class="py-1 pl-8 text-left">mit den Anfangsbuchstaben</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="border-b border-black py-1 pl-8 text-left"><span class="tracking-[0.35em]">M</span> und <span class="tracking-[0.35em]">E</span></td>
                      <td class="border-b border-black py-1 pl-4 text-left">zusammen</td>
                      <td class="border-b border-black py-1 text-center">=</td>
                      <td class="border-b border-black py-1 text-right">20 % aller Briefe</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="py-1 pl-4 text-left">zusammen</td>
                      <td class="py-1 text-center">=</td>
                      <td class="py-1 text-right">100 % aller Briefe</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="flex-1 overflow-hidden px-6 pb-4 pt-2 font-serif">
        <div class="flex h-full flex-col">
          <div class="border-t border-black" />
          <div class="flex-1">
            <div class="mt-3 text-left text-base font-semibold">Aufgabe 4</div>
            <table class="mt-2 w-full table-fixed border border-black text-base">
              <tbody>
                <tr>
                  <th class="w-28 border border-black p-1 text-left">Ordner-Nr</th>
                  <td v-for="index in 10" :key="`ordner-${index}`" class="border border-black p-1 text-center">
                    {{ index }}
                  </td>
                  <td class="w-16 border border-black p-1" rowspan="2" />
                </tr>
                <tr>
                  <th class="border border-black p-1 text-left">Buchstaben</th>
                  <td
                    v-for="index in 10"
                    :key="`letters-${index}`"
                    class="border border-black p-1 text-center"
                  >
                    <input
                      :value="folderAnswers[index]"
                      type="text"
                      inputmode="text"
                      maxlength="5"
                      class="h-6 w-16 border border-black text-center text-base uppercase"
                      @input="(event) => handleFolderInput(index, event)"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
