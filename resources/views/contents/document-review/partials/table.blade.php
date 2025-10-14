<table class="min-w-full table-auto text-sm text-left text-gray-700">
    <thead class="bg-gray-100 text-gray-600 uppercase">
        <tr>
            <th class="px-4 py-2">No</th>
            <th class="px-4 py-2">Part Number</th>
            <th class="px-4 py-2">Model</th>
            <th class="px-4 py-2">Process</th>
            <th class="px-4 py-2 text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($groupedData as $index => $group)
            @php
                $first = $group->first();
                $partNumber = $first->partNumber;
                $model = $partNumber->productModel->name ?? '-';
                $process = $partNumber->process ?? '-';
            @endphp
            <tr class="border-b border-gray-200 bg-white hover:bg-gray-50">
                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                <td class="px-4 py-2">{{ $partNumber->part_number }}</td>
                <td class="px-4 py-2">{{ $model }}</td>
                <td class="px-4 py-2">{{ ucwords($process) }}</td>
                <td class="px-4 py-2 text-center space-x-1">
                    <!-- 👁 Toggle Detail -->
                    <button type="button"
                        class="toggle-detail bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs"
                        data-target="#detail-{{ $index }}" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>

                    <!-- ✏️ Revisi -->
                    <a href="" class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs"
                        title="Revisi">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                </td>

            </tr>

            <!-- 🔽 Detail Row -->
            <tr id="detail-{{ $index }}" class="hidden bg-gray-50 border-t border-b border-gray-200">
                <td colspan="5" class="p-4">
                    <table class="w-full table-fixed text-sm text-left text-gray-600">
                        <thead class="bg-gray-100 text-gray-500 uppercase">
                            <tr>
                                {{-- <th class="px-2 py-1 w-8">No</th> --}}
                                <th class="px-2 py-1">Document Name</th>
                                <th class="px-2 py-1">Document Number</th>
                                <th class="px-2 py-1">Notes</th>
                                <th class="px-2 py-1">Reminder Date</th>
                                <th class="px-2 py-1">Deadline</th>
                                <th class="px-2 py-1">Last Update</th>
                                <th class="px-2 py-1">Updated By</th>
                                <th class="px-2 py-1">Status</th>
                                <th class="px-2 py-1 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group as $i => $doc)
                                <tr class="border-b">
                                    {{-- <td class="px-2 py-1">{{ $i + 1 }}</td> --}}
                                    <td class="px-2 py-1">{{ $doc->document->name }}</td>
                                    <td class="px-2 py-1">{{ $doc->document_number }}</td>
                                    <td class="px-2 py-1">{{ $doc->notes ?? '-' }}</td>
                                    <td class="px-2 py-1">
                                        {{ optional($doc->reminder_date)->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-2 py-1">
                                        {{ optional($doc->deadline)->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-2 py-1">
                                        {{ optional($doc->updated_at)->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-2 py-1">{{ $doc->user->name ?? '-' }}</td>
                                    <td class="px-2 py-1">{{ $doc->status->name ?? '-' }}</td>
                                    <td class="px-2 py-1 text-center space-x-1">
                                        <!-- 🔍 View Files -->
                                        <div class="dropdown d-inline">
                                            <button type="button"
                                                class="btn btn-outline-secondary px-2 py-1 rounded text-xs dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-paperclip"></i> Files
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end text-sm"
                                                style="min-width: 220px;">
                                                @forelse ($doc->files as $file)
                                                    @php
                                                        $fileUrl = asset('storage/' . $file->file_path);
                                                        $extension = strtolower(
                                                            pathinfo($file->file_path, PATHINFO_EXTENSION),
                                                        );
                                                        $isPdf = $extension === 'pdf';
                                                        $isOffice = in_array($extension, [
                                                            'doc',
                                                            'docx',
                                                            'xls',
                                                            'xlsx',
                                                        ]);
                                                        $viewerUrl = $isPdf
                                                            ? $fileUrl
                                                            : ($isOffice
                                                                ? 'https://docs.google.com/gview?url=' .
                                                                    urlencode($fileUrl) .
                                                                    '&embedded=true'
                                                                : null);
                                                    @endphp

                                                    <li class="px-3 py-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="text-truncate small" style="max-width: 140px;">
                                                                {{ $file->file_name ?? basename($file->file_path) }}
                                                            </span>
                                                            <div class="d-flex gap-1">
                                                                @if ($viewerUrl)
                                                                    <button type="button"
                                                                        class="btn btn-outline-primary btn-sm view-file-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#viewFileModal"
                                                                        data-file="{{ $viewerUrl }}" title="View">
                                                                        <i class="bi bi-eye"></i>
                                                                    </button>
                                                                @else
                                                                    <button class="btn btn-outline-secondary btn-sm"
                                                                        disabled title="Preview Not Supported">
                                                                        <i class="bi bi-ban"></i>
                                                                    </button>
                                                                @endif
                                                                <a href="{{ $fileUrl }}"
                                                                    class="btn btn-outline-success btn-sm" download
                                                                    title="Download">
                                                                    <i class="bi bi-download"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </li>

                                                    @if (!$loop->last)
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                    @endif
                                                @empty
                                                    <li class="dropdown-item-text text-muted small">No files available
                                                    </li>
                                                @endforelse
                                            </ul>
                                        </div>

                                        {{-- <!-- ✅ Approve -->
                                        <form action="" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-outline-success px-2 py-1 rounded text-xs"
                                                onclick="return confirm('Are you sure you want to approve this document?')"
                                                title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>

                                        <!-- ❌ Reject -->
                                        <form action="" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-outline-danger px-2 py-1 rounded text-xs"
                                                onclick="return confirm('Are you sure you want to reject this document?')"
                                                title="Reject">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400">
                        <p class="text-sm">No data found. Apply filters to see results.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
