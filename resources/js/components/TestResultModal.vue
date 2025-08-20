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
<<<<<<< HEAD
=======

function oklchToRgb(color: string): string {
  const match = /oklch\(\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)(?:deg)?(?:\s*\/\s*([\d.]+))?\s*\)/i.exec(color);
  if (!match) return color;
  const l = parseFloat(match[1]);
  const c = parseFloat(match[2]);
  const h = (parseFloat(match[3]) * Math.PI) / 180;
  const a = match[4] !== undefined ? parseFloat(match[4]) : 1;

  const a_ = Math.cos(h) * c;
  const b_ = Math.sin(h) * c;

  let l_ = l + 0.3963377774 * a_ + 0.2158037573 * b_;
  let m_ = l - 0.1055613458 * a_ - 0.0638541728 * b_;
  let s_ = l - 0.0894841775 * a_ - 1.291485548 * b_;

  l_ = l_ ** 3;
  m_ = m_ ** 3;
  s_ = s_ ** 3;

  let r = 4.0767416621 * l_ - 3.3077115913 * m_ + 0.2309699292 * s_;
  let g = -1.2684380046 * l_ + 2.6097574011 * m_ - 0.3413193965 * s_;
  let b = -0.0041960863 * l_ - 0.7034186147 * m_ + 1.707614701 * s_;

  function toSRGB(x: number) {
    const x0 = Math.max(0, Math.min(1, x));
    return x0 <= 0.0031308 ? 12.92 * x0 : 1.055 * Math.pow(x0, 1 / 2.4) - 0.055;
  }

  r = Math.round(toSRGB(r) * 255);
  g = Math.round(toSRGB(g) * 255);
  b = Math.round(toSRGB(b) * 255);
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

function replaceOklchColors(el: HTMLElement) {
  const style = window.getComputedStyle(el);
  if (style.color.includes('oklch')) {
    el.style.color = oklchToRgb(style.color);
  }
  if (style.backgroundColor.includes('oklch')) {
    el.style.backgroundColor = oklchToRgb(style.backgroundColor);
  }
  Array.from(el.children).forEach((child) => replaceOklchColors(child as HTMLElement));
}

async function canvasFromElement(el: HTMLElement) {
  const clone = el.cloneNode(true) as HTMLElement;
  clone.style.position = 'fixed';
  clone.style.left = '-10000px';
  clone.style.top = '-10000px';
  document.body.appendChild(clone);
  replaceOklchColors(clone);
  const canvas = await html2canvas(clone, { scale: 2 });
  document.body.removeChild(clone);
  return canvas;
}

function oklchToRgb(color: string): string {
  const match = /oklch\(\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)(?:deg)?(?:\s*\/\s*([\d.]+))?\s*\)/i.exec(color);
  if (!match) return color;
  const l = parseFloat(match[1]);
  const c = parseFloat(match[2]);
  const h = (parseFloat(match[3]) * Math.PI) / 180;
  const a = match[4] !== undefined ? parseFloat(match[4]) : 1;

  const a_ = Math.cos(h) * c;
  const b_ = Math.sin(h) * c;

  let l_ = l + 0.3963377774 * a_ + 0.2158037573 * b_;
  let m_ = l - 0.1055613458 * a_ - 0.0638541728 * b_;
  let s_ = l - 0.0894841775 * a_ - 1.291485548 * b_;

  l_ = l_ ** 3;
  m_ = m_ ** 3;
  s_ = s_ ** 3;

  let r = 4.0767416621 * l_ - 3.3077115913 * m_ + 0.2309699292 * s_;
  let g = -1.2684380046 * l_ + 2.6097574011 * m_ - 0.3413193965 * s_;
  let b = -0.0041960863 * l_ - 0.7034186147 * m_ + 1.707614701 * s_;

  function toSRGB(x: number) {
    const x0 = Math.max(0, Math.min(1, x));
    return x0 <= 0.0031308 ? 12.92 * x0 : 1.055 * Math.pow(x0, 1 / 2.4) - 0.055;
  }

  r = Math.round(toSRGB(r) * 255);
  g = Math.round(toSRGB(g) * 255);
  b = Math.round(toSRGB(b) * 255);
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

function replaceOklchColors(el: HTMLElement) {
  const style = window.getComputedStyle(el);
  if (style.color.includes('oklch')) {
    el.style.color = oklchToRgb(style.color);
  }
  if (style.backgroundColor.includes('oklch')) {
    el.style.backgroundColor = oklchToRgb(style.backgroundColor);
  }
  Array.from(el.children).forEach((child) => replaceOklchColors(child as HTMLElement));
}

async function canvasFromElement(el: HTMLElement) {
  const clone = el.cloneNode(true) as HTMLElement;
  clone.style.position = 'fixed';
  clone.style.left = '-10000px';
  clone.style.top = '-10000px';
  document.body.appendChild(clone);
  replaceOklchColors(clone);
  const canvas = await html2canvas(clone, { scale: 2 });
  document.body.removeChild(clone);
  return canvas;
}

async function exportChartPdf() {
  const el = viewerRef.value?.chartEl;
  if (!el) return;
  const canvas = await canvasFromElement(el);
  const img = canvas.toDataURL('image/png');
  const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
  const pdfWidth = pdf.internal.pageSize.getWidth();
  const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
  pdf.addImage(img, 'PNG', 0, 0, pdfWidth, pdfHeight);
  pdf.save('mrt-chart.pdf');
}

async function exportDetailsPdf() {
  if (!viewerRef.value) return;
  if (!viewerRef.value.showDetails) {
    viewerRef.value.showDetails = true;
    await nextTick();
  }
  const el = viewerRef.value.detailsEl;
  if (!el) return;
  const canvas = await canvasFromElement(el);
  const img = canvas.toDataURL('image/png');
  const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
  const pdfWidth = pdf.internal.pageSize.getWidth();
  const pdfHeight = pdf.internal.pageSize.getHeight();
  let imgWidth = pdfWidth;
  let imgHeight = (canvas.height * imgWidth) / canvas.width;
  if (imgHeight > pdfHeight) {
    imgHeight = pdfHeight;
    imgWidth = (canvas.width * imgHeight) / canvas.height;
  }
  const marginX = (pdfWidth - imgWidth) / 2;
  pdf.addImage(img, 'PNG', marginX, 0, imgWidth, imgHeight);
  pdf.save('mrt-antworten.pdf');
}
>>>>>>> 6134a31... fix: convert OKLCH colors for PDF export
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
