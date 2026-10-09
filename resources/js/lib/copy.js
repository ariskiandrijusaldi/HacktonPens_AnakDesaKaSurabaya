export async function copyText(text) {
    const value = String(text ?? '');
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(value);
            return true;
        } catch {
            /* jatuh ke cara lama */
        }
    }
    const helper = document.createElement('textarea');
    helper.value = value;
    helper.setAttribute('readonly', '');
    helper.style.position = 'fixed';
    helper.style.opacity = '0';
    document.body.appendChild(helper);
    helper.select();
    let ok = false;
    try {
        ok = document.execCommand('copy');
    } catch {
        ok = false;
    }
    helper.remove();
    return ok;
}

export function downloadText(filename, text) {
    const blob = new Blob([String(text ?? '')], { type: 'text/plain;charset=utf-8' });
    downloadBlob(filename, blob);
}

export function downloadBlob(filename, blob) {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.rel = 'noreferrer noopener';
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
}

export function openBlobInNewTab(blob) {
    const url = URL.createObjectURL(blob);
    window.open(url, '_blank', 'noopener,noreferrer');
    window.setTimeout(() => URL.revokeObjectURL(url), 60000);
}
