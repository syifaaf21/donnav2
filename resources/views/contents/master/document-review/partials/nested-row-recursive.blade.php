@php
    $children = $documents->filter(function ($doc) use ($mapping) {
        return $doc->document->parent_id === $mapping->document->id && $doc->part_number_id == $mapping->part_number_id;
    });
@endphp

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
    <td>{{ $numbering ?? '-' }}</td>
    <td>{{ $mapping->document->name }}</td>
    <td>{{ $mapping->document_number }}</td>
    <td>{{ $mapping->partNumber->part_number ?? '-' }}</td>
    <td>{{ $mapping->department->name ?? '-' }}</td>
    <td>{{ $mapping->reminder_date ? \Carbon\Carbon::parse($mapping->reminder_date)->format('Y-m-d') : '-' }}</td>
    <td>{{ $mapping->deadline ? \Carbon\Carbon::parse($mapping->deadline)->format('Y-m-d') : '-' }}</td>
    <td class="text-nowrap">
        @include('contents.master.document-review.partials.action-buttons', ['mapping' => $mapping])
    </td>
</tr>

{{-- Child Rows (Recursive) --}}
@if ($children->count())
    <tr>
        <td colspan="14" class="p-0">
            <div class="collapse" id="childRow{{ $loopIndex }}">
                <table class="table table-sm mb-0">
                    <thead>
                        @include('contents.master.document-review.partials.table-header')
                    </thead>
                    <tbody>
                        @foreach ($children as $index => $child)
                            @include('contents.master.document-review.partials.nested-row-recursive', [
                                'mapping' => $child,
                                'documents' => $documents,
                                'loopIndex' => $loopIndex . '-' . $loop->iteration,
                                'rowNumber' => '-',
                                'depth' => ($depth ?? 0) + 1,
                                'numbering' => ($numbering ?? '') . '.' . $loop->iteration, // contoh: '1.2.1'
                            ])
                        @endforeach

                    </tbody>
                </table>
            </div>
        </td>
    </tr>
@endif
