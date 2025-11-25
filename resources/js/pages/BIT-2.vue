<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { BIT2_QUESTIONS } from '@/pages/Questions/BIT2Questions';
import { useTeacherForceFinish } from '@/composables/useTeacherForceFinish';
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch, toRef } from 'vue';
import { useTimeWarning } from '@/composables/useTimeWarning';

const props = defineProps<{
    pausedTestResult?: {
        answers: { number: number; answer: number | null }[];
        pageIndex?: number;
    };
    timeRemaining?: number | null;
}>();

const timeRemainingRef = toRef(props, 'timeRemaining', null);
const { show5MinWarning, show1MinWarning } = useTimeWarning(timeRemainingRef);

const emit = defineEmits(['complete', 'update:answers']);

const showTest = ref(false);
const pageIndex = ref(0); // 0 first 27, 1 remaining
const answers = ref<Record<number, number | null>>({});
const endConfirmOpen = ref(false);

const { isForcedFinish, forcedFinishCountdown, clearForcedFinish } = useTeacherForceFinish({
    isActive: () => showTest.value && pageIndex.value >= 0,
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

BIT2_QUESTIONS.forEach((q) => (answers.value[q.number] = null));

if (props.pausedTestResult) {
    if (props.pausedTestResult.answers) {
        props.pausedTestResult.answers.forEach((a) => {
            answers.value[a.number] = a.answer;
        });
    }
    if (props.pausedTestResult.pageIndex) {
        pageIndex.value = props.pausedTestResult.pageIndex;
    }
    showTest.value = true;
}

watch(
    [answers, pageIndex],
    ([newAnswers, newPageIndex]) => {
        const results = {
            answers: BIT2_QUESTIONS.map((q) => ({
                number: q.number,
                answer: newAnswers[q.number],
            })),
            pageIndex: newPageIndex,
        };
        emit('update:answers', results);
    },
    { deep: true, immediate: true },
);

const firstPageQuestions = computed(() => BIT2_QUESTIONS.slice(0, 27));
const secondPageLeft = computed(() => BIT2_QUESTIONS.slice(27, 54));
const secondPageRight = computed(() => BIT2_QUESTIONS.slice(54));

const instructions = `Dieser Fragebogen soll ein Bild Ihrer Interessen an verschiedenen beruflichen Arbeiten vermitteln. Dafür werden Ihnen 81 Tätigkeiten aufgeführt. Beurteilen Sie diese der Reihe nach. Sie haben dafür die Ziffern\n\n5  sehr gern\n4  gern\n3  weder gern noch ungern\n2  ungern\n1  sehr ungern\n\n zur Verfügung. `;


function startTest() {
    showTest.value = true;
    pageIndex.value = 0;
}
function nextPage() {
    pageIndex.value++;
}
function prevPage() {
    pageIndex.value--;
}
function finishTest() {
    window.dispatchEvent(new Event('start-finish'));
    endConfirmOpen.value = true;
}
function cancelEnd() {
    if (isForcedFinish.value) {
        return;
    }
    endConfirmOpen.value = false;
    window.dispatchEvent(new Event('cancel-finish'));
    clearForcedFinish(false);
}
function confirmEnd() {
    clearForcedFinish(false);
    endConfirmOpen.value = false;
    const results = {
        answers: BIT2_QUESTIONS.map((q) => ({ number: q.number, answer: answers.value[q.number] })),
    };
    emit('complete', results);
}
</script>

<template>
    <Head title="BIT-2" />
    <div class="p-4">
        <div v-if="show5MinWarning"
            class="absolute top-0 left-0 w-full bg-green-500 text-white text-center p-2">
            5 Minuten verbleibend
        </div>
        <div v-if="show1MinWarning"
            class="absolute top-0 left-0 w-full bg-yellow-500 text-white text-center p-2">
            1 Minute verbleibend
        </div>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">BIT-2</h1>
        </div>
        <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4">
            <div class="flex flex-1 flex-col gap-4">
                <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
                    <h2 class="mb-4 text-2xl font-bold">B – I – T. II</h2>
                    <div
                        class="mb-6 w-full max-w-2xl rounded-lg border bg-yellow-50 p-4 text-base whitespace-pre-line text-foreground dark:bg-yellow-900"
                    >
                        {{ instructions }}
                    </div>
                    <Button @click="startTest" class="rounded-xl px-8 py-3 text-lg font-semibold shadow"> Test starten </Button>
                </div>

                <div v-else-if="pageIndex === 0" class="rounded-lg border bg-background p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <table class="text-base">
                                <tbody>
                                    <tr><td class="pr-2 font-mono">5</td><td>sehr gern</td></tr>
                                    <tr><td class="pr-2 font-mono">4</td><td>gern</td></tr>
                                    <tr><td class="pr-2 font-mono">3</td><td>weder gern noch ungern</td></tr>
                                    <tr><td class="pr-2 font-mono">2</td><td>ungern</td></tr>
                                    <tr><td class="pr-2 font-mono">1</td><td>sehr ungern</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <table class="w-full border-separate text-base" style="border-spacing: 0">
                            <tbody>
                                <tr v-for="q in firstPageQuestions" :key="q.number" :class="{ 'bg-gray-50 dark:bg-gray-700': answers[q.number] === null }">
                                    <td class="font-mono w-8 pr-2 align-top pt-2 text-right border-b-2 border-gray-200 dark:border-gray-700">{{ q.number }}.</td>
                                    <td class="pr-4 align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700">{{ q.text }}</td>
                                    <td v-for="opt in [5, 4, 3, 2, 1]" :key="opt" class="px-2 text-center align-top pt-1 border-b-2 border-gray-200 dark:border-gray-700">
                                        <label class="w-8 h-8 relative flex items-center justify-center border border-gray-400 cursor-pointer">
                                            <input class="sr-only" type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                            <span class="text-sm">{{ opt }}</span>
                                            <span v-if="answers[q.number] === opt" class="absolute inset-0 flex items-center justify-center text-red-600 text-xl">X</span>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <Button @click="nextPage">Weiter</Button>
                    </div>
                </div>

                <div v-else-if="pageIndex === 1" class="rounded-lg border bg-background p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <table class="text-base">
                                <tbody>
                                    <tr><td class="pr-2 font-mono">5</td><td>sehr gern</td></tr>
                                    <tr><td class="pr-2 font-mono">4</td><td>gern</td></tr>
                                    <tr><td class="pr-2 font-mono">3</td><td>weder gern noch ungern</td></tr>
                                    <tr><td class="pr-2 font-mono">2</td><td>ungern</td></tr>
                                    <tr><td class="pr-2 font-mono">1</td><td>sehr ungern</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <table class="w-full border-separate text-base" style="border-spacing: 0">
                            <tbody>
                                <tr v-for="q in secondPageLeft" :key="q.number" :class="{ 'bg-gray-50 dark:bg-gray-700': answers[q.number] === null }">
                                    <td class="font-mono w-8 pr-2 align-top pt-2 text-right border-b-2 border-gray-200 dark:border-gray-700">{{ q.number }}.</td>
                                    <td class="pr-4 align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700">{{ q.text }}</td>
                                    <td v-for="opt in [5, 4, 3, 2, 1]" :key="opt" class="px-2 text-center align-top pt-1 border-b-2 border-gray-200 dark:border-gray-700">
                                        <label class="w-8 h-8 relative flex items-center justify-center border border-gray-400 cursor-pointer">
                                            <input class="sr-only" type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                            <span class="text-sm">{{ opt }}</span>
                                            <span v-if="answers[q.number] === opt" class="absolute inset-0 flex items-center justify-center text-red-600 text-xl">X</span>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <Button variant="outline" @click="prevPage">Zurück</Button>
                        <Button @click="nextPage">Weiter</Button>
                    </div>
                </div>

                <div v-else-if="pageIndex === 2" class="rounded-lg border bg-background p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <table class="text-base">
                                <tbody>
                                    <tr><td class="pr-2 font-mono">5</td><td>sehr gern</td></tr>
                                    <tr><td class="pr-2 font-mono">4</td><td>gern</td></tr>
                                    <tr><td class="pr-2 font-mono">3</td><td>weder gern noch ungern</td></tr>
                                    <tr><td class="pr-2 font-mono">2</td><td>ungern</td></tr>
                                    <tr><td class="pr-2 font-mono">1</td><td>sehr ungern</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <table class="w-full border-separate text-base" style="border-spacing: 0">
                            <tbody>
                                <tr v-for="q in secondPageRight" :key="q.number" :class="{ 'bg-gray-50 dark:bg-gray-700': answers[q.number] === null }">
                                    <td class="font-mono w-8 pr-2 align-top pt-2 text-right border-b-2 border-gray-200 dark:border-gray-700">{{ q.number }}.</td>
                                    <td class="pr-4 align-top pt-2 border-b-2 border-gray-200 dark:border-gray-700">{{ q.text }}</td>
                                    <td v-for="opt in [5, 4, 3, 2, 1]" :key="opt" class="px-2 text-center align-top pt-1 border-b-2 border-gray-200 dark:border-gray-700">
                                        <label class="w-8 h-8 relative flex items-center justify-center border border-gray-400 cursor-pointer">
                                            <input class="sr-only" type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                            <span class="text-sm">{{ opt }}</span>
                                            <span v-if="answers[q.number] === opt" class="absolute inset-0 flex items-center justify-center text-red-600 text-xl">X</span>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <Button variant="outline" @click="prevPage">Zurück</Button>
                        <Button variant="destructive" @click="finishTest">Test beenden</Button>
                    </div>
                </div>

                <div v-else></div>
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
                    <Button v-if="!isForcedFinish" variant="secondary" @click="cancelEnd">Abbrechen</Button>
                    <Button variant="destructive" @click="confirmEnd">
                        {{ isForcedFinish ? 'Jetzt beenden' : 'Ja' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
