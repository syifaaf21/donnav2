document.addEventListener("DOMContentLoaded", () => {
    feather.replace(); // Render feather sekali saja

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebarTexts = document.querySelectorAll(".sidebar-text");
    const logo = document.getElementById("sidebarLogo");
    const profileIcon = document.getElementById("profileIcon");

    const collapsedWidth = "w-20";
    const expandedWidth = "w-64";

    const toggleIconEl = null; // replaced usage with direct injection into the button

    function setToggleIcon(name) {
        if (!toggleBtn) return;
        // keep accessible label/span, replace icon markup then render feather SVGs
        const sr = toggleBtn.querySelector('.sr-only') ? '<span class="sr-only">Toggle sidebar</span>' : '';
        toggleBtn.innerHTML = `${sr}<i data-feather="${name}" class="w-5 h-5"></i>`;
        feather.replace();
    }

    /* ----------------------------
       SIDEBAR COLLAPSE / EXPAND
    ----------------------------- */

    function collapseSidebar() {
        sidebar.classList.remove(expandedWidth);
        sidebar.classList.add(collapsedWidth);

        sidebarTexts.forEach(t => {
            t.classList.add("hidden");
            t.setAttribute("aria-hidden", "true");
        });

        if (logo) {
            logo.src = logo.dataset.icon;
            logo.style.width = "32px";
            logo.style.margin = "0 auto";
        }

        profileIcon?.classList.add("scale-90");

        if (toggleBtn) toggleBtn.setAttribute("aria-expanded", "false");
        setToggleIcon("chevron-right");
    }

    function expandSidebar() {
        sidebar.classList.remove(collapsedWidth);
        sidebar.classList.add(expandedWidth);

        sidebarTexts.forEach(t => {
            t.classList.remove("hidden");
            t.setAttribute("aria-hidden", "false");
        });

        if (logo) {
            logo.src = logo.dataset.full;
            logo.style.width = "150px";
            logo.style.margin = "0";
        }

        if (toggleBtn) toggleBtn.setAttribute("aria-expanded", "true");
        setToggleIcon("chevron-left");
    }

    if (toggleBtn) {
        // ensure toggle has ARIA defaults
        if (!toggleBtn.hasAttribute('aria-expanded')) toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.addEventListener("click", () => {
            if (sidebar.classList.contains(expandedWidth)) collapseSidebar();
            else expandSidebar();
        });
    }

    /* Apply initial visual state based on the sidebar's class */
    if (sidebar) {
        if (sidebar.classList.contains(collapsedWidth)) {
            collapseSidebar();
        } else {
            expandSidebar();
        }
    }

    /* ----------------------------
       DROPDOWN COLLAPSE (MASTER)
    ----------------------------- */

    document.querySelectorAll(".collapse-toggle").forEach(btn => {
        const targetId = btn.dataset.collapse;
        const target = document.getElementById(targetId);
        const icon = btn.querySelector("i[data-feather]");

        // mark button for accessibility
        btn.setAttribute('role', 'button');
        btn.setAttribute('aria-controls', targetId);

        // Jika submenu memiliki item dengan class "active" → jangan biarkan collapse (expand terus)
        const locked = target && target.querySelector(".active");

        // initialize aria-expanded based on visibility
        const isVisible = target && !target.classList.contains('hidden');
        btn.setAttribute('aria-expanded', isVisible ? 'true' : 'false');

        if (locked) {
            // Pastikan submenu terlihat dan icon ter-rotate
            target.classList.remove("hidden");
            if (icon) icon.classList.add("rotate-90");

            // Pastikan sidebar dalam keadaan expand
            expandSidebar();
            btn.setAttribute('aria-expanded', 'true');
        }

        btn.addEventListener("click", () => {
            // Jika submenu "locked" karena ada active, jangan toggle collapse-nya
            if (locked) {
                if (sidebar.classList.contains(collapsedWidth)) expandSidebar();
                return;
            }

            // Jika sidebar collapsed → expand dulu
            if (sidebar.classList.contains(collapsedWidth)) {
                expandSidebar();
            }

            // Toggle submenu
            if (target) {
                target.classList.toggle("hidden");
                const nowVisible = !target.classList.contains("hidden");
                btn.setAttribute('aria-expanded', nowVisible ? 'true' : 'false');
            }

            // Rotate icon
            if (icon) icon.classList.toggle("rotate-90");
        });
    });

    /* ----------------------------
       TOOLTIP (AUTO FROM .sidebar-text)
    ----------------------------- */
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el, {
            title: el.getAttribute('data-bs-title'),
            placement: 'right',          // paksa tampil di kanan
            container: 'body',          // render di body supaya tidak terpotong
            trigger: 'hover focus',     // hover + keyboard focus
            boundary: 'viewport',
            delay: { "show": 50, "hide": 0 }
        });
    });
});
