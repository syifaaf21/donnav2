{{-- Header --}}
<div class="flex justify-between items-center mb-3">
    <h2 class="text-lg font-semibold text-gray-700">Finding Category</h2>
    <button id="btnAddCategory"
        class="px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
        <i class="bi bi-plus"></i> Add Category
    </button>
</div>

{{-- Table --}}
<div
    class="overflow-hidden bg-white rounded-xl shadow border border-gray-100 overflow-x-auto overflow-y-auto max-h-[460px]">
    <table class="min-w-full text-sm text-gray-700">
        <thead class="sticky top-0 z-10" style="background: #f3f6ff; border-bottom: 2px solid #e0e7ff;">
            <tr>
                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">No</th>
                <th class="px-4 py-3 text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Name</th>
                <th class="px-4 py-3 text-center text-sm font-bold uppercase tracking-wider border-r border-gray-200" style="color: #1e2b50; letter-spacing: 0.5px;">Action
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($findingCategories as $index => $category)
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

{{-- MODAL ADD FINDING CATEGORY --}}
<div class="modal fade" id="modalAddCategory" tabindex="-1" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('master.ftpp.finding-category.store') }}" method="POST"
            class="modal-content rounded-4 shadow-lg">
            @csrf

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalAddCategoryLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-plus-circle me-2 text-primary"></i> Add Finding Category
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div class="row g-4">
                    <div class="col-md-12">
                        <label for="category_name" class="form-label fw-semibold">Category Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="category_name" placeholder="Input Category Name"
                            class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2" data-bs-dismiss="modal"
                    style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit"
                    class="btn px-3 py-2 bg-gradient-to-r from-primary
                    to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL EDIT FINDING CATEGORY --}}
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-labelledby="modalEditCategoryLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditCategory" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf
            @method('PUT')

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalEditCategoryLabel"
                    style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Finding Category
                </h5>
                <button type="button"
                    class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                    data-bs-dismiss="modal" aria-label="Close"
                    style="width: 36px; height: 36px; border: 1px solid #ddd;">
                    <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                <div class="row g-4">
                    <div class="col-md-12">
                        <label for="edit_category_name" class="form-label fw-semibold">Category Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_category_name" placeholder="Input Category Name"
                            required
                            class="form-control border-0 shadow-sm rounded-3 @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                    data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">

                    Save Changes
                </button>
            </div>
        </form>
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
                    const response = await fetch(`/master/ftpp/finding-category/${id}`);
                    if (!response.ok) {
                        alert('Failed to fetch category data.');
                        return;
                    }
                    const data = await response.json();

                    document.getElementById('edit_category_name').value = data.name;
                    document.getElementById('formEditCategory').action =
                        `/master/ftpp/finding-category/update/${id}`;

                    const modal = new bootstrap.Modal(document.getElementById(
                        'modalEditCategory'));
                    modal.show();
                });
            });

            // === Delete Action ===
            document.querySelectorAll('#section-finding-category .btn-delete').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.id;
                    if (confirm('Are you sure you want to delete this data?')) {
                        const form = document.getElementById('form-delete-category');
                        form.action = `/master/ftpp/finding-category/${id}`;
                        form.submit();
                    }
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
@endpush
