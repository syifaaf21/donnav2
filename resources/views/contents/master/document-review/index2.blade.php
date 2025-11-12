@extends('layouts.app')
@section('title', 'Master Document Review')

@section('content')
    <div class="container mx-auto px-4 py-2" x-data="documentReviewTabs('{{ \Illuminate\Support\Str::slug(array_key_first($groupedByPlant)) }}')">

        {{-- Header: Breadcrumbs + Add Button --}}
        <div class="flex justify-between items-center mb-3">
            {{-- Breadcrumbs --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-reset flex space-x-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline flex items-center">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li>/</li>
                    <li>Master</li>
                    <li>/</li>
                    <li>Documents</li>
                    <li>/</li>
                    <li class="text-gray-700 font-medium">Review</li>
                </ol>
            </nav>

            {{-- Add Document Button --}}
            <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                <i class="bi bi-plus-circle"></i>
                <span>Add Document</span>
            </button>
            @include('contents.master.document-review.partials.modal-add2')
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden p-3">
            {{-- Tabs + Search --}}
            <div class="flex flex-wrap justify-between items-center border-b border-gray-100 p-4">
                {{-- Tabs --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ($groupedByPlant as $plant => $documents)
                        @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                        <button type="button" @click="setActiveTab('{{ $slug }}')"
                            :class="activeTab === '{{ $slug }}'
                                ?
                                'bg-gray-100 text-gray-800 border-gray-100' :
                                'bg-white text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-lg border border-gray-200 text-sm font-medium transition">
                            {{ ucfirst(strtolower($plant)) }}
                        </button>
                    @endforeach
                </div>

                {{-- Search Bar --}}
                <form id="searchForm" method="GET" class="flex items-center w-full max-w-sm relative">
                    <input type="text" name="search" id="searchInput"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" id="clearSearch"
                        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>

            {{-- Table per Plant --}}
            <div class="overflow-x-auto overflow-y-auto">
                @foreach ($groupedByPlant as $plant => $documents)
                    @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                    <div x-show="activeTab === '{{ $slug }}'" x-transition>
                        <div class="overflow-auto rounded-bottom-lg">
                            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-600">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">No</th>
                                        <th class="px-4 py-2 text-left">Document Number</th>
                                        {{-- <th class="px-4 py-2 text-left">Document Name</th> --}}
                                        <th class="px-4 py-2 text-left">Part Number</th>
                                        <th class="px-4 py-2 text-left">Model</th>
                                        <th class="px-4 py-2 text-left">Product</th>
                                        <th class="px-4 py-2 text-left">Process</th>
                                        <th class="px-4 py-2 text-left">Department</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($documents->isEmpty())
                                        <tr>
                                            <td colspan="7" class="text-center text-gray-500 py-4">
                                                <i data-feather="folder-x" class="mx-auto w-6 h-6 mb-1"></i>
                                                No Document found for this tab.
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($documents as $index => $doc)
                                            <tr>
                                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                                <td class="px-4 py-2">{{ $doc->document_number }}</td>
                                                {{-- <td class="px-4 py-2">{{ $doc->document->name ?? '-' }}</td> --}}
                                                <td class="px-4 py-2">{{ $doc->partNumber->part_number ?? '-' }}</td>
                                                <td class="px-4 py-2">{{ $doc->model->name ?? '-' }}</td>
                                                <td class="px-4 py-2">{{ $doc->product->name ?? '-' }}</td>
                                                <td class="px-4 py-2">{{ $doc->process->name ?? '-' }}</td>
                                                <td class="px-4 py-2">{{ $doc->department->name ?? '-' }}</td>
                                                <td class="px-4 py-2">{{ $doc->status->name ?? '-' }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#editDocumentModal-{{ $doc->id }}">
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>

                            {{-- Include edit modal per document --}}
                            @foreach ($documents as $doc)
                                @include('contents.master.document-review.partials.modal-edit', [
                                    'mapping' => $doc,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 📄 Modal Fullscreen View File -->
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 rounded-0 shadow-none">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-semibold">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i> Document Viewer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <iframe id="fileViewer" src="" width="100%" height="100%" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, Alpine available:', typeof Alpine !== 'undefined');

            // Pastikan Alpine sudah siap
            if (typeof Alpine !== 'undefined') {
                Alpine.data('documentReviewTabs', (defaultTab = 'unit') => ({
                    activeTab: defaultTab,
                    setActiveTab(tab) {
                        console.log('Switching tab to', tab);
                        this.activeTab = tab;
                    },
                }));

                console.log('✅ Alpine component registered: documentReviewTabs');
            } else {
                console.error('❌ Alpine is not available!');
            }
        });
    </script>
@endpush
