import { API_BASE_URL } from '../api/client.js';

export function parseSseChunk(chunk) {
    const lines = chunk.split('\n');
    let event = 'message';
    const dataLines = [];
    for (const line of lines) {
        if (line.startsWith('event:')) {
            event = line.slice(6).trim();
        } else if (line.startsWith('data:')) {
            dataLines.push(line.slice(5).trim());
        }
    }
    if (!dataLines.length) {
        return null;
    }
    const raw = dataLines.join('\n');
    let data = raw;
    try {
        data = JSON.parse(raw);
    } catch {
        data = raw;
    }
    return { event, data };
}

export function openOfficerStream({ getToken, onEvent, onStatus, onError }) {
    const controller = new AbortController();
    const token = typeof getToken === 'function' ? getToken() : null;

    const run = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}/api/officer/stream`, {
                method: 'GET',
                headers: {
                    Accept: 'text/event-stream',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                },
                signal: controller.signal,
                credentials: 'omit',
                referrerPolicy: 'no-referrer',
            });

            if (!response.ok || !response.body) {
                throw new Error('stream unavailable');
            }

            if (typeof onStatus === 'function') {
                onStatus('live');
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { value, done } = await reader.read();
                if (done) {
                    break;
                }
                buffer += decoder.decode(value, { stream: true });
                let boundary = buffer.indexOf('\n\n');
                while (boundary !== -1) {
                    const chunk = buffer.slice(0, boundary);
                    buffer = buffer.slice(boundary + 2);
                    const parsed = parseSseChunk(chunk);
                    if (parsed && typeof onEvent === 'function') {
                        onEvent(parsed);
                    }
                    boundary = buffer.indexOf('\n\n');
                }
            }
            throw new Error('stream ended');
        } catch (error) {
            if (controller.signal.aborted) {
                return;
            }
            if (typeof onStatus === 'function') {
                onStatus('polling');
            }
            if (typeof onError === 'function') {
                onError(error);
            }
        }
    };

    run();

    return () => controller.abort();
}
