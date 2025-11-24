document.addEventListener("DOMContentLoaded", () => {
    feather.replace(); // Render feather sekali saja

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebarTexts = document.querySelectorAll(".sidebar-text");
    const logo = document.getElementById("sidebarLogo");
    const profileIcon = document.getElementById("profileIcon");

    const collapsedWidth = "w-20";
    const expandedWidth = "w-64";

    /* ----------------------------
       SIDEBAR COLLAPSE / EXPAND
    ----------------------------- */

    function collapseSidebar() {
        sidebar.classList.remove(expandedWidth);
        sidebar.classList.add(collapsedWidth);

        sidebarTexts.forEach(t => t.classList.add("hidden"));

        logo.src = logo.dataset.icon;
        logo.style.width = "32px";
        logo.style.margin = "0 auto";

        profileIcon.classList.add("scale-90");
    }

    function expandSidebar() {
        sidebar.classList.remove(collapsedWidth);
        sidebar.classList.add(expandedWidth);

        sidebarTexts.forEach(t => t.classList.remove("hidden"));

        logo.src = logo.dataset.full;
        logo.style.width = "150px";
        logo.style.margin = "0";
    }

    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            if (sidebar.classList.contains(expandedWidth)) collapseSidebar();
            else expandSidebar();
        });
    }

    /* ----------------------------
       DROPDOWN COLLAPSE (MASTER)
    ----------------------------- */

    document.querySelectorAll(".collapse-toggle").forEach(btn => {
        const targetId = btn.dataset.collapse;
        const target = document.getElementById(targetId);

        btn.addEventListener("click", () => {

            // Jika sidebar collapsed → expand dulu
            if (sidebar.classList.contains(collapsedWidth)) {
                expandSidebar();
            }

            // Toggle submenu
            target.classList.toggle("hidden");

            // Rotate icon
            const icon = btn.querySelector("i[data-feather]");
            if (icon) {
                icon.classList.toggle("rotate-90");
            }
        });
    });
});
