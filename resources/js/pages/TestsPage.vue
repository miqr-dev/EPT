<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button'; // Assuming a Button component is available

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tests',
        href: '/tests',
    },
];

const questions = ref([
    "What is 2+2?",
    "What is the capital of France?",
    "What is the color of the sky on a clear day?",
]);

const currentQuestionIndex = ref(0);
const nextButtonClickCount = ref(0);

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
        if (currentQuestionIndex.value < questions.value.length - 1) {
            currentQuestionIndex.value++;
            nextButtonClickCount.value = 0; // Reset for the new question
        } else {
            // All questions answered
            currentQuestionIndex.value = questions.value.length; // Move past the last question
            nextButtonClickCount.value = 0;
        }
    }
};

const isTestComplete = computed(() => {
    return currentQuestionIndex.value >= questions.value.length;
});

</script>

<template>
    <Head title="Tests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div v-if="!isTestComplete && currentQuestion" class="p-6 bg-background border rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Question {{ currentQuestionIndex + 1 }}:</h2>
                <p class="text-lg mb-6">{{ currentQuestion }}</p>
                <Button @click="handleNextClick">
                    {{ nextButtonText }}
                </Button>
                <p v-if="nextButtonClickCount === 1" class="text-sm text-muted-foreground mt-2">
                    Click "Next (Confirm)" again to proceed.
                </p>
            </div>
            <div v-else-if="isTestComplete" class="p-6 bg-background border rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Test Complete!</h2>
                <p>You have answered all the questions.</p>
            </div>
            <div v-else>
                <p>Loading questions...</p>
            </div>
        </div>
    </AppLayout>
</template>
