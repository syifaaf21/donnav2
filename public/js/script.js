// Sidebar
document.addEventListener("DOMContentLoaded", () => {
    feather.replace();

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const toggleExternal = document.getElementById("sidebarToggleExternal");
    const sidebarTexts = document.querySelectorAll(".sidebar-text");
    const logo = document.getElementById("sidebarLogo");
    const documentDropdown = document.getElementById("documentsDropdown");
    const profileIcon = document.getElementById("profileIcon");
    const profileDropup = document.getElementById("profileDropup");

    const collapsedWidth = "w-20";
    const expandedWidth = "w-64";

    // Preload icon version supaya tak delay saat switch
    if (logo && logo.dataset && logo.dataset.icon) {
        const imgPre = new Image();
        imgPre.src = logo.dataset.icon;
    }

    function safeAddHidden(el) {
        if (!el) return;
        el.classList.add("hidden");
    }
    function safeToggle(el) {
        if (!el) return;
        el.classList.toggle("hidden");
    }

    function collapseSidebar() {
        // pastikan class replace aman
        if (sidebar.classList.contains(expandedWidth)) {
            sidebar.classList.replace(expandedWidth, collapsedWidth);
        } else {
            // jika tidak ada expandedWidth jangan error - tambahkan collapsed kalau belum ada
            sidebar.classList.add(collapsedWidth);
        }

        sidebarTexts.forEach(t => t.classList.add("hidden"));

        // switch logo ke icon (gunakan data attribute)
        if (logo && logo.dataset && logo.dataset.icon) {
            logo.src = logo.dataset.icon;
            logo.style.width = "32px";
            logo.style.height = "32px";
            logo.style.margin = "0 auto";
            logo.style.display = "block";
        }

        if (profileIcon) profileIcon.classList.add("scale-90");

        try {
            const iconEl = toggleBtn && toggleBtn.querySelector("i");
            if (iconEl) iconEl.setAttribute("data-feather", "chevron-right");
        } catch (e) { /* ignore */ }

        // Tutup semua dropdown/submenu (safe)
        document.querySelectorAll("[data-collapse]").forEach(btn => {
            const target = document.getElementById(btn.dataset.collapse);
            const icon = btn.querySelector("i");
            if (target) target.classList.add("hidden");
            if (icon) icon.classList.remove("rotate-90");
        });

        // Tutup documentsDropdown dan profileDropup jika ada (safe)
        safeAddHidden(documentDropdown);
        safeAddHidden(profileDropup);

        mainWrapper.classList.remove("ml-64");
        mainWrapper.classList.add("ml-32");
        if (navbar) navbar.classList.add("px-24");

        feather.replace();
    }

    function expandSidebar() {
        if (sidebar.classList.contains(collapsedWidth)) {
            sidebar.classList.replace(collapsedWidth, expandedWidth);
        } else {
            sidebar.classList.add(expandedWidth);
        }

        sidebarTexts.forEach(t => t.classList.remove("hidden"));

        if (logo && logo.dataset && logo.dataset.full) {
            logo.src = logo.dataset.full;
            logo.style.width = "150px";
            logo.style.height = "auto";
            logo.style.margin = "0";
            logo.style.display = "block";
        }

        if (profileIcon) profileIcon.classList.remove("scale-90");

        try {
            const iconEl = toggleBtn && toggleBtn.querySelector("i");
            if (iconEl) iconEl.setAttribute("data-feather", "chevron-left");
        } catch (e) { /* ignore */ }

        mainWrapper.classList.remove("ml-32");
        mainWrapper.classList.add("ml-64");
        if (navbar) navbar.classList.remove("px-24");

        feather.replace();
    }

    // event listeners (safe null-check)
    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            if (sidebar.classList.contains(expandedWidth)) collapseSidebar();
            else expandSidebar();
        });
    }

    if (toggleExternal) toggleExternal.addEventListener("click", expandSidebar);

    const profileToggle = document.getElementById("profileToggle");
    if (profileToggle) {
        profileToggle.addEventListener("click", () => {
            if (sidebar.classList.contains(collapsedWidth)) expandSidebar();
            safeToggle(profileDropup);
        });
    }

    document.querySelectorAll("[data-collapse]").forEach(btn => {
        const target = document.getElementById(btn.dataset.collapse);
        const icon = btn.querySelector("i");

        btn.addEventListener("click", () => {
            if (sidebar.classList.contains(collapsedWidth)) expandSidebar();
            if (target) target.classList.toggle("hidden");
            if (icon) icon.classList.toggle("rotate-90");
        });
    });
});



