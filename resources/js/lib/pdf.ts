import html2canvas from 'html2canvas-pro';
import jsPDF from 'jspdf';

type GeneratePdfOptions = {
    zoom?: number;
    scale?: number;
    maxCanvasWidth?: number;
    imageType?: 'jpeg' | 'png';
    imageQuality?: number;
};

const DEFAULT_RENDER_SCALE = 2;
const DEFAULT_MAX_CANVAS_WIDTH = 2480; // A4 at about 300 DPI.
const DEFAULT_IMAGE_TYPE: NonNullable<GeneratePdfOptions['imageType']> = 'png';
const DEFAULT_IMAGE_QUALITY = 0.98;

function resolveCanvasScale(element: HTMLElement, options: GeneratePdfOptions) {
    const requestedScale = Math.max(options.scale ?? DEFAULT_RENDER_SCALE, window.devicePixelRatio || 1);
    const maxCanvasWidth = options.maxCanvasWidth ?? DEFAULT_MAX_CANVAS_WIDTH;
    const elementWidth = element.offsetWidth || element.scrollWidth || element.getBoundingClientRect().width;

    if (maxCanvasWidth > 0 && elementWidth > 0 && elementWidth * requestedScale > maxCanvasWidth) {
        return Math.max(1, maxCanvasWidth / elementWidth);
    }

    return requestedScale;
}

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

        const resolvedScale = resolveCanvasScale(element, { ...options, scale });

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

function addCanvasToPdfPage(pdf: jsPDF, canvas: HTMLCanvasElement, options: GeneratePdfOptions = {}) {
    const pdfW = pdf.internal.pageSize.getWidth();
    const pdfH = pdf.internal.pageSize.getHeight();

    let imgW = pdfW;
    let imgH = (canvas.height * imgW) / canvas.width;
    if (imgH > pdfH) {
        imgH = pdfH;
        imgW = (canvas.width * imgH) / canvas.height;
    }

    const marginX = (pdfW - imgW) / 2;
    const imageType = options.imageType ?? DEFAULT_IMAGE_TYPE;
    const imageFormat = imageType === 'png' ? 'PNG' : 'JPEG';
    const imageData =
        imageType === 'png'
            ? canvas.toDataURL('image/png')
            : canvas.toDataURL('image/jpeg', options.imageQuality ?? DEFAULT_IMAGE_QUALITY);

    pdf.addImage(imageData, imageFormat, marginX, 0, imgW, imgH, undefined, imageType === 'png' ? 'SLOW' : 'MEDIUM');
}

export async function generatePdfFromElements(elements: HTMLElement[], filename: string, options: GeneratePdfOptions = {}) {
    const validElements = elements.filter(Boolean);

    if (validElements.length === 0) {
        console.error('Elements for PDF generation not found.');
        return;
    }

    const pdf = new jsPDF({ unit: 'mm', format: 'a4', compress: true });
    let renderedPages = 0;

    for (let index = 0; index < validElements.length; index++) {
        const canvas = await captureElement(validElements[index], options);

        if (!canvas) {
            continue;
        }

        if (renderedPages > 0) {
            pdf.addPage();
        }

        addCanvasToPdfPage(pdf, canvas, options);
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
