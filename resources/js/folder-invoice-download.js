const LOADING_HTML =
    '<span class="inline-flex items-center justify-center gap-1" aria-hidden="true"><span class="loading-dot"></span><span class="loading-dot"></span><span class="loading-dot"></span></span>';

function parseFilename(contentDisposition, fallback = 'invoice.pdf') {
    if (!contentDisposition) {
        return fallback;
    }

    const utf8Match = contentDisposition.match(/filename\*=UTF-8''([^;]+)/i);
    if (utf8Match?.[1]) {
        try {
            return decodeURIComponent(utf8Match[1]);
        } catch {
            return utf8Match[1];
        }
    }

    const quotedMatch = contentDisposition.match(/filename="([^"]+)"/i);
    if (quotedMatch?.[1]) {
        return quotedMatch[1];
    }

    const plainMatch = contentDisposition.match(/filename=([^;]+)/i);
    if (plainMatch?.[1]) {
        return plainMatch[1].trim();
    }

    return fallback;
}

function setLinkLoading(link, isLoading) {
    if (!(link instanceof HTMLAnchorElement)) {
        return false;
    }

    if (isLoading) {
        if (link.dataset.loading === '1') {
            return false;
        }

        link.dataset.loading = '1';
        link.dataset.originalHtml = link.innerHTML;
        link.setAttribute('aria-busy', 'true');
        link.classList.add('pointer-events-none', 'opacity-60');
        link.innerHTML = LOADING_HTML;

        return true;
    }

    if (link.dataset.originalHtml) {
        link.innerHTML = link.dataset.originalHtml;
    }

    delete link.dataset.originalHtml;
    delete link.dataset.loading;
    link.removeAttribute('aria-busy');
    link.classList.remove('pointer-events-none', 'opacity-60');

    return true;
}

async function downloadInvoice(link) {
    const url = link.getAttribute('href');
    if (!url) {
        return;
    }

    if (!setLinkLoading(link, true)) {
        return;
    }

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/pdf',
            },
        });

        if (!response.ok) {
            throw new Error('Could not download invoice. Please try again.');
        }

        const blob = await response.blob();
        const filename = parseFilename(response.headers.get('Content-Disposition'));
        const objectUrl = URL.createObjectURL(blob);
        const downloadLink = document.createElement('a');

        downloadLink.href = objectUrl;
        downloadLink.download = filename;
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        URL.revokeObjectURL(objectUrl);
    } catch (error) {
        const message = error instanceof Error ? error.message : 'Could not download invoice.';

        if (window.Swal) {
            window.Swal.fire({
                icon: 'error',
                text: message,
                confirmButtonColor: '#10253f',
            });
        } else {
            window.alert(message);
        }
    } finally {
        setLinkLoading(link, false);
    }
}

function initFolderInvoiceDownload() {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-invoice-download]');
        if (!(link instanceof HTMLAnchorElement)) {
            return;
        }

        event.preventDefault();
        void downloadInvoice(link);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFolderInvoiceDownload);
} else {
    initFolderInvoiceDownload();
}
