import './bootstrap';
import Swal from 'sweetalert2';
import { TravelLoader } from './travel-loader';
import './folder-date-range-picker';
import './folder-form-date-pickers';

window.Swal = Swal;
window.TravelLoader = TravelLoader;

function initAdminSidebar() {
    const html = document.documentElement;
    const body = document.body;
    const toggle = document.getElementById('admin-sidebar-toggle');
    const overlay = document.getElementById('admin-sidebar-overlay');
    const sidebar = document.getElementById('admin-sidebar');

    if (!toggle || !overlay || !sidebar) {
        return;
    }

    /** Align with Tailwind `lg` (1024px): drawer only below this width. */
    const mqDesktop = window.matchMedia('(min-width: 1024px)');
    const forceDrawerMode = body.classList.contains('folder-form-sidebar-drawer');
    const useDrawerMode = () => forceDrawerMode || !mqDesktop.matches;

    if (forceDrawerMode) {
        toggle.classList.remove('lg:hidden');
        overlay.classList.remove('lg:hidden');
        sidebar.querySelectorAll('.admin-sidebar-close').forEach((btn) => btn.classList.remove('lg:hidden'));
    }

    function setOpen(open) {
        if (!useDrawerMode()) {
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
        sidebar.style.transform = open ? 'translate3d(0, 0, 0)' : 'translate3d(-100%, 0, 0)';

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

    mqDesktop.addEventListener('change', () => {
        if (useDrawerMode()) {
            setOpen(false);
            return;
        }
        setOpen(false);
    });

    if (useDrawerMode()) {
        sidebar.setAttribute('aria-hidden', 'true');
        sidebar.style.transform = 'translate3d(-100%, 0, 0)';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && html.classList.contains('admin-sidebar-open')) {
            setOpen(false);
        }
    });
}

function initLogoutConfirmation() {
    const logoutForm = document.getElementById('logout-form');
    const logoutButton = document.getElementById('logout-button');

    if (!logoutForm || !logoutButton) {
        return;
    }

    logoutButton.addEventListener('click', async () => {
        if (typeof window.Swal === 'undefined') {
            logoutForm.submit();
            return;
        }

        const result = await window.Swal.fire({
            title: 'Log out?',
            text: 'You will need to sign in again to access your account.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, log out',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#152c49',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            focusCancel: true,
        });

        if (result.isConfirmed) {
            logoutForm.submit();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initAdminSidebar();
        initLogoutConfirmation();
    });
} else {
    initAdminSidebar();
    initLogoutConfirmation();
}
