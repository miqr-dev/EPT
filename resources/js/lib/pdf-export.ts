export async function downloadPdfOrOpenPrint(pdfUrl: string, printUrl: string, filename: string) {
    const response = await fetch(pdfUrl, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/pdf',
        },
    });

    const contentType = response.headers.get('Content-Type') || '';

    if (!response.ok || !contentType.includes('application/pdf')) {
        const fallbackUrl = response.headers.get('X-Pdf-Print-Url') || printUrl;
        openPrintPreview(fallbackUrl);
        return;
    }

    const blob = await response.blob();
    const objectUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = objectUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();

    window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
}

export function openPrintPreview(url: string) {
    const opened = window.open(url, '_blank');

    if (!opened) {
        window.location.assign(url);
    }
}
