let audioContext = null;
let muted = false;

try {
    muted = window.sessionStorage.getItem('ppks.alarm.muted') === '1';
} catch {
    muted = false;
}

function getContext() {
    if (!audioContext) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) {
            return null;
        }
        audioContext = new Ctx();
    }
    if (audioContext.state === 'suspended') {
        audioContext.resume().catch(() => {});
    }
    return audioContext;
}

export function isMuted() {
    return muted;
}

export function setMuted(value) {
    muted = Boolean(value);
    try {
        window.sessionStorage.setItem('ppks.alarm.muted', muted ? '1' : '0');
    } catch {
        /* ignore */
    }
}

export function enableAudio() {
    const ctx = getContext();
    if (!ctx) {
        return Promise.resolve(false);
    }
    if (ctx.state === 'suspended') {
        return ctx.resume().then(() => true).catch(() => false);
    }
    return Promise.resolve(true);
}

export function playAlarm(times = 3) {
    if (muted) {
        return;
    }
    const ctx = getContext();
    if (!ctx) {
        return;
    }
    const start = ctx.currentTime;
    const beep = 0.18;
    const gap = 0.14;
    for (let i = 0; i < times; i += 1) {
        const offset = start + i * (beep + gap);
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'square';
        osc.frequency.setValueAtTime(i % 2 === 0 ? 880 : 660, offset);
        gain.gain.setValueAtTime(0.0001, offset);
        gain.gain.exponentialRampToValueAtTime(0.18, offset + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, offset + beep);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(offset);
        osc.stop(offset + beep + 0.02);
    }
}

export async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        return false;
    }
    if (Notification.permission === 'granted') {
        return true;
    }
    if (Notification.permission === 'denied') {
        return false;
    }
    try {
        const result = await Notification.requestPermission();
        return result === 'granted';
    } catch {
        return false;
    }
}

export function notify(title, body) {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return;
    }
    try {
        new Notification(title, { body, tag: 'ppks-darurat', renotify: true });
    } catch {
        /* ignore */
    }
}
