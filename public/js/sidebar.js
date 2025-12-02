document.addEventListener("DOMContentLoaded", () => {
    // safe guard feather call if not initialized elsewhere
    if (typeof feather !== 'undefined' && typeof feather.replace === 'function') {
        feather.replace();
    }

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar"); // collapse button inside sidebar
    const openBtn = document.getElementById("openSidebarBtn"); // open button in navbar
    const sidebarTexts = document.querySelectorAll(".sidebar-text");
    const logo = document.getElementById("sidebarLogo");
    const profileIcon = document.getElementById("profileIcon");

    const COLLAPSED = "w-20";
    const EXPANDED = "w-64";

    function setToggleIcon(btn, name) {
        if (!btn) return;
        // keep sr-only if present
        const sr = btn.querySelector('.sr-only') ? '<span class="sr-only">Toggle sidebar</span>' : '';
        btn.innerHTML = `${sr}<i data-feather="${name}" class="w-5 h-5"></i>`;
        if (typeof feather !== 'undefined' && typeof feather.replace === 'function') feather.replace();
    }

    function applyCollapsedVisuals() {
        sidebar.classList.remove(EXPANDED);
        sidebar.classList.add(COLLAPSED);

        sidebarTexts.forEach(t => {
            t.classList.add("hidden");
            t.setAttribute("aria-hidden", "true");
        });

        if (logo && logo.dataset.icon) {
            logo.src = logo.dataset.icon;
            logo.style.width = "32px";
            logo.style.margin = "0 auto";
        }

        profileIcon?.classList.add("scale-90");

        if (openBtn) openBtn.classList.remove("hidden");
        if (toggleBtn) {
            toggleBtn.classList.add("hidden");
            toggleBtn.setAttribute("aria-expanded", "false");
            setToggleIcon(toggleBtn, "chevron-right"); // icon if it becomes visible later
        }
    }

    function applyExpandedVisuals() {
        sidebar.classList.remove(COLLAPSED);
        sidebar.classList.add(EXPANDED);

        sidebarTexts.forEach(t => {
            t.classList.remove("hidden");
            t.setAttribute("aria-hidden", "false");
        });

        if (logo && logo.dataset.full) {
            logo.src = logo.dataset.full;
            logo.style.width = "150px";
            logo.style.margin = "0";
        }

        profileIcon?.classList.remove("scale-90");

        if (openBtn) openBtn.classList.add("hidden");
        if (toggleBtn) {
            toggleBtn.classList.remove("hidden");
            toggleBtn.setAttribute("aria-expanded", "true");
            setToggleIcon(toggleBtn, "chevron-left");
        }
    }

    function expandSidebar() {
        applyExpandedVisuals();
        // persist state if desired (localStorage)
        try { localStorage.setItem('sidebarState', 'expanded'); } catch(e){}
    }

    function collapseSidebar() {
        applyCollapsedVisuals();
        try { localStorage.setItem('sidebarState', 'collapsed'); } catch(e){}
    }

    // toggle helper used by both buttons
    function toggleSidebar() {
        if (!sidebar) return;
        if (sidebar.classList.contains(EXPANDED)) collapseSidebar();
        else expandSidebar();
    }

    // wire events
    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            expandSidebar();
        });
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            collapseSidebar();
        });
    }

    // initialize state:
    (function init() {
        // priority: explicit class on sidebar -> keep it
        if (sidebar) {
            // if stored preference exists, apply it
            let pref = null;
            try { pref = localStorage.getItem('sidebarState'); } catch(e){}

            if (pref === 'expanded') {
                applyExpandedVisuals();
                return;
            } else if (pref === 'collapsed') {
                applyCollapsedVisuals();
                return;
            }

            // fallback to existing classes on element (server-side)
            if (sidebar.classList.contains(EXPANDED)) applyExpandedVisuals();
            else applyCollapsedVisuals();
        }
    })();

    /* ----------------------------
       Collapse/expand behavior for nested menus
    ----------------------------- */

    document.querySelectorAll(".collapse-toggle").forEach(btn => {
        const targetId = btn.dataset.collapse;
        const target = document.getElementById(targetId);
        const icon = btn.querySelector("i[data-feather]");

        btn.setAttribute('role', 'button');
        if (target) btn.setAttribute('aria-controls', targetId);

        const locked = target && target.querySelector(".active");
        const isVisible = target && !target.classList.contains('hidden');
        btn.setAttribute('aria-expanded', isVisible ? 'true' : 'false');

        if (locked) {
            if (target) target.classList.remove("hidden");
            if (icon) icon.classList.add("rotate-90");
            expandSidebar();
            btn.setAttribute('aria-expanded', 'true');
        }

        btn.addEventListener("click", () => {
            if (locked) {
                if (sidebar.classList.contains(COLLAPSED)) expandSidebar();
                return;
            }

            if (sidebar.classList.contains(COLLAPSED)) {
                expandSidebar();
            }

            if (target) {
                target.classList.toggle("hidden");
                const nowVisible = !target.classList.contains("hidden");
                btn.setAttribute('aria-expanded', nowVisible ? 'true' : 'false');
            }

            if (icon) icon.classList.toggle("rotate-90");
        });
    });

    /* ----------------------------
       Bootstrap tooltip init if present
    ----------------------------- */
    try {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el, {
                title: el.getAttribute('data-bs-title'),
                placement: 'right',
                container: 'body',
                trigger: 'hover focus',
                boundary: 'viewport',
                delay: { "show": 50, "hide": 0 }
            });
        });
    } catch (e) {
        // ignore if bootstrap not available
    }
});
