@extends('layouts.app')
@section('title', 'Document Hierarchy')

@section('content')
    <div class="mx-auto px-4 py-2">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>Master</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Hierarchy</li>
                </ol>
            </nav>

            {{-- Add Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#createDocumentModal"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="bi bi-plus-circle"></i>
                <span>Add Document</span>
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-gray-100 flex justify-end">
                <form method="GET" id="searchForm" class="flex items-center w-full max-w-sm relative">
                    <input type="text" name="search" id="searchInput"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto overflow-y-auto max-h-96">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2">Document Name</th>
                            <th class="px-4 py-2">Code</th>
                            <th class="px-4 py-2">Actions</th>
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
            {{-- Pagination --}}
            @if (method_exists($documents, 'links'))
                <div class="mt-4">
                    {{ $documents->withQueryString()->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>


    {{-- 📄 Create Document Modal --}}
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.hierarchy.store') }}" method="POST"
                class="modal-content border-0 shadow-lg rounded-4">
                @csrf

                {{-- Header --}}
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">
                    <h5 class="modal-title fw-semibold" id="createDocumentModalLabel"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                        <i class="bi bi-file-earmark-plus me-2 text-primary"></i> Create New Document
                    </h5>
                    <button type="button"
                        class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="width: 36px; height: 36px; border: 1px solid #ddd;">
                        <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-5">
                    {{-- Document Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                            class="form-control rounded-3 border-0 shadow-sm @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Enter document name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Parent Document --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Parent Document</label>
                        <select name="parent_id"
                            class="form-select rounded-3 tomselect @error('parent_id') is-invalid @enderror">
                            <option value="">-- No Parent (Top Level) --</option>
                            @foreach ($parents as $parentDoc)
                                <option value="{{ $parentDoc->id }}" @selected(old('parent_id') == $parentDoc->id)>
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

                    {{-- Code --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" placeholder="Enter Document Code"
                            class="form-control rounded-3 border-0 shadow-sm @error('code') is-invalid @enderror"
                            value="{{ old('code') }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer bg-light rounded-b-xl flex justify-between p-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pr transition">
                        Submit
                    </button>
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
