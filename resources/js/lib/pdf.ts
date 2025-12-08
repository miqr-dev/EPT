import html2canvas from 'html2canvas-pro';
import jsPDF from 'jspdf';

export async function generatePdfFromElement(element: HTMLElement, filename: string) {
  if (!element) {
    console.error("Element for PDF generation not found.");
    return;
  }

  const testName = element.dataset?.testName;
  const zoomFactor = testName === 'AVEM' ? 1.4 : 1;
  const originalTransform = element.style.transform;
  const originalTransformOrigin = element.style.transformOrigin;

  try {
    if (zoomFactor !== 1) {
      element.style.transformOrigin = 'top left';
      element.style.transform = `scale(${zoomFactor})`;
    }

    const rect = element.getBoundingClientRect();
    const pixelRatio = Math.max(2, window.devicePixelRatio || 1);

    // Render the element at a higher resolution to avoid blurry text in the PDF.
    const canvas = await html2canvas(element, {
      scale: pixelRatio,
      useCORS: true,
      backgroundColor: '#ffffff',
      width: rect.width,
      height: rect.height,
    });

    const img = canvas.toDataURL('image/png');
    const mmPerPx = 25.4 / 96; // convert canvas pixels to millimeters
    const targetWidthPx = rect.width / zoomFactor;
    const targetHeightPx = rect.height / zoomFactor;
    const pdfW = targetWidthPx * mmPerPx;
    const pdfH = targetHeightPx * mmPerPx;

    const pdf = new jsPDF({
      orientation: targetWidthPx > targetHeightPx ? 'landscape' : 'portrait',
      unit: 'mm',
      format: [pdfW, pdfH],
    });

    pdf.addImage(img, 'PNG', 0, 0, pdfW, pdfH);
    pdf.save(filename);
  } finally {
    element.style.transform = originalTransform;
    element.style.transformOrigin = originalTransformOrigin;
  }
}
