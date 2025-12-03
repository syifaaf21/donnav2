@php
    $rowId = 'row-' . $document->id;
    $parentClass = $parent_id ? 'child-of-' . $parent_id : '';
@endphp

<tr id="{{ $rowId }}" class="{{ $parentClass }} {{ $parent_id ? 'hidden' : '' }}"
    class="hover:bg-gray-50 transition-all duration-150">
    {{-- Document Name & toggle --}}
    <td class="px-4 py-3">
        <div class="flex items-center" style="margin-left: {{ $level * 30 }}px">
            @if ($document->children->isNotEmpty())
                <button type="button" class="toggle-children mr-1" data-target="child-of-{{ $document->id }}">
                    <i data-feather="chevron-right" class="w-4 h-4 transition-transform"></i>
                </button>
            @else
                <span class="w-4 h-4 inline-block mr-1"></span>
            @endif

            @if ($document->children->isNotEmpty())
                <i data-feather="folder" class="w-4 h-4 text-yellow-500 mr-1"></i>
            @else
                <i data-feather="file-text" class="w-4 h-4 text-blue-500 mr-1"></i>
            @endif

            <span class="text-gray-800 font-medium">{{ ucwords($document->name) }}</span>
        </div>
    </td>

    <td class="px-4 py-3 text-gray-500">{{ ucwords($document->code) ?: '-' }}</td>

    {{-- Actions --}}
    <td class="px-4 py-3 text-center">
        <div class="flex gap-2 justify-center">
            {{-- Edit Button --}}
            <button type="button" data-bs-toggle="modal" data-bs-target="#editDocumentModal-{{ $document->id }}"
                data-bs-title="Edit Document"
                class="w-8 h-8 rounded-full bg-yellow-500 text-white hover:bg-yellow-500 transition-colors p-2 duration-200">
                <i data-feather="edit" class="w-4 h-4"></i>
            </button>

            {{-- Delete Button --}}
            <form method="POST" action="{{ route('master.hierarchy.destroy', $document->id) }}"
                class="d-inline delete-form">
                @csrf
                @method('DELETE')
                <button type="submit" data-bs-title="Delete Document"
                    class=" w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors p-2">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </td>

</tr>

{{-- Modal Edit --}}
<div class="modal fade" id="editDocumentModal-{{ $document->id }}" tabindex="-1"
    aria-labelledby="editDocumentModalLabel-{{ $document->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('master.hierarchy.update', $document->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header justify-content-center position-relative p-4 rounded-top-4"
                    style="background-color: #f5f5f7;">
                    <h5 class="modal-title fw-semibold" id="editDocumentModalLabel-{{ $document->id }}"
                        style="font-family: 'Inter', sans-serif; font-size: 1.25rem;">
                        <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Document
                    </h5>
                    <button type="button"
                        class="btn btn-light position-absolute top-0 end-0 m-3 p-2 rounded-circle shadow-sm"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="width: 36px; height: 36px; border: 1px solid #ddd;">
                        <span aria-hidden="true" class="text-dark fw-bold">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-5" style="font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                    <div class="row g-4">
                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control rounded-3 @error('name') is-invalid @enderror"
                                value="{{ old('name', $document->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Parent Document --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent Document</label>
                            <select name="parent_id"
                                class="form-select rounded-3 @error('parent_id') is-invalid @enderror tomselect">
                                <option value="">-- No Parent (Top Level) --</option>
                                @foreach ($parents as $parentDoc)
                                    @if (!isset($document) || $parentDoc->id !== $document->id)
                                        <option value="{{ $parentDoc->id }}"
                                            {{ old('parent_id', $document->parent_id ?? null) == $parentDoc->id ? 'selected' : '' }}>
                                            {{ $parentDoc->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="type" value="review">

                        {{-- Code --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code"
                                class="form-control rounded-3 @error('code') is-invalid @enderror"
                                value="{{ $document->code }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 justify-content-between bg-white rounded-bottom-4">
                    <button type="button" class="btn btn-link text-secondary fw-semibold px-4 py-2"
                        data-bs-dismiss="modal" style="text-decoration: none; transition: background-color 0.3s ease;">
                        Cancel
                    </button>
                    <button type="submit" class="btn px-3 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded hover:from-primaryDark hover:to-primary transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($document->children->isNotEmpty())
    @foreach ($document->children as $child)
        @include('contents.master.hierarchy.partials.tree-node', [
            'document' => $child,
            'level' => $level + 1,
            'number' => $number,
            'parent_id' => $document->id,
        ])
    @endforeach
@endif
