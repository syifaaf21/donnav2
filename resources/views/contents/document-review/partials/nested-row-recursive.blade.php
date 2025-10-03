@php
    $children = $documents->filter(function ($doc) use ($mapping) {
        return $doc->document->parent_id === $mapping->document->id && $doc->part_number_id == $mapping->part_number_id;
    });
@endphp

<table class="table modern-table align-middle text-center table-hover mb-0">
    <thead class="table-light">
        <tr>
            <th></th>
            <th>#</th>
            <th>Document Name</th>
            <th>Document Number</th>
            <th>Part Number</th>
            <th>File</th>
            <th>Department</th>
            <th>Reminder Date</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Version</th>
            <th>Notes</th>
            <th>Updated By</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        {{-- Parent Row --}}
        <tr>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">
                @if ($children->count() > 0)
                    <button class="btn btn-sm btn-link toggle-children" data-bs-toggle="collapse"
                        data-bs-target="#childRow{{ $loopIndex }}" aria-expanded="false">
                        <i class="bi bi-plus-square"></i>
                    </button>
                @endif
            </td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $rowNumber ?? '-' }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->document->name }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->document_number }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->partNumber->part_number ?? '-' }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">
                @if ($mapping->file_path)
                    <button type="button" class="btn btn-outline-primary btn-sm view-file-btn" data-bs-toggle="modal"
                        data-bs-target="#viewFileModal" data-file="{{ asset('storage/' . $mapping->file_path) }}">
                        <i class="bi bi-file-earmark-text me-1"></i> View
                    </button>
                @endif
            </td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->department->name ?? '-' }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}
            </td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">
                @switch($mapping->status->name)
                    @case('Approved')
                        <span class="badge bg-success">Approved</span>
                    @break

                    @case('Rejected')
                        <span class="badge bg-danger">Rejected</span>
                    @break

                    @case('Need Review')
                        <span class="badge bg-warning text-dark">Need Review</span>
                    @break

                    @default
                        <span class="badge bg-secondary">{{ $mapping->status->name ?? '-' }}</span>
                @endswitch
            </td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->version }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->notes }}</td>
            <td style="padding-left: {{ ($depth ?? 0) * 20 }}px;">{{ $mapping->user->name ?? '-' }}</td>
            <td class="text-nowrap">
                @include('contents.document-review.partials.action-buttons', ['mapping' => $mapping])
            </td>
        </tr>

        {{-- Child Rows (Recursive) --}}
        @if ($children->count())
            <tr>
                <td colspan="14" class="p-0">
                    <div class="collapse" id="childRow{{ $loopIndex }}">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach ($children as $index => $child)
                                    @include('contents.document-review.partials.nested-row-recursive', [
                                        'mapping' => $child,
                                        'documents' => $documents,
                                        'loopIndex' => $loopIndex . '-' . $index,
                                        'rowNumber' => '-',
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>
