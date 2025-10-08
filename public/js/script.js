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

    function collapseSidebar() {
        sidebar.classList.replace(expandedWidth, collapsedWidth);
        sidebarTexts.forEach(t => t.classList.add("hidden"));

        logo.src = "/public/images/donna-icon.png";
        logo.style.width = "32px";
        logo.style.height = "32px";
        logo.style.margin = "0 auto"; // center
        logo.style.display = "block";

        profileIcon.classList.add("scale-90");
        toggleBtn.querySelector("i").setAttribute("data-feather", "chevron-right");

        // Tutup semua dropdown/submenu
        document.querySelectorAll("[data-collapse]").forEach(btn => {
            const target = document.getElementById(btn.dataset.collapse);
            const icon = btn.querySelector("i");
            target.classList.add("hidden");
            icon.classList.remove("rotate-90");
        });

        // Tutup documentsDropdown dan profileDropup jika sedang terbuka
        documentDropdown.classList.add("hidden");
        profileDropup.classList.add("hidden");

        feather.replace();
    }


    function expandSidebar() {
        sidebar.classList.replace(collapsedWidth, expandedWidth);
        sidebarTexts.forEach(t => t.classList.remove("hidden"));

        logo.src = "/public/images/donna.png";
        logo.style.width = "100%";
        logo.style.height = "100%";
        logo.style.margin = "0";
        logo.style.display = "block";

        profileIcon.classList.remove("scale-90");
        toggleBtn.querySelector("i").setAttribute("data-feather", "chevron-left");
        feather.replace();
    }

    toggleBtn.addEventListener("click", () => {
        if (sidebar.classList.contains(expandedWidth)) collapseSidebar();
        else expandSidebar();
    });

    toggleExternal.addEventListener("click", expandSidebar);

    document.getElementById("profileToggle").addEventListener("click", () => {
        if (sidebar.classList.contains(collapsedWidth)) expandSidebar();
        profileDropup.classList.toggle("hidden");
    });

    document.querySelectorAll("[data-collapse]").forEach(btn => {
        const target = document.getElementById(btn.dataset.collapse);
        const icon = btn.querySelector("i");

        btn.addEventListener("click", () => {
            if (sidebar.classList.contains(collapsedWidth)) expandSidebar();
            target.classList.toggle("hidden");
            icon.classList.toggle("rotate-90");
        });
    });
});
