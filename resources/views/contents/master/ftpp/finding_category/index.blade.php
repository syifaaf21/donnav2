@extends('layouts.app')
@section('title', 'Master Finding Category')
@section('subtitle', 'Manage Finding Categories')
@section('breadcrumbs')
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
            <li class="text-gray-700 font-bold">Finding Category</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div id="section-finding-category" class="mx-auto px-4 py-2 bg-white rounded-lg shadow">
        {{-- Header --}}
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
                <button id="btnAddCategory"
                    class="px-3 py-2 bg-gradient-to-r from-primaryLight to-primaryDark text-white border border-white rounded hover:from-primaryDark hover:to-primaryLight transition-colors">
                    <i class="bi bi-plus"></i> Add Category
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div
            class="mb-2 overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
                    <tr>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                        <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Name</th>
                        <th class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200"
                            style="color: #1e2b50; letter-spacing: 0.5px;">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($categories as $index => $category)
                        <tr class="hover:bg-gray-50 transition-all duration-150">
                            <td class="px-4 py-3 border-r border-gray-200 text-sm">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 border-r border-gray-200 text-sm font-semibold">{{ $category->name }}</td>
                            <td class="px-4 py-3 border-r border-gray-200 text-sm text-center">
                                <div class="flex justify-center gap-2">
                                    <button data-id="{{ $category->id }}"
                                        class="btn-edit w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </button>
                                    |
                                    <button data-id="{{ $category->id }}"
                                        class="btn-delete w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-400 py-4">No data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD FINDING CATEGORY --}}
    <div class="modal fade" id="modalAddCategory" tabindex="-1" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i> Add Finding Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('master.ftpp.finding-category.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 space-y-3" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                        <div>
                            <label for="category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="category_name" placeholder="Input Category Name"
                                class="form-control border-1 @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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



    {{-- MODAL EDIT FINDING CATEGORY --}}
    <div class="modal fade" id="modalEditCategory" tabindex="-1" aria-labelledby="modalEditCategoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-lg shadow-lg">
                <div class="modal-header border-b bg-gradient-to-r from-primaryLight to-primaryDark text-white rounded-t-lg">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i> Edit Finding Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditCategory" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 space-y-3" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                        <div>
                            <label for="edit_category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_category_name" placeholder="Input Category Name" required class="form-control border-1 @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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


    {{-- HIDDEN DELETE FORM --}}
    <form class="delete-form" id="form-delete-category" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // === Show Add Modal ===
                document.getElementById('btnAddCategory').addEventListener('click', () => {
                    const modal = new bootstrap.Modal(document.getElementById('modalAddCategory'));
                    modal.show();
                });

                // === Show Edit Modal ===
                document.querySelectorAll('#section-finding-category .btn-edit').forEach(button => {
                    button.addEventListener('click', async () => {
                        const id = button.dataset.id;
                        try {
                            const response = await fetch(`/master/ftpp/finding-category/${id}`);
                            if (!response.ok) {
                                alert('Failed to fetch category data.');
                                return;
                            }
                            const data = await response.json();

                            document.getElementById('edit_category_name').value = data.name;
                            document.getElementById('formEditCategory').action =
                                `/master/ftpp/finding-category/${id}`;

                            const modal = new bootstrap.Modal(document.getElementById(
                                'modalEditCategory'));
                            modal.show();
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Failed to load category data');
                        }
                    });
                });

                // === Delete Action ===
                document.querySelectorAll('#section-finding-category .btn-delete').forEach(button => {
                    button.addEventListener('click', () => {
                        const id = button.getAttribute('data-id');
                        const form = document.getElementById('form-delete-category');

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You want to delete this finding category?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.action = `/master/ftpp/finding-category/${id}`;
                                form.submit();
                            }
                        });
                    });
                });
            });

            document.addEventListener('hidden.bs.modal', function() {
                // Hapus semua backdrop yang masih tersisa
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = ''; // biar bisa scroll lagi
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
            #modalAddCategory input.form-control,
            #modalAddCategory select.form-select {
                border: 1px solid #d1d5db !important;
                /* abu-abu halus */
                box-shadow: none !important;
            }

            /* Hover (opsional) */
            #modalAddCategory input.form-control:hover,
            #modalAddCategory select.form-select:hover {
                border-color: #bfc3ca !important;
            }

            /* Fokus / diklik */
            #modalAddCategory input.form-control:focus,
            #modalAddCategory select.form-select:focus {
                border-color: #3b82f6 !important;
                /* biru */
                box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
                /* efek biru lembut */
            }

            [id^="modalEditCategory"] input.form-control,
            [id^="modalEditCategory"] select.form-select {
                border: 1px solid #d1d5db !important;
                box-shadow: none !important;
            }

            /* Hover */
            [id^="modalEditCategory"] input.form-control:hover,
            [id^="modalEditCategory"] select.form-select:hover {
                border-color: #bfc3ca !important;
            }

            /* Fokus */
            [id^="modalEditCategory"] input.form-control:focus,
            [id^="modalEditCategory"] select.form-select:focus {
                border-color: #3b82f6 !important;
                /* biru */
                box-shadow: 0 0 0 3px rgba(59, 130, 246, .25) !important;
            }
        </style>
    @endpush
@endsection
