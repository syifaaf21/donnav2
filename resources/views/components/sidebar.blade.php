<!-- Sidebar -->
<aside id="bsbSidebar" class="bsb-sidebar-1">
  <div class="offcanvas-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
    <a class="sidebar-brand" href="#!">
      <img src="{{ asset('images/donna-logo.png') }}" id="bsbSidebarLabel1" class="img-fluid" alt="Donna Logo" width="135" height="44">
    </a>
  </div>

  <div class="offcanvas-body pt-0 px-0">
    <hr class="sidebar-divider mb-3">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link p-3 {{ request()->is('/') ? 'active bg-light rounded' : '' }}" href="/">
          <div class="nav-link-icon text-primary">
            <i class="bi bi-house-gear"></i>
          </div>
          <span class="nav-link-text fw-bold">Dashboards</span>
        </a>
      </li>
      <li class="nav-item mt-3">
        <h6 class="py-1 text-secondary text-uppercase fs-7">Docs</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link p-3" href="#controlExamples" role="button" aria-expanded="false" aria-controls="docsExamples">
          <div class="nav-link-icon text-info">
            <i class="bi bi-gear"></i>
          </div>
          <span class="nav-link-text fw-bold">Document Control</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link p-3" href="#reviewExamples" role="button" aria-expanded="false" aria-controls="reviewExamples">
          <div class="nav-link-icon text-secondary">
            <i class="bi bi-clipboard-check"></i>
          </div>
          <span class="nav-link-text fw-bold">Document Review</span>
        </a>
      </li>

      <hr class="sidebar-divider my-4">

      <li class="nav-item">
        <a class="nav-link p-3 collapsed" data-bs-toggle="collapse" href="#masterExamples" role="button" aria-expanded="false" aria-controls="masterExamples">
          <div class="nav-link-icon text-danger">
            <i class="bi bi-database-check"></i>
          </div>
          <span class="nav-link-text fw-bold">Master Data</span>
        </a>
        <div class="collapse" id="masterExamples">
          <ul class="nav flex-column sidebar-submenu">
            <li class="nav-item">
              <a class="nav-link {{ Route::is('users.*') ? 'active bg-light rounded' : '' }}" href="{{ route('users.index') }}">
                <div class="nav-link-icon" style="color: cornflowerblue;">
                  <i class="bi bi-person"></i>
                </div>
                <span class="nav-link-text">Users</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#!">
                <div class="nav-link-icon text-success">
                  <i class="bi bi-box-seam"></i>
                </div>
                <span class="nav-link-text">Product</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ Route::is('documents.*') ? 'active bg-light rounded' : '' }}" href="{{ route('documents.index') }}">
                <div class="nav-link-icon text-warning">
                  <i class="bi bi-file-earmark-text"></i>
                </div>
                <span class="nav-link-text">Documents</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>

    <hr class="sidebar-divider my-4">
  </div>

  <!-- Collapse Button -->
  <button id="sidebarToggleBtn" class="btn btn-light border position-absolute toggle-sidebar-btn" title="Toggle Sidebar">
    <i id="sidebarToggleIcon" class="bi bi-layout-sidebar-inset fs-5"></i>
  </button>
</aside>

<!-- NAVBAR -->
{{-- <nav class="navbar navbar-light bg-white border-bottom shadow-sm px-4 py-2 position-fixed w-100" style="left: 250px; z-index:1030; transition: left 0.3s ease;" id="mainNavbar">
  <div class="d-flex justify-content-between align-items-center w-100">
    <div class="page-title fw-bold fs-5">@yield('title')</div>
    <div class="d-flex align-items-center gap-3">
      <!-- Datetime -->
      <span id="datetime" class="text-muted small"></span>

      <!-- Notification -->
      <a href="#!" class="text-dark position-relative">
        <i class="bi bi-bell fs-5"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          3
        </span>
      </a>

      <!-- Profile -->
      <a href="#!" class="d-flex align-items-center text-decoration-none">
        <i class="bi bi-person-circle fs-5 text-dark"></i>
      </a>
    </div>
  </div>
</nav> --}}

{{-- <!-- Content Wrapper -->
<div id="mainContent" class="pt-2" style="margin-left: 200px;">
  @yield('content')
</div> --}}

