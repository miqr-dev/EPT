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

    <div class="h-1/2 overflow-hidden px-6 pb-4 pt-2 font-serif">
      <div class="flex h-full flex-col">
        <div class="border-t border-black" />
        <div class="flex flex-1 flex-col px-10 pt-6 text-base leading-relaxed">
          <div class="text-center">
            <h2 class="text-lg font-semibold tracking-[0.4em]">Aufgabe 2</h2>
          </div>
          <div class="mt-6 flex flex-col gap-4 text-center">
            <p>Sie erhalten ungeordnet 220 Kopien von Rechnungen aus den Orten</p>
            <p>Essen, Düsseldorf und Köln.</p>
            <p>Sie sollen möglichst schnell feststellen:</p>
            <p class="tracking-[0.6em]">_____</p>
            <p class="mt-2">In diesem Heft bitte keine Notizen machen!</p>
          </div>
          <div class="mt-6 flex flex-col gap-2 text-left">
            <p>a) wie viele dieser Rechnungen aus jedem der drei Orte kommen</p>
            <p>
              b) welche bereits bezahlt und welche noch unbezahlt sind
              <span class="block pl-6">Bezahlte Rechnungen haben entsprechenden Stempelaufdruck</span>
              <span class="mt-1 inline-block border border-black px-4 py-1 tracking-[0.3em]">BEZAHLT</span>
            </p>
            <p>c) welche Höhe die unbezahlten Rechnungen insgesamt haben.</p>
          </div>
          <div class="mt-auto pt-4 text-left">
            <p>
              Geben Sie bitte auf dem Lösungsblatt in Stichworten an, wie Sie diese Aufgabe angepackt würden.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
