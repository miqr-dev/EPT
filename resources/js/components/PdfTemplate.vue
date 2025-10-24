<script setup lang="ts">
import TestResultViewer from '@/components/TestResultViewer.vue';

defineProps<{
  assignment: any;
  participant: any;
  teacherName: string;
}>();

const testDate = new Date().toLocaleDateString('de-DE', {
  year: 'numeric',
  month: 'long',
  day: 'numeric',
});
</script>

<template>
  <div class="bg-white text-black p-8 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center pb-4 border-b-2 border-gray-200">
      <div>
        <h1 class="text-3xl font-bold">Testergebnis</h1>
        <p class="text-gray-600">{{ assignment.test.name }}</p>
      </div>
      <div class="text-right">
        <p class="text-gray-600">Datum: {{ testDate }}</p>
      </div>
    </div>

    <!-- Participant and Teacher Info -->
    <div class="grid grid-cols-2 gap-4 mt-8">
      <div>
        <h2 class="text-xl font-semibold mb-2">Teilnehmer/in</h2>
        <p v-if="participant">
          {{ participant.name }}
        </p>
        <p v-else>
          N/A
        </p>
      </div>
      <div>
        <h2 class="text-xl font-semibold mb-2">Prüfer/in</h2>
        <p>{{ teacherName }}</p>
      </div>
    </div>

    <!-- Results -->
    <div class="mt-8">
      <h2 class="text-xl font-semibold mb-4">Auswertung</h2>
      <div class="p-4 border rounded-lg bg-gray-50">
        <TestResultViewer v-if="assignment && assignment.results.length > 0" :test="assignment.test"
          :model-value="assignment.results[0].result_json" :participant-profile="participant?.participant_profile"
          :show-answers="false" />
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
