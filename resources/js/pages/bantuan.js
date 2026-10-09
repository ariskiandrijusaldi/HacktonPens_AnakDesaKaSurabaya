import { getHelpResources } from '../api/public.js';
import { toastError } from '../lib/toast.js';

const FALLBACK = [
    {
        name: 'SAPA 129',
        description: 'Layanan pendampingan dan pelaporan kekerasan berbasis gender, gratis dan rahasia.',
        phone: '129',
        url: '',
        hours: '24 jam',
    },
    {
        name: 'Polisi',
        description: 'Untuk situasi darurat yang mengancam nyawa atau membutuhkan tindakan segera.',
        phone: '110',
        url: '',
        hours: '24 jam',
    },
    {
        name: 'Komnas Perempuan',
        description: 'Pendampingan dan rujukan untuk kasus kekerasan terhadap perempuan.',
        phone: '',
        url: 'https://komnasperempuan.go.id',
        hours: 'Jam kerja',
    },
];

function render(resources) {
    const host = document.querySelector('[data-help-cards]');
    const template = document.querySelector('[data-help-template]');
    const status = document.querySelector('[data-help-status]');
    if (!host || !template) {
        return;
    }

    host.textContent = '';

    if (!resources.length) {
        host.innerHTML = '<p class="text-ink-500">Belum ada kontak yang tersedia.</p>';
        return;
    }

    resources.forEach((resource) => {
        const node = template.content.firstElementChild.cloneNode(true);
        node.querySelector('[data-help-name]').textContent = resource.name || 'Layanan';
        node.querySelector('[data-help-description]').textContent = resource.description || '';
        const hours = node.querySelector('[data-help-hours]');
        if (resource.hours) {
            hours.textContent = resource.hours;
        } else {
            hours.remove();
        }

        const phone = node.querySelector('[data-help-phone]');
        if (resource.phone) {
            phone.href = `tel:${String(resource.phone).replace(/\s+/g, '')}`;
            phone.setAttribute('rel', 'noreferrer noopener');
            phone.textContent = `Telepon ${resource.phone}`;
        } else {
            phone.remove();
        }

        const url = node.querySelector('[data-help-url]');
        if (resource.url) {
            url.href = resource.url;
            url.setAttribute('target', '_blank');
            url.setAttribute('rel', 'noreferrer noopener');
        } else {
            url.remove();
        }

        host.appendChild(node);
    });

    if (status) {
        status.textContent = 'Kontak diperbarui dari layanan.';
        status.classList.add('sr-only');
    }
}

async function load() {
    const status = document.querySelector('[data-help-status]');
    try {
        const data = await getHelpResources();
        const resources = Array.isArray(data) ? data : [];
        render(resources.length ? resources : FALLBACK);
    } catch {
        render(FALLBACK);
        if (status) {
            status.textContent = 'Menampilkan kontak darurat standar.';
        }
        toastError('Tidak dapat memuat kontak terbaru. Menampilkan kontak darurat standar.');
    }
}

document.querySelector('[data-help-retry]')?.addEventListener('click', () => {
    load();
});

load();
