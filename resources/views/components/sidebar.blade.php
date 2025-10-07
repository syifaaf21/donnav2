<!-- Sidebar -->
<aside id="bsbSidebar" class="bsb-sidebar-1 bg-transparent">
    <div class="offcanvas-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
        <a class="sidebar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/donna-logo.png') }}" id="bsbSidebarLabel1" class="logo" alt="Donna Logo">
        </a>
    </div>

    <div class="offcanvas-body pt-0 px-0">
        <hr class="sidebar-divider mb-1">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link p-3 {{ request()->is('/') ? 'active bg-light rounded' : '' }}"
                    href="{{ route('dashboard') }}">
                    <div class="nav-link-icon text-primary">
                        <i class="bi bi-house-gear"></i>
                    </div>
                    <span class="nav-link-text fw-bold">Dashboards</span>
                </a>
            </li>
            <li class="nav-item mt-1">
                <h6 class="py-1 text-secondary text-uppercase fs-7">Docs</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link p-3 {{ Route::is('document-control') ? 'active bg-light rounded' : '' }}"
                    href="{{ route('document-control.index') }}">
                    <div class="nav-link-icon text-info">
                        <i class="bi bi-gear"></i>
                    </div>
                    <span class="nav-link-text fw-bold">Document Control</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link p-3 {{ Route::is('document-review') ? 'active bg-light rounded' : '' }}"
                    href="{{ route('document-review.index') }}">
                    <div class="nav-link-icon text-secondary">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <span class="nav-link-text fw-bold">Document Review</span>
                </a>
            </li>


            <hr class="sidebar-divider my-2">

            <!-- Master Data -->
            <li class="nav-item">
                <a class="nav-link p-3 collapsed" data-bs-toggle="collapse" href="#collapseMasterData" role="button"
                    aria-expanded="false" aria-controls="collapseMasterData">
                    <div class="nav-link-icon text-danger">
                        <i class="bi bi-database-check"></i>
                    </div>
                    <span class="nav-link-text fw-bold">Master Data</span>
                </a>

                <div class="collapse" id="collapseMasterData">
                    <ul class="nav flex-column sidebar-submenu">
                        <!-- Users -->
                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('users.*') ? 'active bg-light rounded' : '' }}"
                                href="{{ route('master.users.index') }}">
                                <div class="nav-link-icon" style="color: cornflowerblue;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <span class="nav-link-text">Users</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('part_numbers.*') ? 'active bg-light rounded' : '' }}"
                                href="{{ route('master.part_numbers.index') }}">
                                <div class="nav-link-icon text-success">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <span class="nav-link-text">Part Numbers</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Route::is('hierarchy.*') ? 'active bg-light rounded' : '' }}"
                                href="{{ route('master.hierarchy.index') }}">
                                <div class="nav-link-icon text-warning">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <span class="nav-link-text">Hierarchy</span>
                            </a>
                        </li>

                        {{-- Dropdown for Documents --}}
                        <li class="nav-item">
                            <a class="nav-link p-3 collapsed" data-bs-toggle="collapse" href="#documentsDropdown"
                                role="button"
                                aria-expanded="{{ Route::is('document-control.*', 'document-review.*') ? 'true' : 'false' }}"
                                aria-controls="documentsDropdown">
                                <div class="nav-link-icon text-danger">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <span class="nav-link-text fw-bold">Documents</span>
                            </a>

                            <div class="collapse {{ Route::is('document-control.*', 'document-review.*') ? 'show' : '' }}"
                                id="documentsDropdown">
                                <ul class="nav flex-column sidebar-submenu">
                                    <li class="nav-item">
                                        <a class="nav-link {{ Route::is('document-control.*') ? 'active bg-light rounded' : '' }}"
                                            href="{{ route('master.document-control.index') }}">
                                            <div class="nav-link-icon text-info">
                                                <i class="bi bi-gear"></i>
                                            </div>
                                            <span class="nav-link-text">Control</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ Route::is('document-review.*') ? 'active bg-light rounded' : '' }}"
                                            href="{{ route('master.document-review.index') }}">
                                            <div class="nav-link-icon text-secondary">
                                                <i class="bi bi-search me-1"></i>
                                            </div>
                                            <span class="nav-link-text">Review</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    </ul>
                </div>
            </li>
        </ul>
        <hr class="sidebar-divider my-4">
    </div>

    <!-- Collapse Button -->
    <button id="sidebarToggleBtn" class="btn btn-light border position-absolute toggle-sidebar-btn"
        title="Toggle Sidebar">
        <i id="sidebarToggleIcon" class="bi bi-layout-sidebar-inset fs-5"></i>
    </button>
</aside>
