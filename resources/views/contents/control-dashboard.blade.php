@extends('layouts.app')
@section('title', 'Document Control Dashboard')
@section('subtitle', 'Comprehensive overview of all Document Control activities and statuses.')

@section('content')
    <div class="px-4 mt-4">
        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-4 mb-5">
            @php
                // determine Need Review count robustly (case / separator insensitive)
                $needReviewCount = 0;
                if (!empty($statusBreakdown) && is_array($statusBreakdown)) {
                    foreach ($statusBreakdown as $k => $v) {
                        $norm = strtolower(str_replace([' ', '-', '_'], '', $k));
                        if ($norm === 'needreview') {
                            $needReviewCount = $v;
                            break;
                        }
                    }
                }

                $cards = [
                    [
                        'label' => 'Total Documents',
                        'value' => $totalDocuments,
                        'color' => 'primary',
                        'icon' => 'bi-collection',
                        'route' => route('document-control.index'),
                    ],
                    // [
                    //     'label' => 'Active Documents',
                    //     'value' => $activeDocuments,
                    //     'color' => 'success',
                    //     'icon' => 'bi-check-circle',
                    // ],
                    [
                        'label' => 'Obsolete Documents',
                        'value' => $obsoleteDocuments->count(),
                        'color' => 'secondary',
                        'icon' => 'bi-slash-circle',
                    ],
                    [
                        'label' => 'Uncomplete Documents',
                        'value' => $uncompleteDocuments,
                        'color' => 'warning',
                        'icon' => 'bi-exclamation-triangle',
                    ],
                    [
                        'label' => 'Rejected Documents',
                        'value' => $rejectedDocuments,
                        'color' => 'danger',
                        'icon' => 'bi-x-circle',
                    ],
                    [
                        'label' => 'Need Review',
                        'value' => $needReviewCount,
                        'color' => 'info',
                        'icon' => 'bi-search',
                        'route' => route('document-control.approval'),
                    ],
                ];
            @endphp

            @foreach ($cards as $c)
                <div class="col-6 col-lg-2-4">
                    @if(!empty($c['route']))
                        <a href="{{ $c['route'] }}" class="d-block text-decoration-none" style="color:inherit;">
                            <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:translate-y-[-4px]
                                transition-all duration-200 border-0 h-100 overflow-hidden"
                                style="border-radius: 14px; border-left: 4px solid var(--bs-{{ $c['color'] }});">

                                <div class="card-body p-3 d-flex flex-column justify-content-between">

                                    {{-- Label --}}
                                    <small class="text-muted fw-semibold mb-2" style="font-size: 0.9rem; letter-spacing: 0.3px;">
                                        {{ $c['label'] }}
                                    </small>

                                    {{-- Value --}}
                                    <div class="fw-bold mb-2 text-{{ $c['color'] }}" style="font-size: 2.2rem; line-height: 1;">
                                        {{ $c['value'] }}
                                    </div>

                                    {{-- Icon --}}
                                    <div class="ms-auto mt-2"
                                        style="
                                    font-size: 1.7rem;
                                    padding: 8px 12px;
                                    border-radius: 12px;
                                    background: rgba(var(--bs-{{ $c['color'] }}-rgb), 0.12);
                                    color: var(--bs-{{ $c['color'] }});
                                ">
                                        <i class="bi {{ $c['icon'] }}"></i>
                                    </div>

                                </div>
                            </div>
                        </a>
                    @else
                        <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:translate-y-[-4px]
                            transition-all duration-200 border-0 h-100 overflow-hidden"
                            style="border-radius: 14px; border-left: 4px solid var(--bs-{{ $c['color'] }});">

                            <div class="card-body p-3 d-flex flex-column justify-content-between">

                                {{-- Label --}}
                                <small class="text-muted fw-semibold mb-2" style="font-size: 0.9rem; letter-spacing: 0.3px;">
                                    {{ $c['label'] }}
                                </small>

                                {{-- Value --}}
                                <div class="fw-bold mb-2 text-{{ $c['color'] }}" style="font-size: 2.2rem; line-height: 1;">
                                    {{ $c['value'] }}
                                </div>

                                {{-- Icon --}}
                                <div class="ms-auto mt-2"
                                    style="
                                font-size: 1.7rem;
                                padding: 8px 12px;
                                border-radius: 12px;
                                background: rgba(var(--bs-{{ $c['color'] }}-rgb), 0.12);
                                color: var(--bs-{{ $c['color'] }});
                            ">
                                    <i class="bi {{ $c['icon'] }}"></i>
                                </div>

                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ===== CHARTS SECTION ===== --}}
        <div class="row g-3">
            {{-- Pie Chart - Status Breakdown --}}
            <div class="col-lg-4">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 h-100 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2"
                            style="font-size: 0.95rem; color: #1f2937;">
                            <div
                                style="width: 28px; height: 28px; background-color: rgba(34,197,94,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="pie-chart"
                                    style="width: 16px; height: 16px; color: rgba(34,197,94,0.7);"></i>
                            </div>
                            <span>Status Distribution</span>
                        </div>
                        <div class="d-flex justify-content-center">
                            <canvas id="statusPie" style="max-width: 100%; max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Summary (new card placed to the right of the pie chart) --}}
            <div class="col-lg-8">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 h-100 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2"
                                style="font-size: 1rem; color: #1f2937;">
                                <span
                                    style="display: inline-flex; align-items: center; justify-content: center;
                                   width: 20px; height: 20px;
                                   background-color: rgba(99,102,241,0.12); border-radius: 4px;">
                                    <i data-feather="layers" style="width: 16px; height: 16px; color: rgba(99,102,241,0.8);"></i>
                                </span>
                                Status Summary
                            </h6>
                        </div>

                        <div class="d-flex flex-wrap align-items-stretch gap-3 justify-content-between">
                            @php
                                // Only display these five statuses in this specific order
                                $orderedStatuses = [
                                    'active' => 'Active',
                                    'uncomplete' => 'Uncomplete',
                                    'obsolete' => 'Obsolete',
                                    'rejected' => 'Rejected',
                                    'need_review' => 'Need Review',
                                ];

                                // Color mapping (hex) aligned with the pie chart palette
                                $colorMap = [
                                    'active' => '#A8E6CF',     // green
                                    'uncomplete' => '#F59E0B',  // amber (distinct)
                                    'obsolete' => '#A8D8EA',    // light blue
                                    'rejected' => '#FF6B6B',    // brighter red (distinct)
                                    'need_review' => '#FFD3B6', // peach/yellow
                                ];

                                // Normalize statusBreakdown keys: accept both 'need review' and 'need_review'
                                $normalized = [];
                                foreach ($statusBreakdown as $k => $v) {
                                    $norm = strtolower(str_replace([' ', '-'], '_', $k));
                                    $normalized[$norm] = $v;
                                }
                            @endphp

                            @foreach ($orderedStatuses as $sKey => $sLabel)
                                @php
                                    $count = $normalized[$sKey] ?? 0;
                                    $hex = $colorMap[$sKey] ?? '#6b7280';
                                    // determine readable text color (white for colored badges)
                                    $textColor = '#ffffff';
                                @endphp
                                <div class="status-card d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="text-muted small mb-1">{{ $sLabel }}</div>
                                    </div>
                                    <div class="ms-2">
                                        <span class="rounded-pill py-2 px-3" style="background: {{ $hex }}; color: {{ $textColor }}; display: inline-block; min-width:40px; text-align:center;">{{ $count }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents per Department (moved below) --}}
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 h-100 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2"
                                style="font-size: 1rem; color: #1f2937;">
                                <span
                                    style="display: inline-flex; align-items: center; justify-content: center;
                                   width: 20px; height: 20px;
                                   background-color: rgba(34,197,94,0.15); border-radius: 4px;">
                                    <i data-feather="bar-chart-2"
                                        style="width: 16px; height: 16px; color: rgba(34,197,94,0.7);"></i>
                                </span>
                                Documents per Department
                            </h6>

                            {{-- Toggle for Obsolete Table --}}
                            <div class="d-flex align-items-center gap-2">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggleObsoleteSwitch">
                                    <span class="slider"></span>
                                </label>
                                <span class="label-text" style="font-size: 0.75rem; color: #6b7280;">
                                    View Obsolete
                                </span>
                            </div>
                        </div>

                        <canvas id="controlDocsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Obsolete Documents Table --}}
        <div id="obsoleteTableContainer"
            class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
       hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2 mt-3 d-none"
            style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-2">

                {{-- Label --}}
                <div class="fw-semibold mb-2 d-flex align-items-center gap-2" style="color: #1f2937; font-size: 1rem;">
                    <div
                        style="width: 24px; height: 24px;
                        background: rgba(239,68,68,0.15); border-radius: 6px;
                        display: flex; align-items: center; justify-content: center;">
                        <i data-feather="file-minus" style="width: 16px; height: 16px; color: rgba(239,68,68,0.7);"></i>
                    </div>
                    <span>Obsolete Documents</span>
                </div>

                {{-- Table content --}}
                <div id="obsoleteTableContent" style="font-size: 0.8rem;"></div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center gap-2 mt-2">
                    <button class="btn btn-outline-secondary btn-sm" id="prevPage"
                        style="border-radius: 6px; font-size: 0.75rem; padding: 3px 10px;">
                        <i class="bi bi-chevron-left me-1" style="font-size: 0.8rem;"></i> Previous
                    </button>

                    <button class="btn btn-outline-secondary btn-sm" id="nextPage"
                        style="border-radius: 6px; font-size: 0.75rem; padding: 3px 10px;">
                        Next <i class="bi bi-chevron-right ms-1" style="font-size: 0.8rem;"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Scroll to Top Button --}}
        <button id="scrollUpBtn"
            class="fixed bottom-5 right-5 w-12 h-12 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300"
            title="Scroll to top" style="background: linear-gradient(135deg, #3b82f6, #0ea5e9); z-index: 50;">
            <i class="bi bi-chevron-up text-lg"></i>
        </button>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const obsoleteDocs = @json($obsoleteDocuments);
        const departments = @json($departments);
        const controlDocuments = @json($controlDocuments);
        const controlExtraData = @json($controlExtraData);
        const statusBreakdown = @json($statusBreakdown);
    </script>

    <script>
        /* ===================== PIE CHART - STATUS DISTRIBUTION ===================== */
        const statusLabels = Object.keys(statusBreakdown);
        const statusData = Object.values(statusBreakdown);
        const statusColors = ['#A8E6CF', '#FFD3B6', '#FF6B6B', '#F59E0B', '#A8D8EA', '#D4A5A5'];

        new Chart(document.getElementById('statusPie'), {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        /* ===================== BAR CHART - DOCUMENTS PER DEPARTMENT (per-status colors) ===================== */
        const orderedStatusKeys = ['active','need_review','rejected','uncomplete','obsolete'];

        const controlLabels = Object.values(departments);
        const shortControlLabels = controlLabels.map(name => {
            const words = name.split(' ');
            return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
        });

        const deptIds = Object.keys(departments);

        // Helper: robust lookup for possible backend key naming (snake_case, camelCase, spaced, Count suffixes)
        function lookupStatusCount(extra, key) {
            if (!extra) return 0;
            const camel = key.replace(/_([a-z])/g, (m, c) => c.toUpperCase());
            const spaced = key.replace(/_/g, ' ');
            const compact = key.replace(/_/g, '');
            const candidates = [key, camel, spaced, compact, key + 'Count', camel + 'Count', compact + 'Count'];
            for (let k of candidates) {
                if (typeof extra[k] !== 'undefined' && extra[k] !== null) return extra[k];
            }
            return 0;
        }

        // readable labels & colors per status
        const statusReadableMap = {
            'active': 'Active',
            'need_review': 'Need Review',
            'rejected': 'Rejected',
            'uncomplete': 'Uncomplete',
            'obsolete': 'Obsolete'
        };

        const statusColorMap = {
            'active': '#A8E6CF',
            'need_review': '#FFD3B6',
            'rejected': '#FF6B6B',
            'uncomplete': '#F59E0B',
            'obsolete': '#A8D8EA'
        };

        // Build datasets: one dataset per status key
        const statusDatasets = orderedStatusKeys.map(k => {
            return {
                label: statusReadableMap[k] || k,
                data: deptIds.map(id => Number(lookupStatusCount(controlExtraData[id] || {}, k) || 0)),
                backgroundColor: statusColorMap[k] || '#CBD5E1',
                borderRadius: 6,
                barThickness: 18,
                stack: 'statuses'
            };
        });

        new Chart(document.getElementById('controlDocsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shortControlLabels,
                datasets: statusDatasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                const deptId = Object.keys(departments)[index];
                                return departments[deptId];
                            },
                            label: function(context) {
                                const datasetLabel = context.dataset.label || '';
                                const value = context.raw || 0;
                                return `${datasetLabel}: ${value}`;
                            },
                            labelColor: function(context) {
                                return {
                                    backgroundColor: context.dataset.backgroundColor || '#000',
                                    borderColor: context.dataset.backgroundColor || '#000',
                                    borderWidth: 1,
                                    width: 12,
                                    height: 12
                                };
                            }
                        }
                    }

                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#e9ecef'
                        },
                        stacked: true
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        },
                        stacked: true
                    }
                }
            }
        });

        /* ===================== OBSOLETE TABLE TOGGLE + PAGINATION ===================== */
        const obsoleteData = obsoleteDocs;
        let currentPage = 1;
        const rowsPerPage = 10;

        const container = document.getElementById("obsoleteTableContainer");
        const content = document.getElementById("obsoleteTableContent");
        const toggleSwitch = document.getElementById("toggleObsoleteSwitch");

        toggleSwitch.addEventListener("change", () => {
            container.classList.toggle("d-none");

            if (!container.classList.contains("d-none")) {
                renderTable();
            }
        });

        function renderTable() {
            const start = (currentPage - 1) * rowsPerPage;
            const paginated = obsoleteData.slice(start, start + rowsPerPage);

            let html = `
            <div class="table-responsive">
                <table class="table mb-0 align-middle"
                    style="border-radius: 12px; overflow: hidden; border:1px solid #e5e7eb;">
                    <thead class="table-header-blue">
                        <tr>
                            <th class="small py-2">Document Name</th>
                            <th class="small py-2">Department</th>
                            <th class="small py-2">Obsolete Date</th>
                        </tr>
                    </thead>
                    <tbody>
                 `;

            if (paginated.length === 0) {
                html += `
                <tr>
                    <td colspan="3" class="text-muted small py-4 text-center">
                        No obsolete documents found.
                    </td>
                </tr>`;
            } else {
                paginated.forEach(d => {
                    html += `
                    <tr class="table-row-hover">
                        <td class="small py-2">${d.document?.name ?? '-'}</td>
                        <td class="small py-2">${d.department?.name ?? '-'}</td>
                        <td class="small py-2">${d.obsolete_date ?? '-'}</td>
                    </tr>`;
                });
            }

            html += `</tbody></table></div>`;
            content.innerHTML = html;
        }

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            if ((currentPage * rowsPerPage) < obsoleteData.length) {
                currentPage++;
                renderTable();
            }
        });

        /* ===================== SCROLL TO TOP BUTTON ===================== */
        document.addEventListener('DOMContentLoaded', function() {
            const scrollBtn = document.getElementById('scrollUpBtn');

            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    scrollBtn.classList.remove('hidden');
                } else {
                    scrollBtn.classList.add('hidden');
                }
            });

            scrollBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            scrollBtn.classList.add('hidden');
        });

        feather.replace();
    </script>
