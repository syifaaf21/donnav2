@extends('layouts.app')

@section('content')
    <div class="mx-auto px-4 py-2">
        {{-- Header --}}
        <div class="flex justify-between items-center my-2 pt-4">
            <div class="py-3 mt-2 text-white">
                <div class="mb-2 text-white">
                    <h3 class="fw-bold">Hierarchy Master</h3>
                    <p class="text-sm" style="font-size: 0.85rem;">
                        Manage hierarchies. Use the "Add Hierarchy" button to create new entries and the actions column
                        to edit or delete existing records.
                    </p>
                </div>
            </div>

            {{-- Breadcrumbs --}}
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
                    <li class="text-gray-700 font-bold">Hierarchy</li>
                </ol>
            </nav>
        </div>

        <div class="overflow-hidden">
            {{-- Search Bar and Add Button aligned --}}
            <div class="py-4 flex items-center justify-between gap-4">
                <form method="GET" id="searchForm" class="relative w-full sm:w-96">
                    <input id="searchInput" type="text" name="search"
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

                    <!-- Clear button -->
                    @if (request('search'))
                        <button id="clearSearch" type="button"
                            class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-gray-400 hover:text-red-600 transition"
                            onclick="document.getElementById('searchInput').value=''; document.getElementById('searchForm').submit();">
                            <i data-feather="x" class="w-5 h-5"></i>
                        </button>
                    @endif
                </form>

                {{-- Add Button on the right --}}
                {{-- Add Button --}}
                <button type="button" data-bs-toggle="modal" data-bs-target="#createDocumentModal"
                    class="px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                    <i class="bi bi-plus-circle"></i>
                    <span>Add Document</span>
                </button>
            </div>

            {{-- Table --}}
            <div
                class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[540px]">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                        <tr>
                            <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Document Name
                            </th>
                            <th class="px-4 py-3 border-r border-gray-200 text-sm font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">Code</th>
                            <th class="px-4 py-3 border-r border-gray-200 text-center text-sm font-bold uppercase tracking-wider"
                                style="color: #1e2b50; letter-spacing: 0.5px;">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody id="documentTableBody" class="divide-y divide-gray-200" @php $number = 1; @endphp
                        @if ($documents->count() === 0) <tr>
                                <td colspan="3" class="text-center py-6 text-gray-500">
                                    <i class="bi bi-inbox text-2xl block mb-1"></i>
                                    No data found
                                </td>
                            </tr>
                        @else
                            @foreach ($documents as $document)
                                @include('contents.master.hierarchy.partials.tree-node', [
                                    'document' => $document,
                                    'level' => 0,
                                    'number' => $number++,
                                    'parent_id' => null,
                                ])
                            @endforeach @endif
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
                <div class="modal-footer bg-light rounded-b-xl justify-content-between p-4">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-200"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
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
            document.addEventListener('shown.bs.modal', function(event) {
                // Modal Add & semua modal Edit
                if (
                    event.target.matches('#createDocumentModal') ||
                    event.target.matches('[id^="editDocumentModal-"]')
                ) {

                    event.target.querySelectorAll('select.tomselect').forEach(select => {
                        if (!select.tomselect) {
                            new TomSelect(select, {
                                create: false,
                                sortField: {
                                    field: "text",
                                    direction: "asc"
                                }
                            });
                        }
                    });
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
