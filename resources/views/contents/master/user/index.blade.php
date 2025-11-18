@extends('layouts.app')
@section('title', 'User')

@section('content')
    <div class="px-6 py-4">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-4">
            <nav class="text-sm text-gray-500">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                            <i class="bi bi-house-door"></i> Dashboard</a>
                    </li>
                    <li>/</li>
                    <li>Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">User</li>
                </ol>
            </nav>

            {{-- Add User Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#addUserModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add User</span>
            </button>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-tab-pane"
                    type="button" role="tab" aria-controls="all-tab-pane" aria-selected="true">
                    All Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="depthead-tab" data-bs-toggle="tab" data-bs-target="#depthead-tab-pane"
                    type="button" role="tab" aria-controls="depthead-tab-pane" aria-selected="false">
                    Department Heads
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="auditor-tab" data-bs-toggle="tab" data-bs-target="#auditor-tab-pane"
                    type="button" role="tab" aria-controls="auditor-tab-pane" aria-selected="false">
                    Auditors
                </button>
            </li>
        </ul>
        {{-- Tab Contents --}}
        <div class="tab-content" id="userTabsContent">
            <div class="tab-pane fade show active" id="all-tab-pane" role="tabpanel" aria-labelledby="all-tab">
                <div id="ajaxUserTableAll">
                    @include('contents.master.user.partials.all')
                </div>
            </div>
            <div class="tab-pane fade" id="depthead-tab-pane" role="tabpanel" aria-labelledby="depthead-tab">
                <div id="ajaxUserTableDeptHead">
                    @include('contents.master.user.partials.dept-head')
                </div>
            </div>
            <div class="tab-pane fade" id="auditor-tab-pane" role="tabpanel" aria-labelledby="auditor-tab">
                <div id="ajaxUserTableAuditor">
                    @include('contents.master.user.partials.auditor')
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
                new bootstrap.Modal(document.getElementById("editUserModal-{{ session('edit_modal') }}")).show();
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
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or search a role',
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
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                placeholder: 'Select or search a department',
                load: function(query, callback) {
                    let url = '/api/departments?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                }
            });

            // TomSelect untuk modal Edit (semua modal edit role select)
            document.querySelectorAll('select[id^="role_select_edit_"]').forEach(function(el) {
                new TomSelect(el, {
                    create: false, // TIDAK boleh create baru
                    maxItems: 1,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select a role',
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
                    create: false, // TIDAK boleh create baru
                    maxItems: 1,
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    preload: true,
                    placeholder: 'Select a department',
                    load: function(query, callback) {
                        let url = '/api/departments?q=' + encodeURIComponent(query);
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
                            maxItems: 1
                        });
                        new TomSelect(`#department_select_edit_${userId}`, {
                            create: false,
                            maxItems: 1
                        });

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
