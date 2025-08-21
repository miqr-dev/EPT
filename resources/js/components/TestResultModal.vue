<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import TestResultViewer from '@/components/TestResultViewer.vue';
import html2canvas from 'html2canvas-pro';
import jsPDF from 'jspdf';

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

async function exportChartPdf() {
  await nextTick(); // ensure chart has rendered

  // 1) Try what you exposed already
  let canvasEl = viewerRef.value?.chartEl as HTMLCanvasElement | undefined;

  // 2) If that isn’t a <canvas>, look for one inside the child’s root DOM
  if (!(canvasEl instanceof HTMLCanvasElement)) {
    const root = viewerRef.value?.$el as HTMLElement | undefined;
    canvasEl = root?.querySelector('canvas') ?? undefined;
  }

  // 3) If still not found, fall back to DOM snapshot of the chart container
  if (!(canvasEl instanceof HTMLCanvasElement)) {
    const chartContainer =
      (viewerRef.value?.chartEl as HTMLElement) ??
      (viewerRef.value?.$el as HTMLElement);
    if (!chartContainer) return;
    const snap = await canvasFromElement(chartContainer);
    const img = snap.toDataURL('image/png');
    const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pdfW = pdf.internal.pageSize.getWidth();
    const imgH = (snap.height * pdfW) / snap.width;
    pdf.addImage(img, 'PNG', 0, 0, pdfW, imgH);
    pdf.save('mrt-chart.pdf');
    return;
  }

  // Happy path: real canvas
  const img = canvasEl.toDataURL('image/png');
  const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
  const pdfW = pdf.internal.pageSize.getWidth();
  const imgH = (canvasEl.height * pdfW) / canvasEl.width;
  pdf.addImage(img, 'PNG', 0, 0, pdfW, imgH);
  pdf.save('mrt-chart.pdf');
}

// 3) Answers/details → PDF via html2canvas-pro snapshot
async function exportDetailsPdf() {
  if (!viewerRef.value) return;
  if (!viewerRef.value.showDetails) {
    viewerRef.value.showDetails = true;
    await nextTick();
  }

  const el = viewerRef.value.detailsEl as HTMLElement | undefined;
  if (!el) return;

  const canvas = await canvasFromElement(el);
  const img = canvas.toDataURL('image/png');

  const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
  const pdfW = pdf.internal.pageSize.getWidth();
  const pdfH = pdf.internal.pageSize.getHeight();

  let imgW = pdfW;
  let imgH = (canvas.height * imgW) / canvas.width;
  if (imgH > pdfH) {
    imgH = pdfH;
    imgW = (canvas.width * imgH) / canvas.height;
  }

  const marginX = (pdfW - imgW) / 2;
  pdf.addImage(img, 'PNG', marginX, 0, imgW, imgH);
  pdf.save('mrt-antworten.pdf');
}

</script>

<template>
  <Dialog :open="isOpen" @update:open="(open) => { if (!open) closeModal(); }">
    <DialogContent
      class="w-screen !h-screen !max-w-none !top-0 !left-0 !translate-x-0 !translate-y-0 !rounded-none"
    >
      <DialogHeader>
        <div class="flex items-center justify-between w-full">
          <DialogTitle>Edit Test Result</DialogTitle>
          <div v-if="assignment && assignment.test && assignment.test.name === 'MRT-A'" class="flex gap-2">
            <Button variant="outline" size="sm" @click="exportChartPdf">Chart PDF</Button>
            <Button variant="outline" size="sm" @click="exportDetailsPdf">Antworten PDF</Button>
          </div>
        </div>
      </DialogHeader>
      <TestResultViewer
        v-if="editable"
        ref="viewerRef"
        :test="assignment?.test"
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

