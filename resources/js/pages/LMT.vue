<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { LMT_QUESTIONS } from '@/pages/Questions/LmtQuestions';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { useTestPause } from '@/composables/useTestPause';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const emit = defineEmits(['complete']);

const props = defineProps<{
  participant: any;
  examStepId: number;
  pausedTestResult: any;
}>();

const showTest = ref(false);
const answers = ref<Record<number, number | null>>({});
const endConfirmOpen = ref(false);

if (props.pausedTestResult) {
  props.pausedTestResult.answers.forEach((answer: any) => {
    answers.value[answer.number] = answer.answer;
  });
} else {
  LMT_QUESTIONS.forEach((q) => (answers.value[q.number] = null));
}

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

function getAnswers() {
  return {
    answers: LMT_QUESTIONS.map((q) => ({ number: q.number, answer: answers.value[q.number] })),
  };
}

function confirmEnd() {
    clearForcedFinish(false);
    endConfirmOpen.value = false;
    emit('complete', getAnswers());
}

const { isPaused, pauseCountdown } = useTestPause(props.participant.id, props.examStepId, getAnswers);

const questions = computed(() => LMT_QUESTIONS);

const currentPage = ref(0);
const questionsPerPage = 10;
const totalPages = computed(() => Math.ceil(questions.value.length / questionsPerPage));
const paginatedQuestions = computed(() => {
    const start = currentPage.value * questionsPerPage;
    const end = start + questionsPerPage;
    return questions.value.slice(start, end);
});

function nextPage() {
    if (currentPage.value < totalPages.value - 1) {
        currentPage.value++;
    }
}

function prevPage() {
    if (currentPage.value > 0) {
        currentPage.value--;
    }
}
</script>

<template>
    <Head title="LMT" />
    <div class="p-4 relative">
        <div v-if="isPaused" class="absolute inset-0 bg-white bg-opacity-80 z-10 flex flex-col items-center justify-center">
            <h2 class="text-2xl font-bold">Test Paused</h2>
            <p v-if="pauseCountdown > 0" class="text-lg">Pausing in {{ pauseCountdown }} seconds...</p>
        </div>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">LMT</h1>
        </div>
        <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4">
            <div class="flex flex-1 flex-col gap-4">
                <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
                    <h2 class="mb-4 text-2xl font-bold">LMT</h2>
                    <Button @click="showTest = true" class="rounded-xl px-8 py-3 text-lg font-semibold shadow"> Test starten </Button>
                </div>
                <div v-else class="rounded-lg border bg-background p-6">
                    <table class="w-full border-separate text-base" style="border-spacing: 0">
                        <tbody>
                            <tr v-for="q in paginatedQuestions" :key="q.number" :class="{ 'bg-gray-50 dark:bg-gray-700': answers[q.number] === null }">
                                <td class="font-mono w-8 pr-2 align-top pt-2 text-right border-b-2 border-gray-200 dark:border-gray-700">{{ q.number }}.</td>
                                <td class="pr-4 align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700">{{ q.text }}</td>
                                <td class="px-2 text-center align-top pt-1 border-b-2 border-gray-200 dark:border-gray-700">
                                    <label class="w-8 h-8 relative flex items-center justify-center border border-gray-400 cursor-pointer">
                                        <input class="sr-only" type="radio" :name="'q' + q.number" :value="1" v-model="answers[q.number]" />
                                        <span class="text-sm">Richtig</span>
                                        <span v-if="answers[q.number] === 1" class="absolute inset-0 flex items-center justify-center text-red-600 text-xl">X</span>
                                    </label>
                                </td>
                                <td class="px-2 text-center align-top pt-1 border-b-2 border-gray-200 dark:border-gray-700">
                                    <label class="w-8 h-8 relative flex items-center justify-center border border-gray-400 cursor-pointer">
                                        <input class="sr-only" type="radio" :name="'q' + q.number" :value="0" v-model="answers[q.number]" />
                                        <span class="text-sm">Falsch</span>
                                        <span v-if="answers[q.number] === 0" class="absolute inset-0 flex items-center justify-center text-red-600 text-xl">X</span>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4 flex justify-between">
                        <Button @click="prevPage" :disabled="currentPage === 0">Zurück</Button>
                        <Button @click="nextPage" :disabled="currentPage === totalPages - 1">Weiter</Button>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <Button variant="destructive" @click="endConfirmOpen = true">Test beenden</Button>
                    </div>
                </div>
            </div>
        </div>
        <Dialog v-model:open="endConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Test beenden</DialogTitle>
                    <DialogDescription v-if="!isForcedFinish">
                        Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück.
                    </DialogDescription>
                    <DialogDescription v-else>
                        Der Test wird automatisch in {{ forcedFinishCountdown }} Sekunden beendet.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button v-if="!isForcedFinish" variant="secondary" @click="endConfirmOpen = false">Abbrechen</Button>
                    <Button variant="destructive" @click="confirmEnd">
                        {{ isForcedFinish ? 'Jetzt beenden' : 'Ja' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
