<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { BIT2_QUESTIONS } from '@/pages/Questions/BIT2Questions';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const emit = defineEmits(['complete']);

const showTest = ref(false);
const pageIndex = ref(0); // 0 first 27, 1 remaining
const answers = ref<Record<number, number | null>>({});
const endConfirmOpen = ref(false);

BIT2_QUESTIONS.forEach((q) => (answers.value[q.number] = null));

const firstPageQuestions = computed(() => BIT2_QUESTIONS.slice(0, 27));
const secondPageLeft = computed(() => BIT2_QUESTIONS.slice(27, 54));
const secondPageRight = computed(() => BIT2_QUESTIONS.slice(54));

const instructions = `B – I – T. II    Form BA\n\nHinweise zur Bearbeitung\n\nDiese Untersuchung soll ein Bild Ihrer Interessen an verschiedenen beruflichen Arbeiten vermitteln. Sie finden im Innern dieses Fragebogens 81 solcher Tätigkeiten aufgeführt. Der Reihe nach, von 1–81, sollen Sie Ihr Interesse an jeder Tätigkeit beurteilen. Sie haben dafür die Ziffern\n\n5  sehr gern\n4  gern\n3  weder gern noch ungern\n2  ungern\n1  sehr ungern\n\nWenn Sie eine Tätigkeit sehr gern ausüben würden, dann geben Sie ihr 5 Punkte, indem Sie das Kästchen mit der "5" ankreuzen und so weiter. Wenn Sie eine Tätigkeit ungern ausüben würden, geben Sie ihr 2 Punkte, indem Sie das Kästchen mit der "2" ankreuzen. Wenn Sie eine Tätigkeit weder gern noch ungern ausüben würden, geben Sie ihr 3 Punkte.\n\nBenutzen Sie bitte keine Wischmethode, sondern kreuzen Sie jedes Kästchen sauber an. Beginnen Sie links oben und arbeiten Sie der Reihe nach. Wenn Sie die 81. Tätigkeit beurteilt haben, ist diese Bearbeitung abgeschlossen.`;

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
    endConfirmOpen.value = false;
    window.dispatchEvent(new Event('cancel-finish'));
}
function confirmEnd() {
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
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">BIT-2</h1>
        </div>
        <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4">
            <div class="flex flex-1 flex-col gap-4">
                <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
                    <h2 class="mb-4 text-2xl font-bold">B – I – T. II Form BA</h2>
                    <div
                        class="mb-6 w-full max-w-2xl rounded-lg border bg-yellow-50 p-4 text-base whitespace-pre-line text-foreground dark:bg-yellow-900"
                    >
                        {{ instructions }}
                    </div>
                    <Button @click="startTest" class="rounded-xl px-8 py-3 text-lg font-semibold shadow"> Test starten </Button>
                </div>

                <div v-else-if="pageIndex === 0" class="rounded-lg border bg-background p-6">
                    <table class="mb-6 w-full border-separate text-base" style="border-spacing: 0">
                        <tbody>
                            <tr v-for="q in firstPageQuestions" :key="q.number">
                                <td class="w-8 pr-2 align-top">{{ q.number }}.</td>
                                <td class="pr-4 align-top">{{ q.text }}</td>
                                <td v-for="opt in [5, 4, 3, 2, 1]" :key="opt" class="px-1 text-center">
                                    <label class="flex flex-col items-center">
                                        <input type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                        <span class="text-xs">{{ opt }}</span>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="flex justify-end">
                        <Button @click="nextPage">Weiter</Button>
                    </div>
                </div>

                <div v-else-if="pageIndex === 1" class="rounded-lg border bg-background p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <table class="w-full border-separate text-base" style="border-spacing: 0">
                            <tbody>
                                <tr v-for="q in secondPageLeft" :key="q.number">
                                    <td class="w-8 pr-2 align-top">{{ q.number }}.</td>
                                    <td class="pr-4 align-top">{{ q.text }}</td>
                                    <td v-for="opt in [5, 4, 3, 2, 1]" :key="opt" class="px-1 text-center">
                                        <label class="flex flex-col items-center">
                                            <input type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                            <span class="text-xs">{{ opt }}</span>
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="w-full border-separate text-base" style="border-spacing: 0">
                            <tbody>
                                <tr v-for="q in secondPageRight" :key="q.number">
                                    <td class="w-8 pr-2 align-top">{{ q.number }}.</td>
                                    <td class="pr-4 align-top">{{ q.text }}</td>
                                    <td v-for="opt in [5, 4, 3, 2, 1]" :key="opt" class="px-1 text-center">
                                        <label class="flex flex-col items-center">
                                            <input type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                            <span class="text-xs">{{ opt }}</span>
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
                    <DialogDescription> Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück. </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="secondary" @click="cancelEnd">Abbrechen</Button>
                    <Button variant="destructive" @click="confirmEnd">Ja</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
