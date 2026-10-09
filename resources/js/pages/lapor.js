import { submitReport } from '../api/public.js';
import { ApiError } from '../api/client.js';
import { toastSuccess, toastError } from '../lib/toast.js';
import { copyText, downloadText } from '../lib/copy.js';

const form = document.getElementById('report-form');
const formWrap = document.getElementById('report-form-wrap');
const successPanel = document.getElementById('report-success');
const ticketCodeEl = document.getElementById('ticket-code');
const submitButton = document.getElementById('submit-button');

const MAX_FILES = 3;
const MAX_SIZE = 5 * 1024 * 1024;
const ALLOWED = ['image/jpeg', 'image/png', 'application/pdf'];

let selectedFiles = [];
let submittedCode = '';

function setLoading(loading) {
    if (!submitButton) {
        return;
    }
    submitButton.disabled = loading;
    submitButton.querySelector('[data-submit-label]').textContent = loading ? 'Mengirim…' : 'Kirim Laporan';
    submitButton.querySelector('[data-submit-spinner]').classList.toggle('hidden', !loading);
}

const textInput = document.getElementById('text');
const counter = document.getElementById('text-counter');
const textError = document.getElementById('text-error');

function initCounter() {
    if (!textInput || !counter) {
        return;
    }
    const update = () => {
        counter.textContent = `${textInput.value.length} / 10.000`;
    };
    textInput.addEventListener('input', update);
    update();
}

const filesInput = document.getElementById('files');
const previewList = document.getElementById('file-preview');
const filesError = document.getElementById('files-error');

function fileKindLabel(file) {
    if (file.type === 'application/pdf') {
        return 'PDF';
    }
    return 'Gambar';
}

function renderFiles() {
    if (!previewList) {
        return;
    }
    previewList.textContent = '';
    selectedFiles.forEach((file, index) => {
        const li = document.createElement('li');
        li.className = 'flex items-center gap-3 rounded-xl border border-ink-200 p-2';

        let thumb;
        if (file.type.startsWith('image/')) {
            thumb = document.createElement('img');
            thumb.src = URL.createObjectURL(file);
            thumb.alt = '';
            thumb.className = 'h-12 w-12 rounded-lg object-cover';
            thumb.addEventListener('load', () => URL.revokeObjectURL(thumb.src), { once: true });
        } else {
            thumb = document.createElement('span');
            thumb.className = 'grid h-12 w-12 place-items-center rounded-lg bg-ink-100 text-xs font-bold text-ink-600';
            thumb.textContent = fileKindLabel(file);
        }

        const meta = document.createElement('div');
        meta.className = 'min-w-0 flex-1';
        const name = document.createElement('p');
        name.className = 'truncate text-sm font-semibold text-ink-800';
        name.textContent = file.name;
        const size = document.createElement('p');
        size.className = 'text-xs text-ink-500';
        size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
        meta.append(name, size);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn-ghost btn-sm';
        remove.textContent = 'Hapus';
        remove.addEventListener('click', () => {
            selectedFiles.splice(index, 1);
            renderFiles();
        });

        li.append(thumb, meta, remove);
        previewList.appendChild(li);
    });
}

function validateFiles(incoming) {
    const errors = [];
    const accepted = [];
    for (const file of incoming) {
        if (!ALLOWED.includes(file.type)) {
            errors.push(`"${file.name}" bukan format yang didukung (JPG, PNG, PDF).`);
            continue;
        }
        if (file.size > MAX_SIZE) {
            errors.push(`"${file.name}" melebihi 5 MB.`);
            continue;
        }
        accepted.push(file);
    }
    const combined = [...selectedFiles, ...accepted];
    if (combined.length > MAX_FILES) {
        errors.push('Maksimal 3 berkas.');
        selectedFiles = combined.slice(0, MAX_FILES);
    } else {
        selectedFiles = combined;
    }
    if (filesError) {
        filesError.textContent = errors.join(' ');
    }
    return errors.length === 0;
}

