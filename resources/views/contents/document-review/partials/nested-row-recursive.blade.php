@php
    $children = $documents->filter(function ($doc) use ($mapping) {
        return $doc->document->parent_id === $mapping->document->id && $doc->part_number_id == $mapping->part_number_id;
    });
@endphp

<tbody>
    {{-- Parent Row --}}
    <tr>
        <td>
            @if ($children->count() > 0)
                <button class="btn btn-sm btn-link toggle-children" data-bs-toggle="collapse"
                    data-bs-target="#childRow{{ $loopIndex }}" aria-expanded="false">
                    <i class="bi bi-plus-square"></i>
                </button>
            @endif
        </td>
        <td>{{ $rowNumber ?? '-' }}</td>
        <td>{{ $mapping->document->name }}</td>
        <td>{{ $mapping->document_number }}</td>
        <td>{{ $mapping->partNumber->part_number ?? '-' }}</td>
        <td>
            @if ($mapping->files->count())
                @foreach ($mapping->files as $file)
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                        class="btn btn-sm btn-outline-primary mb-1">
                        View {{ $loop->iteration }}
                    </a>
                @endforeach
            @else
                -
            @endif
        </td>
        <td>{{ $mapping->department->name ?? '-' }}</td>
        <td>
            {{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}
        </td>
        <td>
            {{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}</td>
        <td>
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
        <td>{{ $mapping->version }}</td>
        <td>{{ $mapping->notes }}</td>
        <td>{{ $mapping->user->name ?? '-' }}</td>
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
                        @include('contents.document-review.partials.table-header')
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
