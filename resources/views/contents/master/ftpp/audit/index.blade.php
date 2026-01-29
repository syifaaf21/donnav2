@extends('layouts.app')
@section('title', 'Master Audit Types')
@section('subtitle', 'Manage Audit Types')
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
            <li class="text-gray-700 font-bold">Audit Types</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div id="section-audit" class="mx-auto px-4 py-2 bg-white rounded-lg shadow">
        {{-- Audit type table --}}
        <div class="flex justify-between items-center mb-2">
            <div>
                <form method="GET" class="searchForm flex items-center w-full max-w-sm relative">
                    <div class="relative w-96">
                        <input type="text" name="search"
                            class="searchInput peer w-full rounded-xl border border-gray-300 bg-white pl-4 pr-20 py-2.5
                        text-sm text-gray-700 shadow-sm transition-all duration-200
                        focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                            placeholder="Type to search..." value="{{ request('search') }}">

                        <!-- Floating Label -->
                        <label for="searchInput"
                            class="absolute left-4 px-1 bg-white text-gray-400 rounded transition-all duration-150
                        pointer-events-none
                        {{ request('search')
                            ? '-top-3 text-xs text-sky-600'
                            : 'top-2.5 peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-focus:-top-3 peer-focus:text-xs peer-focus:text-sky-600' }}">
                            Type to search...
                        </label>

                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-blue-700 transition">
                            <i data-feather="search" class="w-5 h-5"></i>
                        </button>
                        @if (request('search'))
                            <button type="button"
                                class="clearSearch absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-red-600 transition">
                                <i data-feather="x" class="w-5 h-5"></i>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <div>
                <button id="btn-add"
                    class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors"
                    data-bs-toggle="modal" data-bs-target="#modalAddAudit">
                    <i class="bi bi-plus"></i> Add Audit
                </button>
            </div>
        </div>

        <div
            class="mb-3 overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                    <tr>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Audit Type</th>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Prefix Code</th>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Registration Format</th>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Sub Audit Type</th>
                        <th class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($audits as $index => $audit)
                        <tr class="hover:bg-gray-50 transition-all duration-150">
                            <td class="px-4 py-3 border-r border-gray-200 text-sm">
                                {{ $index + 1 }}</td>
                            <td class="py-2 px-3 border-r border-gray-200 text-sm font-semibold">
                                {{ $audit->name }}</td>
                            <td class="py-2 px-3 border-r border-gray-200 text-sm text-center">
                                <span
                                    class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">{{ $audit->prefix_code ?: '-' }}</span>
                            </td>
                            <td class="py-2 px-3 border-r border-gray-200 text-sm">
                                <code
                                    class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $audit->registration_number_format ?: '-' }}</code>
                            </td>
                            <td class="py-2 px-3 border-r border-gray-200 text-sm">
                                @if ($audit->subAudit->isNotEmpty())
                                    <ul class="list-disc list-inside space-y-0.5">
                                        @foreach ($audit->subAudit as $sub)
                                            <li>{{ $sub->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 border-r border-gray-200">
                                <div class="flex justify-center gap-2">
                                    <button data-id="{{ $audit->id }}" data-name="{{ $audit->name }}"
                                        class="btn-edit w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    |
                                    <form action="{{ route('master.ftpp.audit.destroy', $audit->id) }}" method="POST"
                                        class="inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-400 py-4">No data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- MODAL ADD AUDIT --}}
        <div class="modal fade" id="modalAddAudit" tabindex="-1" aria-labelledby="modalAddAuditLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-lg shadow-lg">
                    <div class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                        <h5 class="modal-title" id="modalAddAuditLabel">
                            <i class="bi bi-plus-circle me-2"></i> Add Audit Type
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('master.ftpp.audit.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4 space-y-3" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                            <div>
                                <label for="audit_name" class="form-label fw-semibold">Audit Type <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="audit_name" required placeholder="Enter audit type" class="form-control border-1 @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="prefix_code" class="form-label fw-semibold">Prefix Code</label>
                                <input type="text" name="prefix_code" id="prefix_code" placeholder="e.g. FTPP" class="form-control border-1 @error('prefix_code') is-invalid @enderror" value="{{ old('prefix_code') }}">
                                @error('prefix_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Registration Number Format</label>
                                <div id="format-builder-container" class="border rounded p-3 bg-light">
                                    <div id="format-components" class="d-flex flex-wrap gap-2 mb-2 min-h-[40px] align-items-center">
                                        <span class="text-muted text-sm">Add components below to build format...</span>
                                    </div>
                                    <input type="hidden" name="registration_number_format" id="registration_number_format">
                                </div>
                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="PREFIX">+ Prefix</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="YYYY">+ Year (4)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="YY">+ Year (2)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="MM">+ Month</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="NNN">+ Number (3)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="NNNN">+ Number (4)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-format-component" data-type="REV">+ Revision</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-format-separator" data-sep="-">+ Dash (-)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-format-separator" data-sep="/">+ Slash (/)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-format-separator" data-sep=".">+ Dot (.)</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="clear-format">Clear All</button>
                                </div>
                                <small class="text-muted d-block mt-2">Preview: <code id="format-preview" class="bg-white px-2 py-1 rounded">-</code></small>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Sub Audit Type</label>
                                <div id="sub-audit-container" class="space-y-2 mt-2">
                                    <div class="d-flex gap-2 sub-audit-item">
                                        <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type" class="flex-grow-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <button type="button" class="btn-remove-sub btn btn-sm btn-danger d-none" title="Remove Sub Audit">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" id="btn-add-sub" class="mt-2 d-flex align-items-center gap-1 text-primary fw-semibold btn btn-link p-0" style="font-size: 0.875rem;">
                                    <i class="bi bi-plus-circle"></i> Add Sub Audit
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer border-t">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        {{-- MODAL EDIT AUDIT --}}
        <div class="modal fade" id="modalEditAudit" tabindex="-1" aria-labelledby="modalEditAuditLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-lg shadow-lg">
                    <div class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                        <h5 class="modal-title" id="modalEditAuditLabel">
                            <i class="bi bi-pencil-square me-2"></i> Edit Audit Type
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditAudit" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4 space-y-3" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                            <div>
                                <label for="edit_audit_name" class="form-label fw-semibold">Audit Type <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_audit_name" required class="form-control border-1 @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="edit_prefix_code" class="form-label fw-semibold">Prefix Code</label>
                                <input type="text" name="prefix_code" id="edit_prefix_code" placeholder="e.g. FTPP" class="form-control border-1 @error('prefix_code') is-invalid @enderror">
                                @error('prefix_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Registration Number Format</label>
                                <div id="edit-format-builder-container" class="border rounded p-3 bg-light">
                                    <div id="edit-format-components" class="d-flex flex-wrap gap-2 mb-2 min-h-[40px] align-items-center">
                                        <span class="text-muted text-sm">Add components below to build format...</span>
                                    </div>
                                    <input type="hidden" name="registration_number_format" id="edit_registration_number_format">
                                </div>
                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="PREFIX">+ Prefix</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="YYYY">+ Year (4)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="YY">+ Year (2)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="MM">+ Month</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="NNN">+ Number (3)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="NNNN">+ Number (4)</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-add-format-component" data-type="REV">+ Revision</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-add-format-separator" data-sep="-">+ Dash (-)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-add-format-separator" data-sep="/">+ Slash (/)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-add-format-separator" data-sep=".">+ Dot (.)</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="edit-clear-format">Clear All</button>
                                </div>
                                <small class="text-muted d-block mt-2">Preview: <code id="edit-format-preview" class="bg-white px-2 py-1 rounded">-</code></small>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Sub Audit Type</label>
                                <div id="edit-sub-audit-container" class="space-y-2 mt-2">
                                    {{-- akan diisi via JavaScript --}}
                                </div>
                                <button type="button" id="btn-edit-add-sub" class="mt-2 d-flex align-items-center gap-1 text-primary fw-semibold btn btn-link p-0" style="font-size: 0.875rem;">
                                    <i class="bi bi-plus-circle"></i> Add Sub Audit
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer border-t">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary bg-gradient-to-r from-primaryLight to-primaryDark border-0">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            // Modal Edit Handler (delegated)
            document.addEventListener('DOMContentLoaded', function() {
                const editForm = document.getElementById('formEditAudit');
                const nameInput = document.getElementById('edit_audit_name');
                const editModalEl = document.getElementById('modalEditAudit');
                const editModal = new bootstrap.Modal(editModalEl);

                document.querySelectorAll('#section-audit .btn-edit').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const id = btn.getAttribute('data-id');

                        try {
                            const response = await fetch(`/master/ftpp/audit/${id}`);
                            const data = await response.json();

                            nameInput.value = data.name;
                            editForm.action = `/master/ftpp/audit/update/${id}`;

                            // Set prefix code
                            document.getElementById('edit_prefix_code').value = data.prefix_code ||
                                '';

                            // Set registration format
                            const editFormatContainer = document.getElementById(
                                'edit-format-components');
                            editFormatContainer.innerHTML = '';
                            if (data.registration_number_format) {
                                document.getElementById('edit_registration_number_format').value =
                                    data.registration_number_format;

                                // Parse format string
                                const format = data.registration_number_format;
                                let i = 0;
                                while (i < format.length) {
                                    const char = format[i];
                                    // Check if separator
                                    if (['-', '/', '.'].includes(char)) {
                                        addEditFormatItem(char, true);
                                        i++;
                                    } else {
                                        // Find continuous same characters
                                        let component = char;
                                        let j = i + 1;
                                        while (j < format.length && format[j] === char && !['-',
                                                '/', '.'
                                            ].includes(format[j])) {
                                            component += format[j];
                                            j++;
                                        }
                                        addEditFormatItem(component, false);
                                        i = j;
                                    }
                                }
                                updateEditFormatPreview();
                            } else {
                                document.getElementById('edit_registration_number_format').value =
                                    '';
                                editFormatContainer.innerHTML =
                                    '<span class=\"text-muted text-sm\">Add components below to build format...</span>';
                                document.getElementById('edit-format-preview').textContent = '-';
                            }

                            const container = document.getElementById('edit-sub-audit-container');
                            container.innerHTML = '';

                            if (data.sub_audit?.length) {
                                data.sub_audit.forEach(sub => {
                                    const field = document.createElement('div');
                                    field.classList.add('d-flex', 'gap-2',
                                        'sub-audit-item');
                                    field.innerHTML = `
                                    <input type="text" name="sub_audit_existing[${sub.id}]" value="${sub.name}"
                                        class="flex-grow-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <button type="button"
                                        class="btn-remove-sub btn btn-sm btn-danger"
                                        title="Remove Sub Audit">
                                        <i class="bi bi-x"></i>
                                    </button>
                                `;
                                    field.querySelector('.btn-remove-sub').addEventListener(
                                        'click', () => field.remove());
                                    container.appendChild(field);
                                });
                            }

                            editModal.show();
                        } catch (error) {
                            console.error('Error loading audit data:', error);
                            alert('Failed to load audit data');
                        }
                    });
                });
            });

            // add handler
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('sub-audit-container');
                const addBtn = document.getElementById('btn-add-sub');

                addBtn.addEventListener('click', () => {
                    const newField = document.createElement('div');
                    newField.classList.add('d-flex', 'gap-2', 'sub-audit-item');

                    newField.innerHTML = `
                    <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type"
                        class="flex-grow-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <button type="button"
                        class="btn-remove-sub btn btn-sm btn-danger"
                        title="Remove Sub Audit">
                        <i class="bi bi-x"></i>
                    </button>
                `;

                    container.appendChild(newField);

                    // event untuk tombol hapus
                    const removeBtn = newField.querySelector('.btn-remove-sub');
                    removeBtn.addEventListener('click', () => {
                        newField.remove();
                    });
                });
            });

            // edit handler - tombol tambah sub audit di modal edit
            document.addEventListener('DOMContentLoaded', () => {
                const editContainer = document.getElementById('edit-sub-audit-container');
                const btnAddEditSub = document.getElementById('btn-edit-add-sub');

                if (btnAddEditSub) {
                    btnAddEditSub.addEventListener('click', () => {
                        const field = document.createElement('div');
                        field.classList.add('d-flex', 'gap-2', 'sub-audit-item');
                        field.innerHTML = `
                        <input type="text" name="sub_audit[]" placeholder="Enter Sub Audit Type"
                            class="flex-grow-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <button type="button"
                            class="btn-remove-sub btn btn-sm btn-danger"
                            title="Remove Sub Audit">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                        editContainer.appendChild(field);
                        field.querySelector('.btn-remove-sub').addEventListener('click', () => field.remove());
                    });
                }

                document.getElementById('modalEditAudit')?.addEventListener('hidden.bs.modal', function() {
                    try {
                        setTimeout(() => {
                            if (document.querySelectorAll('.modal.show').length === 0) {
                                document.querySelectorAll('.modal-backdrop').forEach(backdrop =>
                                    backdrop.remove());
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }
                        }, 50);
                    } catch (e) {
                        console.warn('Backdrop cleanup skipped:', e);
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', () => {
                // Delete confirmation -gunakan selector spesifik untuk audit
                document.querySelectorAll('#section-audit .delete-form').forEach(form => {
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You want to delete this audit data?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });

            // Registration Number Format Builder - Add Modal
            document.addEventListener('DOMContentLoaded', () => {
                const formatContainer = document.getElementById('format-components');
                const formatInput = document.getElementById('registration_number_format');
                const formatPreview = document.getElementById('format-preview');

                function updateFormat() {
                    const components = Array.from(formatContainer.querySelectorAll('.format-item'));
                    const format = components.map(c => c.dataset.value).join('');
                    formatInput.value = format;
                    formatPreview.textContent = format || '-';
                }

                function addFormatItem(value, isSeparator = false) {
                    const emptyMsg = formatContainer.querySelector('.text-muted');
                    if (emptyMsg) emptyMsg.remove();

                    const item = document.createElement('span');
                    item.className =
                        `format-item badge ${isSeparator ? 'bg-secondary' : 'bg-primary'} d-inline-flex align-items-center gap-1`;
                    item.dataset.value = value;
                    item.innerHTML = `
                    ${value}
                    <i class="bi bi-x-circle" style="cursor: pointer;" onclick="this.parentElement.remove(); updateFormat();"></i>
                `;
                    formatContainer.appendChild(item);
                    updateFormat();
                }

                window.updateFormat = updateFormat;

                document.querySelectorAll('.add-format-component').forEach(btn => {
                    btn.addEventListener('click', () => {
                        addFormatItem(btn.dataset.type, false);
                    });
                });

                document.querySelectorAll('.add-format-separator').forEach(btn => {
                    btn.addEventListener('click', () => {
                        addFormatItem(btn.dataset.sep, true);
                    });
                });

                document.getElementById('clear-format')?.addEventListener('click', () => {
                    formatContainer.innerHTML =
                        '<span class="text-muted text-sm">Add components below to build format...</span>';
                    formatInput.value = '';
                    formatPreview.textContent = '-';
                });
            });

            // Registration Number Format Builder - Edit Modal
            document.addEventListener('DOMContentLoaded', () => {
                const editFormatContainer = document.getElementById('edit-format-components');
                const editFormatInput = document.getElementById('edit_registration_number_format');
                const editFormatPreview = document.getElementById('edit-format-preview');

                function updateEditFormatPreview() {
                    const components = Array.from(editFormatContainer.querySelectorAll('.format-item'));
                    const format = components.map(c => c.dataset.value).join('');
                    editFormatInput.value = format;
                    editFormatPreview.textContent = format || '-';
                }

                window.updateEditFormatPreview = updateEditFormatPreview;

                function addEditFormatItem(value, isSeparator = false) {
                    const emptyMsg = editFormatContainer.querySelector('.text-muted');
                    if (emptyMsg) emptyMsg.remove();

                    const item = document.createElement('span');
                    item.className =
                        `format-item badge ${isSeparator ? 'bg-secondary' : 'bg-primary'} d-inline-flex align-items-center gap-1`;
                    item.dataset.value = value;
                    item.innerHTML = `
                    ${value}
                    <i class="bi bi-x-circle" style="cursor: pointer;" onclick="this.parentElement.remove(); updateEditFormatPreview();"></i>
                `;
                    editFormatContainer.appendChild(item);
                    updateEditFormatPreview();
                }

                window.addEditFormatItem = addEditFormatItem;

                document.querySelectorAll('.edit-add-format-component').forEach(btn => {
                    btn.addEventListener('click', () => {
                        addEditFormatItem(btn.dataset.type, false);
                    });
                });

                document.querySelectorAll('.edit-add-format-separator').forEach(btn => {
                    btn.addEventListener('click', () => {
                        addEditFormatItem(btn.dataset.sep, true);
                    });
                });

                document.getElementById('edit-clear-format')?.addEventListener('click', () => {
                    editFormatContainer.innerHTML =
                        '<span class="text-muted text-sm">Add components below to build format...</span>';
                    editFormatInput.value = '';
                    editFormatPreview.textContent = '-';
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Use class selectors because the elements use class, not id
                const clearBtn = document.querySelector('.clearSearch');
                const searchInput = document.querySelector('.searchInput');
                const searchForm = document.querySelector('.searchForm');

                clearBtn?.addEventListener('click', function(e) {
                    if (searchInput && searchForm) {
                        e.preventDefault();
                        searchInput.value = '';
                        searchForm.submit();
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            /* Default border */
            #modalAddAudit input.form-control,
            #modalAddAudit select.form-select {
                border: 1px solid #d1d5db !important;
                /* abu-abu halus */
                box-shadow: none !important;
            }

            /* Hover (opsional) */
            #modalAddAudit input.form-control:hover,
            #modalAddAudit select.form-select:hover {
                border-color: #bfc3ca !important;
            }

            /* Fokus / diklik */
            #modalAddAudit input.form-control:focus,
            #modalAddAudit select.form-select:focus {
                border-color: #3b82f6 !important;
                /* biru */
                box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
                /* efek biru lembut */
            }

            [id^="modalEditAudit"] input.form-control,
            [id^="modalEditAudit"] select.form-select {
                border: 1px solid #d1d5db !important;
                box-shadow: none !important;
            }

            /* Hover */
            [id^="modalEditAudit"] input.form-control:hover,
            [id^="modalEditAudit"] select.form-select:hover {
                border-color: #bfc3ca !important;
            }

            /* Fokus */
            [id^="modalEditAudit"] input.form-control:focus,
            [id^="modalEditAudit"] select.form-select:focus {
                border-color: #3b82f6 !important;
                /* biru */
                box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
            }

            /* Format Builder Styles */
            .format-item {
                padding: 4px 8px;
                font-size: 0.875rem;
                cursor: default;
                user-select: none;
            }

            .format-item i {
                font-size: 0.75rem;
                opacity: 0.7;
                transition: opacity 0.2s;
            }

            .format-item i:hover {
                opacity: 1;
            }

            #format-builder-container,
            #edit-format-builder-container {
                min-height: 60px;
            }

            #format-components,
            #edit-format-components {
                min-height: 40px;
            }
        </style>
    @endpush

@endsection
