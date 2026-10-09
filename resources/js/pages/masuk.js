import { login } from '../api/officer.js';
import { ApiError } from '../api/client.js';
import { saveSession, isAuthenticated } from '../auth.js';
import { toastSuccess, toastError } from '../lib/toast.js';

const params = new URLSearchParams(window.location.search);
const message = params.get('pesan');
if (message) {
    const box = document.getElementById('login-message');
    box.textContent = message;
    box.classList.remove('hidden');
}

if (isAuthenticated()) {
    window.location.replace('/dasbor');
}

const form = document.getElementById('login-form');
const button = document.getElementById('login-button');
const errorEl = document.getElementById('login-error');

function setLoading(loading) {
    button.disabled = loading;
    button.querySelector('[data-login-label]').textContent = loading ? 'Memeriksa…' : 'Masuk';
    button.querySelector('[data-login-spinner]').classList.toggle('hidden', !loading);
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorEl.textContent = '';
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const totpCode = document.getElementById('totp').value.trim();

    if (!username || !password) {
        errorEl.textContent = 'Isi nama pengguna dan kata sandi.';
        return;
    }

    setLoading(true);
    try {
        const data = await login({ username, password, totpCode });
        saveSession(data);
        toastSuccess('Berhasil masuk. Mengalihkan…');
        window.location.replace('/dasbor');
    } catch (error) {
        if (error instanceof ApiError && error.status === 429) {
            errorEl.textContent = 'Terlalu banyak percobaan, coba lagi sebentar.';
        } else if (error instanceof ApiError && error.status === 401) {
            errorEl.textContent = 'Nama pengguna atau kata sandi salah.';
        } else if (error instanceof ApiError && error.status === 422) {
            errorEl.textContent = error.detail || 'Periksa kembali data yang dimasukkan.';
        } else {
            errorEl.textContent = error.detail || 'Tidak dapat masuk saat ini. Coba lagi.';
        }
        toastError(errorEl.textContent);
    } finally {
        setLoading(false);
    }
});
