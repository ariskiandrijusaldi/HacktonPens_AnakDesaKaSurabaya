import { apiRequest } from './client.js';
import { getToken } from '../auth.js';

const token = () => getToken();

export function login({ username, password, totpCode }, options = {}) {
    const body = { username, password };
    if (totpCode) {
        body.totp_code = totpCode;
    }
    return apiRequest('/api/auth/login', {
        method: 'POST',
        body,
        auth: false,
        signal: options.signal,
    });
}

export function listReports(params = {}, options = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') {
            return;
        }
        query.set(key, String(value));
    });
    const suffix = query.toString() ? `?${query.toString()}` : '';
    return apiRequest(`/api/officer/reports${suffix}`, {
        method: 'GET',
        getToken: token,
        signal: options.signal,
    });
}

export function getReport(id, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}`, {
        method: 'GET',
        getToken: token,
        signal: options.signal,
    });
}

export function updateStatus(id, status, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}/status`, {
        method: 'PATCH',
        body: { status },
        getToken: token,
        signal: options.signal,
    });
}

export function updateUrgency(id, urgency, reason, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}/urgency`, {
        method: 'PATCH',
        body: { urgency, reason },
        getToken: token,
        signal: options.signal,
    });
}

export function assignReport(id, officerId, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}/assign`, {
        method: 'PATCH',
        body: { officer_id: officerId ?? null },
        getToken: token,
        signal: options.signal,
    });
}

export function addNote(id, note, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}/notes`, {
        method: 'POST',
        body: { note },
        getToken: token,
        signal: options.signal,
    });
}

export function sendOfficerMessage(id, body, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}/messages`, {
        method: 'POST',
        body: { body },
        getToken: token,
        signal: options.signal,
    });
}

export function fetchAttachment(id, attachmentId, options = {}) {
    return apiRequest(
        `/api/officer/reports/${encodeURIComponent(id)}/attachments/${encodeURIComponent(attachmentId)}`,
        {
            method: 'GET',
            parse: 'blob',
            getToken: token,
            signal: options.signal,
        },
    );
}

export function requestOverride(id, { pin, confirmPin, reason }, options = {}) {
    return apiRequest(`/api/officer/reports/${encodeURIComponent(id)}/override`, {
        method: 'POST',
        body: { pin, confirm_pin: confirmPin, reason },
        getToken: token,
        signal: options.signal,
    });
}

export function getStats(options = {}) {
    return apiRequest('/api/officer/stats', {
        method: 'GET',
        getToken: token,
        signal: options.signal,
    });
}

export function getPatterns(options = {}) {
    return apiRequest('/api/officer/patterns', {
        method: 'GET',
        getToken: token,
        signal: options.signal,
    });
}

export function getAuditLogs(limit = 200, options = {}) {
    return apiRequest(`/api/officer/audit-logs?limit=${encodeURIComponent(limit)}`, {
        method: 'GET',
        getToken: token,
        signal: options.signal,
    });
}

export function getOfficers(options = {}) {
    return apiRequest('/api/officer/officers', {
        method: 'GET',
        getToken: token,
        signal: options.signal,
    });
}
