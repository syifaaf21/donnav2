@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Product, Model, & Part Number</h1>

        <!-- Tombol Add -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Part Number</button>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table id="dataTable" class="table table-hover align-middle mt-4">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Part Number</th>
                        <th>Product</th>
                        <th>Model</th>
                        <th>Process</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partNumbers as $part)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $part->part_number }}</td>
                            <td>{{ $part->product->name ?? '-' }}</td>
                            <td>{{ $part->productModel->name ?? '-' }}</td>
                            <td>{{ ucfirst($part->process) }}</td>
                            <td>
                                <!-- Tombol Edit (bawa data ke modal) -->
                                <button class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#editModal" data-id="{{ $part->id }}"
                                    data-part_number="{{ $part->part_number }}" data-product_id="{{ $part->product_id }}"
                                    data-model_id="{{ $part->model_id }}" data-process="{{ $part->process }}">Edit</button>

                                <form action="{{ route('part_numbers.destroy', $part->id) }}" method="POST"
                                    style="display:inline-block" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data part number.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Add -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('part_numbers.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Tambah Part Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields -->
                    <div class="mb-3">
                        <label for="add_part_number" class="form-label">Part Number</label>
                        <input type="text" class="form-control" id="add_part_number" name="part_number" required>
                    </div>

                    <div class="mb-3">
                        <label for="add_product_id" class="form-label">Product</label>
                        <select name="product_id" id="add_product_id" class="form-select" required>
                            <option value="">-- Pilih Product --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="add_model_id" class="form-label">Model</label>
                        <select name="model_id" id="add_model_id" class="form-select" required>
                            <option value="">-- Pilih Model --</option>
                            @foreach ($models as $model)
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="add_process" class="form-label">Process</label>
                        <select name="process" id="add_process" class="form-select" required>
                            <option value="">-- Pilih Process --</option>
                            @foreach (['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'electric'] as $process)
                                <option value="{{ $process }}">{{ ucfirst($process) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Part Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields -->
                    <div class="mb-3">
                        <label for="edit_part_number" class="form-label">Part Number</label>
                        <input type="text" class="form-control" id="edit_part_number" name="part_number" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_product_id" class="form-label">Product</label>
                        <select name="product_id" id="edit_product_id" class="form-select" required>
                            <option value="">-- Pilih Product --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_model_id" class="form-label">Model</label>
                        <select name="model_id" id="edit_model_id" class="form-select" required>
                            <option value="">-- Pilih Model --</option>
                            @foreach ($models as $model)
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_process" class="form-label">Process</label>
                        <select name="process" id="edit_process" class="form-select" required>
                            <option value="">-- Pilih Process --</option>
                            @foreach (['injection', 'painting', 'assembling body', 'die casting', 'machining', 'assembling unit', 'electric'] as $process)
                                <option value="{{ $process }}">{{ ucfirst($process) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Saat tombol edit diklik, isi modal edit dengan data dari tombol
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const part_number = this.dataset.part_number;
                const product_id = this.dataset.product_id;
                const model_id = this.dataset.model_id;
                const process = this.dataset.process;

                const form = document.getElementById('editForm');
                form.action = `/part_numbers/${id}`;

                document.getElementById('edit_part_number').value = part_number;
                document.getElementById('edit_product_id').value = product_id;
                document.getElementById('edit_model_id').value = model_id;
                document.getElementById('edit_process').value = process;
            });
        });
    </script>
@endpush
