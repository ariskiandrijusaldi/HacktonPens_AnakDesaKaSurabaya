import { getStats } from '../api/officer.js';
import { statusInfo, urgencyInfo, URGENCY_ORDER, STATUS_ORDER } from '../lib/enums.js';

const URGENCY_COLORS = {
    MERAH: '#dc2626',
    KUNING: '#f59e0b',
    HIJAU: '#0f766e',
};

const STATUS_COLORS = {
    MENUNGGU_TIM: '#9ca3af',
    SEDANG_DIDAMPINGI: '#14b8a6',
    SELESAI: '#0f766e',
};

function renderDonut(byUrgency, total) {
    const svg = document.querySelector('[data-urgency-donut]');
    const legend = document.querySelector('[data-urgency-legend]');
    if (!svg || !legend) {
        return;
    }
    svg.innerHTML = '<circle cx="21" cy="21" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="6"></circle>';
    legend.textContent = '';

    const counts = {};
    byUrgency.forEach((entry) => {
        counts[entry.key] = Number(entry.count) || 0;
    });
    const sum = URGENCY_ORDER.reduce((acc, key) => acc + (counts[key] || 0), 0) || total || 0;
    const circumference = 2 * Math.PI * 15.9;
    let offset = 0;

    URGENCY_ORDER.forEach((key) => {
        const count = counts[key] || 0;
        const fraction = sum > 0 ? count / sum : 0;
        if (fraction > 0) {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', '21');
            circle.setAttribute('cy', '21');
            circle.setAttribute('r', '15.9');
            circle.setAttribute('fill', 'none');
            circle.setAttribute('stroke', URGENCY_COLORS[key]);
            circle.setAttribute('stroke-width', '6');
            circle.setAttribute('stroke-dasharray', `${(fraction * circumference).toFixed(2)} ${circumference.toFixed(2)}`);
            circle.setAttribute('stroke-dashoffset', `${(-offset * circumference).toFixed(2)}`);
            svg.appendChild(circle);
            offset += fraction;
        }
        const li = document.createElement('li');
        li.className = 'flex items-center gap-2';
        const dot = document.createElement('span');
        dot.className = 'h-3 w-3 rounded-full';
        dot.style.backgroundColor = URGENCY_COLORS[key];
        const label = document.createElement('span');
        label.className = 'font-semibold text-ink-700';
        label.textContent = urgencyInfo(key).label;
        const value = document.createElement('span');
        value.className = 'ml-auto text-ink-500';
        value.textContent = `${count} (${sum ? Math.round(fraction * 100) : 0}%)`;
        li.append(dot, label, value);
        legend.appendChild(li);
    });
}

function renderBars(hostSelector, entries, labelFn, colorFn, maxFallback) {
    const host = document.querySelector(hostSelector);
    if (!host) {
        return;
    }
    host.textContent = '';
    if (!entries.length) {
        host.innerHTML = '<li class="text-sm text-ink-500">Belum ada data.</li>';
        return;
    }
    const max = Math.max(...entries.map((entry) => Number(entry.count) || 0), maxFallback || 1);
    entries.forEach((entry) => {
        const li = document.createElement('li');
        const head = document.createElement('div');
        head.className = 'flex items-center justify-between text-sm';
        const label = document.createElement('span');
        label.className = 'font-semibold text-ink-700';
        label.textContent = labelFn(entry);
        const value = document.createElement('span');
        value.className = 'font-bold text-ink-900';
        value.textContent = String(entry.count);
        head.append(label, value);

        const track = document.createElement('div');
        track.className = 'mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-ink-100';
        const bar = document.createElement('div');
        bar.className = 'h-full rounded-full';
        bar.style.width = `${Math.max(4, (Number(entry.count) / max) * 100)}%`;
        bar.style.backgroundColor = colorFn(entry);
        track.appendChild(bar);

        li.append(head, track);
        host.appendChild(li);
    });
}

async function load() {
    const loading = document.getElementById('stats-loading');
    const error = document.getElementById('stats-error');
    const content = document.getElementById('stats-content');
    loading.classList.remove('hidden');
    error.classList.add('hidden');
    content.classList.add('hidden');

    try {
        const data = await getStats();
        loading.classList.add('hidden');
        content.classList.remove('hidden');

        document.querySelector('[data-stat="total"]').textContent = data.total_reports ?? 0;
        const avg = Number(data.average_first_response_hours);
        document.querySelector('[data-stat="response"]').textContent = Number.isFinite(avg) && avg > 0 ? avg.toFixed(1) : '—';
        document.querySelector('[data-stat="aifailed"]').textContent = data.ai_failed_count ?? 0;

        const byStatus = {};
        (data.by_status || []).forEach((entry) => {
            byStatus[entry.key] = Number(entry.count) || 0;
        });
        document.querySelector('[data-stat="done"]').textContent = byStatus.SELESAI ?? 0;

        renderDonut(data.by_urgency || [], data.total_reports);
        renderBars(
            '[data-status-bars]',
            STATUS_ORDER.map((key) => ({ key, count: byStatus[key] || 0 })),
            (entry) => statusInfo(entry.key).label,
            (entry) => STATUS_COLORS[entry.key],
            1,
        );
        renderBars(
            '[data-category-bars]',
            (data.by_category || []).slice(0, 10),
            (entry) => entry.category || 'Tanpa kategori',
            () => '#0d9488',
            1,
        );
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.textContent = err.detail || 'Gagal memuat statistik. Coba lagi.';
    }
}

document.getElementById('stats-refresh').addEventListener('click', load);
load();
