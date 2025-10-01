<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm px-3 py-2">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">

        {{-- LEFT: Page Title --}}
        <div class="d-flex align-items-center">
            @php
                $routeName = Route::currentRouteName();
                $titles = [
                    'dashboard' => 'Dashboard',
                    'users.index' => 'User List',
                    'documents.index' => 'Document List',
                    'documents.show' => 'Child Document',
                    'part_numbers.index' => 'Part Number List',
                    'document-review.index' => 'Document Review'
                ];
                $pageTitle = $titles[$routeName] ?? ucwords(str_replace('.', ' ', $routeName));
            @endphp

            <h4 class="mb-0 fw-semibold">{{ $pageTitle }}</h4>

        </div>

        {{-- CENTER: Current Date & Time --}}
        <div class="text-center mx-auto d-none d-md-block">
            <div id="currentDatetime" class="text-muted small fw-medium"></div>
        </div>

        {{-- RIGHT: Notification, User Dropdown, Logo --}}
        <div class="d-flex align-items-center gap-3 ms-auto">

            {{-- Notification Icon --}}
            <a href="#" class="text-dark position-relative">
                <i class="bi bi-bell fs-5"></i>
                <span
                    class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </a>

            {{-- Profile Dropdown --}}
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                    id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff"
                        alt="avatar" class="rounded-circle me-2" width="32" height="32">
                    <div class="d-none d-md-block text-start">
                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                        <small class="text-muted">Logged in</small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

@push('scripts')
    <script>
        // Show current date and time (updates every second)
        function updateTime() {
            const now = new Date();
            const options = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            const dateStr = now.toLocaleDateString('en-US', options);
            const timeStr = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.getElementById('currentDatetime').textContent = `${dateStr}, ${timeStr}`;
        }

        setInterval(updateTime, 1000);
        updateTime(); // Initial call
    </script>
@endpush
