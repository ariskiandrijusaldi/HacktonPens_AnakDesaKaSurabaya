import { getAuditLogs } from '../api/officer.js';
import { formatDateTime } from '../lib/format.js';

let logs = [];

function fillActions() {
    const select = document.getElementById('audit-action');
    const actions = [...new Set(logs.map((log) => log.action).filter(Boolean))].sort();
    const current = select.value;
    select.textContent = '';
    const all = document.createElement('option');
    all.value = '';
    all.textContent = 'Semua aksi';
    select.appendChild(all);
    actions.forEach((action) => {
        const option = document.createElement('option');
        option.value = action;
        option.textContent = action;
        select.appendChild(option);
    });
    select.value = actions.includes(current) ? current : '';
}

function filteredLogs() {
    const action = document.getElementById('audit-action').value;
    const from = document.getElementById('audit-from').value;
    const to = document.getElementById('audit-to').value;
    return logs.filter((log) => {
        if (action && log.action !== action) {
            return false;
        }
        const created = log.created_at ? new Date(log.created_at) : null;
        if (from && created && created < new Date(`${from}T00:00:00`)) {
            return false;
        }
        if (to && created && created > new Date(`${to}T23:59:59`)) {
            return false;
        }
        return true;
    });
}

function render() {
    const body = document.getElementById('audit-body');
    const shell = document.getElementById('audit-shell');
    const empty = document.getElementById('audit-empty');
    body.textContent = '';

    const rows = filteredLogs();
    if (!rows.length) {
        shell.classList.add('hidden');
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');
    shell.classList.remove('hidden');

    rows.forEach((log) => {
        const tr = document.createElement('tr');
        const cells = [
            formatDateTime(log.created_at),
            log.action || '—',
            log.officer_id || '—',
            log.report_id || '—',
            log.detail || '—',
        ];
        cells.forEach((value, index) => {
            const td = document.createElement('td');
            if (index === 4) {
                td.className = 'max-w-md whitespace-pre-wrap break-words text-ink-600';
            }
            td.textContent = String(value);
            tr.appendChild(td);
        });
        body.appendChild(tr);
    });
}

async function load() {
    const loading = document.getElementById('audit-loading');
    const error = document.getElementById('audit-error');
    loading.classList.remove('hidden');
    error.classList.add('hidden');
    try {
        const data = await getAuditLogs(200);
        logs = Array.isArray(data) ? data : [];
        loading.classList.add('hidden');
        fillActions();
        render();
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.textContent = err.detail || 'Gagal memuat audit log. Pastikan kamu Admin.';
    }
}

['audit-action', 'audit-from', 'audit-to'].forEach((id) => {
    document.getElementById(id).addEventListener('change', render);
});
document.getElementById('audit-reset').addEventListener('click', () => {
    document.getElementById('audit-action').value = '';
    document.getElementById('audit-from').value = '';
    document.getElementById('audit-to').value = '';
    render();
});
document.getElementById('audit-refresh').addEventListener('click', load);
load();
