export const URGENCY = {
    MERAH: {
        key: 'MERAH',
        label: 'Merah',
        short: 'Darurat',
        description: 'Perlu penanganan segera',
        badgeClass: 'bg-alert-600 text-white border-alert-700',
        dotClass: 'bg-alert-600',
        rowClass: 'bg-alert-50/70 border-l-4 border-l-alert-600',
        textClass: 'text-alert-700',
        icon: 'alert',
    },
    KUNING: {
        key: 'KUNING',
        label: 'Kuning',
        short: 'Perhatian',
        description: 'Perlu perhatian',
        badgeClass: 'bg-exit-500 text-ink-900 border-exit-600',
        dotClass: 'bg-exit-500',
        rowClass: 'bg-exit-500/10 border-l-4 border-l-exit-500',
        textClass: 'text-exit-600',
        icon: 'warning',
    },
    HIJAU: {
        key: 'HIJAU',
        label: 'Hijau',
        short: 'Aman',
        description: 'Dapat dijadwalkan',
        badgeClass: 'bg-brand-700 text-white border-brand-800',
        dotClass: 'bg-brand-700',
        rowClass: 'border-l-4 border-l-brand-600',
        textClass: 'text-brand-700',
        icon: 'check',
    },
};

export const URGENCY_ORDER = ['MERAH', 'KUNING', 'HIJAU'];

export const STATUS = {
    MENUNGGU_TIM: {
        key: 'MENUNGGU_TIM',
        label: 'Menunggu Tim',
        badgeClass: 'bg-ink-100 text-ink-700 border-ink-300',
        step: 1,
    },
    SEDANG_DIDAMPINGI: {
        key: 'SEDANG_DIDAMPINGI',
        label: 'Sedang Didampingi',
        badgeClass: 'bg-brand-100 text-brand-800 border-brand-500',
        step: 2,
    },
    SELESAI: {
        key: 'SELESAI',
        label: 'Selesai',
        badgeClass: 'bg-brand-700 text-white border-brand-800',
        step: 3,
    },
};

export const STATUS_ORDER = ['MENUNGGU_TIM', 'SEDANG_DIDAMPINGI', 'SELESAI'];

export const VICTIM_WISH = {
    KONSULTASI: 'Hanya ingin konsultasi',
    PENDAMPINGAN: 'Ingin didampingi',
    PROSES_FORMAL: 'Ingin proses formal',
    BELUM_TAHU: 'Belum tahu',
};

export const VICTIM_WISH_ORDER = ['KONSULTASI', 'PENDAMPINGAN', 'PROSES_FORMAL', 'BELUM_TAHU'];

export const MESSAGE_SENDER = {
    SATGAS: 'Satgas',
    PELAPOR: 'Pelapor',
};

export function urgencyInfo(key) {
    return (
        URGENCY[key] || {
            key: key || 'TIDAK_DIKETAHUI',
            label: key || 'Tidak diketahui',
            short: key || '-',
            description: '',
            badgeClass: 'bg-ink-100 text-ink-700 border-ink-300',
            dotClass: 'bg-ink-400',
            rowClass: '',
            textClass: 'text-ink-700',
            icon: 'info',
        }
    );
}

export function statusInfo(key) {
    return (
        STATUS[key] || {
            key: key || 'TIDAK_DIKETAHUI',
            label: key || 'Tidak diketahui',
            badgeClass: 'bg-ink-100 text-ink-700 border-ink-300',
            step: 0,
        }
    );
}

export function victimWishLabel(key) {
    return VICTIM_WISH[key] || key || '—';
}

export const SLA_HOURS = {
    MERAH: 1,
    KUNING: 24,
};

export function isSlaBreached(urgency, waitingHours) {
    const threshold = SLA_HOURS[urgency];
    if (!threshold || !Number.isFinite(Number(waitingHours))) {
        return false;
    }
    return Number(waitingHours) > threshold;
}
