import { initQuickExit } from './lib/exit.js';
import { setUnauthorizedHandler } from './api/client.js';
import { clearSession, isAdmin, logout, requireOfficer, getUsername, getRole, watchSession } from './auth.js';
import { toastWarning } from './lib/toast.js';

function initDropdowns() {
    const dropdowns = Array.from(document.querySelectorAll('[data-dropdown]'));
    if (!dropdowns.length) {
        return;
    }

    const closeAll = (except) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown === except) {
                return;
            }
            const button = dropdown.querySelector('[data-dropdown-toggle]');
            const panel = dropdown.querySelector('[data-dropdown-panel]');
            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
            if (panel) {
                panel.classList.add('hidden');
            }
        });
    };

    dropdowns.forEach((dropdown) => {
        const button = dropdown.querySelector('[data-dropdown-toggle]');
        const panel = dropdown.querySelector('[data-dropdown-panel]');
        if (!button || !panel) {
            return;
        }

        const toggle = (open) => {
            const next = open ?? button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', next ? 'true' : 'false');
            panel.classList.toggle('hidden', !next);
            if (next) {
                closeAll(dropdown);
            }
        };

        button.addEventListener('click', (event) => {
            event.preventDefault();
            toggle();
        });

        dropdown.addEventListener('mouseenter', () => {
            if (window.matchMedia('(min-width: 1024px)').matches) {
                toggle(true);
            }
        });
        dropdown.addEventListener('mouseleave', () => {
            if (window.matchMedia('(min-width: 1024px)').matches) {
                toggle(false);
            }
        });

        dropdown.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                toggle(false);
                button.focus();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!dropdowns.some((dropdown) => dropdown.contains(event.target))) {
            closeAll();
        }
    });
}

function initMobileMenu() {
    const button = document.querySelector('[data-mobile-toggle]');
    const panel = document.querySelector('[data-mobile-panel]');
    if (!button || !panel) {
        return;
    }
    button.addEventListener('click', () => {
        const open = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', open ? 'false' : 'true');
        panel.classList.toggle('hidden', open);
    });
}

function initYear() {
    document.querySelectorAll('[data-year]').forEach((el) => {
        el.textContent = String(new Date().getFullYear());
    });
}

function initOfficerHeader() {
    if (document.body.dataset.guard !== 'officer') {
        return;
    }

    const username = getUsername();
    document.querySelectorAll('[data-officer-name]').forEach((el) => {
        el.textContent = username || 'Petugas';
    });
    document.querySelectorAll('[data-officer-initial]').forEach((el) => {
        el.textContent = (username || 'P').charAt(0).toUpperCase();
    });
    document.querySelectorAll('[data-role-label]').forEach((el) => {
        el.textContent = getRole() || 'ANGGOTA';
    });

    if (!isAdmin()) {
        document.querySelectorAll('[data-admin-only]').forEach((el) => {
            el.remove();
        });
    }

    document.querySelectorAll('[data-logout]').forEach((button) => {
        button.addEventListener('click', () => {
            clearSession();
            logout();
        });
    });
}

export function bootstrap() {
    initQuickExit();
    initDropdowns();
    initMobileMenu();
    initYear();
    initOfficerHeader();

    setUnauthorizedHandler(() => {
        clearSession();
        const path = window.location.pathname;
        if (path.startsWith('/masuk')) {
            return;
        }
        const target = `/masuk?pesan=${encodeURIComponent('Sesi berakhir. Silakan masuk kembali.')}`;
        window.location.replace(target);
    });

    const guard = document.body.dataset.guard;
    if (guard === 'officer') {
        if (requireOfficer()) {
            watchSession({
                onWarning: () => {
                    toastWarning('Sesi akan berakhir dalam 2 menit. Simpan pekerjaanmu.');
                },
            });
        }
    }
}

bootstrap();
