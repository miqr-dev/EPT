<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
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
const viewerRef = ref<any>(null);

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
        onSuccess: closeModal,
      });
    } else {
      const oldForm = useForm({ result_json: JSON.stringify(editable.value) });
      oldForm.put(route('test-results.update', { testResult: props.assignment.results[0].id }), {
        onSuccess: closeModal,
      });
    }
  }
}

function closeModal() {
  emit('close');
}

function inlineStyles(el: HTMLElement) {
  const style = window.getComputedStyle(el);
  const css = Array.from(style)
    .map((prop) => `${prop}:${style.getPropertyValue(prop)};`)
    .join('');
  el.setAttribute('style', css);
  Array.from(el.children).forEach((child) => inlineStyles(child as HTMLElement));
}

async function exportPdf(type: string, el: HTMLElement, filename: string) {
  const clone = el.cloneNode(true) as HTMLElement;
  inlineStyles(clone);
  const html = clone.outerHTML;
  const token = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  const res = await fetch(route('mrt-a.export.pdf', { type }), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token ?? '',
    },
    body: JSON.stringify({ html }),
  });
  const blob = await res.blob();
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
}

async function exportChartPdf() {
  const el = viewerRef.value?.chartEl;
  if (!el) return;
  await exportPdf('chart', el, 'mrt-chart.pdf');
}

async function exportDetailsPdf() {
  if (!viewerRef.value) return;
  if (!viewerRef.value.showDetails) {
    viewerRef.value.showDetails = true;
    await nextTick();
  }
  const el = viewerRef.value.detailsEl;
  if (!el) return;
  await exportPdf('details', el, 'mrt-antworten.pdf');
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="closeModal">
    <DialogContent
      class="w-screen !h-screen !max-w-none !top-0 !left-0 !translate-x-0 !translate-y-0 !rounded-none"
    >
      <DialogHeader>
        <div class="flex items-center justify-between w-full">
          <DialogTitle>Edit Test Result</DialogTitle>
          <div v-if="assignment.test.name === 'MRT-A'" class="flex gap-2">
            <Button variant="outline" size="sm" @click="exportChartPdf">Chart PDF</Button>
            <Button variant="outline" size="sm" @click="exportDetailsPdf">Antworten PDF</Button>
          </div>
        </div>
      </DialogHeader>
      <TestResultViewer
        v-if="editable"
        ref="viewerRef"
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

