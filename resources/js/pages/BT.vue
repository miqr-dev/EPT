<script setup lang="ts">
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
</script>

<template>
  <Head title="BT" />
  <div class="h-screen overflow-hidden bg-background text-black">
    <div class="h-1/2 overflow-hidden px-6 pt-3 font-serif text-base">
      <div class="flex h-full flex-col gap-4">
        <div class="border border-black/10 px-4 py-2 text-center">
          <h1 class="text-xl font-semibold tracking-[0.4em]">Aufgabe 1</h1>
        </div>
        <div class="grid h-full grid-cols-[1.6fr,1fr] gap-8 text-left">
          <div class="flex flex-col items-center justify-between border border-black/10 px-6 py-4">
            <div class="space-y-3 text-center text-base leading-relaxed">
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
            <div class="text-center text-base">_____</div>
          </div>

          <div class="flex items-center justify-center border border-black/10 px-4 py-4">
            <div class="flex items-start gap-6">
              <div
                class="border border-black px-4 py-3 text-sm leading-tight"
                @dragover="allowDrop"
                @drop="handleDropOnPool"
              >
                <div class="grid grid-cols-[1fr,1fr] gap-x-8">
                  <div class="space-y-0.5">
                    <div
                      v-for="apprentice in leftNames"
                      :key="apprentice.id"
                      class="flex items-center justify-between gap-2"
                    >
                      <span class="w-4 text-right">{{ apprentice.id }}</span>
                      <span
                        class="flex-1 text-left"
                        :class="{ 'line-through text-gray-500': isAssigned(apprentice.name) }"
                        :draggable="!isAssigned(apprentice.name)"
                        @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })"
                      >
                        {{ apprentice.name }}
                      </span>
                      <span class="w-6 text-right">{{ apprentice.restriction ?? '' }}</span>
                    </div>
                  </div>
                  <div class="space-y-0.5">
                    <div
                      v-for="apprentice in rightNames"
                      :key="apprentice.id"
                      class="flex items-center justify-between gap-2"
                    >
                      <span class="w-4 text-right">{{ apprentice.id }}</span>
                      <span
                        class="flex-1 text-left"
                        :class="{ 'line-through text-gray-500': isAssigned(apprentice.name) }"
                        :draggable="!isAssigned(apprentice.name)"
                        @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })"
                      >
                        {{ apprentice.name }}
                      </span>
                      <span class="w-6 text-right">{{ apprentice.restriction ?? '' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-3 text-sm text-left leading-relaxed">
                <p class="text-center font-semibold tracking-[0.35em] leading-relaxed">
                  Liste<br />der Namen
                </p>
                <p>F = kann keinen Frühdienst machen</p>
                <p>S = kann keinen Spätdienst machen</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="h-1/2 overflow-hidden px-6 pb-4 pt-2 font-serif">
      <div class="flex h-full flex-col justify-center">
        <div class="border-t border-black" />
        <table class="mt-3 w-full border border-black text-lg">
          <thead>
            <tr>
              <th class="w-24 border border-black p-2 text-left">Aufgabe 1</th>
              <th v-for="day in days" :key="day" class="border border-black p-2 text-center">
                {{ day }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th class="border border-black p-2 text-left" rowspan="2">Frühdienst</th>
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
                    class="flex-1 cursor-move"
                    draggable="true"
                    @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 1, day)] ?? '', from: 'cell', key: buildCellKey('early', 1, day) })"
                  >
                    {{ assignments[buildCellKey('early', 1, day)] }}
                  </span>
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
                    class="flex-1 cursor-move"
                    draggable="true"
                    @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 2, day)] ?? '', from: 'cell', key: buildCellKey('early', 2, day) })"
                  >
                    {{ assignments[buildCellKey('early', 2, day)] }}
                  </span>
                  <span v-else class="flex-1">&nbsp;</span>
                </div>
              </td>
            </tr>
            <tr>
              <th class="border border-black p-2 text-left" rowspan="3">Spätdienst</th>
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
                    class="flex-1 cursor-move"
                    draggable="true"
                    @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 1, day)] ?? '', from: 'cell', key: buildCellKey('late', 1, day) })"
                  >
                    {{ assignments[buildCellKey('late', 1, day)] }}
                  </span>
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
                    class="flex-1 cursor-move"
                    draggable="true"
                    @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 2, day)] ?? '', from: 'cell', key: buildCellKey('late', 2, day) })"
                  >
                    {{ assignments[buildCellKey('late', 2, day)] }}
                  </span>
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
                    class="flex-1 cursor-move"
                    draggable="true"
                    @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 3, day)] ?? '', from: 'cell', key: buildCellKey('late', 3, day) })"
                  >
                    {{ assignments[buildCellKey('late', 3, day)] }}
                  </span>
                  <span v-else class="flex-1">&nbsp;</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
