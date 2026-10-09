import { listReports, getStats, getOfficers } from '../api/officer.js';
import { ApiError } from '../api/client.js';
import { openOfficerStream } from '../lib/sse.js';
import { formatDateTime, formatWaitingHours } from '../lib/format.js';
import { urgencyInfo, statusInfo, isSlaBreached } from '../lib/enums.js';
import { toastError, toastWarning } from '../lib/toast.js';
import {
    enableAudio,
    playAlarm,
    requestNotificationPermission,
    notify,
    isMuted,
    setMuted,
} from '../lib/alarm.js';
import { getToken } from '../auth.js';

const state = {
    page: 1,
    limit: 20,
    total: 0,
    items: [],
    seen: new Set(),
    polling: null,
    stopStream: null,
    filters: {},
};

const els = {
    form: document.getElementById('filter-form'),
    summary: document.getElementById('list-summary'),
    loading: document.getElementById('list-loading'),
    empty: document.getElementById('list-empty'),
    shell: document.getElementById('list-shell'),
    body: document.getElementById('list-body'),
    template: document.getElementById('row-template'),
    prev: document.getElementById('page-prev'),
    next: document.getElementById('page-next'),
    pageInfo: document.getElementById('page-info'),
    indicator: document.querySelector('[data-connection-indicator]'),
    indicatorDot: document.querySelector('[data-connection-dot]'),
    indicatorLabel: document.querySelector('[data-connection-label]'),
    alarmBanner: document.getElementById('alarm-banner'),
    alarmBannerText: document.querySelector('[data-alarm-banner-text]'),
    alarmDismiss: document.querySelector('[data-alarm-dismiss]'),
    alarmToggle: document.querySelector('[data-alarm-toggle]'),
};

const baseTitle = document.title;
let alarmCount = 0;

function readFilters() {
    const data = new FormData(els.form);
    const filters = {};
    for (const [key, value] of data.entries()) {
        const clean = String(value).trim();
        if (clean) {
            filters[key] = clean;
        }
    }
    return filters;
}

function updateConnection(status) {
    const map = {
        live: { label: 'Live', dot: 'bg-brand-600', text: 'text-brand-700' },
        polling: { label: 'Polling', dot: 'bg-exit-500', text: 'text-exit-600' },
        offline: { label: 'Terputus', dot: 'bg-alert-600', text: 'text-alert-700' },
        connecting: { label: 'Menghubungkan…', dot: 'bg-ink-400', text: 'text-ink-600' },
    };
    const info = map[status] || map.connecting;
    if (!els.indicator) {
        return;
    }
    els.indicator.dataset.state = status;
    els.indicatorDot.className = `h-2.5 w-2.5 rounded-full ${info.dot}`;
    els.indicatorLabel.textContent = info.label;
    els.indicatorLabel.className = `text-xs font-semibold ${info.text}`;
}

function buildUrgencyBadge(urgency) {
    const info = urgencyInfo(urgency);
    const span = document.createElement('span');
    span.className = `inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold uppercase ${info.badgeClass}`;
    const dot = document.createElement('span');
    dot.className = 'h-2 w-2 rounded-full bg-current opacity-80';
    dot.setAttribute('aria-hidden', 'true');
    const text = document.createElement('span');
    text.textContent = info.label;
    span.append(dot, text);
    return span;
}

function buildStatusBadge(status) {
    const info = statusInfo(status);
    const span = document.createElement('span');
    span.className = `inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${info.badgeClass}`;
    span.textContent = info.label;
    return span;
}

