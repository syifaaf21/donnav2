document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('bsbSidebar');
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const toggleIcon = document.getElementById('sidebarToggleIcon');
  const navbar = document.getElementById('mainNavbar');
  const mainContent = document.getElementById('mainContent');
  const datetimeEl = document.getElementById('datetime');

  toggleBtn.addEventListener('click', function () {
    sidebar.classList.toggle('collapsed');

    // Ganti ikon tombol
    if (sidebar.classList.contains('collapsed')) {
      toggleIcon.classList.remove('bi-layout-sidebar-inset');
      toggleIcon.classList.add('bi-layout-sidebar-reverse');

      // Geser navbar dan konten ke kiri
      navbar.style.left = '70px';
      mainContent.style.marginLeft = '70px';
    } else {
      toggleIcon.classList.remove('bi-layout-sidebar-reverse');
      toggleIcon.classList.add('bi-layout-sidebar-inset');

      navbar.style.left = '250px';
      mainContent.style.marginLeft = '250px';
    }
  });

  // Waktu real-time
  function updateTime() {
    const now = new Date();
    datetimeEl.textContent = now.toLocaleString();
  }

  if (datetimeEl) {
    updateTime();
    setInterval(updateTime, 1000);
  }
});
