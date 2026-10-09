import { apiRequest } from './client.js';

export function submitReport({ text, victimWish, consent, contact, location, files }, options = {}) {
    const form = new FormData();
    form.append('text', text);
    form.append('victim_wish', victimWish);
    form.append('consent', consent ? 'true' : 'false');
    if (contact) {
        form.append('contact', contact);
    }
    if (location) {
        form.append('location', location);
    }
    if (Array.isArray(files)) {
        files.slice(0, 3).forEach((file) => form.append('files', file, file.name));
    }
    return apiRequest('/api/reports', {
        method: 'POST',
        formData: form,
        auth: false,
        signal: options.signal,
    });
}

export function getTicket(ticketCode, options = {}) {
    return apiRequest(`/api/tickets/${encodeURIComponent(ticketCode)}`, {
        method: 'GET',
        auth: false,
        signal: options.signal,
    });
}

export function sendTicketMessage(ticketCode, body, options = {}) {
    return apiRequest(`/api/tickets/${encodeURIComponent(ticketCode)}/messages`, {
        method: 'POST',
        body: { body },
        auth: false,
        signal: options.signal,
    });
}

export function getHelpResources(options = {}) {
    return apiRequest('/api/help-resources', {
        method: 'GET',
        auth: false,
        signal: options.signal,
    });
}
