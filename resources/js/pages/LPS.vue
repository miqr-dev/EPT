<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import TimeRemainingAlerts from '@/components/TimeRemainingAlerts.vue';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { LPS_PAGE1_ROWS } from '@/pages/Questions/LPSPage1';

const props = defineProps<{
  pausedTestResult?: {
    pageIndex?: number;
    total_time_seconds?: number;
    page1?: Array<{ col1: string; col2: string; col3: string; col4: string; col5: string }>;
  };
  timeRemainingSeconds?: number | null;
}>();

const emit = defineEmits(['complete', 'update:answers']);

const showTest = ref(false);
const pageIndex = ref(0);
const elapsedSecondsBeforeResume = ref(props.pausedTestResult?.total_time_seconds ?? 0);
const runningElapsedSeconds = ref(0);
const timerHandle = ref<number | null>(null);

const page1Responses = ref(
  LPS_PAGE1_ROWS.map((_, idx) => {
    const pausedRow = props.pausedTestResult?.page1?.[idx];
    return {
      col1: pausedRow?.col1 ?? '',
      col2: pausedRow?.col2 ?? '',
      col3: pausedRow?.col3 ?? '',
      col4: pausedRow?.col4 ?? '',
      col5: pausedRow?.col5 ?? '',
    };
  }),
);

if (props.pausedTestResult) {
  if (typeof props.pausedTestResult.pageIndex === 'number') {
    pageIndex.value = props.pausedTestResult.pageIndex;
  }
  showTest.value = true;
  startTimer();
}

const totalElapsed = computed(() => elapsedSecondsBeforeResume.value + runningElapsedSeconds.value);

const endConfirmOpen = ref(false);
const { isForcedFinish, forcedFinishCountdown, clearForcedFinish } = useTeacherForceFinish({
  isActive: () => showTest.value,
  onStart: () => {
    endConfirmOpen.value = true;
    window.dispatchEvent(new Event('start-finish'));
  },
  onCountdownFinished: () => {
    confirmEnd();
  },
  onCancel: () => {
    if (endConfirmOpen.value) {
      window.dispatchEvent(new Event('cancel-finish'));
      endConfirmOpen.value = false;
    }
  },
});

function startTimer() {
  stopTimer();
  timerHandle.value = window.setInterval(() => {
    runningElapsedSeconds.value += 1;
  }, 1000);
}

function stopTimer() {
  if (timerHandle.value) {
    clearInterval(timerHandle.value);
    timerHandle.value = null;
  }
}

onBeforeUnmount(() => stopTimer());

watch(
  [page1Responses, pageIndex, totalElapsed],
  () => {
    emit('update:answers', {
      pageIndex: pageIndex.value,
      total_time_seconds: totalElapsed.value,
      page1: page1Responses.value,
    });
  },
  { deep: true },
);

function startTest() {
  showTest.value = true;
  pageIndex.value = 0;
  elapsedSecondsBeforeResume.value = 0;
  runningElapsedSeconds.value = 0;
  startTimer();
}

function nextPage() {
  pageIndex.value = Math.min(pageIndex.value + 1, 2);
}

function prevPage() {
  pageIndex.value = Math.max(pageIndex.value - 1, 0);
}

function finishTest() {
  window.dispatchEvent(new Event('start-finish'));
  endConfirmOpen.value = true;
}

function cancelEnd() {
  if (isForcedFinish.value) return;
  endConfirmOpen.value = false;
  window.dispatchEvent(new Event('cancel-finish'));
  clearForcedFinish(false);
}

function confirmEnd() {
  clearForcedFinish(false);
  endConfirmOpen.value = false;
  stopTimer();
  emit('complete', {
    pageIndex: pageIndex.value,
    total_time_seconds: totalElapsed.value,
    page1: page1Responses.value,
  });
}

function formatTime(seconds: number) {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
}
</script>

