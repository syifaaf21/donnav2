@extends('layouts.app')
@section('title', 'Document Hierarchy')

@section('content')
    <div class="container py-4">
        {{-- Header: Title + Action Buttons --}}
        <div class="flex items-center justify-between mb-4">
            {{-- Breadcrumbs --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none text-secondary">Master</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-decoration-none text-secondary">Hieararchy</a>
                    </li>
                </ol>
            </nav>

            {{-- Add Document Button --}}
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#createDocumentModal">
                <i class="bi bi-plus-circle"></i> Add Document
            </button>
        </div>
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="d-flex justify-content-end m-3">
                <form method="GET" class="flex items-center gap-2 flex-wrap" id="searchForm">
                    <div class="relative max-w-md w-full">
                        <input type="text" name="search" id="searchInput"
                            class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search..." value="{{ request('search') }}">
                        <button
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                            type="submit" title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        <button type="button"
                            class="absolute right-8 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600"
                            id="clearSearch" title="Clear">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
            {{-- Table Wrapper --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2">Document Name</th>
                            <th class="px-4 py-2">Code</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="documentTableBody">
                        @php $number = 1; @endphp
                        @foreach ($documents as $document)
                            @include('contents.master.hierarchy.partials.tree-node', [
                                'document' => $document,
                                'level' => 0,
                                'number' => $number++,
                                'parent_id' => null,
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📄 Create Document Modal --}}
        <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('master.hierarchy.store') }}" method="POST">
                    @csrf
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-header bg-light text-dark rounded-top-4">
                            <h5 class="modal-title fw-semibold">
                                <i class="bi bi-file-earmark-plus me-2 text-primary"></i> Create New Document
                            </h5>
                        </div>

                        <div class="modal-body px-4 py-3">
                            {{-- Document Name --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" name="name"
                                    class="form-control rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Enter document name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Parent Document --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Parent Document</label>
                                <select name="parent_id"
                                    class="form-select rounded-3 tomselect @error('parent_id') is-invalid @enderror">
                                    <option value="">-- No Parent (Top Level) --</option>
                                    @foreach ($parents as $parentDoc)
                                        <option value="{{ $parentDoc->id }}"
                                            {{ old('parent_id') == $parentDoc->id ? 'selected' : '' }}>
                                            {{ $parentDoc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Hidden field: type default ke review --}}
                            <input type="hidden" name="type" value="review">


                            <div class="col-md-6">
                                <label class="form-label fw-medium">Code</label>
                                <input type="text" name="code"
                                    class="form-control rounded-3 @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endsection
    @push('scripts')
        <x-sweetalert-confirm />
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Clear Search functionality
                const clearBtn = document.getElementById("clearSearch");
                const searchInput = document.getElementById("searchInput");
                const searchForm = document.getElementById("searchForm");

                if (clearBtn && searchInput && searchForm) {
                    clearBtn.addEventListener("click", function() {
                        searchInput.value = "";
                        searchForm.submit();
                    });
                }

                // Tooltip initialization
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
                tooltipTriggerList.map(function(el) {
                    return new bootstrap.Tooltip(el, {
                        title: el.getAttribute('data-bs-title'),
                        placement: 'top',
                        trigger: 'hover'
                    });
                });

                // Feather icons initialization
                feather.replace();

                // Toggle visibility of child documents (expand/collapse)
                document.querySelectorAll('.toggle-children').forEach(button => {
                    button.addEventListener('click', function() {
                        const targetClass = this.dataset.target;
                        const children = document.querySelectorAll('.' + targetClass);
                        children.forEach(row => {
                            row.classList.toggle('hidden');
                        });
                        // Rotate the chevron icon
                        const icon = this.querySelector('i');
                        icon.classList.toggle('rotate-90');
                        // Re-replace feather icons to keep icons visible
                        feather.replace();
                    });
                });

                // Initialize TomSelect for dropdowns with class .tomselect
                new TomSelect('.tomselect', {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });

                // Reset form and clear TomSelect & validation on modal cancel for all edit modals
                document.querySelectorAll('[id^="editDocumentModal-"]').forEach(modalEl => {
                    const form = modalEl.querySelector('form');
                    const cancelBtn = modalEl.querySelector('button[data-bs-dismiss="modal"]');

                    if (cancelBtn && form) {
                        cancelBtn.addEventListener('click', function() {
                            form.reset();

                            // Clear TomSelects inside this modal only
                            modalEl.querySelectorAll('.ts-wrapper').forEach(wrapper => {
                                const select = wrapper.previousElementSibling;
                                if (select && select.tomselect) {
                                    select.tomselect.clear();
                                }
                            });

                            // Remove error classes and feedback messages
                            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                                'is-invalid'));
                            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                        });
                    }
                });
            });
        </script>

        <style>
            .rotate-90 {
                transform: rotate(90deg);
                transition: transform 0.2s ease;
            }
        </style>
    @endpush
