import './bootstrap';

function initAdminSidebar() {
    const html = document.documentElement;
    const toggle = document.getElementById('admin-sidebar-toggle');
    const overlay = document.getElementById('admin-sidebar-overlay');
    const sidebar = document.getElementById('admin-sidebar');

    if (!toggle || !overlay || !sidebar) {
        return;
    }

    /** Align with Tailwind `lg` (1024px): drawer only below this width. */
    const mqDesktop = window.matchMedia('(min-width: 1024px)');

    function setOpen(open) {
        const isDesktop = mqDesktop.matches;

        if (isDesktop) {
            html.classList.remove('admin-sidebar-open');
            sidebar.style.removeProperty('transform');
            sidebar.removeAttribute('aria-hidden');
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
            toggle.setAttribute('aria-expanded', 'false');
            const srDesktop = toggle.querySelector('.sr-only');
            if (srDesktop) {
                srDesktop.textContent = 'Open menu';
            }
            document.body.classList.remove('overflow-hidden');
            overlay.setAttribute('aria-hidden', 'true');
            return;
        }

        html.classList.toggle('admin-sidebar-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        const sr = toggle.querySelector('.sr-only');
        if (sr) {
            sr.textContent = open ? 'Close menu' : 'Open menu';
        }
        document.body.classList.toggle('overflow-hidden', open);
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');

        if (open) {
            sidebar.setAttribute('aria-hidden', 'false');
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
        } else {
            sidebar.setAttribute('aria-hidden', 'true');
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
        }
    }

    toggle.addEventListener('click', () => {
        setOpen(!html.classList.contains('admin-sidebar-open'));
    });

    overlay.addEventListener('click', () => setOpen(false));

    document.querySelectorAll('.admin-sidebar-close').forEach((btn) => {
        btn.addEventListener('click', () => setOpen(false));
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    mqDesktop.addEventListener('change', () => setOpen(false));

    if (!mqDesktop.matches) {
        sidebar.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && html.classList.contains('admin-sidebar-open')) {
            setOpen(false);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminSidebar);
} else {
    initAdminSidebar();
}
