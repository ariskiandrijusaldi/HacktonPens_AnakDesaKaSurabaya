import {
    getReport,
    updateStatus,
    updateUrgency,
    assignReport,
    addNote,
    sendOfficerMessage,
    fetchAttachment,
    requestOverride,
    getOfficers,
} from '../api/officer.js';
import { ApiError } from '../api/client.js';
import { formatDateTime, formatBytes } from '../lib/format.js';
import { urgencyInfo, statusInfo, victimWishLabel } from '../lib/enums.js';
import { toastSuccess, toastError } from '../lib/toast.js';
import { openBlobInNewTab, downloadBlob } from '../lib/copy.js';

const root = document.querySelector('[data-report-root]');
const reportId = root ? root.dataset.reportId : '';
let report = null;
let overrideData = null;
let overrideTimer = null;
let lockTimer = null;
let lockUntil = 0;

const els = {
    loading: document.getElementById('detail-loading'),
    error: document.getElementById('detail-error'),
    content: document.getElementById('detail-content'),
};

function el(id) {
    return document.getElementById(id);
}

function showError(message) {
    els.loading.classList.add('hidden');
    els.error.textContent = message;
    els.error.classList.remove('hidden');
}

function buildBadge(info) {
    const span = document.createElement('span');
    span.className = `inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-bold uppercase ${info.badgeClass}`;
    span.textContent = info.label;
    return span;
}

function renderReport(data) {
    report = data;
    els.content.classList.remove('hidden');

    el('d-ticket').textContent = data.ticket_code || '—';
    el('d-summary').textContent = data.summary || '(tanpa ringkasan)';
    el('d-sanitized').textContent = data.sanitized_text || '(isi laporan tidak tersedia)';
    el('d-rationale').textContent = data.urgency_rationale || 'Tidak ada catatan dari triase AI.';
    el('d-category').textContent = data.category || '—';
    el('d-location').textContent = data.location || '—';
    el('d-wish').textContent = victimWishLabel(data.victim_wish);
    el('d-urgency-ai').textContent = urgencyInfo(data.urgency_ai).label;
    el('d-created').textContent = formatDateTime(data.created_at);

    el('d-urgency-final').textContent = '';
    el('d-urgency-final').appendChild(buildBadge(urgencyInfo(data.urgency_final || data.urgency_ai)));
    el('d-status').textContent = '';
    el('d-status').appendChild(buildBadge(statusInfo(data.status)));

    const aiFailed = Boolean(data.ai_failed);
    el('d-aifailed-badge').classList.toggle('hidden', !aiFailed);
    el('d-aifailed-note').classList.toggle('hidden', !aiFailed);

    const locked = Boolean(data.contact_available);
    el('d-contact-locked').classList.toggle('hidden', !locked);
    el('d-contact-none').classList.toggle('hidden', locked);

    el('status-select').value = data.status;
    renderAttachments(data.attachments);
    renderNotes(data.notes);
    renderMessages(data.messages);
}

function renderAttachments(attachments) {
    const host = el('d-attachments');
    const empty = el('d-attachments-empty');
    host.textContent = '';
    const list = Array.isArray(attachments) ? attachments : [];
    empty.classList.toggle('hidden', list.length > 0);

    list.forEach((attachment) => {
        const li = document.createElement('li');
        li.className = 'flex flex-wrap items-center gap-3 rounded-xl border border-ink-200 p-3';

        const info = document.createElement('div');
        info.className = 'min-w-0 flex-1';
        const name = document.createElement('p');
        name.className = 'truncate text-sm font-semibold text-ink-800';
        name.textContent = attachment.filename_random || 'lampiran';
        const meta = document.createElement('p');
        meta.className = 'text-xs text-ink-500';
        meta.textContent = `${attachment.mime || 'berkas'} · ${formatBytes(attachment.size)} · sha256 ${String(attachment.sha256 || '').slice(0, 12)}…`;
        info.append(name, meta);

        const actions = document.createElement('div');
        actions.className = 'flex gap-2';

        const open = document.createElement('button');
        open.type = 'button';
        open.className = 'btn-outline btn-sm';
        open.textContent = 'Lihat';
        open.addEventListener('click', () => handleAttachment(attachment, 'open'));

        const download = document.createElement('button');
        download.type = 'button';
        download.className = 'btn-ghost btn-sm';
        download.textContent = 'Unduh';
        download.addEventListener('click', () => handleAttachment(attachment, 'download'));

        actions.append(open, download);
        li.append(info, actions);
        host.appendChild(li);
    });
}

