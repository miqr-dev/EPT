<script setup lang="ts">
import TestResultViewer from '@/components/TestResultViewer.vue';

const props = defineProps<{
  testResult: {
    result_json: any;
    assignment: {
      test: { name: string };
    };
  };
}>();
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-4">Test Result</h1>
    <TestResultViewer :modelValue="testResult.result_json" :test="testResult.assignment.test" />
  </div>
</template>
