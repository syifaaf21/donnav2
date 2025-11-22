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
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header bg-blue-500 text-white rounded-t-xl">
                <h5 class="modal-title text-sm font-medium" id="modalAddCategoryLabel">
                    <i class="bi bi-plus-circle me-1"></i> Add Finding Category
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form action="{{ route('master.ftpp.finding-category.store') }}" method="POST">
                @csrf
                <div class="modal-body space-y-3">
                    <div>
                        <label for="category_name" class="text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="name" id="category_name" required
                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="modal-footer border-t">
                    <button type="button" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT FINDING CATEGORY --}}
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-labelledby="modalEditCategoryLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header bg-blue-500 text-white rounded-t-xl">
                <h5 class="modal-title text-sm font-medium" id="modalEditCategoryLabel">
                    <i class="bi bi-pencil-square me-1"></i> Edit Finding Category
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form id="formEditCategory" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body space-y-3">
                    <div>
                        <label for="edit_category_name" class="text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="name" id="edit_category_name" required
                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="modal-footer border-t">
                    <button type="button" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm">Update</button>
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
