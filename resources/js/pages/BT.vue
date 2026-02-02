<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
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
const availableApprentices = computed(() =>
  apprentices.value.filter((apprentice) => !assignedNames.value.has(apprentice.name)),
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

function restrictionLabel(restriction: Restriction) {
  if (restriction === 'F') return 'Kein Frühdienst';
  if (restriction === 'S') return 'Kein Spätdienst';
  return 'Keine Einschränkung';
}
</script>

<template>
  <Head title="BT" />
  <div class="p-6 space-y-6">
    <header class="space-y-2">
      <h1 class="text-2xl font-bold">BT (Büro-Test)</h1>
      <h2 class="text-lg font-semibold">Aufgabe 1</h2>
      <p class="text-sm text-muted-foreground">
        Ziehen Sie die Namen aus der Liste in die Tabelle, um den Wochenplan für den Früh- und Spätdienst
        zu erstellen. Jeder Name darf nur einmal verwendet werden. Zum Entfernen ziehen Sie den Namen
        zurück in die Liste oder klicken Sie auf das Feld.
      </p>
    </header>

    <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
      <div class="rounded-xl border bg-background p-4">
        <div class="overflow-x-auto">
          <table class="min-w-[720px] w-full border-collapse text-sm">
            <thead>
              <tr>
                <th class="w-28 border border-muted bg-muted/40 p-2 text-left">Aufgabe 1</th>
                <th v-for="day in days" :key="day" class="border border-muted bg-muted/40 p-2 text-center">
                  {{ day }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="border border-muted bg-muted/40 p-2 text-left" rowspan="2">Frühdienst</th>
                <td
                  v-for="day in days"
                  :key="`early-1-${day}`"
                  class="border border-muted p-2 align-top"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('early', 1, day))"
                >
                  <div
                    class="flex min-h-[44px] items-center justify-between rounded-md border border-dashed border-muted-foreground/40 bg-background px-2 py-1"
                  >
                    <span
                      v-if="assignments[buildCellKey('early', 1, day)]"
                      class="cursor-move text-sm font-medium"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 1, day)] as string, from: 'cell', key: buildCellKey('early', 1, day) })"
                    >
                      {{ assignments[buildCellKey('early', 1, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('early', 1, day)]"
                      class="text-xs text-muted-foreground hover:text-foreground"
                      type="button"
                      @click="clearCell(buildCellKey('early', 1, day))"
                    >
                      Entfernen
                    </button>
                    <span v-else class="text-xs text-muted-foreground">Feld frei</span>
                  </div>
                </td>
              </tr>
              <tr>
                <td
                  v-for="day in days"
                  :key="`early-2-${day}`"
                  class="border border-muted p-2 align-top"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('early', 2, day))"
                >
                  <div
                    class="flex min-h-[44px] items-center justify-between rounded-md border border-dashed border-muted-foreground/40 bg-background px-2 py-1"
                  >
                    <span
                      v-if="assignments[buildCellKey('early', 2, day)]"
                      class="cursor-move text-sm font-medium"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('early', 2, day)] as string, from: 'cell', key: buildCellKey('early', 2, day) })"
                    >
                      {{ assignments[buildCellKey('early', 2, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('early', 2, day)]"
                      class="text-xs text-muted-foreground hover:text-foreground"
                      type="button"
                      @click="clearCell(buildCellKey('early', 2, day))"
                    >
                      Entfernen
                    </button>
                    <span v-else class="text-xs text-muted-foreground">Feld frei</span>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="border border-muted bg-muted/40 p-2 text-left" rowspan="3">Spätdienst</th>
                <td
                  v-for="day in days"
                  :key="`late-1-${day}`"
                  class="border border-muted p-2 align-top"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('late', 1, day))"
                >
                  <div
                    class="flex min-h-[44px] items-center justify-between rounded-md border border-dashed border-muted-foreground/40 bg-background px-2 py-1"
                  >
                    <span
                      v-if="assignments[buildCellKey('late', 1, day)]"
                      class="cursor-move text-sm font-medium"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 1, day)] as string, from: 'cell', key: buildCellKey('late', 1, day) })"
                    >
                      {{ assignments[buildCellKey('late', 1, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('late', 1, day)]"
                      class="text-xs text-muted-foreground hover:text-foreground"
                      type="button"
                      @click="clearCell(buildCellKey('late', 1, day))"
                    >
                      Entfernen
                    </button>
                    <span v-else class="text-xs text-muted-foreground">Feld frei</span>
                  </div>
                </td>
              </tr>
              <tr>
                <td
                  v-for="day in days"
                  :key="`late-2-${day}`"
                  class="border border-muted p-2 align-top"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('late', 2, day))"
                >
                  <div
                    class="flex min-h-[44px] items-center justify-between rounded-md border border-dashed border-muted-foreground/40 bg-background px-2 py-1"
                  >
                    <span
                      v-if="assignments[buildCellKey('late', 2, day)]"
                      class="cursor-move text-sm font-medium"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 2, day)] as string, from: 'cell', key: buildCellKey('late', 2, day) })"
                    >
                      {{ assignments[buildCellKey('late', 2, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('late', 2, day)]"
                      class="text-xs text-muted-foreground hover:text-foreground"
                      type="button"
                      @click="clearCell(buildCellKey('late', 2, day))"
                    >
                      Entfernen
                    </button>
                    <span v-else class="text-xs text-muted-foreground">Feld frei</span>
                  </div>
                </td>
              </tr>
              <tr>
                <td
                  v-for="day in days"
                  :key="`late-3-${day}`"
                  class="border border-muted p-2 align-top"
                  @dragover="allowDrop"
                  @drop="(event) => handleDropOnCell(event, buildCellKey('late', 3, day))"
                >
                  <div
                    class="flex min-h-[44px] items-center justify-between rounded-md border border-dashed border-muted-foreground/40 bg-background px-2 py-1"
                  >
                    <span
                      v-if="assignments[buildCellKey('late', 3, day)]"
                      class="cursor-move text-sm font-medium"
                      draggable="true"
                      @dragstart="(event) => handleDragStart(event, { name: assignments[buildCellKey('late', 3, day)] as string, from: 'cell', key: buildCellKey('late', 3, day) })"
                    >
                      {{ assignments[buildCellKey('late', 3, day)] }}
                    </span>
                    <button
                      v-if="assignments[buildCellKey('late', 3, day)]"
                      class="text-xs text-muted-foreground hover:text-foreground"
                      type="button"
                      @click="clearCell(buildCellKey('late', 3, day))"
                    >
                      Entfernen
                    </button>
                    <span v-else class="text-xs text-muted-foreground">Feld frei</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <aside
        class="rounded-xl border bg-background p-4"
        @dragover="allowDrop"
        @drop="handleDropOnPool"
      >
        <div class="flex items-center justify-between">
          <h3 class="text-base font-semibold">Liste der Namen</h3>
          <Badge variant="secondary">{{ availableApprentices.length }}</Badge>
        </div>
        <p class="mt-2 text-xs text-muted-foreground">
          Ziehen Sie einen Namen in die Tabelle. Die Kennzeichnung zeigt an, ob der Früh- oder Spätdienst
          ausgeschlossen ist.
        </p>
        <ul class="mt-4 space-y-2">
          <li
            v-for="apprentice in availableApprentices"
            :key="apprentice.id"
            class="flex items-center justify-between rounded-lg border border-muted bg-muted/20 px-3 py-2"
            draggable="true"
            @dragstart="(event) => handleDragStart(event, { name: apprentice.name, from: 'pool' })"
          >
            <div class="flex items-center gap-2">
              <span class="font-medium">{{ apprentice.name }}</span>
              <Badge v-if="apprentice.restriction === 'F'" variant="outline">F</Badge>
              <Badge v-else-if="apprentice.restriction === 'S'" variant="outline">S</Badge>
            </div>
            <span class="text-xs text-muted-foreground">{{ restrictionLabel(apprentice.restriction) }}</span>
          </li>
        </ul>
      </aside>
    </div>
  </div>
</template>