async function handleAttachment(attachment, mode) {
    try {
        const blob = await fetchAttachment(reportId, attachment.id);
        const filename = attachment.filename_random || 'lampiran';
        if (mode === 'download') {
            downloadBlob(filename, blob);
        } else if (attachment.mime === 'application/pdf' || attachment.mime?.startsWith('image/')) {
            openBlobInNewTab(blob);
        } else {
            downloadBlob(filename, blob);
        }
    } catch (error) {
        toastError(error.detail || 'Gagal mengambil lampiran.');
    }
}

function renderNotes(notes) {
    const host = el('d-notes');
    host.textContent = '';
    const list = Array.isArray(notes) ? notes : [];
    if (!list.length) {
        host.innerHTML = '<li class="text-sm text-ink-500">Belum ada catatan.</li>';
        return;
    }
    list.forEach((note) => {
        const li = document.createElement('li');
        li.className = 'rounded-xl bg-ink-50 p-3';
        const body = document.createElement('p');
        body.className = 'whitespace-pre-wrap break-words text-sm text-ink-700';
        body.textContent = note.note || '';
        const meta = document.createElement('p');
        meta.className = 'mt-1 text-xs text-ink-400';
        meta.textContent = `${note.officer_id || 'petugas'} · ${formatDateTime(note.created_at)}`;
        li.append(body, meta);
        host.appendChild(li);
    });
}

function renderMessages(messages) {
    const host = el('d-messages');
    const empty = el('d-messages-empty');
    host.textContent = '';
    const list = Array.isArray(messages) ? messages : [];
    empty.classList.toggle('hidden', list.length > 0);

    list.forEach((message) => {
        const isSatgas = message.sender === 'SATGAS';
        const li = document.createElement('li');
        li.className = 'flex ' + (isSatgas ? 'justify-end' : 'justify-start');
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[85%] rounded-2xl px-4 py-3 text-sm ' + (isSatgas ? 'bg-brand-700 text-white' : 'bg-ink-100 text-ink-800');
        const sender = document.createElement('p');
        sender.className = 'text-xs font-bold uppercase tracking-wide ' + (isSatgas ? 'text-brand-100' : 'text-ink-500');
        sender.textContent = isSatgas ? 'Satgas' : 'Pelapor';
        const body = document.createElement('p');
        body.className = 'mt-1 whitespace-pre-wrap break-words';
        body.textContent = message.body || '';
        const time = document.createElement('p');
        time.className = 'mt-1 text-[11px] opacity-80';
        time.textContent = formatDateTime(message.created_at);
        bubble.append(sender, body, time);
        li.appendChild(bubble);
        host.appendChild(li);
    });
}

async function loadReport() {
    try {
        const data = await getReport(reportId);
        renderReport(data);
        els.loading.classList.add('hidden');
    } catch (error) {
        if (error instanceof ApiError && error.status === 404) {
            showError('Laporan tidak ditemukan.');
        } else {
            showError(error.detail || 'Gagal memuat laporan.');
        }
    }
}

