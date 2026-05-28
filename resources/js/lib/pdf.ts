import html2canvas from 'html2canvas-pro';
import jsPDF from 'jspdf';

type GeneratePdfOptions = {
    zoom?: number;
    scale?: number;
};

async function captureElement(element: HTMLElement, options: GeneratePdfOptions = {}) {
    if (!element) {
        console.error('Element for PDF generation not found.');
        return;
    }

    const { zoom, scale } = options;
    const shouldZoom = typeof zoom === 'number' && zoom > 0 && zoom !== 1;

    const originalTransform = element.style.transform;
    const originalTransformOrigin = element.style.transformOrigin;

    try {
        if (shouldZoom) {
            element.style.transform = `scale(${zoom})`;
            element.style.transformOrigin = 'top left';
        }

        const resolvedScale = Math.max(scale ?? 3, window.devicePixelRatio || 1);

        return await html2canvas(element, {
            scale: resolvedScale,
            useCORS: true,
            backgroundColor: '#ffffff',
        });
    } finally {
        if (shouldZoom) {
            element.style.transform = originalTransform;
            element.style.transformOrigin = originalTransformOrigin;
        }
    }
}

function addCanvasToPdfPage(pdf: jsPDF, canvas: HTMLCanvasElement) {
    const pdfW = pdf.internal.pageSize.getWidth();
    const pdfH = pdf.internal.pageSize.getHeight();

    let imgW = pdfW;
    let imgH = (canvas.height * imgW) / canvas.width;
    if (imgH > pdfH) {
        imgH = pdfH;
        imgW = (canvas.width * imgH) / canvas.height;
    }

    const marginX = (pdfW - imgW) / 2;
    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', marginX, 0, imgW, imgH);
}

export async function generatePdfFromElements(elements: HTMLElement[], filename: string, options: GeneratePdfOptions = {}) {
    const validElements = elements.filter(Boolean);

    if (validElements.length === 0) {
        console.error('Elements for PDF generation not found.');
        return;
    }

    const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
    let renderedPages = 0;

    for (let index = 0; index < validElements.length; index++) {
        const canvas = await captureElement(validElements[index], options);

        if (!canvas) {
            continue;
        }

        if (renderedPages > 0) {
            pdf.addPage();
        }

        addCanvasToPdfPage(pdf, canvas);
        renderedPages++;
    }

    if (renderedPages === 0) {
        console.error('PDF generation failed because no pages could be rendered.');
        return;
    }

    pdf.save(filename);
}

export async function generatePdfFromElement(element: HTMLElement, filename: string, options: GeneratePdfOptions = {}) {
    await generatePdfFromElements([element], filename, options);
}
