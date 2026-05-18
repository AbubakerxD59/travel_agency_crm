function formatCalendarDayLabel(iso) {
    const date = new Date(`${iso}T12:00:00`);
    if (Number.isNaN(date.getTime())) {
        return iso;
    }
    return date.toLocaleDateString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function initPanelHeightSync(root) {
    const gridWrap = root.querySelector('.dash-folder-calendar__grid-wrap');
    const panel = root.querySelector('.dash-folder-calendar__panel');
    if (!gridWrap || !panel) {
        return () => {};
    }

    const mqDesktop = window.matchMedia('(min-width: 1024px)');

    const sync = () => {
        if (!mqDesktop.matches) {
            panel.style.removeProperty('height');
            panel.style.removeProperty('max-height');
            return;
        }

        const height = Math.round(gridWrap.getBoundingClientRect().height);
        panel.style.height = `${height}px`;
        panel.style.maxHeight = `${height}px`;
    };

    const resizeObserver = new ResizeObserver(sync);
    resizeObserver.observe(gridWrap);
    mqDesktop.addEventListener('change', sync);
    window.addEventListener('resize', sync);
    sync();

    return sync;
}

function initAdminDashboardFolderCalendar() {
    const root = document.getElementById('dash-folder-calendar');
    if (!root) {
        return;
    }

    const syncPanelHeight = initPanelHeightSync(root);
    const inlineFolderDetails = root.dataset.inlineFolderDetails === 'true';
    const folderDetailsUrlBase = (root.dataset.folderDetailsUrl || '').replace(/\/$/, '');
    const folderShowUrlBase = (root.dataset.folderShowUrlBase || '/admin/folders').replace(/\/$/, '');
    const emptyDayMessage = root.dataset.emptyDay || 'No folders on this day.';
    const emptyMonthMessage = root.dataset.emptyMonth || 'No folders in this month.';
    const noTravelLabel = root.dataset.noTravelLabel || 'No folders this month';

    const dayButtons = root.querySelectorAll('[data-calendar-day]');
    const listEl = document.getElementById('dash-folder-calendar-day-list');
    const labelEl = document.getElementById('dash-folder-calendar-selected-label');
    const emptyEl = document.getElementById('dash-folder-calendar-empty');

    const detailsOuter = document.getElementById('dash-folder-calendar-details-outer');
    const detailsEl = document.getElementById('dash-folder-calendar-details');
    const detailsLoadingEl = document.getElementById('dash-folder-calendar-details-loading');
    const detailsTitleEl = document.getElementById('dash-folder-calendar-details-title');
    const detailsFullLinkEl = document.getElementById('dash-folder-calendar-details-full-link');

    if (!listEl || !labelEl) {
        return;
    }

    const templates = new Map();
    root.querySelectorAll('[data-calendar-day-template]').forEach((template) => {
        const iso = template.getAttribute('data-calendar-day-template');
        if (iso) {
            templates.set(iso, template);
        }
    });

    let selectedIso = root.dataset.defaultDay || '';
    let activeFolderRequest = 0;

    function clearFolderSelection() {
        root.querySelectorAll('[data-calendar-folder-id]').forEach((btn) => {
            btn.classList.remove('dash-folder-calendar__folder-item--selected');
            btn.setAttribute('aria-pressed', 'false');
        });
    }

    function hideFolderDetails() {
        detailsOuter?.classList.add('hidden');
        detailsEl && (detailsEl.innerHTML = '');
        detailsFullLinkEl?.classList.add('hidden');
        if (detailsTitleEl) {
            detailsTitleEl.textContent = 'Select a folder';
        }
        clearFolderSelection();
    }

    async function loadFolderDetails(folderId, activeBtn, shouldScroll = false) {
        if (!inlineFolderDetails || !folderDetailsUrlBase || !detailsOuter || !detailsEl) {
            return;
        }

        const requestId = ++activeFolderRequest;
        clearFolderSelection();
        if (activeBtn) {
            activeBtn.classList.add('dash-folder-calendar__folder-item--selected');
            activeBtn.setAttribute('aria-pressed', 'true');
            const name = activeBtn.querySelector('p')?.textContent?.trim();
            if (detailsTitleEl && name) {
                detailsTitleEl.textContent = name;
            }
        }

        detailsOuter.classList.remove('hidden');
        detailsLoadingEl?.classList.remove('hidden');
        detailsEl.innerHTML = '';

        try {
            const response = await fetch(`${folderDetailsUrlBase}/${folderId}`, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load folder');
            }

            if (requestId !== activeFolderRequest) {
                return;
            }

            detailsEl.innerHTML = await response.text();

            if (detailsFullLinkEl) {
                detailsFullLinkEl.href = `${folderShowUrlBase}/${folderId}`;
                detailsFullLinkEl.classList.remove('hidden');
            }

            if (shouldScroll) {
                detailsOuter.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        } catch {
            if (requestId !== activeFolderRequest) {
                return;
            }
            detailsEl.innerHTML =
                '<p class="text-center text-sm text-rose-600">Unable to load folder details. Please try again.</p>';
        } finally {
            if (requestId === activeFolderRequest) {
                detailsLoadingEl?.classList.add('hidden');
            }
        }
    }

    function selectFirstFolderInList() {
        if (!inlineFolderDetails) {
            return;
        }

        const firstFolder = listEl.querySelector('[data-calendar-folder-id]');
        if (firstFolder) {
            const folderId = firstFolder.getAttribute('data-calendar-folder-id');
            if (folderId) {
                loadFolderDetails(folderId, firstFolder, false);
                return;
            }
        }

        hideFolderDetails();
    }

    function clearSelection() {
        dayButtons.forEach((btn) => {
            btn.classList.remove('dash-folder-calendar__day--selected');
            btn.setAttribute('aria-pressed', 'false');
        });
    }

    function renderDay(iso) {
        clearSelection();
        document.getElementById('dash-folder-calendar-month-empty')?.classList.add('hidden');

        const activeBtn = root.querySelector(`[data-calendar-day="${iso}"]`);
        if (activeBtn && !activeBtn.disabled) {
            activeBtn.classList.add('dash-folder-calendar__day--selected');
            activeBtn.setAttribute('aria-pressed', 'true');
        }

        labelEl.textContent = formatCalendarDayLabel(iso);

        listEl.querySelectorAll('[data-calendar-day-list]').forEach((node) => node.remove());
        if (emptyEl) {
            emptyEl.classList.add('hidden');
        }

        const template = templates.get(iso);
        if (template) {
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-calendar-day-list', iso);
            wrapper.appendChild(template.content.cloneNode(true));
            listEl.appendChild(wrapper);
            listEl.scrollTop = 0;
            syncPanelHeight();
            selectFirstFolderInList();
            return;
        }

        if (emptyEl) {
            emptyEl.classList.remove('hidden');
        }

        listEl.scrollTop = 0;
        syncPanelHeight();
        hideFolderDetails();
    }

    dayButtons.forEach((btn) => {
        if (btn.disabled) {
            return;
        }
        btn.addEventListener('click', () => {
            const iso = btn.getAttribute('data-calendar-day');
            if (!iso) {
                return;
            }
            selectedIso = iso;
            renderDay(iso);
        });
    });

    if (inlineFolderDetails) {
        listEl.addEventListener('click', (event) => {
            const folderBtn = event.target.closest('[data-calendar-folder-id]');
            if (!folderBtn) {
                return;
            }
            event.preventDefault();
            const folderId = folderBtn.getAttribute('data-calendar-folder-id');
            if (folderId) {
                loadFolderDetails(folderId, folderBtn, true);
            }
        });
    }

    if (templates.size === 0) {
        labelEl.textContent = noTravelLabel;
        const monthEmpty = document.getElementById('dash-folder-calendar-month-empty');
        if (monthEmpty) {
            monthEmpty.textContent = emptyMonthMessage;
            monthEmpty.classList.remove('hidden');
        }
        hideFolderDetails();
        return;
    }

    if (emptyEl) {
        emptyEl.textContent = emptyDayMessage;
    }

    document.getElementById('dash-folder-calendar-month-empty')?.classList.add('hidden');

    const firstWithFolders = root.querySelector(
        '[data-calendar-day]:not([disabled])[data-folder-count]:not([data-folder-count="0"])',
    );
    if (firstWithFolders) {
        selectedIso = firstWithFolders.getAttribute('data-calendar-day') || selectedIso;
    }

    if (selectedIso) {
        renderDay(selectedIso);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboardFolderCalendar);
} else {
    initAdminDashboardFolderCalendar();
}
