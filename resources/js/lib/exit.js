export const QUICK_EXIT_URL = import.meta.env.VITE_QUICK_EXIT_URL || 'https://www.google.com';

export function triggerQuickExit() {
    document.dispatchEvent(new CustomEvent('ppks:quick-exit'));
    try {
        window.location.replace(QUICK_EXIT_URL);
    } catch {
        window.location.href = QUICK_EXIT_URL;
    }
}

export function initQuickExit() {
    const buttons = document.querySelectorAll('[data-quick-exit]');
    buttons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            triggerQuickExit();
        });
    });

    let lastEsc = 0;
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }
        const target = event.target;
        const tag = target && target.tagName ? target.tagName.toLowerCase() : '';
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || (target && target.isContentEditable)) {
            return;
        }
        if (document.querySelector('[data-modal-open]')) {
            return;
        }
        const now = Date.now();
        if (now - lastEsc <= 1200) {
            triggerQuickExit();
        } else {
            lastEsc = now;
        }
    });
}
