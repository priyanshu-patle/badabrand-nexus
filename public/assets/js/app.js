(() => {
    const root = document.documentElement;
    const body = document.body;
    const toggleButtons = document.querySelectorAll('[data-theme-toggle]');
    const sidebar = document.querySelector('[data-dashboard-sidebar]');
    const sidebarToggle = document.querySelector('[data-dashboard-menu-toggle]');
    const sidebarCollapseToggle = document.querySelector('[data-sidebar-collapse-toggle]');
    const sidebarOverlay = document.querySelector('[data-dashboard-overlay]');
    const sidebarNav = document.querySelector('[data-sidebar-nav]');
    const themeStorageKey = 'badabrand-theme';
    const themeSequence = ['dark', 'light', 'midnight'];
    const collapsedStorageKey = 'badabrand-sidebar-collapsed';
    const openGroupStorageKey = 'badabrand-sidebar-open-group';
    const desktopSidebarMedia = window.matchMedia('(max-width: 991px)');
    const savedTheme = localStorage.getItem(themeStorageKey);

    const themeLabels = {
        dark: 'Dark Pro',
        light: 'Light Classic',
        midnight: 'Midnight Glass',
    };

    const applyTheme = (theme, persist = true) => {
        const resolvedTheme = themeSequence.includes(theme) ? theme : 'dark';
        root.setAttribute('data-theme', resolvedTheme);
        body.setAttribute('data-theme', resolvedTheme);
        root.style.colorScheme = resolvedTheme === 'light' ? 'light' : 'dark';

        toggleButtons.forEach((button) => {
            const currentLabel = themeLabels[resolvedTheme] || 'Theme';
            button.setAttribute('title', `Current theme: ${currentLabel}. Click to switch theme.`);
            button.setAttribute('aria-label', `Current theme: ${currentLabel}. Click to switch theme.`);
        });

        if (persist) {
            localStorage.setItem(themeStorageKey, resolvedTheme);
        }

        return resolvedTheme;
    };

    applyTheme(savedTheme && themeSequence.includes(savedTheme) ? savedTheme : (root.getAttribute('data-theme') || 'dark'), false);

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const currentTheme = root.getAttribute('data-theme') || 'dark';
            const currentIndex = Math.max(0, themeSequence.indexOf(currentTheme));
            const next = themeSequence[(currentIndex + 1) % themeSequence.length];
            applyTheme(next);
        });
    });

    const getSidebarGroups = () => Array.from(document.querySelectorAll('[data-sidebar-group]'));
    const isMobileSidebar = () => desktopSidebarMedia.matches;
    const closeSidebar = () => body.classList.remove('dashboard-sidebar-open');
    const openSidebar = () => body.classList.add('dashboard-sidebar-open');
    const isDesktopCollapsed = () => body.classList.contains('dashboard-sidebar-collapsed') && !isMobileSidebar();

    const syncSidebarGroupHeights = () => {
        getSidebarGroups().forEach((group) => {
            const panel = group.querySelector('.sidebar-child-links');
            const toggle = group.querySelector('[data-sidebar-toggle]');
            if (!panel) {
                return;
            }

            const isOpen = group.classList.contains('is-open');
            if (isDesktopCollapsed()) {
                panel.style.maxHeight = 'none';
                panel.hidden = false;
            } else {
                panel.hidden = false;
                panel.style.maxHeight = isOpen ? `${panel.scrollHeight}px` : '0px';
            }

            if (toggle) {
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        });
    };

    const setSidebarCollapsed = (collapsed) => {
        const shouldCollapse = collapsed && !isMobileSidebar();
        body.classList.toggle('dashboard-sidebar-collapsed', shouldCollapse);

        if (shouldCollapse) {
            localStorage.setItem(collapsedStorageKey, '1');
        } else {
            localStorage.removeItem(collapsedStorageKey);
        }

        if (sidebarCollapseToggle) {
            sidebarCollapseToggle.setAttribute('aria-label', shouldCollapse ? 'Expand sidebar' : 'Collapse sidebar');
            sidebarCollapseToggle.setAttribute('title', shouldCollapse ? 'Expand sidebar' : 'Collapse sidebar');
        }

        window.requestAnimationFrame(syncSidebarGroupHeights);
    };

    const preferredOpenGroup = () => {
        const activeGroup = getSidebarGroups().find((group) => group.dataset.groupActive === 'true' || group.classList.contains('is-active'));
        if (activeGroup?.dataset.groupKey) {
            return activeGroup.dataset.groupKey;
        }

        return localStorage.getItem(openGroupStorageKey) || '';
    };

    const setOpenGroup = (groupKey, persist = true) => {
        getSidebarGroups().forEach((group) => {
            const isOpen = groupKey !== '' && group.dataset.groupKey === groupKey;
            group.classList.toggle('is-open', isOpen);
        });

        if (persist) {
            if (groupKey) {
                localStorage.setItem(openGroupStorageKey, groupKey);
            } else {
                localStorage.removeItem(openGroupStorageKey);
            }
        }

        window.requestAnimationFrame(syncSidebarGroupHeights);
    };

    const restoreSidebarState = () => {
        if (!sidebar) {
            return;
        }

        if (isMobileSidebar()) {
            body.classList.remove('dashboard-sidebar-collapsed');
        } else {
            setSidebarCollapsed(localStorage.getItem(collapsedStorageKey) === '1');
        }

        const initialGroupKey = preferredOpenGroup();
        if (getSidebarGroups().length > 0) {
            setOpenGroup(initialGroupKey, false);
        }
    };

    restoreSidebarState();

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            if (isMobileSidebar()) {
                body.classList.toggle('dashboard-sidebar-open');
                return;
            }

            setSidebarCollapsed(!body.classList.contains('dashboard-sidebar-collapsed'));
        });
    }

    if (sidebarCollapseToggle) {
        sidebarCollapseToggle.addEventListener('click', () => {
            if (isMobileSidebar()) {
                openSidebar();
                return;
            }

            setSidebarCollapsed(!body.classList.contains('dashboard-sidebar-collapsed'));
        });
    }

    if (sidebarNav) {
        sidebarNav.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-sidebar-toggle]');
            if (toggle) {
                const group = toggle.closest('[data-sidebar-group]');
                if (!group) {
                    return;
                }

                event.preventDefault();
                const groupKey = group.dataset.groupKey || '';
                const isOpen = group.classList.contains('is-open');
                setOpenGroup(isOpen ? '' : groupKey);

                if (event.detail > 0 && typeof toggle.blur === 'function') {
                    window.requestAnimationFrame(() => toggle.blur());
                }
                return;
            }

            const link = event.target.closest('a');
            if (link && isMobileSidebar()) {
                closeSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    const handleSidebarViewportChange = () => {
        closeSidebar();
        restoreSidebarState();
    };

    if (typeof desktopSidebarMedia.addEventListener === 'function') {
        desktopSidebarMedia.addEventListener('change', handleSidebarViewportChange);
    } else if (typeof desktopSidebarMedia.addListener === 'function') {
        desktopSidebarMedia.addListener(handleSidebarViewportChange);
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    let resizeTimer = null;
    window.addEventListener('resize', () => {
        if (resizeTimer !== null) {
            window.clearTimeout(resizeTimer);
        }

        resizeTimer = window.setTimeout(() => {
            restoreSidebarState();
        }, 90);
    }, { passive: true });

    const syncRichEditorState = (surface) => {
        if (!surface) {
            return;
        }
        const text = surface.textContent.replace(/\u200B/g, '').trim();
        surface.classList.toggle('is-empty', text.length === 0);
    };

    const createButton = (label, command, value = null) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'rich-editor-btn';
        button.textContent = label;
        button.dataset.command = command;
        if (value !== null) {
            button.dataset.value = value;
        }
        return button;
    };

    const initRichEditor = (textarea) => {
        if (!textarea || textarea.dataset.editorReady === '1') {
            return;
        }

        textarea.dataset.editorReady = '1';
        textarea.style.display = 'none';

        const wrapper = document.createElement('div');
        wrapper.className = 'rich-editor';

        const toolbar = document.createElement('div');
        toolbar.className = 'rich-editor-toolbar';

        const controls = [
            createButton('B', 'bold'),
            createButton('I', 'italic'),
            createButton('U', 'underline'),
            createButton('H2', 'formatBlock', 'h2'),
            createButton('Bullet List', 'insertUnorderedList'),
            createButton('1. List', 'insertOrderedList'),
            createButton('Quote', 'formatBlock', 'blockquote'),
            createButton('Link', 'createLink'),
            createButton('Clear', 'removeFormat'),
        ];

        controls.forEach((control) => toolbar.appendChild(control));

        const surface = document.createElement('div');
        surface.className = 'rich-editor-surface is-empty';
        surface.contentEditable = 'true';
        surface.dataset.placeholder = textarea.getAttribute('placeholder') || 'Write here...';
        surface.innerHTML = textarea.value || '';

        const syncToTextarea = () => {
            const html = surface.innerHTML
                .replace(/<div><br><\/div>/g, '')
                .replace(/<div>/g, '<p>')
                .replace(/<\/div>/g, '</p>');
            textarea.value = html.trim();
            syncRichEditorState(surface);
        };

        toolbar.addEventListener('click', (event) => {
            const button = event.target.closest('.rich-editor-btn');
            if (!button) {
                return;
            }

            event.preventDefault();
            surface.focus();
            const command = button.dataset.command;
            const value = button.dataset.value || null;

            if (command === 'createLink') {
                const url = window.prompt('Enter link URL');
                if (url) {
                    document.execCommand(command, false, url);
                }
            } else if (command === 'formatBlock' && value) {
                document.execCommand(command, false, value);
            } else {
                document.execCommand(command, false, value);
            }

            syncToTextarea();
        });

        surface.addEventListener('input', syncToTextarea);
        surface.addEventListener('blur', syncToTextarea);
        surface.addEventListener('keyup', () => syncRichEditorState(surface));
        surface.addEventListener('focus', () => syncRichEditorState(surface));

        wrapper.appendChild(toolbar);
        wrapper.appendChild(surface);
        textarea.insertAdjacentElement('afterend', wrapper);

        const form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', syncToTextarea);
        }

        syncToTextarea();
    };

    document.querySelectorAll('textarea[data-rich-editor]').forEach(initRichEditor);
})();
