@extends('layouts.app')
@section('title', 'Recycle Bin')
@section('subtitle', 'Deleted master data (Super Admin only)')

@section('content')
    <div class="mx-auto px-6 bg-white rounded-xl py-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Recycle Bins</h3>
        </div>

        <form method="POST" action="{{ route('recycle.bulk-restore') }}" id="bulk-restore-form" class="d-none">
            @csrf
        </form>

        <form method="POST" action="{{ route('recycle.bulk-force-delete') }}" id="bulk-delete-form" class="d-none">
            @csrf
        </form>

        <div id="bulk-action-bar"
            style="position:fixed;left:50%;bottom:24px;transform:translateX(-50%) translateY(18px);opacity:0;pointer-events:none;z-index:1060;transition:all .18s ease;">
            <div class="d-flex align-items-center"
                style="gap:10px;background:rgba(15,23,42,.92);color:#fff;border-radius:999px;padding:10px 14px;box-shadow:0 12px 28px rgba(2,6,23,.28);">
                <span id="bulk-selected-count" style="font-size:.86rem;font-weight:600;white-space:nowrap;">0 item dipilih</span>
                <button type="button" id="bulk-restore-btn" onclick="confirmBulkRestore()"
                    class="d-inline-flex align-items-center justify-content-center"
                    style="height:34px;padding:0 12px;border-radius:999px;color:#fff;border:none;background-image:linear-gradient(90deg, rgba(147,51,234,0.88), rgba(124,58,237,0.98));opacity:.55;cursor:not-allowed;"
                    disabled>
                    Bulk Restore
                </button>
                <button type="button" id="bulk-delete-btn" onclick="confirmBulkForceDelete()"
                    class="d-inline-flex align-items-center justify-content-center"
                    style="height:34px;padding:0 12px;border-radius:999px;color:#fff;border:none;background:#dc2626;opacity:.55;cursor:not-allowed;"
                    disabled>
                    Bulk Delete
                </button>
            </div>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm shadow-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="text-xs text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left" style="width:42px;">
                            <input type="checkbox" id="select-all-recycle" aria-label="Select all items">
                        </th>
                        <th class="px-3 py-2 text-left">Document Name</th>
                        <th class="px-3 py-2 text-left">Document No</th>
                        <th class="px-3 py-2 text-left">Registration No</th>
                        <th class="px-3 py-2 text-left">Department</th>
                        <th class="px-3 py-2 text-left">Scheduled Deletion</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($docs as $doc)
                        @php
                            $rowResource = $doc->resource ?? (($resource ?? 'all') === 'ftpp' ? 'ftpp' : 'mapping');
                        @endphp
                        <tr class="border-t">
                            <td class="px-3 py-2">
                                <input type="checkbox" class="bulk-item-checkbox" value="{{ $rowResource }}:{{ $doc->id }}"
                                    aria-label="Select item {{ $doc->id }}">
                            </td>
                            <td class="px-3 py-2">{{ optional($doc->document)->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $doc->document_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $doc->registration_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ optional($doc->department)->name ?? '—' }}</td>
                            <td class="px-3 py-2">
                                @if ($doc->marked_for_deletion_at)
                                    {{ \Carbon\Carbon::parse($doc->marked_for_deletion_at)->toDateString() }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <div class="d-flex align-items-center" style="gap:8px;">
                                    <form method="POST" action="{{ route('recycle.restore', ['id' => $doc->id]) }}"
                                        id="restore-form-{{ $rowResource }}-{{ $doc->id }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="resource" value="{{ $rowResource }}">
                                        <button type="button" onclick="confirmRestore('{{ $rowResource }}', {{ $doc->id }})"
                                            class="d-inline-flex align-items-center justify-content-center" aria-label="Restore"
                                            title="Restore"
                                            style="width:36px;height:36px;border-radius:18px;color:#fff;border:none;display:inline-flex;align-items:center;justify-content:center;background-image:linear-gradient(90deg, rgba(147,51,234,0.16), rgba(124,58,237,0.26));box-shadow:0 6px 14px rgba(124,58,237,0.12);transition:transform .12s ease;">
                                            <i class="bi bi-arrow-counterclockwise" style="font-size:1.05rem;line-height:1"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('recycle.force-delete', ['id' => $doc->id]) }}"
                                        id="delete-form-{{ $rowResource }}-{{ $doc->id }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="resource" value="{{ $rowResource }}">
                                        <button type="button" onclick="confirmForceDelete('{{ $rowResource }}', {{ $doc->id }})"
                                            class="d-inline-flex align-items-center justify-content-center" aria-label="Delete permanently"
                                            title="Delete permanently"
                                            style="width:34px;height:34px;border-radius:8px;color:#fff;border:none;display:inline-flex;align-items:center;justify-content:center;background-image:linear-gradient(90deg,#ef4444,#dc2626);box-shadow:0 4px 8px rgba(220,38,38,0.12);transition:transform .12s ease;">
                                            <i class="bi bi-trash" style="font-size:0.92rem;line-height:1"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-gray-500">No items in recycle bin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $docs->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmRestore(resource, id) {
            var formId = 'restore-form-' + resource + '-' + id;
            var form = document.getElementById(formId);
            var label = (resource === 'ftpp') ? 'FTPP finding' : 'mapping';

            if (typeof Swal === 'undefined') {
                if (confirm('Restore this ' + label + ' and its related files?')) {
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Restore item?',
                text: 'This will restore the ' + label + ' and its related files.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Restoring...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    document.getElementById(formId).submit();
                }
            });
        }

        function confirmForceDelete(resource, id) {
            var formId = 'delete-form-' + resource + '-' + id;
            var form = document.getElementById(formId);
            var label = (resource === 'ftpp') ? 'FTPP finding' : 'mapping';

            if (typeof Swal === 'undefined') {
                if (confirm('Permanently delete this ' + label + ' and its files?')) {
                    document.getElementById(formId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Delete permanently?',
                text: 'This will permanently delete the ' + label + ' and all related files. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    document.getElementById(formId).submit();
                }
            });
        }

        function getSelectedBulkItems() {
            var selected = [];
            document.querySelectorAll('.bulk-item-checkbox:checked').forEach(function(checkbox) {
                selected.push(checkbox.value);
            });
            return selected;
        }

        function resetBulkForm(form) {
            form.querySelectorAll('input[name="items[]"]').forEach(function(input) {
                input.remove();
            });
        }

        function fillBulkForm(form, items) {
            resetBulkForm(form);
            items.forEach(function(value) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'items[]';
                input.value = value;
                form.appendChild(input);
            });
        }

        function updateBulkButtons() {
            var selectedCount = getSelectedBulkItems().length;
            var restoreBtn = document.getElementById('bulk-restore-btn');
            var deleteBtn = document.getElementById('bulk-delete-btn');
            var actionBar = document.getElementById('bulk-action-bar');
            var selectedCountLabel = document.getElementById('bulk-selected-count');
            var enabled = selectedCount > 0;

            if (selectedCountLabel) {
                selectedCountLabel.textContent = selectedCount + ' item dipilih';
            }

            if (actionBar) {
                actionBar.style.opacity = enabled ? '1' : '0';
                actionBar.style.pointerEvents = enabled ? 'auto' : 'none';
                actionBar.style.transform = enabled
                    ? 'translateX(-50%) translateY(0)'
                    : 'translateX(-50%) translateY(18px)';
            }

            [restoreBtn, deleteBtn].forEach(function(btn) {
                if (!btn) {
                    return;
                }

                btn.disabled = !enabled;
                btn.style.opacity = enabled ? '1' : '.55';
                btn.style.cursor = enabled ? 'pointer' : 'not-allowed';
            });
        }

        function syncSelectAllState() {
            var selectAll = document.getElementById('select-all-recycle');
            var checkboxes = Array.from(document.querySelectorAll('.bulk-item-checkbox'));

            if (!selectAll) {
                return;
            }

            if (checkboxes.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
                return;
            }

            var checkedCount = checkboxes.filter(function(cb) {
                return cb.checked;
            }).length;

            selectAll.checked = checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        function confirmBulkRestore() {
            var items = getSelectedBulkItems();
            if (items.length === 0) {
                return;
            }

            var form = document.getElementById('bulk-restore-form');
            fillBulkForm(form, items);

            if (typeof Swal === 'undefined') {
                if (confirm('Restore ' + items.length + ' selected item(s) and related files?')) {
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Restore selected items?',
                text: 'This will restore ' + items.length + ' selected item(s) and related files.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Restoring...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                }
            });
        }

        function confirmBulkForceDelete() {
            var items = getSelectedBulkItems();
            if (items.length === 0) {
                return;
            }

            var form = document.getElementById('bulk-delete-form');
            fillBulkForm(form, items);

            if (typeof Swal === 'undefined') {
                if (confirm('Permanently delete ' + items.length + ' selected item(s) and related files?')) {
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Delete selected items permanently?',
                text: 'This will permanently delete ' + items.length + ' selected item(s) and all related files. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var selectAll = document.getElementById('select-all-recycle');
            var checkboxes = Array.from(document.querySelectorAll('.bulk-item-checkbox'));

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    updateBulkButtons();
                    syncSelectAllState();
                });
            }

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    updateBulkButtons();
                    syncSelectAllState();
                });
            });

            updateBulkButtons();
            syncSelectAllState();
        });
    </script>
@endpush
