<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import TestResultViewer from '@/components/TestResultViewer.vue';
import PdfTemplate from './PdfTemplate.vue';
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
const pdfTemplateRef = ref<any>(null);
const isGeneratingPdf = ref(false);

watch(
  () => props.assignment,
  (val) => {
    editable.value = val && val.results.length > 0 ? JSON.parse(JSON.stringify(val.results[0].result_json)) : null;
  }
);

function submit() {
  if (props.assignment && props.assignment.results.length > 0 && editable.value) {
    if (['MRT-A', 'MRT-B'].includes(props.assignment.test.name)) {
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

async function downloadUnifiedPdf() {
  isGeneratingPdf.value = true;
  await nextTick();

  const el = pdfTemplateRef.value?.$el as HTMLElement | undefined;
  if (!el) {
    console.error("PDF template element not found.");
    isGeneratingPdf.value = false;
    return;
  }

  const canvas = await html2canvas(el, {
      scale: 2, // Higher scale for better quality
  });

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
  pdf.save(`${props.participant.participant_profile.lastname}_${props.assignment.test.name}_Ergebnis.pdf`);
  isGeneratingPdf.value = false;
}

</script>

<template>
  <Dialog :open="isOpen" @update:open="(open) => { if (!open) closeModal(); }">
    <DialogContent class="w-screen !h-screen !max-w-none !top-0 !left-0 !translate-x-0 !translate-y-0 !rounded-none">
      <DialogHeader>
        <div class="flex items-center justify-between w-full">
          <DialogTitle>Testergebnis bearbeiten</DialogTitle>
          <div v-if="assignment" class="flex gap-2">
             <Button variant="outline" size="sm" @click="downloadUnifiedPdf" :disabled="isGeneratingPdf">
              {{ isGeneratingPdf ? 'Generiere PDF...' : 'Ergebnis PDF' }}
            </Button>
          </div>
        </div>
      </DialogHeader>
      <TestResultViewer v-if="editable" ref="viewerRef" :test="assignment?.test" v-model="editable"
        :participant-profile="participant?.participant_profile" class="flex-1 overflow-y-auto mb-4" />
      <DialogFooter>
        <Button type="submit" @click="submit">Änderungen speichern</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <!-- Hidden template for PDF generation -->
  <div v-show="isGeneratingPdf" class="fixed top-0 left-0" style="z-index: -1; width: 210mm; height: 297mm;">
      <PdfTemplate
        v-if="assignment"
        ref="pdfTemplateRef"
        :assignment="assignment"
        :participant="participant"
        teacherName="Frau Dr. Musterfrau"
      />
  </div>
</template>