function openModal(id) {
    const modal = el(`${id}-modal`);
    if (!modal) {
        return;
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const first = modal.querySelector('input, select, textarea, button');
    first?.focus();
}

function closeModal(id) {
    const modal = el(`${id}-modal`);
    if (!modal) {
        return;
    }
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    if (id === 'override') {
        clearOverrideData();
    }
}

function clearOverrideData() {
    overrideData = null;
    const contactEl = el('override-contact');
    const textEl = el('override-text');
    if (contactEl) {
        contactEl.textContent = '';
    }
    if (textEl) {
        textEl.textContent = '';
    }
    el('override-pin').value = '';
    el('override-pin-confirm').value = '';
    el('override-reason').value = '';
    el('override-reason-counter').textContent = '0 / 1.000';
    el('override-error').textContent = '';
    setOverrideStep(1);
    if (overrideTimer) {
        window.clearInterval(overrideTimer);
        overrideTimer = null;
    }
}

function setOverrideStep(step) {
    document.querySelectorAll('[data-override-step]').forEach((section) => {
        section.classList.toggle('hidden', section.dataset.overrideStep !== String(step));
    });
}

function initStatus() {
    el('status-save').addEventListener('click', async () => {
        const status = el('status-select').value;
        try {
            await updateStatus(reportId, status);
            toastSuccess('Status diperbarui.');
            await loadReport();
        } catch (error) {
            toastError(error.detail || 'Gagal memperbarui status.');
        }
    });
}

async function initAssign() {
    const select = el('assign-select');
    try {
        const officers = await getOfficers();
        (officers || []).forEach((officer) => {
            const option = document.createElement('option');
            option.value = officer.id;
            option.textContent = `${officer.username}${officer.role === 'ADMIN' ? ' (Admin)' : ''}`;
            select.appendChild(option);
        });
    } catch {
        /* biarkan kosong selain "Belum ditugaskan" */
    }
    if (report && report.assigned_to) {
        select.value = report.assigned_to;
    }
    el('assign-save').addEventListener('click', async () => {
        const value = select.value;
        try {
            await assignReport(reportId, value || null);
            toastSuccess('Penugasan diperbarui.');
            await loadReport();
        } catch (error) {
            toastError(error.detail || 'Gagal memperbarui penugasan.');
        }
    });
}

function initUrgency() {
    const reason = el('urgency-reason');
    reason.addEventListener('input', () => {
        el('urgency-reason-counter').textContent = `${reason.value.length} / 1.000`;
    });
    el('urgency-open').addEventListener('click', () => {
        if (report) {
            el('urgency-select').value = report.urgency_final || report.urgency_ai || 'KUNING';
        }
        openModal('urgency');
    });
    el('urgency-submit').addEventListener('click', async () => {
        const urgency = el('urgency-select').value;
        const text = reason.value.trim();
        if (text.length < 3) {
            el('urgency-reason-error').textContent = 'Alasan minimal 3 karakter.';
            return;
        }
        if (text.length > 1000) {
            el('urgency-reason-error').textContent = 'Alasan maksimal 1000 karakter.';
            return;
        }
        el('urgency-reason-error').textContent = '';
        try {
            await updateUrgency(reportId, urgency, text);
            toastSuccess('Urgensi diperbarui.');
            closeModal('urgency');
            reason.value = '';
            await loadReport();
        } catch (error) {
            toastError(error.detail || 'Gagal memperbarui urgensi.');
        }
    });
}

function initNotes() {
    const input = el('note-input');
    input.addEventListener('input', () => {
        el('note-counter').textContent = `${input.value.length} / 5.000`;
    });
    el('note-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const note = input.value.trim();
        if (!note) {
            el('note-error').textContent = 'Tulis catatan dulu.';
            return;
        }
        el('note-error').textContent = '';
        try {
            await addNote(reportId, note);
            input.value = '';
            el('note-counter').textContent = '0 / 5.000';
            toastSuccess('Catatan disimpan.');
            await loadReport();
        } catch (error) {
            el('note-error').textContent = error.detail || 'Gagal menyimpan catatan.';
        }
    });
}

function initMessages() {
    const input = el('message-input');
    input.addEventListener('input', () => {
        el('message-counter').textContent = `${input.value.length} / 2.000`;
    });
    el('message-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = input.value.trim();
        if (!body) {
            el('message-error').textContent = 'Tulis pesan dulu.';
            return;
        }
        el('message-error').textContent = '';
        try {
            await sendOfficerMessage(reportId, body);
            input.value = '';
            el('message-counter').textContent = '0 / 2.000';
            toastSuccess('Pesan terkirim ke pelapor.');
            await loadReport();
        } catch (error) {
            el('message-error').textContent = error.detail || 'Gagal mengirim pesan.';
        }
    });
}

