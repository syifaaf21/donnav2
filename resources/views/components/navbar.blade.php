<head>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 py-2 shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">

            <!-- Left: Page Title -->
            <div class="d-flex align-items-center">
                <h5 class="mb-0 fw-semibold"></h5>
            </div>

            <!-- Center: Breadcrumb -->
            <div class="d-none d-md-block">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                    </ol>
                </nav>
            </div>

            <!-- Right: Notification + User -->
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Icon -->
                <a href="#" class="text-dark position-relative">
                    <i class="bi bi-bell fs-5"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                </a>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://ui-avatars.com/api/?" alt="avatar"
                            class="rounded-circle me-2" width="32" height="32">
                        <div class="d-none d-md-block text-start">
                            <div class="fw-semibold"></div>
                            <small class="text-muted"></small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Logout</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </nav>
</head>
