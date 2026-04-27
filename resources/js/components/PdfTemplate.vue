<script setup lang="ts">
import TestResultViewer from '@/components/TestResultViewer.vue';

defineProps<{
    assignment: any;
    participant: any;
    teacherName: string;
    showTeacher?: boolean;
}>();

const testDate = new Date().toLocaleDateString('de-DE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
});
</script>

<template>
    <div class="bg-white p-8 font-sans text-black">
        <!-- Header -->
        <div class="flex items-center justify-between border-b-2 border-gray-200 pb-4">
            <div>
                <h1 class="text-3xl font-bold">Testergebnis</h1>
                <p class="text-gray-600">{{ assignment.test.name }}</p>
            </div>
            <div class="text-right">
                <p class="text-gray-600">Datum: {{ testDate }}</p>
            </div>
        </div>

        <!-- Participant and Teacher Info -->
        <div class="mt-8 grid grid-cols-2 gap-4">
            <div>
                <h2 class="mb-2 text-xl font-semibold">Teilnehmer/in</h2>
                <p v-if="participant">
                    {{ participant.name }}
                </p>
                <p v-else>N/A</p>
            </div>
            <div v-if="showTeacher">
                <h2 class="mb-2 text-xl font-semibold">Prüfer/in</h2>
                <p>{{ teacherName }}</p>
            </div>
        </div>

        <!-- Results -->
        <div class="mt-8">
            <h2 class="mb-4 text-xl font-semibold">Auswertung</h2>
            <div class="rounded-lg border bg-gray-50 p-4">
                <TestResultViewer
                    v-if="assignment && assignment.results.length > 0"
                    :test="assignment.test"
                    :model-value="assignment.results[0].result_json"
                    :participant-profile="participant?.participant_profile"
                    :manual-scores="assignment.results[0].manual_scores ?? []"
                    :show-answers="assignment.test.name === 'BT'"
                    :force-open-answers="assignment.test.name === 'BT'"
                />
                <div v-else class="text-center text-gray-500">
                    <p>Keine Ergebnisse verfügbar.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <!-- <div class="mt-12 text-center text-gray-500 text-sm">
      <p>Dieses Dokument wurde automatisch generiert.</p>
    </div> -->
    </div>
</template>
