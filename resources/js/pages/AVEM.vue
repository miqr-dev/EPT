<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AVEM_QUESTIONS } from '@/pages/Questions/AVEMQuestions';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const emit = defineEmits(['complete']);

const showTest = ref(false);
const answers = ref<Record<number, number | null>>({});
const endConfirmOpen = ref(false);

AVEM_QUESTIONS.forEach((q) => (answers.value[q.number] = null));

const instructions = `Wir bitten Sie, einige Ihrer üblichen Verhaltensweisen, Einstellungen und Gewohnheiten zu beschreiben, wobei vor allem Ihr Arbeitsleben Bezug genommen wird. Dazu finden Sie im Folgenden eine Reihe von Aussagen. Lesen Sie jeden dieser Sätze gründlich und entscheiden Sie, in welchem Maße er auf Sie persönlich zutrifft.`;

function startTest() {
    showTest.value = true;
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
        answers: AVEM_QUESTIONS.map((q) => ({ number: q.number, answer: answers.value[q.number] })),
    };
    emit('complete', results);
}
</script>

<template>
    <Head title="AVEM" />
    <div class="p-4">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">AVEM</h1>
        </div>
        <div class="flex min-h-[600px] flex-1 gap-4 rounded-xl bg-muted/20 p-4">
            <div class="flex flex-1 flex-col gap-4">
                <div v-if="!showTest" class="flex h-full flex-col items-center justify-center">
                    <div
                        class="mb-6 w-full max-w-2xl rounded-lg border bg-yellow-50 p-4 text-base whitespace-pre-line text-foreground dark:bg-yellow-900"
                    >
                        {{ instructions }}
                        <table class="mt-4 text-base">
                            <tbody>
                                <tr>
                                    <td class="pr-2 font-mono">1</td>
                                    <td>trifft überhaupt nicht zu</td>
                                </tr>
                                <tr>
                                    <td class="pr-2 font-mono">2</td>
                                    <td>trifft überwiegend nicht zu</td>
                                </tr>
                                <tr>
                                    <td class="pr-2 font-mono">3</td>
                                    <td>teils/teils</td>
                                </tr>
                                <tr>
                                    <td class="pr-2 font-mono">4</td>
                                    <td>trifft überwiegend zu</td>
                                </tr>
                                <tr>
                                    <td class="pr-2 font-mono">5</td>
                                    <td>trifft völlig zu</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Button @click="startTest" class="rounded-xl px-8 py-3 text-lg font-semibold shadow">Test starten</Button>
                </div>

                <div v-else class="overflow-x-auto rounded-lg border bg-background p-6">
                    <table class="w-full border-separate text-base" style="border-spacing: 0">
                        <tbody>
                            <tr v-for="q in AVEM_QUESTIONS" :key="q.number" :class="{ 'bg-gray-50 dark:bg-gray-700': answers[q.number] === null }">
                                <td class="w-8 border-b-2 border-gray-200 pt-2 pr-2 text-right align-top font-mono dark:border-gray-700">
                                    {{ q.number }}.
                                </td>
                                <td class="border-b-2 border-gray-200 pt-2 pr-4 align-top dark:border-gray-700">{{ q.text }}</td>
                                <td
                                    v-for="opt in [1, 2, 3, 4, 5]"
                                    :key="opt"
                                    class="border-b-2 border-gray-200 px-2 pt-1 text-center align-top dark:border-gray-700"
                                >
                                    <label class="relative flex h-8 w-8 cursor-pointer items-center justify-center border border-gray-400">
                                        <input class="sr-only" type="radio" :name="'q' + q.number" :value="opt" v-model="answers[q.number]" />
                                        <span class="text-sm">{{ opt }}</span>
                                        <span
                                            v-if="answers[q.number] === opt"
                                            class="absolute inset-0 flex items-center justify-center text-xl text-red-600"
                                            >X</span
                                        >
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4 flex justify-end">
                        <Button variant="destructive" @click="finishTest">Test beenden</Button>
                    </div>
                </div>
            </div>
        </div>
        <Dialog v-model:open="endConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Test beenden</DialogTitle>
                    <DialogDescription>Sind Sie sicher, dass Sie den Test beenden möchten? Es gibt kein Zurück.</DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="secondary" @click="cancelEnd">Abbrechen</Button>
                    <Button variant="destructive" @click="confirmEnd">Ja</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
