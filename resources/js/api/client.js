const RAW_BASE = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8001';

export const API_BASE_URL = String(RAW_BASE).replace(/\/+$/, '');

export class ApiError extends Error {
    constructor(status, detail, payload) {
        super(detail || `Kesalahan ${status}`);
        this.name = 'ApiError';
        this.status = status;
        this.detail = detail;
        this.payload = payload;
    }
}

const STATUS_MESSAGES = {
    400: 'Permintaan tidak dapat diproses. Periksa kembali isianmu.',
    401: 'Sesi berakhir. Silakan masuk kembali.',
    403: 'Kamu tidak memiliki akses untuk tindakan ini.',
    404: 'Kode tidak ditemukan. Periksa kembali penulisannya.',
    409: 'Ada perubahan lain. Muat ulang halaman lalu coba lagi.',
    413: 'Berkas terlalu besar.',
    422: 'Ada isian yang belum sesuai. Periksa kembali.',
    429: 'Terlalu banyak percobaan, coba lagi sebentar.',
    500: 'Terjadi gangguan di server. Coba lagi sebentar.',
    502: 'Layanan sedang tidak tersedia. Coba lagi sebentar.',
    503: 'Layanan sedang tidak tersedia. Coba lagi sebentar.',
    504: 'Layanan terlalu lama merespons. Coba lagi sebentar.',
};

function friendlyDetail(status, payload) {
    const detail = payload && payload.detail;
    if (typeof detail === 'string' && detail.trim()) {
        return detail.trim();
    }
    if (Array.isArray(detail) && detail.length) {
        const first = detail[0];
        if (typeof first === 'string') {
            return first;
        }
        if (first && typeof first.msg === 'string') {
            return first.msg;
        }
    }
    return STATUS_MESSAGES[status] || 'Terjadi kesalahan yang tidak terduga.';
}

let unauthorizedHandler = null;

export function setUnauthorizedHandler(fn) {
    unauthorizedHandler = fn;
}

async function readPayload(response) {
    const contentType = response.headers.get('content-type') || '';
    if (contentType.includes('application/json')) {
        try {
            return await response.json();
        } catch {
            return null;
        }
    }
    try {
        const text = await response.text();
        return text ? { detail: text } : null;
    } catch {
        return null;
    }
}

export async function apiRequest(path, options = {}) {
    const {
        method = 'GET',
        body,
        formData,
        auth = true,
        getToken,
        signal,
        parse = 'json',
    } = options;

    const headers = {};
    if (auth && typeof getToken === 'function') {
        const token = getToken();
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }
    }

    let payloadBody;
    if (formData instanceof FormData) {
        payloadBody = formData;
    } else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        payloadBody = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(`${API_BASE_URL}${path}`, {
            method,
            headers,
            body: payloadBody,
            signal,
            credentials: 'omit',
            referrerPolicy: 'no-referrer',
        });
    } catch (error) {
        if (error && error.name === 'AbortError') {
            throw error;
        }
        throw new ApiError(0, 'Tidak dapat terhubung ke layanan. Periksa koneksimu.', null);
    }

    if (response.status === 401 && auth) {
        if (typeof unauthorizedHandler === 'function') {
            unauthorizedHandler();
        }
        throw new ApiError(401, STATUS_MESSAGES[401], null);
    }

    if (parse === 'blob') {
        if (!response.ok) {
            const payload = await readPayload(response);
            throw new ApiError(response.status, friendlyDetail(response.status, payload), payload);
        }
        return response.blob();
    }

    if (response.status === 204) {
        return null;
    }

    const data = await readPayload(response);

    if (!response.ok) {
        throw new ApiError(response.status, friendlyDetail(response.status, data), data);
    }

    return data;
}

export { STATUS_MESSAGES, friendlyDetail };