function renderRows(items) {
    els.body.textContent = '';
    if (!items.length) {
        els.empty.classList.remove('hidden');
        els.shell.classList.add('hidden');
        return;
    }
    els.empty.classList.add('hidden');
    els.shell.classList.remove('hidden');

    items.forEach((item) => {
        const row = els.template.content.firstElementChild.cloneNode(true);
        const urgency = item.urgency_final || item.urgency_ai || 'HIJAU';
        const info = urgencyInfo(urgency);
        row.className = info.rowClass || '';

        row.querySelector('[data-cell="urgency"]').replaceWith(buildUrgencyBadge(urgency));
        row.querySelector('[data-cell="ticket"]').textContent = item.ticket_code || '—';
        row.querySelector('[data-cell="summary"]').textContent = item.summary || '(tanpa ringkasan)';
        row.querySelector('[data-cell="category"]').textContent = item.category || '—';
        row.querySelector('[data-cell="location"]').textContent = item.location || '—';
        row.querySelector('[data-cell="status"]').replaceWith(buildStatusBadge(item.status));

        const waiting = row.querySelector('[data-cell="waiting"]');
        waiting.textContent = formatWaitingHours(item.waiting_hours);
        if (isSlaBreached(urgency, item.waiting_hours)) {
            waiting.classList.add('text-alert-700');
            waiting.insertAdjacentHTML('afterbegin', '<span aria-hidden="true">⏱</span> ');
        } else {
            waiting.classList.add('text-ink-600');
        }

        const assignee = row.querySelector('[data-cell="assignee"]');
        assignee.textContent = item.assigned_to || 'Belum ditugaskan';

        if (item.ai_failed) {
            row.querySelector('[data-cell="aifailed"]').classList.remove('hidden');
        }

        const link = row.querySelector('[data-cell="link"]');
        link.href = `/dasbor/laporan/${encodeURIComponent(item.id)}`;
        link.setAttribute('aria-label', `Lihat laporan ${item.ticket_code || item.id}`);

        els.body.appendChild(row);
    });
}

function updateSummary() {
    const start = (state.page - 1) * state.limit + 1;
    const end = Math.min(state.page * state.limit, state.total);
    if (state.total === 0) {
        els.summary.textContent = 'Tidak ada laporan.';
    } else {
        els.summary.textContent = `Menampilkan ${start}–${end} dari ${state.total} laporan.`;
    }
    const totalPages = Math.max(1, Math.ceil(state.total / state.limit));
    els.pageInfo.textContent = `Halaman ${state.page} / ${totalPages}`;
    els.prev.disabled = state.page <= 1;
    els.next.disabled = state.page >= totalPages;
}

async function loadReports({ quiet = false } = {}) {
    if (!quiet) {
        els.loading.classList.remove('hidden');
    }
    const params = { ...state.filters, page: state.page, limit: state.limit };
    try {
        const data = await listReports(params);
        state.items = Array.isArray(data.items) ? data.items : [];
        state.total = Number(data.total) || 0;
        state.items.forEach((item) => state.seen.add(String(item.id)));
        renderRows(state.items);
        updateSummary();
        updateConnection(els.indicator?.dataset.state === 'live' ? 'live' : 'polling');
    } catch (error) {
        if (error instanceof ApiError && error.status === 429) {
            toastError('Terlalu banyak permintaan. Menunggu sebentar.');
        } else {
            updateConnection('offline');
            if (!quiet) {
                toastError(error.detail || 'Gagal memuat daftar laporan.');
            }
        }
    } finally {
        els.loading.classList.add('hidden');
    }
}

async function loadStats() {
    try {
        const data = await getStats();
        const byUrgency = {};
        (data.by_urgency || []).forEach((entry) => {
            byUrgency[entry.key] = Number(entry.count) || 0;
        });
        document.querySelector('[data-stat-merah]').textContent = byUrgency.MERAH ?? 0;
        document.querySelector('[data-stat-kuning]').textContent = byUrgency.KUNING ?? 0;
        document.querySelector('[data-stat-hijau]').textContent = byUrgency.HIJAU ?? 0;
        document.querySelector('[data-stat-total]').textContent = data.total_reports ?? 0;
        document.querySelector('[data-stat-aifailed]').textContent = data.ai_failed_count ?? 0;
    } catch {
        ['merah', 'kuning', 'hijau', 'total'].forEach((key) => {
            const el = document.querySelector(`[data-stat-${key}]`);
            if (el) {
                el.textContent = '—';
            }
        });
    }
}

async function loadOfficers() {
    try {
        const officers = await getOfficers();
        const select = document.getElementById('f-assignee');
        if (!select || !Array.isArray(officers)) {
            return;
        }
        officers.forEach((officer) => {
            const option = document.createElement('option');
            option.value = officer.id;
            option.textContent = `${officer.username}${officer.role === 'ADMIN' ? ' (Admin)' : ''}${officer.is_active ? '' : ' — nonaktif'}`;
            select.appendChild(option);
        });
    } catch {
        /* dropdown tetap berisi "Semua petugas" */
    }
}

function setAlertTitle(count) {
    document.title = count > 0 ? `(${count}) DARURAT — ${baseTitle}` : baseTitle;
}

