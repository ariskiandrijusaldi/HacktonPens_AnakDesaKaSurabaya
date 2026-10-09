const DATE_TIME = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const DATE_ONLY = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
});

function toDate(value) {
    if (!value) {
        return null;
    }
    if (value instanceof Date) {
        return value;
    }
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

export function formatDateTime(value) {
    const date = toDate(value);
    return date ? DATE_TIME.format(date) : '—';
}

export function formatDate(value) {
    const date = toDate(value);
    return date ? DATE_ONLY.format(date) : '—';
}

export function formatRelative(value) {
    const date = toDate(value);
    if (!date) {
        return '—';
    }
    const diffMs = Date.now() - date.getTime();
    const minutes = Math.round(diffMs / 60000);
    if (Math.abs(minutes) < 1) {
        return 'baru saja';
    }
    const rtf = new Intl.RelativeTimeFormat('id-ID', { numeric: 'auto' });
    if (Math.abs(minutes) < 60) {
        return rtf.format(-minutes, 'minute');
    }
    const hours = Math.round(minutes / 60);
    if (Math.abs(hours) < 24) {
        return rtf.format(-hours, 'hour');
    }
    const days = Math.round(hours / 24);
    if (Math.abs(days) < 30) {
        return rtf.format(-days, 'day');
    }
    return formatDate(date);
}

export function formatWaitingHours(hours) {
    const value = Number(hours);
    if (!Number.isFinite(value)) {
        return '—';
    }
    if (value < 1) {
        const minutes = Math.max(1, Math.round(value * 60));
        return `${minutes} menit`;
    }
    if (value < 24) {
        return `${value.toFixed(value < 10 ? 1 : 0)} jam`;
    }
    const days = value / 24;
    return `${days.toFixed(1)} hari`;
}

export function formatBytes(bytes) {
    const value = Number(bytes);
    if (!Number.isFinite(value) || value <= 0) {
        return '0 B';
    }
    const units = ['B', 'KB', 'MB', 'GB'];
    const index = Math.min(units.length - 1, Math.floor(Math.log(value) / Math.log(1024)));
    const size = value / 1024 ** index;
    return `${size.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
}

export function maskTicket(code) {
    const value = String(code || '').trim();
    if (value.length <= 6) {
        return value;
    }
    return `${value.slice(0, 6)}…${value.slice(-4)}`;
}

export function shortHash(hash) {
    const value = String(hash || '');
    if (value.length <= 10) {
        return value;
    }
    return value.slice(0, 10);
}
