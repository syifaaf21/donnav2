@extends('layouts.app')
@section('title', 'Master User')
@section('subtitle', 'Manage users')
@section('breadcrumbs')
    <nav class="text-xs text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
        <ol class="list-reset flex space-x-2">
            <li>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </li>
            <li>/</li>
            <li class="text-gray-500 font-medium">Master</li>
            <li>/</li>
            <li class="text-gray-700 font-bold">User</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="px-6 bg-white rounded-lg shadow">
        {{-- Header --}}
        {{-- <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2 text-white">
                    <h3 class="fw-bold">Master User</h3>
                    <p class="text-sm" style="font-size: 0.85rem;">
                        Use this page to manage user master data, including Department Heads and Auditors.
                    </p>
                </div>
            </div>
            <nav class="text-sm text-gray-500 bg-white rounded-full pt-3 pb-1 pr-6 shadow w-fit mb-1" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-gray-500 font-medium">Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-bold">User</li>
                </ol>
            </nav>
        </div> --}}

        {{-- Enhanced Tabs Container --}}
        <div class="p-3">
            <div class="pt-2 mx-8">
                <div class="flex items-center justify-between gap-4">
                    <nav class="flex-1-mb-px mt-6" aria-label="User categories">
                        <ul class="nav nav-tabs" id="userTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-t-lg" id="all-tab" data-bs-toggle="tab"
                                    data-bs-target="#all-tab-pane" type="button" role="tab"
                                    aria-controls="all-tab-pane" aria-selected="true">
                                    <i class="bi bi-people-fill me-1"></i>
                                    <span class="align-middle">All Users</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-t-lg" id="depthead-tab" data-bs-toggle="tab"
                                    data-bs-target="#depthead-tab-pane" type="button" role="tab"
                                    aria-controls="depthead-tab-pane" aria-selected="false">
                                    <i class="bi bi-person-badge me-1"></i>
                                    <span class="align-middle">Department Heads</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-t-lg" id="auditor-tab" data-bs-toggle="tab"
                                    data-bs-target="#auditor-tab-pane" type="button" role="tab"
                                    aria-controls="auditor-tab-pane" aria-selected="false">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <span class="align-middle">Auditors</span>
                                </button>
                            </li>
                        </ul>
                    </nav>

                    <div class="text-sm text-gray-500 ml-4">
                        {{-- Add User Button aligned to the right --}}
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addUserModal"
                            class="px-3 py-2.5 bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add User</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tab Contents (unchanged logic / IDs) --}}
            <div>
                <div class="tab-content" id="userTabsContent" role="tablist" aria-live="polite">
                    <div class="tab-pane fade show active" id="all-tab-pane" role="tabpanel" aria-labelledby="all-tab">
                        <div id="ajaxUserTableAll" class="space-y-3">
                            @include('contents.master.user.partials.all')
                        </div>
                    </div>
                    <div class="tab-pane fade" id="depthead-tab-pane" role="tabpanel" aria-labelledby="depthead-tab">
                        <div id="ajaxUserTableDeptHead" class="space-y-3">
                            @include('contents.master.user.partials.dept-head')
                        </div>
                    </div>
                    <div class="tab-pane fade" id="auditor-tab-pane" role="tabpanel" aria-labelledby="auditor-tab">
                        <div id="ajaxUserTableAuditor" class="space-y-3">
                            @include('contents.master.user.partials.auditor')
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    {{-- Include modal Add/Edit (sama seperti kode kamu sebelumnya) --}}
    @include('contents.master.user.partials.modal-add')
    @include('contents.master.user.partials.modal-edit')
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    <script>
        // Clear Search functionality
        document.addEventListener("DOMContentLoaded", function() {
            const clearBtn = document.getElementById("clearSearch");
            const searchInput = document.getElementById("searchInput");
            const searchForm = document.getElementById("searchForm");

            if (clearBtn && searchInput && searchForm) {
                clearBtn.addEventListener("click", function() {
                    searchInput.value = "";
                    searchForm.submit();
                });
            }

            const forms = document.querySelectorAll('.needs-validation');

            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault(); // Stop form submit
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated'); // Tambahkan class validasi Bootstrap
                }, false);
            });
        });

        //Tooltip
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el, {
                    title: el.getAttribute('data-bs-title'),
                    placement: 'top',
                    trigger: 'hover'
                });
            });
        });
    </script>
    @if ($errors->any() && session('edit_modal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const userId = "{{ session('edit_modal') }}";
                const modalEl = document.getElementById('editUserModal');
                const modalContent = document.getElementById('editUserModalContent');

                if (!modalEl || !modalContent) {
                    console.warn('Edit modal element not found: editUserModal');
                    return;
                }

                const modalInstance = new bootstrap.Modal(modalEl);
                modalContent.innerHTML = '<div class="p-5 text-center text-gray-500">Loading...</div>';
                modalInstance.show();

                // Load the edit form via AJAX so validation errors are shown inside modal
                fetch(`/master/users/${userId}/edit`)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch edit form');
                        return response.text();
                    })
                    .then(html => {
                        modalContent.innerHTML = html;
                        try {
                            // attempt to reinitialize TomSelects if present
                            if (typeof TomSelect !== 'undefined') {
                                try {
                                    new TomSelect(`#role_select_edit_${userId}`, {
                                        create: false,
                                        maxItems: null
                                    });
                                } catch (e) {}
                                try {
                                    new TomSelect(`#department_select_edit_${userId}`, {
                                        create: false,
                                        maxItems: 1
                                    });
                                } catch (e) {}
                                try {
                                    // Ensure audit type select also becomes TomSelect when validation returns the form
                                    new TomSelect(`#audit_type_select_edit_${userId}`, {
                                        create: false,
                                        maxItems: null
                                    });
                                } catch (e) {}
                            }
                        } catch (e) {
                            console.warn('TomSelect init failed', e);
                        }
                        if (typeof feather !== 'undefined') feather.replace();
                    })
                    .catch(err => {
                        modalContent.innerHTML =
                            '<div class="p-5 text-center text-red-500">Failed to load edit form.</div>';
                        console.error(err);
                    });
            });
        </script>
    @endif

    @if ($errors->any() && old('_form') === 'add')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new bootstrap.Modal(document.getElementById("addUserModal")).show();
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // TomSelect untuk modal Add - Role
            new TomSelect('#role_select', {
                create: false,
                // allow multiple selections by not limiting maxItems
                maxItems: null,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                plugins: ['remove_button'],
                preload: true,
                placeholder: 'Select or search roles',
                load: function(query, callback) {
                    let url = '/api/roles?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            // TomSelect untuk modal Add - Department
            new TomSelect('#department_select', {
                create: false,
                maxItems: null,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or search departments',
                load: function(query, callback) {
                    let url = '/api/departments?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            // TomSelect untuk modal Add - Audit Type
            new TomSelect('#audit_type_select', {
                create: false,
                maxItems: null,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or search audit types',
                load: function(query, callback) {
                    let url = '/api/audit-types?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            // TomSelect untuk modal Edit (semua modal edit role select)
            document.querySelectorAll('select[id^="role_select_edit_"]').forEach(function(el) {
                new TomSelect(el, {
                    create: false,
                    maxItems: null,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select roles',
                    plugins: ['remove_button'],
                    load: function(query, callback) {
                        let url = '/api/roles?q=' + encodeURIComponent(query);
                        fetch(url)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    }
                });
            });

            // TomSelect untuk modal Edit (semua modal edit department select)
            document.querySelectorAll('select[id^="department_select_edit_"]').forEach(function(el) {
                new TomSelect(el, {
                    create: false,
                    maxItems: null,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select departments',
                    load: function(query, callback) {
                        let url = '/api/departments?q=' + encodeURIComponent(query);
                        fetch(url)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    }
                });
            });

            // TomSelect untuk modal Edit (semua modal edit audit type select)
            document.querySelectorAll('select[id^="audit_type_select_edit_"]').forEach(function(el) {
                new TomSelect(el, {
                    create: false,
                    maxItems: null,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select audit types',
                    load: function(query, callback) {
                        let url = '/api/audit-types?q=' + encodeURIComponent(query);
                        fetch(url)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Utility: ambil active tab key (all | depthead | auditor)
            function getActiveTabKey() {
                const id = $('.nav-link.active').attr('id') || 'all-tab';
                return id.replace('-tab', '');
            }

            // Trigger when bootstrap tab becomes visible -> load page 1 untuk tab itu
            $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function(e) {
                const tabKey = getActiveTabKey();
                const perPage = $('#perPageSelect').val() || 10;
                const url = "{{ route('master.users.index') }}" + "?tab=" + tabKey + "&per_page=" + perPage;
                fetchUserData(url);
            });

            // Clear search (delegated) -> langsung fetch tab page 1 with empty search
            $(document).on('click', '.clearSearch', function() {
                const $form = $(this).closest('form');
                $form.find('.searchInput').val('');
                const tabKey = getActiveTabKey();
                const perPage = $('#perPageSelect').val() || 10;
                const url = "{{ route('master.users.index') }}" + "?tab=" + tabKey + "&per_page=" +
                    perPage + "&search=";
                fetchUserData(url);
            });

            // Search form submit -> AJAX (ke page 1)
            $(document).on('submit', '.searchForm', function(e) {
                e.preventDefault();
                const $form = $(this);
                const query = $form.find('.searchInput').val();
                const tabKey = getActiveTabKey();
                const perPage = $('#perPageSelect').val() || 10;
                const url = "{{ route('master.users.index') }}" + "?tab=" + tabKey + "&per_page=" +
                    perPage + "&search=" + encodeURIComponent(query);
                fetchUserData(url);
            });

            // per-page dropdown change
            $(document).on('change', '#perPageSelect', function() {
                const perPage = $(this).val();
                const tabKey = getActiveTabKey();
                const url = "{{ route('master.users.index') }}" + "?tab=" + tabKey + "&per_page=" + perPage;
                fetchUserData(url);
            });

            // Pagination link click (delegated)
            $(document).on('click', '#pagination-links a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href'); // might include ?page=2
                // ensure tab & per_page ada
                const tabKey = getActiveTabKey();
                const perPage = $('#perPageSelect').val() || 10;
                const sep = url.includes('?') ? '&' : '?';
                url = url + sep + 'tab=' + tabKey + '&per_page=' + perPage;
                fetchUserData(url);
            });

            // Central AJAX function
            function fetchUserData(url) {
                const activeTabPane = $('.tab-pane.active').find('[id^="ajaxUserTable"]');

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'html',
                    beforeSend: function() {
                        activeTabPane.html(
                            '<div class="text-center py-4 text-gray-500">Loading...</div>');
                    },
                    success: function(data) {
                        // replace content of the active tab container with returned partial HTML
                        activeTabPane.html(data);

                        // reinitialize icons / tooltip / tomselect for any elements in returned HTML if needed
                        if (typeof feather !== 'undefined') feather.replace();

                        // reinit tooltips for newly injected elements
                        const tooltipList = [].slice.call(activeTabPane.find('[data-bs-title]'));
                        tooltipList.map(function(el) {
                            return new bootstrap.Tooltip(el, {
                                title: el.getAttribute('data-bs-title'),
                                placement: 'top',
                                trigger: 'hover'
                            });
                        });
                    },
                    error: function(xhr) {
                        console.error('AJAX error:', xhr.responseText || xhr.statusText);
                        activeTabPane.html(
                            '<div class="text-center py-4 text-red-500">Failed to load data.</div>');
                    }
                });
            }

            // initial: ensure perPageSelect exists; if not, default behavior still works
        });

        // Modal Edit Handler (delegated)
        $(document).on('click', '.btn-edit-user', function() {
            const userId = $(this).data('id');
            const modalEl = document.getElementById(`editUserModal`);
            const modal = new bootstrap.Modal(modalEl);
            const modalContent = $('#editUserModalContent');

            modalContent.html('<div class="p-5 text-center text-gray-500">Loading...</div>');
            modal.show();

            // Tambahkan sedikit delay untuk memastikan modal siap tampil di tab nonaktif
            setTimeout(() => {
                $.ajax({
                    url: `/master/users/${userId}/edit`,
                    type: 'GET',
                    success: function(response) {
                        modalContent.html(response);

                        // Reinit TomSelect untuk select di dalam modal edit
                        new TomSelect(`#role_select_edit_${userId}`, {
                            create: false,
                            maxItems: null,
                            placeholder: 'Select roles'
                        });
                        new TomSelect(`#department_select_edit_${userId}`, {
                            create: false,
                            maxItems: null,
                            placeholder: 'Select departments'
                        });

                        // TomSelect untuk audit type di modal edit
                        const auditTypeSelect = document.getElementById(`audit_type_select_edit_${userId}`);
                        if (auditTypeSelect) {
                            new TomSelect(`#audit_type_select_edit_${userId}`, {
                                create: false,
                                maxItems: null,
                                placeholder: 'Select audit types'
                            });
                        }

                        if (typeof feather !== 'undefined') feather.replace();
                    },
                    error: function(xhr) {
                        modalContent.html(
                            '<div class="p-5 text-center text-red-500">Failed to load edit form.</div>'
                        );
                        console.error(xhr.responseText);
                    }
                });
            }, 150); // ← delay 150ms, cukup supaya modal benar-benar muncul
        });
    </script>

@endpush

<style>
    /* Remove default tab container border */
    #userTabs {
        border-bottom: none !important;
    }

    /* Base styling for all tabs: default text-white, no bottom border */
    #userTabs .nav-link {
        color: #373737 !important;
        /* default text-white */
        background: transparent;
        border: none !important;
        /* remove any borders including bottom */
        padding: .5rem 1rem;
        transition: all .15s ease-in-out;
    }

    /* Hover style (subtle) */
    #userTabs .nav-link:hover {
        color: #e6eefc !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);
        transform: translateY(-1px);
    }

    /* Active tab: text-gray-700 and keep subtle background */
    #userTabs .nav-link.active {
        color: #374151 !important;
        /* text-gray-700 */
        background: linear-gradient(to bottom, #bfdbfe 0%, #ffffff 100%);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        transform: translateY(-2px);
        font-weight: 600;
    }

    /* Default border */
    #addUserModal input.form-control,
    #addUserModal select.form-select {
        border: 1px solid #d1d5db !important;
        /* abu-abu halus */
        box-shadow: none !important;
    }

    /* Hover (opsional) */
    #addUserModal input.form-control:hover,
    #addUserModal select.form-select:hover {
        border-color: #bfc3ca !important;
    }

    /* Fokus / diklik */
    #addUserModal input.form-control:focus,
    #addUserModal select.form-select:focus {
        border-color: #3b82f6 !important;
        /* biru */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
        /* efek biru lembut */
    }

    [id^="editUserModal"] input.form-control,
    [id^="editUserModal"] select.form-select {
        border: 1px solid #d1d5db !important;
        box-shadow: none !important;
    }

    /* Hover */
    [id^="editUserModal"] input.form-control:hover,
    [id^="editUserModal"] select.form-select:hover {
        border-color: #bfc3ca !important;
    }

    /* Fokus */
    [id^="editUserModal"] input.form-control:focus,
    [id^="editUserModal"] select.form-select:focus {
        border-color: #3b82f6 !important;
        /* biru */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
    }

</style>
