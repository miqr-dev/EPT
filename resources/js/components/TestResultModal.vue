<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import TestResultViewer from '@/components/TestResultViewer.vue';

const props = defineProps<{
  isOpen: boolean;
  assignment: any;
  participant: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
  answers: [] as any[],
});

const editable = ref<any | null>(null);

watch(
  () => props.assignment,
  (val) => {
    editable.value = val && val.results.length > 0 ? JSON.parse(JSON.stringify(val.results[0].result_json)) : null;
  }
);

function submit() {
  if (props.assignment && props.assignment.results.length > 0 && editable.value) {
    if (props.assignment.test.name === 'MRT-A') {
      form.answers = editable.value.answers;
      form.put(route('test-results.update', { testResult: props.assignment.results[0].id }), {
        onSuccess: () => {
          closeModal();
        },
      });
    } else {
      // Keep old behavior for other tests
      const oldForm = useForm({ result_json: JSON.stringify(editable.value) });
      oldForm.put(route('test-results.update', { testResult: props.assignment.results[0].id }), {
        onSuccess: () => {
        closeModal();
      },
    });
    }
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
      </DialogHeader>
      <TestResultViewer
        v-if="editable"
        :test="assignment.test"
        v-model="editable"
        :participant-profile="participant?.participant_profile"
        class="flex-1 overflow-y-auto mb-4"
      />
      <DialogFooter>
        <Button type="submit" @click="submit">Save changes</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
