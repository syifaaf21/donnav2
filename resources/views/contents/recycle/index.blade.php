@extends('layouts.app')
@section('title', 'Recycle Bin')
@section('subtitle', 'Deleted master data (Super Admin only)')

@section('content')
    <div class="mx-auto px-6 bg-white rounded-xl py-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Recycle Bins</h3>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm shadow-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="text-xs text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Document Name</th>
                        <th class="px-3 py-2 text-left">Document No</th>
                        <th class="px-3 py-2 text-left">Registration No</th>
                        <th class="px-3 py-2 text-left">Department</th>
                        <th class="px-3 py-2 text-left">Scheduled Deletion</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($docs as $doc)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ optional($doc->document)->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $doc->document_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $doc->registration_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ optional($doc->department)->name ?? '—' }}</td>
                            <td class="px-3 py-2">
                                @if ($doc->marked_for_deletion_at)
                                    {{ \Carbon\Carbon::parse($doc->marked_for_deletion_at)->toDateString() }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <form method="POST" action="{{ route('recycle.restore', ['id' => $doc->id]) }}"
                                    id="restore-form-{{ $doc->resource ?? 'mapping' }}-{{ $doc->id }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="resource" value="{{ $doc->resource ?? 'mapping' }}">
                                    <button type="button" onclick="confirmRestore('{{ $doc->resource ?? 'mapping' }}', {{ $doc->id }})"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-xs">Restore</button>
                                </form>

                                <form method="POST" action="{{ route('recycle.force-delete', ['id' => $doc->id]) }}"
                                    id="delete-form-{{ $doc->resource ?? 'mapping' }}-{{ $doc->id }}" class="inline ml-2">
                                    @csrf
                                    <input type="hidden" name="resource" value="{{ $doc->resource ?? 'mapping' }}">
                                    <button type="button" onclick="confirmForceDelete('{{ $doc->resource ?? 'mapping' }}', {{ $doc->id }})"
                                        class="px-3 py-1 bg-red-600 text-white rounded text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">No items in recycle bin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $docs->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmRestore(resource, id) {
            var formId = 'restore-form-' + resource + '-' + id;
            var form = document.getElementById(formId);
            var label = (resource === 'ftpp') ? 'FTPP finding' : 'mapping';

            if (typeof Swal === 'undefined') {
                if (confirm('Restore this ' + label + ' and its related files?')) {
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Restore item?',
                text: 'This will restore the ' + label + ' and its related files.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Restoring...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    document.getElementById(formId).submit();
                }
            });
        }

        function confirmForceDelete(resource, id) {
            var formId = 'delete-form-' + resource + '-' + id;
            var form = document.getElementById(formId);
            var label = (resource === 'ftpp') ? 'FTPP finding' : 'mapping';

            if (typeof Swal === 'undefined') {
                if (confirm('Permanently delete this ' + label + ' and its files?')) {
                    document.getElementById(formId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Delete permanently?',
                text: 'This will permanently delete the ' + label + ' and all related files. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endpush
