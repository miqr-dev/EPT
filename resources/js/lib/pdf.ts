import html2canvas from 'html2canvas-pro';
import jsPDF from 'jspdf';

export async function generatePdfFromElement(element: HTMLElement, filename: string) {
  if (!element) {
    console.error("Element for PDF generation not found.");
    return;
  }

  const canvas = await html2canvas(element, {
    scale: 4, // Higher scale for better quality
    useCORS: true,
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
  pdf.save(filename);
}