if (filesInput) {
    filesInput.addEventListener('change', () => {
        const incoming = Array.from(filesInput.files || []);
        if (incoming.length) {
            validateFiles(incoming);
        }
        filesInput.value = '';
        renderFiles();
    });
}

function clearFieldErrors() {
    ['text-error', 'files-error', 'consent-error'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = '';
        }
    });
}

function readVictimWish() {
    const checked = form.querySelector('input[name="victim_wish"]:checked');
    return checked ? checked.value : '';
}

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFieldErrors();

        const text = textInput.value.trim();
        const victimWish = readVictimWish();
        const consent = document.getElementById('consent').checked;
        let valid = true;

        if (text.length < 20) {
            textError.textContent = 'Ceritakan sedikitnya 20 karakter agar kami bisa memahami konteksnya.';
            valid = false;
        }
        if (!victimWish) {
            document.getElementById('text-error').textContent = 'Pilih dulu harapanmu pada bagian "Apa yang kamu harapkan?".';
            valid = false;
        }
        if (!consent) {
            document.getElementById('consent-error').textContent = 'Persetujuan ini wajib dicentang sebelum mengirim.';
            valid = false;
        }
        if (!valid) {
            toastError('Ada isian yang perlu dilengkapi.');
            return;
        }

        const payload = {
            text,
            victimWish,
            consent,
            contact: document.getElementById('contact').value.trim(),
            location: document.getElementById('location').value.trim(),
            files: selectedFiles,
        };

        setLoading(true);
        try {
            const result = await submitReport(payload);
            submittedCode = result && result.ticket_code ? String(result.ticket_code) : '';
            ticketCodeEl.textContent = submittedCode || 'Tiket dibuat';
            formWrap.classList.add('hidden');
            successPanel.classList.remove('hidden');
            successPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            toastSuccess('Laporan berhasil dikirim. Simpan kode tiketmu.');
            window.addEventListener('beforeunload', beforeUnloadHandler);
        } catch (error) {
            if (error instanceof ApiError && error.status === 429) {
                toastError(error.detail || 'Terlalu banyak percobaan, coba lagi sebentar.');
            } else if (error instanceof ApiError && error.status === 422) {
                toastError(error.detail || 'Ada isian yang belum sesuai. Periksa kembali.');
            } else {
                toastError(error.detail || 'Gagal mengirim laporan. Isianmu tetap tersimpan, silakan coba lagi.');
            }
        } finally {
            setLoading(false);
        }
    });
}

function beforeUnloadHandler(event) {
    event.preventDefault();
    event.returnValue = '';
}

document.getElementById('copy-ticket')?.addEventListener('click', async () => {
    if (!submittedCode) {
        return;
    }
    const ok = await copyText(submittedCode);
    if (ok) {
        toastSuccess('Kode tiket disalin.');
    } else {
        toastError('Tidak bisa menyalin otomatis. Salin manual ya.');
    }
});

document.getElementById('download-ticket')?.addEventListener('click', () => {
    if (!submittedCode) {
        return;
    }
    const content = [
        'SATGAS PPKS KAMPUS',
        '==================',
        '',
        `Kode Tiket: ${submittedCode}`,
        '',
        'Simpan kode ini. Tanpa kode ini status laporan tidak bisa ditampilkan',
        'dan kode tidak dapat dipulihkan.',
        '',
        'Cek status di: /tiket',
        'Dalam darurat hubungi SAPA 129 atau 110.',
        '',
    ].join('\n');
    downloadText(`tiket-${submittedCode}.txt`, content);
    toastSuccess('Berkas tiket diunduh.');
});

window.addEventListener('pageshow', () => {
    if (submittedCode) {
        window.addEventListener('beforeunload', beforeUnloadHandler);
    }
});

document.addEventListener('ppks:quick-exit', () => {
    if (form) {
        form.reset();
    }
    selectedFiles = [];
    submittedCode = '';
    previewList && (previewList.textContent = '');
});

initCounter();
