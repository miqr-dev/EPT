<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import TestResultViewer from '@/components/TestResultViewer.vue';

const props = defineProps<{
  isOpen: boolean;
  testResult: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
  result_json: '',
});

const editable = ref<any | null>(null);

watch(
  () => props.testResult,
  (val) => {
    editable.value = val ? JSON.parse(JSON.stringify(val.result_json)) : null;
  }
);

function submit() {
  if (props.testResult && editable.value) {
    form.result_json = JSON.stringify(editable.value);
    form.put(route('test-results.update', { testResult: props.testResult.id }), {
      preserveState: true,
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
      class="max-w-6xl max-h-[80vh] flex flex-col"
    >
      <DialogHeader>
        <DialogTitle>Edit Test Result</DialogTitle>
      </DialogHeader>
      <TestResultViewer
        v-if="editable"
        v-model="editable"
        class="flex-1 overflow-y-auto mb-4"
      />
      <DialogFooter>
        <Button type="submit" @click="submit">Save changes</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