function startOverrideCountdown() {
    let remaining = 60;
    const el2 = el('override-countdown');
    el2.textContent = `Jendela ini menutup otomatis dalam ${remaining} detik.`;
    overrideTimer = window.setInterval(() => {
        remaining -= 1;
        if (remaining <= 0) {
            window.clearInterval(overrideTimer);
            overrideTimer = null;
            closeModal('override');
            return;
        }
        el2.textContent = `Jendela ini menutup otomatis dalam ${remaining} detik.`;
    }, 1000);
}

function startLockCountdown() {
    lockUntil = Date.now() + 15 * 60 * 1000;
    el('override-submit').disabled = true;
    const tick = () => {
        const remaining = Math.max(0, lockUntil - Date.now());
        if (remaining <= 0) {
            lockUntil = 0;
            el('override-submit').disabled = false;
            el('override-error').textContent = '';
            window.clearInterval(lockTimer);
            lockTimer = null;
            return;
        }
        const minutes = Math.floor(remaining / 60000);
        const seconds = Math.floor((remaining % 60000) / 1000);
        el('override-error').textContent = `Terkunci karena 3 kali PIN salah. Coba lagi dalam ${minutes}:${String(seconds).padStart(2, '0')}.`;
    };
    tick();
    lockTimer = window.setInterval(tick, 1000);
}

function initOverride() {
    const reason = el('override-reason');
    reason.addEventListener('input', () => {
        el('override-reason-counter').textContent = `${reason.value.length} / 1.000`;
    });

    el('override-open')?.addEventListener('click', () => {
        clearOverrideData();
        openModal('override');
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.dataset.modalClose));
    });
    document.querySelectorAll('[data-override-next]').forEach((button) => {
        button.addEventListener('click', () => {
            const step = button.dataset.overrideNext;
            if (step === '2' && reason.value.trim()) {
                const len = reason.value.trim().length;
                if (len < 20) {
                    el('override-reason-error').textContent = 'Alasan minimal 20 karakter.';
                    return;
                }
            }
            el('override-reason-error').textContent = '';
            setOverrideStep(step);
        });
    });
    document.querySelectorAll('[data-override-back]').forEach((button) => {
        button.addEventListener('click', () => setOverrideStep(button.dataset.overrideBack));
    });

    el('override-close-final').addEventListener('click', () => closeModal('override'));

    el('override-submit').addEventListener('click', async () => {
        const text = reason.value.trim();
        const pin = el('override-pin').value;
        const confirmPin = el('override-pin-confirm').value;
        if (text.length < 20) {
            el('override-error').textContent = 'Alasan minimal 20 karakter.';
            setOverrideStep(2);
            return;
        }
        if (!pin || pin !== confirmPin) {
            el('override-error').textContent = 'PIN dan konfirmasi PIN harus sama.';
            return;
        }
        el('override-error').textContent = '';
        try {
            const result = await requestOverride(reportId, { pin, confirmPin, reason: text });
            overrideData = result;
            el('override-contact').textContent = result.contact || 'Tidak ada kontak tersimpan.';
            el('override-contact-message').classList.toggle('hidden', Boolean(result.contact));
            el('override-contact-message').textContent = !result.contact && result.message ? result.message : '';
            el('override-text').textContent = result.original_text || '(tidak tersedia)';
            setOverrideStep(4);
            startOverrideCountdown();
        } catch (error) {
            if (error instanceof ApiError && error.status === 403) {
                el('override-error').textContent = 'PIN salah.';
            } else if (error instanceof ApiError && error.status === 429) {
                startLockCountdown();
            } else {
                el('override-error').textContent = error.detail || 'Gagal membuka kontak.';
            }
        }
    });
}

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }
    if (!el('override-modal').classList.contains('hidden')) {
        closeModal('override');
    } else if (!el('urgency-modal').classList.contains('hidden')) {
        closeModal('urgency');
    }
});

document.addEventListener('ppks:quick-exit', () => {
    overrideData = null;
});

loadReport().then(() => {
    initStatus();
    initAssign();
    initUrgency();
    initNotes();
    initMessages();
    initOverride();
});
