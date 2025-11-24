document.addEventListener("DOMContentLoaded", () => {
    feather.replace(); // cukup sekali

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebarTexts = document.querySelectorAll(".sidebar-text");
    const logo = document.getElementById("sidebarLogo");
    const profileIcon = document.getElementById("profileIcon");

    function collapseSidebar() {
        sidebar.classList.add("w-20");
        sidebar.classList.remove("w-64");

        sidebarTexts.forEach(t => t.classList.add("hidden"));

        // change logo
        if (logo.dataset.icon) {
            console.log("Collapse → Ganti logo ke:", logo.dataset.icon); // DEBUG
            logo.src = logo.dataset.icon;
        }

        logo.style.width = "32px";
        logo.style.margin = "0 auto";

        profileIcon.classList.add("scale-90");
    }

    function expandSidebar() {
        sidebar.classList.add("w-64");
        sidebar.classList.remove("w-20");

        sidebarTexts.forEach(t => t.classList.remove("hidden"));

        if (logo.dataset.full) {
            console.log("Expand → Ganti logo ke:", logo.dataset.full); // DEBUG
            logo.src = logo.dataset.full;
        }

        logo.style.width = "150px";
        logo.style.margin = "0";

        profileIcon.classList.remove("scale-90");
    }

    toggleBtn.addEventListener("click", () => {
        if (sidebar.classList.contains("w-64")) {
            collapseSidebar();
        } else {
            expandSidebar();
        }
    });
});
