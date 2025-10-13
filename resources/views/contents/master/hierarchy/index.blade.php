@extends('layouts.app')
@section('title', 'Document Hierarchy')

@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp
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
                <!-- 🔍 Search Bar -->
                <form method="GET" id="searchForm" class="input-group" style="width: 400px; max-width: 100%;">
                    <input type="text" name="search" id="searchInput" class="form-control form-control-sm"
                        placeholder="Search by Name" value="{{ request('search') }}">

                    <button class="btn btn-outline-secondary btn-sm" type="submit" title="Search">
                        <i class="bi bi-search"></i>
                    </button>

                    <button type="button" class="btn btn-outline-danger btn-sm" id="clearSearch" title="Clear">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>
            {{-- Table Wrapper --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-700 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2">Document Name</th>
                            <th class="px-4 py-2">Type</th>
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
        {{-- Modal Edit Document --}}
        @foreach ($documents as $document)
            <div class="modal fade" id="editDocumentModal-{{ $document->id }}" tabindex="-1"
                aria-labelledby="editDocumentModalLabel-{{ $document->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('master.hierarchy.update', $document->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header bg-light text-dark rounded-top-4">
                                <h5 class="modal-title fw-semibold">
                                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Document
                                </h5>
                            </div>

                            <div class="modal-body px-4 py-3">
                                {{-- Document Name --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Name</label>
                                    <input type="text" name="name"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        value="{{ old('name', $document->name) }}" placeholder="Enter document name"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Document Type --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach (\App\Models\Document::getTypes() as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('type', $document->type) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="modal-footer border-0 p-3 justify-content-between bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-outline-success px-4">
                                    <i class="bi bi-check-circle me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

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

                            {{-- Document Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Type</label>
                                <select name="type" class="form-select rounded-3 @error('type') is-invalid @enderror"
                                    required>
                                    <option value="">Select Type</option>
                                    @foreach (\App\Models\Document::getTypes() as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('type') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

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

            // Toggle visibility of child documents
            document.addEventListener('DOMContentLoaded', function() {
                feather.replace();

                document.querySelectorAll('.toggle-children').forEach(button => {
                    button.addEventListener('click', function() {
                        const targetClass = this.dataset.target;
                        const children = document.querySelectorAll('.' + targetClass);
                        children.forEach(row => {
                            row.classList.toggle('hidden');
                        });
                        // rotate icon
                        const icon = this.querySelector('i');
                        icon.classList.toggle('rotate-90');
                        // re-replace feather icons (so icons tetap muncul)
                        feather.replace();
                    });
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
