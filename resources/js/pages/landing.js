export default function initLanding() {
    document.querySelectorAll('a[href^="/#"], a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href');
            if (href === '#' || href.length < 2) {
                return;
            }
            const id = href.slice(href.indexOf('#') + 1);
            const target = document.getElementById(id);
            if (!target) {
                return;
            }
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', `#${id}`);
        });
    });
}

initLanding();
