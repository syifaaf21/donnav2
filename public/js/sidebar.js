document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('bsbSidebar'); // Sidebar element
  const toggleBtn = document.getElementById('sidebarToggleBtn'); // Tombol untuk toggle sidebar
  const toggleIcon = document.getElementById('sidebarToggleIcon'); // Ikon untuk toggle
  const navbar = document.getElementById('mainNavbar'); // Navbar element
  const mainContent = document.getElementById('mainContent'); // Konten utama
  const datetimeEl = document.getElementById('datetime'); // Elemen waktu (jika ada)

  // Fungsi untuk menangani tombol toggle
  toggleBtn.addEventListener('click', function () {
    sidebar.classList.toggle('collapsed'); // Toggle class "collapsed" pada sidebar
    document.body.classList.toggle('sidebar-collapsed', sidebar.classList.contains('collapsed')); // Menambahkan class ke body agar konten ikut bergerak

    // Ganti ikon tombol berdasarkan status sidebar
    if (sidebar.classList.contains('collapsed')) {
      toggleIcon.classList.remove('bi-layout-sidebar-inset'); // Hapus ikon sidebar-inset
      toggleIcon.classList.add('bi-layout-sidebar-reverse'); // Tambahkan ikon sidebar-reverse

      // Geser navbar dan konten
      navbar.style.left = '100px'; // Geser navbar ke kiri
      mainContent.style.marginLeft = '100px'; // Geser konten ke kiri
    } else {
      toggleIcon.classList.remove('bi-layout-sidebar-reverse'); // Hapus ikon sidebar-reverse
      toggleIcon.classList.add('bi-layout-sidebar-inset'); // Tambahkan ikon sidebar-inset

      // Kembalikan posisi navbar dan konten
      navbar.style.left = '250px'; // Posisi navbar kembali
      mainContent.style.marginLeft = '250px'; // Posisi konten kembali
    }
  });

  // Fungsi untuk update waktu (jika ada elemen waktu di halaman)
  function updateTime() {
    const now = new Date();
    datetimeEl.textContent = now.toLocaleString(); // Update waktu dengan format lokal
  }

  if (datetimeEl) {
    updateTime();
    setInterval(updateTime, 1000); // Update waktu setiap detik
  }
});
