<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue'; // Add watch and nextTick
import { Button } from '@/components/ui/button'; // Assuming a Button component is available
import { Input } from '@/components/ui/input'; // Assuming an Input component is available and preferred

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tests',
        href: '/tests',
    },
];

interface Question {
  text: string;
  answer: string; // Storing answers as strings for flexible comparison, e.g. "0.05"
}

const questions = ref<Question[]>([
    { text: "619020 – 541600 = ?", answer: "77420" },
    { text: "619020 = 174309 + ?", answer: "444711" },
    { text: "4 : 80 = ?", answer: "0.05" },
]);

const currentQuestionIndex = ref(0);
const nextButtonClickCount = ref(0);
const userAnswers = ref<string[]>(Array(questions.value.length).fill('')); // New ref for user answers
const answerInput = ref<InstanceType<typeof Input> | null>(null); // Or HTMLInputElement if it's a native input
const score = ref(0); // Add this new ref
const startTime = ref<number | null>(null);
const totalTimeTaken = ref<number | null>(null); // in seconds

const currentQuestion = computed(() => {
    if (currentQuestionIndex.value < questions.value.length) {
        return questions.value[currentQuestionIndex.value];
    }
    return null; // Or some "Test Complete" message
});

const nextButtonText = computed(() => {
    if (nextButtonClickCount.value === 1) {
        return "Next (Confirm)";
    }
    return "Next";
});

const handleNextClick = () => {
    nextButtonClickCount.value++;
    if (nextButtonClickCount.value >= 2) {
        // Check answer for the current question BEFORE moving to the next
        if (currentQuestionIndex.value < questions.value.length) {
            const userAnswer = userAnswers.value[currentQuestionIndex.value]?.trim();
            const correctAnswer = questions.value[currentQuestionIndex.value].answer;
            if (userAnswer === correctAnswer) {
                score.value++;
            }
        }

        // Logic to move to the next question or complete the test
        if (currentQuestionIndex.value < questions.value.length - 1) {
            currentQuestionIndex.value++;
            nextButtonClickCount.value = 0; // Reset for the new question
            // userAnswers.value[currentQuestionIndex.value] = ''; // Optionally clear next answer field
        } else {
            // All questions answered
            currentQuestionIndex.value = questions.value.length; // Move past the last question
            nextButtonClickCount.value = 0;

            // Calculate total time taken
            if (startTime.value) {
                totalTimeTaken.value = Math.round((Date.now() - startTime.value) / 1000);
            }
        }
    }
};

const isTestComplete = computed(() => {
    return currentQuestionIndex.value >= questions.value.length;
});

const percentageScore = computed(() => {
    if (questions.value.length === 0) return 0;
    return Math.round((score.value / questions.value.length) * 100);
});

watch(currentQuestionIndex, async (newIndex, oldIndex) => {
    if (newIndex === 0 && startTime.value === null) {
        startTime.value = Date.now();
    }

    if (newIndex !== oldIndex && newIndex < questions.value.length) {
        await nextTick(); // Wait for the DOM to update
        if (answerInput.value) {
            // If answerInput.value is a Vue component instance, it might have a focus method
            // or you might need to access its underlying element.
            // Let's assume the Input component itself can be focused or has an element to focus.
            // If 'Input' is a simple wrapper, it might be answerInput.value.$el.focus()
            // or if it forwards refs, answerInput.value.focus() might just work.
            // For a standard HTML input, it would be: (answerInput.value as HTMLInputElement).focus();
            // Let's try a common pattern for custom input components:
            if (typeof (answerInput.value as any)?.focus === 'function') {
                 (answerInput.value as any).focus();
            } else if ((answerInput.value as any)?.$el && typeof (answerInput.value as any)?.$el.focus === 'function') {
                 (answerInput.value as any).$el.focus();
            }
        }
    }
}, { immediate: true }); // `immediate: true` to focus on the first question load.

</script>

<template>
    <Head title="Tests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div v-if="!isTestComplete && currentQuestion" class="p-6 bg-background border rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Question {{ currentQuestionIndex + 1 }}:</h2>
                <p class="text-lg mb-6">{{ currentQuestion.text }}</p>

                <!-- New Input Field -->
                <Input
                    ref="answerInput"
                    type="text"
                    v-model="userAnswers[currentQuestionIndex]"
                    placeholder="Your answer"
                    class="mb-4 w-full md:w-1/2"
                />

                <Button @click="handleNextClick">
                    {{ nextButtonText }}
                </Button>
                <p v-if="nextButtonClickCount === 1" class="text-sm text-muted-foreground mt-2">
                    Click "Next (Confirm)" again to proceed.
                </p>
            </div>
            <div v-else-if="isTestComplete" class="p-6 bg-background border rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Test Complete!</h2>
                <div class="space-y-2">
                    <p>Here are your results:</p>
                    <p>Correct Answers: {{ score }} out of {{ questions.length }}</p>
                    <p>Percentage: {{ percentageScore }}%</p>
                    <p v-if="totalTimeTaken !== null">Time Taken: {{ totalTimeTaken }} seconds</p>
                    <p v-else>Time Taken: Calculating...</p>
                </div>

                <!-- Optional: Add a button to retake the test or navigate elsewhere -->
                <!-- For example:
                <Button @click="retakeTest" class="mt-4">Retake Test</Button>
                (retakeTest function would need to be implemented to reset state)
                -->
            </div>
            <div v-else>
                <p>Loading questions...</p>
            </div>
        </div>
    </AppLayout>
</template>
