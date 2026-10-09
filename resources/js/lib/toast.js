let container = null;

function ensureContainer() {
    if (container && document.body.contains(container)) {
        return container;
    }
    container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        container.className =
            'pointer-events-none fixed inset-x-0 bottom-0 z-[70] flex flex-col items-center gap-2 p-4 sm:items-end';
        document.body.appendChild(container);
    }
    return container;
}

const STYLES = {
    success: 'border-brand-600 bg-brand-700 text-white',
    error: 'border-alert-600 bg-alert-600 text-white',
    info: 'border-ink-700 bg-ink-800 text-white',
    warning: 'border-exit-600 bg-exit-500 text-ink-900',
};

export function toast(message, type = 'info', options = {}) {
    const { duration = type === 'error' ? 6000 : 4000 } = options;
    const host = ensureContainer();
    const el = document.createElement('div');
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.className = `pointer-events-auto w-full max-w-sm rounded-xl border-l-4 px-4 py-3 text-sm font-medium shadow-pop ${
        STYLES[type] || STYLES.info
    }`;
    el.style.animation = 'toast-in 180ms ease-out';
    el.textContent = String(message || '');
    host.appendChild(el);

    const remove = () => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 150ms';
        window.setTimeout(() => el.remove(), 160);
    };

    window.setTimeout(remove, duration);
    el.addEventListener('click', remove);
    return remove;
}

export const toastSuccess = (message, options) => toast(message, 'success', options);
export const toastError = (message, options) => toast(message, 'error', options);
export const toastInfo = (message, options) => toast(message, 'info', options);
export const toastWarning = (message, options) => toast(message, 'warning', options);
