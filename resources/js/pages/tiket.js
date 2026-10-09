import { getTicket, sendTicketMessage } from '../api/public.js';
import { ApiError } from '../api/client.js';
import { formatDateTime } from '../lib/format.js';
import { statusInfo, STATUS_ORDER, MESSAGE_SENDER } from '../lib/enums.js';
import { toastSuccess, toastError } from '../lib/toast.js';

const form = document.getElementById('ticket-form');
const input = document.getElementById('ticket-input');
const errorEl = document.getElementById('ticket-error');
const loadingEl = document.getElementById('ticket-loading');
const resultEl = document.getElementById('ticket-result');
const messagesEl = document.getElementById('messages');
const messagesEmpty = document.getElementById('messages-empty');
const replyForm = document.getElementById('reply-form');
const replyBody = document.getElementById('reply-body');
const replyCounter = document.getElementById('reply-counter');
const replyError = document.getElementById('reply-error');
const submitButton = form?.querySelector('button[type="submit"]');

let currentCode = '';
let pollTimer = null;

const GENERIC_NOT_FOUND = 'Kode tidak ditemukan. Periksa kembali penulisannya.';

function normalizeCode(value) {
    return String(value || '').toUpperCase().replace(/[^A-Z0-9-]/g, '').trim();
}

function setLoading(loading) {
    loadingEl?.classList.toggle('hidden', !loading);
    if (submitButton) {
        submitButton.disabled = loading;
        submitButton.querySelector('[data-ticket-label]').textContent = loading ? 'Memeriksa…' : 'Cek Status';
        submitButton.querySelector('[data-ticket-spinner]').classList.toggle('hidden', !loading);
    }
}

function renderStatus(status) {
    const info = statusInfo(status);
    const step = info.step || 1;
    document.getElementById('result-status').textContent = info.label;
    const stepper = resultEl.querySelector('ol');
    if (stepper) {
        stepper.querySelectorAll('li').forEach((li, index) => {
            const number = index + 1;
            const badge = li.querySelector('span');
            badge.className =
                'grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-bold ' +
                (number < step
                    ? 'bg-brand-700 text-white'
                    : number === step
                      ? 'bg-brand-100 text-brand-800 ring-2 ring-brand-600'
                      : 'bg-ink-100 text-ink-400');
        });
    }
}

function renderMessages(messages) {
    if (!messagesEl) {
        return;
    }
    messagesEl.textContent = '';
    const list = Array.isArray(messages) ? messages : [];
    messagesEmpty?.classList.toggle('hidden', list.length > 0);

    list.forEach((message) => {
        const sender = message.sender === 'PELAPOR' ? 'Kamu' : 'Satgas';
        const isSatgas = message.sender === 'SATGAS';
        const li = document.createElement('li');
        li.className = 'flex ' + (isSatgas ? 'justify-start' : 'justify-end');

        const bubble = document.createElement('div');
        bubble.className =
            'max-w-[85%] rounded-2xl px-4 py-3 text-sm ' +
            (isSatgas ? 'bg-brand-50 text-ink-800' : 'bg-brand-700 text-white');

        const senderEl = document.createElement('p');
        senderEl.className = 'text-xs font-bold uppercase tracking-wide ' + (isSatgas ? 'text-brand-700' : 'text-brand-100');
        senderEl.textContent = sender;

        const bodyEl = document.createElement('p');
        bodyEl.className = 'mt-1 whitespace-pre-wrap break-words';
        bodyEl.textContent = String(message.body || '');

        const timeEl = document.createElement('p');
        timeEl.className = 'mt-1 text-[11px] ' + (isSatgas ? 'text-ink-400' : 'text-brand-100');
        timeEl.textContent = formatDateTime(message.created_at);

        bubble.append(senderEl, bodyEl, timeEl);
        li.appendChild(bubble);
        messagesEl.appendChild(li);
    });
}

function applyTicket(data) {
    document.getElementById('result-code').textContent = data.ticket_code || currentCode;
    document.getElementById('result-updated').textContent = `Diperbarui ${formatDateTime(data.updated_at)}`;
    renderStatus(data.status);
    renderMessages(data.messages);
    resultEl.classList.remove('hidden');
}

async function loadTicket({ silent = false } = {}) {
    if (!currentCode) {
        return;
    }
    if (!silent) {
        setLoading(true);
    }
    try {
        const data = await getTicket(currentCode);
        applyTicket(data);
        errorEl.textContent = '';
    } catch (error) {
        if (error instanceof ApiError && error.status === 429) {
            errorEl.textContent = 'Terlalu banyak percobaan, coba lagi sebentar.';
        } else if (error instanceof ApiError && (error.status === 404 || error.status === 422 || error.status === 400)) {
            errorEl.textContent = GENERIC_NOT_FOUND;
            resultEl.classList.add('hidden');
        } else if (!silent) {
            errorEl.textContent = error.detail || 'Tidak dapat memuat tiket. Coba lagi sebentar.';
        }
    } finally {
        if (!silent) {
            setLoading(false);
        }
    }
}

function startPolling() {
    stopPolling();
    pollTimer = window.setInterval(() => {
        if (document.visibilityState === 'visible' && currentCode) {
            loadTicket({ silent: true });
        }
    }, 15000);
}

function stopPolling() {
    if (pollTimer) {
        window.clearInterval(pollTimer);
        pollTimer = null;
    }
}

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorEl.textContent = '';
        const code = normalizeCode(input.value);
        if (!code) {
            errorEl.textContent = 'Masukkan kode tiket dulu ya.';
            input.focus();
            return;
        }
        input.value = code;
        currentCode = code;
        await loadTicket();
        if (!resultEl.classList.contains('hidden')) {
            startPolling();
        }
    });
}

if (input) {
    input.addEventListener('input', () => {
        const caret = input.selectionStart;
        input.value = normalizeCode(input.value);
        input.setSelectionRange(caret, caret);
    });
}

if (replyForm) {
    replyBody.addEventListener('input', () => {
        replyCounter.textContent = `${replyBody.value.length} / 2.000`;
    });

    replyForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        replyError.textContent = '';
        const body = replyBody.value.trim();
        if (!body) {
            replyError.textContent = 'Tulis balasan dulu ya.';
            return;
        }
        if (body.length > 2000) {
            replyError.textContent = 'Balasan maksimal 2000 karakter.';
            return;
        }
        try {
            await sendTicketMessage(currentCode, body);
            replyBody.value = '';
            replyCounter.textContent = '0 / 2.000';
            toastSuccess('Balasan terkirim.');
            await loadTicket({ silent: true });
        } catch (error) {
            if (error instanceof ApiError && error.status === 429) {
                replyError.textContent = 'Terlalu banyak percobaan, coba lagi sebentar.';
            } else {
                replyError.textContent = error.detail || 'Gagal mengirim balasan. Coba lagi.';
            }
        }
    });
}

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && currentCode) {
        loadTicket({ silent: true });
    }
});

document.addEventListener('ppks:quick-exit', () => {
    currentCode = '';
    if (form) {
        form.reset();
    }
    stopPolling();
});

window.addEventListener('beforeunload', stopPolling);
