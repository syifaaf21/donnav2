document.addEventListener("DOMContentLoaded", () => {
    feather.replace();

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebarTexts = document.querySelectorAll(".sidebar-text");
    const logo = document.getElementById("sidebarLogo");
    const documentDropdown = document.getElementById("documentsDropdown");
    const profileIcon = document.getElementById("profileIcon");

    const collapsedWidth = "w-20";
    const expandedWidth = "w-64";

    // preload logo icon
    if (logo && logo.dataset && logo.dataset.icon) {
        const imgPre = new Image();
        imgPre.src = logo.dataset.icon;
    }

    function collapseSidebar() {
        if (sidebar.classList.contains(expandedWidth)) {
            sidebar.classList.replace(expandedWidth, collapsedWidth);
        } else {
            sidebar.classList.add(collapsedWidth);
        }

        sidebarTexts.forEach(t => t.classList.add("hidden"));

        // ganti logo ke icon
        if (logo && logo.dataset && logo.dataset.icon) {
            logo.src = logo.dataset.icon;
            logo.style.width = "32px";
            logo.style.height = "32px";
            logo.style.margin = "0 auto";
        }

        if (profileIcon) profileIcon.classList.add("scale-90");

        // Tutup dropdown dokumen kalau ada
        if (documentDropdown) documentDropdown.classList.add("hidden");

        // tetap chevron-left (tidak diubah ke kanan)
        const iconEl = toggleBtn && toggleBtn.querySelector("i");
        if (iconEl) iconEl.setAttribute("data-feather", "chevron-left");

        feather.replace();
    }

    function expandSidebar() {
        if (sidebar.classList.contains(collapsedWidth)) {
            sidebar.classList.replace(collapsedWidth, expandedWidth);
        } else {
            sidebar.classList.add(expandedWidth);
        }

        sidebarTexts.forEach(t => t.classList.remove("hidden"));

        // ganti logo ke versi penuh
        if (logo && logo.dataset && logo.dataset.full) {
            logo.src = logo.dataset.full;
            logo.style.width = "150px";
            logo.style.height = "auto";
            logo.style.margin = "0";
        }

        if (profileIcon) profileIcon.classList.remove("scale-90");

        // tetap chevron-left
        const iconEl = toggleBtn && toggleBtn.querySelector("i");
        if (iconEl) iconEl.setAttribute("data-feather", "chevron-left");

        feather.replace();
    }

    // event toggle sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            if (sidebar.classList.contains(expandedWidth)) collapseSidebar();
            else expandSidebar();
        });
    }

    // handle dropdown menu (tanpa rotasi / ubah icon)
    document.querySelectorAll("[data-collapse]").forEach(btn => {
        const target = document.getElementById(btn.dataset.collapse);

        btn.addEventListener("click", () => {
            if (sidebar.classList.contains(collapsedWidth)) expandSidebar();
            if (target) target.classList.toggle("hidden");
            btn.classList.toggle("active");
        });
    });
});
