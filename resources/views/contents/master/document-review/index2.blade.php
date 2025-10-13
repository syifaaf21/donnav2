@extends('layouts.app')
@section('title', 'Document Review')

@section('content')
    <div class="container">
        <div x-data="documentReviewTabs('{{ \Illuminate\Support\Str::slug(array_key_first($groupedByPlant)) }}')">
            {{-- 🔹 Header: Breadcrumbs + Add Button --}}
            <div class="flex items-center justify-between mb-4">
                {{-- Breadcrumbs --}}
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
                                <i class="bi bi-house-door me-1"></i> Home
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-decoration-none text-secondary">Documents</a>
                        </li>
                        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
                            Review
                        </li>
                    </ol>
                </nav>

                {{-- Add Document Button --}}
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#addDocumentModal">
                    <i class="bi bi-plus-circle"></i> Add Document
                </button>
                @include('contents.master.document-review.partials.modal-add')
            </div>

            {{-- 🔹 Inline Filter Form --}}
            <form method="GET" id="filterForm" class="card p-3 border-0 shadow-sm mb-3">
                <div class="row g-3 align-items-end">
            {{-- Document Name --}}
            {{-- <div class="col-md-3">
                        <label class="form-label fw-semibold">Document Name</label>
                        <select name="document_id" class="form-select form-select-sm">
                            <option value="">All Documents</option>
                            @foreach ($documentsMaster as $doc)
                                <option value="{{ $doc->id }}"
                                    {{ request('document_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

            {{-- Status --}}
            {{-- <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="Need Review" {{ request('status') == 'Need Review' ? 'selected' : '' }}>Need
                                Review</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div> --}}

            {{-- Department --}}
            {{-- <div class="col-md-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ request('department') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

            {{-- Deadline --}}
            {{-- <div class="col-md-2">
                        <label class="form-label fw-semibold">Deadline</label>
                        <input type="date" name="deadline" class="form-control form-control-sm"
                            value="{{ request('deadline') }}">
                    </div> --}}

            {{-- Buttons --}}
            {{-- <div class="col-md-1 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100" title="Apply Filters">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <button type="button" id="clearFilters" class="btn btn-outline-danger btn-sm w-100"
                            title="Clear All">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </form> --}}

            {{-- 🔹 Tabs + Search + Table --}}
            <div class="card border-0 shadow-sm">
                {{-- Header Tabs + Search --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between border-bottom px-3 pt-3 pb-2">
                    {{-- Tabs --}}
                    <div class="flex flex-wrap">
                        @foreach ($groupedByPlant as $plant => $documents)
                            @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                            <button type="button" @click="setActiveTab('{{ $slug }}')"
                                :class="activeTab === '{{ $slug }}'
                                    ?
                                    'bg-gray-100 text-gray-800 border-gray-100' :
                                    'bg-white text-gray-600 hover:bg-gray-100'"
                                class="px-4 py-2 rounded-t-lg border border-gray-200 text-sm font-medium transition">
                                <i data-feather="settings" class="inline w-4 h-4 me-1"></i>
                                {{ ucfirst(strtolower($plant)) }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Search Bar --}}
                    <div class="input-group mt-2 mt-md-0" style="width: 400px; max-width: 100%;">
                        <input type="text" name="search" form="filterForm" class="form-control form-control-sm"
                            placeholder="Search by Part Number" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary btn-sm" type="submit" form="filterForm" title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearSearch" title="Clear Search">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card-body p-0">
                    @foreach ($groupedByPlant as $plant => $documents)
                        @php $slug = \Illuminate\Support\Str::slug($plant); @endphp
                        <div x-show="activeTab === '{{ $slug }}'" x-transition>
                            <div class="overflow-auto rounded-bottom-lg">
                                <table class="min-w-full text-sm text-left text-gray-700">
                                    @include('contents.master.document-review.partials.table-header')

                                    <tbody>
                                        @php
                                            $parents = $documents->filter(
                                                fn($doc) => $doc->document && is_null($doc->document->parent_id),
                                            );
                                        @endphp

                                        @if ($parents->isEmpty())
                                            <tr>
                                                <td colspan="14" class="text-center py-8 text-gray-400">
                                                    <i data-feather="folder-x" class="mx-auto w-6 h-6 mb-1"></i>
                                                    No Document found for this tab.
                                                </td>
                                            </tr>
                                        @else
                                            @foreach ($parents as $index => $parent)
                                                @include(
                                                    'contents.master.document-review.partials.nested-row-recursive',
                                                    [
                                                        'mapping' => $parent,
                                                        'documents' => $documents,
                                                        'loopIndex' => 'parent-' . $index,
                                                        'rowNumber' => $loop->iteration,
                                                        'depth' => 0,
                                                        'numbering' => $loop->iteration . '',
                                                    ]
                                                )
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>

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
    </div>
@endsection

@push('scripts')
    <x-sweetalert-confirm />
    <script>
        function documentReviewTabs(defaultTab) {
            return {
                activeTab: localStorage.getItem('activeTab') || defaultTab,
                setActiveTab(tab) {
                    this.activeTab = tab;
                    localStorage.setItem('activeTab', tab);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Clear Filters
            document.getElementById('clearFilters')?.addEventListener('click', function() {
                const form = document.getElementById('filterForm');
                form.querySelectorAll('input, select').forEach(el => el.value = '');
                form.submit();
            });

            // Clear Search only
            document.getElementById('clearSearch')?.addEventListener('click', function() {
                document.querySelector('input[name="search"]').value = '';
                document.getElementById('filterForm').submit();
            });

            feather.replace();
        });
        // in form message
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua form yang butuh validasi
            const forms = document.querySelectorAll('.needs-validation');

            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault(); // Stop form submit
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated'); // Tambahkan class validasi Bootstrap
                }, false);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const plantSelect = document.getElementById('addPlantSelect');
            const partNumberSelect = document.getElementById('addPartNumberSelect');

            function filterPartNumbersByPlant(plantValue) {
                const options = partNumberSelect.querySelectorAll('option');

                options.forEach(option => {
                    const dataPlant = option.dataset.plant;

                    // Tampilkan semua jika belum pilih plant
                    if (!plantValue || option.value === '') {
                        option.style.display = '';
                    } else {
                        option.style.display = (dataPlant === plantValue) ? '' : 'none';
                    }
                });

                // Reset value jika tidak cocok
                if (![...partNumberSelect.options].some(opt => opt.value === partNumberSelect.value && opt.style
                        .display !== 'none')) {
                    partNumberSelect.value = '';
                }
            }

            // Saat plant berubah
            plantSelect?.addEventListener('change', function() {
                filterPartNumbersByPlant(this.value);
            });

            // Filter saat modal dibuka (jaga-jaga)
            if (plantSelect?.value) {
                filterPartNumbersByPlant(plantSelect.value);
            }
        });

        //View File in tab
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('viewFileModal');
            const iframe = document.getElementById('fileViewer');

            document.querySelectorAll('.view-file-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const fileUrl = this.dataset.file;
                    iframe.src = fileUrl;
                });
            });

            modal.addEventListener('hidden.bs.modal', () => {
                iframe.src = '';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.toggle-children');
                if (!btn) return;

                // Ambil target class unik, misal: children-of-12-3
                const target = btn.dataset.target;
                if (!target) return;

                // Toggle semua anak dengan class itu
                document.querySelectorAll('.' + target).forEach(el => {
                    el.classList.toggle('d-none');
                });

                // Ganti ikon dari + jadi - dan sebaliknya
                const icon = btn.querySelector('i');
                if (icon) {
                    if (icon.classList.contains('bi-plus-square')) {
                        icon.classList.remove('bi-plus-square');
                        icon.classList.add('bi-dash-square');
                    } else {
                        icon.classList.remove('bi-dash-square');
                        icon.classList.add('bi-plus-square');
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Deteksi posisi dropdown saat akan dibuka
            document.querySelectorAll('.dropdown').forEach(drop => {
                drop.addEventListener('show.bs.dropdown', function() {
                    const rect = drop.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;

                    // Kalau ruang di bawah sempit dan di atas lebih luas → ubah jadi dropup
                    if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                        drop.classList.add('dropup');
                    } else {
                        drop.classList.remove('dropup');
                    }
                });

                // Hapus class dropup saat dropdown ditutup
                drop.addEventListener('hidden.bs.dropdown', function() {
                    drop.classList.remove('dropup');
                });
            });
        });
    </script>
@endpush
@push('styles')
    <style>
        .toggle-children i.rotated {
            transform: rotate(90deg);
            transition: transform 0.15s ease-in-out;
        }
    </style>
@endpush