function triggerAlarm(payload) {
    alarmCount += 1;
    setAlertTitle(alarmCount);
    if (els.alarmBanner) {
        els.alarmBanner.classList.remove('hidden');
        if (els.alarmBannerText) {
            els.alarmBannerText.textContent = `Laporan DARURAT baru: ${payload.category || 'tanpa kategori'} (${payload.ticket_code || ''}).`;
        }
    }
    playAlarm();
    notify('Laporan DARURAT baru', `${payload.category || ''} ${payload.ticket_code || ''}`.trim());
}

function handleStreamEvent({ event, data }) {
    if (event === 'ping') {
        return;
    }
    if (event === 'report' && data && typeof data === 'object') {
        const id = String(data.id ?? '');
        if (!id || state.seen.has(id)) {
            return;
        }
        state.seen.add(id);
        if (data.urgency_final === 'MERAH') {
            triggerAlarm(data);
        }
        window.setTimeout(() => loadReports({ quiet: true }), 800);
        window.setTimeout(loadStats, 800);
    }
}

function startPolling() {
    if (state.polling) {
        return;
    }
    updateConnection('polling');
    state.polling = window.setInterval(async () => {
        if (document.visibilityState !== 'visible') {
            return;
        }
        await loadReports({ quiet: true });
        await loadStats();
    }, 5000);
}

function stopPolling() {
    if (state.polling) {
        window.clearInterval(state.polling);
        state.polling = null;
    }
}

function startStream() {
    if (state.stopStream) {
        state.stopStream();
    }
    state.stopStream = openOfficerStream({
        getToken,
        onEvent: handleStreamEvent,
        onStatus: (status) => {
            if (status === 'live') {
                stopPolling();
                updateConnection('live');
            } else {
                startPolling();
            }
        },
        onError: () => {
            startPolling();
        },
    });
}

function initAlarmToggle() {
    if (!els.alarmToggle) {
        return;
    }
    els.alarmToggle.setAttribute('aria-pressed', isMuted() ? 'false' : 'true');
    els.alarmToggle.querySelector('[data-alarm-icon-on]').classList.toggle('hidden', isMuted());
    els.alarmToggle.querySelector('[data-alarm-icon-off]').classList.toggle('hidden', !isMuted());

    els.alarmToggle.addEventListener('click', async () => {
        const nextMuted = !isMuted();
        setMuted(nextMuted);
        els.alarmToggle.setAttribute('aria-pressed', nextMuted ? 'false' : 'true');
        els.alarmToggle.querySelector('[data-alarm-icon-on]').classList.toggle('hidden', nextMuted);
        els.alarmToggle.querySelector('[data-alarm-icon-off]').classList.toggle('hidden', !nextMuted);
        if (!nextMuted) {
            await enableAudio();
            const granted = await requestNotificationPermission();
            if (!granted) {
                toastWarning('Alarm suara aktif. Izin notifikasi tidak diberikan.');
            } else {
                toastWarning('Alarm dan notifikasi aktif.');
            }
        } else {
            toastWarning('Alarm dibisukan.');
        }
    });
}

function debounce(fn, delay) {
    let timer = null;
    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => fn(...args), delay);
    };
}

els.form.addEventListener('submit', (event) => {
    event.preventDefault();
    state.filters = readFilters();
    state.page = 1;
    loadReports();
});

document.getElementById('filter-reset').addEventListener('click', () => {
    els.form.reset();
    state.filters = {};
    state.page = 1;
    loadReports();
});

const debouncedSearch = debounce(() => {
    state.filters = readFilters();
    state.page = 1;
    loadReports();
}, 450);
document.getElementById('f-q').addEventListener('input', debouncedSearch);

els.prev.addEventListener('click', () => {
    if (state.page > 1) {
        state.page -= 1;
        loadReports();
    }
});
els.next.addEventListener('click', () => {
    const totalPages = Math.max(1, Math.ceil(state.total / state.limit));
    if (state.page < totalPages) {
        state.page += 1;
        loadReports();
    }
});

els.alarmDismiss?.addEventListener('click', () => {
    els.alarmBanner.classList.add('hidden');
    alarmCount = 0;
    setAlertTitle(0);
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        loadReports({ quiet: true });
        loadStats();
    }
});

updateConnection('connecting');
const urlQuery = new URLSearchParams(window.location.search);
if (urlQuery.get('q')) {
    document.getElementById('f-q').value = urlQuery.get('q');
}
state.filters = readFilters();
loadOfficers();
loadReports();
loadStats();
initAlarmToggle();
startStream();
