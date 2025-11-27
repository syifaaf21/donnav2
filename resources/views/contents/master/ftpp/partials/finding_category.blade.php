{{-- Header --}}
<div class="flex justify-between items-center mb-3">
    <h2 class="text-lg font-semibold text-gray-700">Finding Category</h2>
    <button id="btnAddCategory" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600">
        <i class="bi bi-plus"></i> Add Category
    </button>
</div>

{{-- Table --}}
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr class="text-left">
                <th class="px-3 py-2 border-b">No</th>
                <th class="px-3 py-2 border-b">Name</th>
                <th class="px-3 py-2 border-b text-center w-32">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($findingCategories as $index => $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 border-b">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 border-b">{{ $category->name }}</td>
                    <td class="px-3 py-2 border-b text-center">
                        <div class="flex justify-center gap-2">
                            <button data-id="{{ $category->id }}"
                                class="btn-edit bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors duration-200">
                                <i data-feather="edit" class="w-4 h-4"></i>
                            </button>
                            |
                            <button data-id="{{ $category->id }}"
                                class="btn-delete bg-red-600 text-white hover:bg-red-700 p-2 rounded">
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
        <form action="{{ route('master.ftpp.finding-category.store') }}" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4" style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalAddCategoryLabel" style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
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
                        <label for="category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
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
                <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                    data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                    Cancel
                </button>
                <button type="submit" class="btn px-5 py-2 rounded-3 fw-semibold"
                    style="background-color: #3b82f6; border: 1px solid #3b82f6; color: white; transition: background-color 0.3s ease;">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL EDIT FINDING CATEGORY --}}
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-labelledby="modalEditCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditCategory" method="POST" class="modal-content rounded-4 shadow-lg">
            @csrf
            @method('PUT')

            {{-- Header --}}
            <div class="modal-header justify-content-center position-relative p-4 rounded-top-4" style="background-color: #f5f5f7;">
                <h5 class="modal-title fw-semibold text-dark" id="modalEditCategoryLabel" style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
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
                        <label for="edit_category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_category_name" placeholder="Input Category Name" required
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
                <button type="submit" class="btn px-5 py-2 rounded-3 fw-semibold"
                    style="background-color: #3b82f6; border: 1px solid #3b82f6; color: white; transition: background-color 0.3s ease;">
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
            document.querySelectorAll('.btn-edit').forEach(button => {
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
            document.querySelectorAll('.btn-delete').forEach(button => {
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
