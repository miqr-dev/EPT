<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TestResultViewer from './TestResultViewer.vue';

const props = defineProps<{
    isOpen: boolean;
    testResult: any;
}>();

const emit = defineEmits(['close']);

function closeModal() {
    emit('close');
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="closeModal">
        <DialogContent class="max-w-4xl">
            <DialogHeader>
                <DialogTitle>Test Result</DialogTitle>
            </DialogHeader>
            <div v-if="testResult" class="py-4">
                <TestResultViewer :testResult="testResult" />
            </div>
        </DialogContent>
    </Dialog>
</template>
