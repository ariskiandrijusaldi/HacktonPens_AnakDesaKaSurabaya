const STORAGE_KEY = 'ppks.session';

function safeSession() {
    try {
        return window.sessionStorage;
    } catch {
        return null;
    }
}

let session = null;

function loadSession() {
    if (session) {
        return session;
    }
    const store = safeSession();
    if (!store) {
        return null;
    }
    try {
        const raw = store.getItem(STORAGE_KEY);
        if (!raw) {
            return null;
        }
        const parsed = JSON.parse(raw);
        if (!parsed || !parsed.token || !parsed.expires_at) {
            store.removeItem(STORAGE_KEY);
            return null;
        }
        if (Date.now() >= parsed.expires_at) {
            store.removeItem(STORAGE_KEY);
            return null;
        }
        session = parsed;
        return session;
    } catch {
        store.removeItem(STORAGE_KEY);
        return null;
    }
}

export function saveSession({ access_token, role, username, expires_in }) {
    const ttl = Number(expires_in) > 0 ? Number(expires_in) * 1000 : 30 * 60 * 1000;
    const value = {
        token: access_token,
        role: role || 'ANGGOTA',
        username: username || '',
        expires_at: Date.now() + ttl,
    };
    session = value;
    const store = safeSession();
    if (store) {
        store.setItem(STORAGE_KEY, JSON.stringify(value));
    }
    return value;
}

export function getToken() {
    const current = loadSession();
    if (!current) {
        return null;
    }
    if (Date.now() >= current.expires_at) {
        clearSession();
        return null;
    }
    return current.token;
}

export function getSession() {
    return loadSession();
}

export function getRole() {
    const current = loadSession();
    return current ? current.role : null;
}

export function getUsername() {
    const current = loadSession();
    return current ? current.username : '';
}

export function isAuthenticated() {
    return Boolean(getToken());
}

export function isAdmin() {
    return getRole() === 'ADMIN';
}

export function clearSession() {
    session = null;
    const store = safeSession();
    if (store) {
        store.removeItem(STORAGE_KEY);
    }
}

export function logout(redirect = true) {
    clearSession();
    if (redirect) {
        window.location.replace('/masuk');
    }
}

export function requireOfficer({ message = 'Silakan masuk untuk melanjutkan.' } = {}) {
    if (!isAuthenticated()) {
        const target = `/masuk?pesan=${encodeURIComponent(message)}`;
        window.location.replace(target);
        return false;
    }
    return true;
}

export function sessionExpiresIn() {
    const current = loadSession();
    if (!current) {
        return 0;
    }
    return Math.max(0, current.expires_at - Date.now());
}

export function watchSession({ warnSeconds = 120, onWarning, onExpire } = {}) {
    let warned = false;
    const timer = window.setInterval(() => {
        const remaining = sessionExpiresIn();
        if (remaining <= 0) {
            window.clearInterval(timer);
            clearSession();
            if (typeof onExpire === 'function') {
                onExpire();
            } else {
                window.location.replace('/masuk?pesan=Sesi%20berakhir');
            }
            return;
        }
        if (!warned && remaining <= warnSeconds * 1000) {
            warned = true;
            if (typeof onWarning === 'function') {
                onWarning(Math.round(remaining / 1000));
            }
        }
    }, 15000);
    return () => window.clearInterval(timer);
}