<template>
  <Head title="LPS" />

  <div class="p-4">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold">LPS</h1>
      <div class="text-sm text-muted-foreground" v-if="showTest">
        Seite {{ pageIndex + 1 }} / 3
      </div>
    </div>

    <div class="mb-4">
      <TimeRemainingAlerts :time-remaining-seconds="props.timeRemainingSeconds" />
    </div>

    <div class="rounded-xl border bg-background p-6 shadow-sm">
      <div v-if="!showTest" class="flex flex-col items-center gap-4 text-center">
        <p class="max-w-2xl text-lg text-muted-foreground">
          Der LPS-Test umfasst drei Seiten. Beginnen Sie mit der ersten Seite. Die Spalten 1 und 2 sind
          zusammengefasst, die Spalten 3, 4 und 5 stehen getrennt nebeneinander. Arbeiten Sie jede Zeile von oben
          nach unten durch.
        </p>
        <Button class="mt-2" @click="startTest">Test starten</Button>
      </div>

      <div v-else>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-4 text-sm text-muted-foreground">
            <span>Benötigte Zeit: {{ formatTime(totalElapsed) }}</span>
            <span v-if="isForcedFinish" class="font-semibold text-destructive">
              Test wird beendet in {{ forcedFinishCountdown }}s
            </span>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" :disabled="pageIndex === 0" @click="prevPage">Zurück</Button>
            <Button variant="secondary" :disabled="pageIndex >= 2" @click="nextPage">Weiter</Button>
            <Button variant="destructive" @click="finishTest">Test beenden</Button>
          </div>
        </div>

        <div v-if="pageIndex === 0" class="overflow-x-auto rounded-lg border">
          <table class="min-w-[960px] w-full border-collapse text-sm">
            <thead>
              <tr class="bg-muted/40 text-left">
                <th colspan="2" class="px-4 py-3 text-center text-base font-semibold">1 + 2</th>
                <th class="px-4 py-3 text-center text-base font-semibold">3</th>
                <th class="px-4 py-3 text-center text-base font-semibold">4</th>
                <th class="px-4 py-3 text-center text-base font-semibold">5</th>
              </tr>
              <tr class="bg-muted/20 text-left">
                <th class="px-4 py-2 text-center font-semibold">1</th>
                <th class="px-4 py-2 text-center font-semibold">2</th>
                <th class="px-4 py-2 text-center font-semibold">3</th>
                <th class="px-4 py-2 text-center font-semibold">4</th>
                <th class="px-4 py-2 text-center font-semibold">5</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, idx) in LPS_PAGE1_ROWS" :key="row.id" class="border-t">
                <td class="px-3 py-3 align-top">
                  <div class="flex items-center justify-between gap-3">
                    <span class="font-mono text-lg leading-none">{{ row.column12[0] }}</span>
                    <Input v-model="page1Responses[idx].col1" placeholder="Antwort" class="w-28" />
                  </div>
                </td>
                <td class="px-3 py-3 align-top">
                  <div class="flex items-center justify-between gap-3">
                    <span class="font-mono text-lg leading-none">{{ row.column12[1] }}</span>
                    <Input v-model="page1Responses[idx].col2" placeholder="Antwort" class="w-28" />
                  </div>
                </td>
                <td class="px-3 py-3 align-top">
                  <div class="flex items-center justify-between gap-3">
                    <span class="font-mono text-lg leading-none">{{ row.column3 }}</span>
                    <Input v-model="page1Responses[idx].col3" placeholder="Antwort" class="w-28" />
                  </div>
                </td>
                <td class="px-3 py-3 align-top">
                  <div class="flex items-center justify-between gap-3">
                    <span class="font-mono text-lg leading-none">{{ row.column4 }}</span>
                    <Input v-model="page1Responses[idx].col4" placeholder="Antwort" class="w-28" />
                  </div>
                </td>
                <td class="px-3 py-3 align-top">
                  <div class="flex items-center justify-between gap-3">
                    <span class="font-mono text-lg leading-none">{{ row.column5 }}</span>
                    <Input v-model="page1Responses[idx].col5" placeholder="Antwort" class="w-28" />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="flex min-h-[200px] items-center justify-center rounded-lg border border-dashed p-8 text-muted-foreground">
          Weitere Seiten dieses Tests werden in den nächsten Schritten ergänzt.
        </div>
      </div>
    </div>
  </div>

  <Dialog v-model:open="endConfirmOpen">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Test wirklich beenden?</DialogTitle>
        <DialogDescription>Die Antworten für alle bisher bearbeiteten Seiten werden gespeichert.</DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="cancelEnd">Abbrechen</Button>
        <Button variant="destructive" @click="confirmEnd">Beenden</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
