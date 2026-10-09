import { getPatterns } from '../api/officer.js';
import { shortHash } from '../lib/format.js';

const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

function labelFor(index) {
    if (index < 26) {
        return `Terlapor ${ALPHABET[index]}`;
    }
    return `Terlapor ${index + 1}`;
}

function render(groups) {
    const list = document.getElementById('patterns-list');
    const empty = document.getElementById('patterns-empty');
    const template = document.getElementById('pattern-template');
    list.textContent = '';

    if (!groups.length) {
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    groups.forEach((group, index) => {
        const node = template.content.firstElementChild.cloneNode(true);
        node.querySelector('[data-pattern-label]').textContent = labelFor(index);
        node.querySelector('[data-pattern-count]').textContent = String(group.report_count ?? 0);
        node.querySelector('[data-pattern-hash]').textContent = `hash: ${shortHash(group.perpetrator_hash)}`;

        const categories = node.querySelector('[data-pattern-categories]');
        (group.categories || []).forEach((category) => {
            const span = document.createElement('span');
            span.className = 'rounded-full bg-ink-100 px-2.5 py-1 text-xs font-semibold text-ink-700';
            span.textContent = category;
            categories.appendChild(span);
        });
        if (!(group.categories || []).length) {
            categories.textContent = '—';
        }

        const tickets = node.querySelector('[data-pattern-tickets]');
        (group.ticket_codes || []).forEach((code) => {
            const chip = document.createElement('span');
            chip.className = 'rounded-full border border-ink-200 px-2.5 py-1 font-mono text-xs text-ink-700';
            chip.textContent = code;
            tickets.appendChild(chip);
        });
        if (!(group.ticket_codes || []).length) {
            tickets.textContent = '—';
        }

        list.appendChild(node);
    });
}

async function load() {
    const loading = document.getElementById('patterns-loading');
    const error = document.getElementById('patterns-error');
    loading.classList.remove('hidden');
    error.classList.add('hidden');
    try {
        const data = await getPatterns();
        loading.classList.add('hidden');
        render(Array.isArray(data) ? data : []);
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.textContent = err.detail || 'Gagal memuat data pola. Coba lagi.';
    }
}

document.getElementById('patterns-refresh').addEventListener('click', load);
load();
