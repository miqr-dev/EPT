<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import TestResultViewer from '@/components/TestResultViewer.vue';

const props = defineProps<{
  isOpen: boolean;
  testResult: any;
  test: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
  result_json: '',
});

const editable = ref<any | null>(null);
const mrtData = ref<any | null>(null);

watch(
  () => [props.testResult, props.test],
  async ([result, test]) => {
    editable.value = result ? JSON.parse(JSON.stringify(result.result_json)) : null;
    if (result && test?.code?.toLowerCase() === 'mrt-a') {
      try {
        const res = await fetch(
          route('test-results.mrt-a-chart', { testResult: result.id })
        );
        mrtData.value = res.ok ? await res.json() : null;
      } catch (e) {
        mrtData.value = null;
      }
    } else {
      mrtData.value = null;
    }
  },
  { immediate: true }
);

function submit() {
  if (props.testResult && editable.value) {
    form.result_json = JSON.stringify(editable.value);
    form.put(route('test-results.update', { testResult: props.testResult.id }), {
      onSuccess: () => {
        closeModal();
      },
    });
  }
}

function closeModal() {
  emit('close');
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="closeModal">
    <DialogContent
      class="w-screen !h-screen !max-w-none !top-0 !left-0 !translate-x-0 !translate-y-0 !rounded-none"
    >
      <DialogHeader>
        <DialogTitle>Edit Test Result</DialogTitle>
        <DialogDescription class="sr-only">View result details</DialogDescription>
      </DialogHeader>
      <TestResultViewer
        v-if="editable"
        v-model="editable"
        class="flex-1 overflow-y-auto mb-4"
        :mrt-data="mrtData"
      />
      <DialogFooter>
        <Button type="submit" @click="submit">Save changes</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