@endpush

@push('styles')
    <style>
        /* Compact status summary cards */
        .status-card {
            flex: 1 1 320px;
            min-width: 280px;
            max-width: 48%;
            padding: 18px 20px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #eef2ff;
            box-shadow: none;
            gap: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
        }

        .status-card .small {
            font-size: 0.95rem;
            color: #374151;
        }

        .status-card .fw-bold { font-size: 1.3rem; }

        .status-card .badge { font-size: 1rem; padding: 0.45rem 0.75rem; }

        @media (max-width: 1200px) {
            .status-card { max-width: 48%; }
        }

        @media (max-width: 768px) {
            .status-card { min-width: 48%; max-width: 48%; }
        }

        @media (max-width: 480px) {
            .status-card { min-width: 100%; max-width: 100%; }
        }

        /* Small Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 16px;
            vertical-align: middle;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-switch .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 16px;
        }

        .toggle-switch .slider::before {
            position: absolute;
            content: "";
            height: 12px;
            width: 12px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .toggle-switch input:checked+.slider {
            background-color: rgba(34, 197, 94, 0.7);
        }

        .toggle-switch input:checked+.slider::before {
            transform: translateX(16px);
        }

        .table-header-blue th {
            background-color: #dbeafe;
            color: #1f2937;
        }

        /* Custom 5-column grid for large screens */
        @media (min-width: 992px) {
            .col-lg-2-4 {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }
    </style>
@endpush
