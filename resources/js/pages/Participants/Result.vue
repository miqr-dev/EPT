<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TestResultViewer from '@/components/TestResultViewer.vue';
import { ref } from 'vue';

const props = defineProps<{
    assignment: any;
    result: any;
}>();

const resultData = ref(props.result.result_json);
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold mb-4">{{ assignment.test.name }} Result</h1>
            <TestResultViewer :test="assignment.test" v-model="resultData" />
        </div>
    </AppLayout>
</template>
