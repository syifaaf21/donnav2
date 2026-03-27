@extends('layouts.app')
@section('title', 'Document Review Dashboard')
@section('subtitle', 'Comprehensive overview of all Document Review activities and statuses.')

@section('content')
    <div class="px-4 mt-4">
        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-3 mb-4 summary-cards-row">
            @php
                $needReviewTotal = collect($reviewStatusData)->sum('need_review');
                $approvedTotal = collect($reviewStatusData)->sum('approved');
                $rejectedTotal = collect($reviewStatusData)->sum('rejected');
                $uncompleteTotal = collect($reviewStatusData)->sum('uncomplete');
                $roleName = strtolower(trim((string) (auth()->user()->roles->pluck('name')->first() ?? '')));
                $isAdminOrSuper = in_array($roleName, ['admin', 'super admin']);

                $cards = [
                    [
                        'label' => 'Total Documents',
                        'value' => $totalDocuments,
                        'color' => 'primary',
                        'icon' => 'bi-collection',
                        'url' => route('document-review.index'),
                        'admin_only' => false,
                    ],
                    [
                        'label' => 'Need Review',
                        'value' => $needReviewTotal,
                        'color' => 'warning',
                        'icon' => 'bi-clock-history',
                        'url' => route('document-review.approval'),
                        'admin_only' => true,
                    ],
                    [
                        'label' => 'Approved',
                        'value' => $approvedTotal,
                        'color' => 'success',
                        'icon' => 'bi-check-circle',
                        'url' => null,
                        'admin_only' => false,
                    ],
                    [
                        'label' => 'Rejected',
                        'value' => $rejectedTotal,
                        'color' => 'danger',
                        'icon' => 'bi-x-circle',
                        'url' => null,
                        'admin_only' => false,
                    ],
                    [
                        'label' => 'Uncomplete',
                        'value' => $uncompleteTotal,
                        'color' => 'secondary',
                        'icon' => 'bi-exclamation-circle',
                        'url' => null,
                        'admin_only' => false,
                    ],
                ];
            @endphp

            @foreach ($cards as $c)
                @php
                    $cardClickable = !empty($c['url']) && (!($c['admin_only'] ?? false) || $isAdminOrSuper);
                @endphp
                <div class="col-6 col-md-4 col-lg-auto summary-card-col">
                    @if ($cardClickable)
                        <a href="{{ $c['url'] }}" class="summary-card-link">
                    @endif

                    <div class="card summary-card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:translate-y-[-4px] {{ $cardClickable ? 'summary-card-clickable' : '' }}
                        transition-all duration-200 border-0 h-100 overflow-hidden"
                        style="border-radius: 14px; border-left: 4px solid var(--bs-{{ $c['color'] }});">

                        <div class="card-body summary-card-body">

                            <div class="summary-card-content">
                                <small class="text-muted fw-semibold summary-card-label">
                                    {{ $c['label'] }}
                                </small>
                                <div class="fw-bold text-{{ $c['color'] }} summary-card-value">
                                    {{ $c['value'] }}
                                </div>
                            </div>

                            <div class="summary-card-icon"
                                style="background: rgba(var(--bs-{{ $c['color'] }}-rgb), 0.12); color: var(--bs-{{ $c['color'] }});">
                                <i class="bi {{ $c['icon'] }}"></i>
                            </div>

                        </div>
                    </div>

                    @if ($cardClickable)
                        </a>
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
                                style="width: 28px; height: 28px; background-color: rgba(239, 68, 68, 0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="pie-chart"
                                    style="width: 16px; height: 16px; color: rgba(239, 68, 68, 0.7);"></i>
                            </div>
                            <span>Status Distribution</span>
                        </div>
                        <div class="d-flex justify-content-center">
                            <canvas id="statusPie" style="max-width: 100%; max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Review Documents per Plant Chart --}}
            <div class="col-lg-8">
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
                                   background-color: rgba(239, 68, 68, 0.15); border-radius: 4px;">
                                    <i data-feather="clipboard"
                                        style="width: 16px; height: 16px; color: rgba(239, 68, 68, 0.7);"></i>
                                </span>
                                Documents per Plant
                            </h6>
                        </div>

                        <canvas id="reviewDocsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card review-plant-card border-0 p-2">
                    <div class="card-body p-3 p-lg-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 1rem; color: #0f172a;">
                                <span class="review-plant-title-icon"><i class="bi bi-diagram-3"></i></span>
                                Document Review by Plant
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <div id="table-filter-group" class="review-filter-switch" role="group" aria-label="Status filter"></div>
                                <div id="plant-switch-group" class="d-flex flex-wrap gap-2 review-plant-switch"></div>
                            </div>
                        </div>
                        <div class="small text-muted mb-3 review-plant-subtitle">Default view focuses on <span class="fw-semibold text-danger">Need Review</span>. Switch to All Status to show <span class="fw-semibold">10 most recently updated</span> documents.</div>

                        <div class="table-responsive review-table-wrap">
                            <table class="table table-sm align-middle mb-0 review-plant-table">
                                <thead class="table-header-blue review-table-head">
                                    <tr>
                                        <th>Document Name</th>
                                        <th>Document Number</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Last Update</th>
                                    </tr>
                                </thead>
                                <tbody id="plant-doc-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Status by Department --}}
        {{-- <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
                    hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                style="display: inline-flex; align-items: center; justify-content: center;
                                 width: 24px; height: 24px;
                                 background-color: rgba(239, 68, 68, 0.15); border-radius: 4px;">
                                <i data-feather="bar-chart-2"
                                    style="width: 16px; height: 16px; color: rgba(239, 68, 68, 0.7);"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold" style="font-size: 1rem; color: #1f2937;">
                                Status Breakdown by Department
                            </h6>
                        </div>

                        <canvas id="statusBreakdownChart" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div> --}}

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
        const departmentsReview = @json($departmentsReview);
        const reviewDocuments = @json($reviewDocuments);
        const reviewStatusData = @json($reviewStatusData);
        const statusBreakdown = @json($statusBreakdown);
        const plantDocumentsTable = @json($plantDocumentsTable ?? []);
    </script>

    <script>
        /* ===================== PIE CHART - STATUS DISTRIBUTION ===================== */
        const statusLabels = Object.keys(statusBreakdown);
        const statusData = Object.values(statusBreakdown);
        const statusColors = ['#FFD3B6', '#A8E6CF', '#FFAAA5', '#FF8B94', '#A8D8EA'];

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

        /* ===================== STACKED BAR CHART - DOCUMENTS PER PLANT ===================== */
        const plantLabels = Object.keys(reviewStatusData || {});
        const shortPlantLabels = plantLabels.map(name => {
            const words = name.split(' ');
            return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
        });

        const reviewOtherStatusData = [];
        const reviewNeedReviewData = [];

        plantLabels.forEach(plant => {
            const data = reviewStatusData[plant] || {};

            const needReview = data.need_review ?? 0;
            const otherStatus = data.other_status ?? ((data.approved ?? 0) + (data.rejected ?? 0) + (data.uncomplete ?? 0));

            reviewOtherStatusData.push(otherStatus);
            reviewNeedReviewData.push(needReview);
        });

        new Chart(document.getElementById('reviewDocsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shortPlantLabels,
                datasets: [{
                        label: 'Other Status',
                        data: reviewOtherStatusData,
                        backgroundColor: '#86efac',
                        borderRadius: {
                            bottomLeft: 6,
                            bottomRight: 6
                        },
                        barThickness: 22
                    },
                    {
                        label: 'Need Review',
                        data: reviewNeedReviewData,
                        backgroundColor: '#ef4444',
                        borderRadius: {
                            topLeft: 6,
                            topRight: 6
                        },
                        barThickness: 22
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: (tooltipItems) => {
                                const idx = tooltipItems?.[0]?.dataIndex ?? 0;
                                return plantLabels[idx] || '';
                            },
                            label: (context) => {
                                const idx = context.dataIndex;
                                const fullPlant = plantLabels[idx];
                                const data = reviewStatusData[fullPlant] || {};

                                if (context.dataset.label === 'Other Status') {
                                    return [
                                        `Other Status: ${context.parsed.y}`,
                                        `Approved: ${data.approved ?? 0}`,
                                        `Rejected: ${data.rejected ?? 0}`,
                                        `Uncomplete: ${data.uncomplete ?? 0}`,
                                    ];
                                }

                                return `Need Review: ${data.need_review ?? context.parsed.y ?? 0}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#e9ecef'
                        }
                    }
                }
            }
        });

        /* ===================== SWITCH + TABLE - DOCUMENTS BY PLANT ===================== */
        const plantSwitchGroup = document.getElementById('plant-switch-group');
        const tableFilterGroup = document.getElementById('table-filter-group');
        const plantDocTableBody = document.getElementById('plant-doc-table-body');
        let activePlant = '';
        let activeStatusFilter = 'need_review';

        function statusBadgeClass(status) {
            const normalized = String(status || '').toLowerCase();
            if (normalized === 'need review') return 'badge bg-danger-subtle text-danger';
            if (normalized === 'approved') return 'badge bg-success-subtle text-success';
            if (normalized === 'rejected') return 'badge bg-danger';
            if (normalized === 'uncomplete') return 'badge bg-secondary-subtle text-secondary';
            return 'badge bg-light text-dark';
        }

        function normalizeStatus(status) {
            return String(status || '').trim().toLowerCase();
        }

        function renderPlantTable(plant) {
            const rawRows = plantDocumentsTable[plant] || [];
            let rows = [];

            if (activeStatusFilter === 'all') {
                rows = [...rawRows]
                    .sort((a, b) => String(b.updated_at || '').localeCompare(String(a.updated_at || '')))
                    .slice(0, 10);
            } else {
                rows = rawRows
                    .filter((row) => normalizeStatus(row.status) === 'need review')
                    .sort((a, b) => String(b.updated_at || '').localeCompare(String(a.updated_at || '')));
            }

            if (!rows.length) {
                plantDocTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No documents found</td></tr>';
                return;
            }

            plantDocTableBody.innerHTML = rows.map((row) => `
                <tr class="${normalizeStatus(row.status) === 'need review' ? 'table-danger' : ''}">
                    <td>${row.document_name ?? '-'}</td>
                    <td>${row.document_number ?? '-'}</td>
                    <td>${row.department ?? '-'}</td>
                    <td><span class="${statusBadgeClass(row.status)}">${row.status ?? '-'}</span></td>
                    <td>${row.updated_at ?? '-'}</td>
                </tr>
            `).join('');
        }

        function renderTableFilters() {
            if (!tableFilterGroup) return;
            const filters = [{
                    key: 'need_review',
                    label: 'Need Review Only'
                },
                {
                    key: 'all',
                    label: 'All Status'
                }
            ];

            tableFilterGroup.innerHTML = '';
            filters.forEach((filter) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'review-filter-btn ' + (activeStatusFilter === filter.key ? 'is-active' : '');
                btn.textContent = filter.label;
                btn.addEventListener('click', () => {
                    activeStatusFilter = filter.key;
                    renderTableFilters();
                    renderPlantTable(activePlant);
                });
                tableFilterGroup.appendChild(btn);
            });
        }

        const availablePlants = Object.keys(plantDocumentsTable || {});
        availablePlants.forEach((plant, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'review-plant-btn ' + (index === 0 ? 'is-active' : '');
            const needReviewCount = (plantDocumentsTable[plant] || []).filter((r) => normalizeStatus(r.status) === 'need review').length;
            btn.innerHTML = `${plant} <span class="review-plant-badge ${needReviewCount > 0 ? 'is-alert' : ''}">${needReviewCount}</span>`;
            btn.dataset.plant = plant;

            btn.addEventListener('click', () => {
                plantSwitchGroup.querySelectorAll('button').forEach((b) => {
                    b.classList.remove('is-active');
                });
                btn.classList.add('is-active');
                activePlant = plant;
                renderPlantTable(activePlant);
            });

            plantSwitchGroup.appendChild(btn);
        });

        renderTableFilters();

        if (availablePlants.length > 0) {
            activePlant = availablePlants[0];
            renderPlantTable(activePlant);
        } else {
            renderPlantTable('');
        }

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
        .table-header-blue th {
            background-color: #dbeafe;
            color: #1f2937;
        }

        .summary-card-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .summary-card-clickable {
            cursor: pointer;
        }

        .summary-card-body {
            padding: 0.95rem 0.95rem;
            min-height: 104px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .summary-card-label {
            font-size: 0.78rem;
            letter-spacing: 0.2px;
            display: block;
            line-height: 1.2;
            margin-bottom: 0.35rem;
        }

        .summary-card-value {
            font-size: 1.8rem;
            line-height: 1;
        }

        .summary-card-icon {
            font-size: 1.35rem;
            width: 44px;
            height: 44px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        @media (min-width: 992px) {
            .summary-card-col {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }

        @media (max-width: 576px) {
            .summary-card-body {
                min-height: 94px;
                padding: 0.8rem 0.8rem;
            }

            .summary-card-value {
                font-size: 1.5rem;
            }

            .summary-card-icon {
                width: 38px;
                height: 38px;
                font-size: 1.1rem;
            }
        }

        .review-plant-card {
            border-radius: 14px;
            background: linear-gradient(160deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.1);
        }

        .review-plant-title-icon {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #0f766e;
            background: linear-gradient(135deg, #ccfbf1, #99f6e4);
            font-size: 0.8rem;
        }

        .review-plant-subtitle {
            color: #64748b !important;
        }

        .review-filter-switch {
            display: inline-flex;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #f8fafc;
            padding: 2px;
            gap: 2px;
        }

        .review-filter-btn {
            border: 0;
            border-radius: 999px;
            padding: 0.3rem 0.8rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            background: transparent;
            transition: all 0.2s ease;
        }

        .review-filter-btn.is-active {
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 6px 14px rgba(220, 38, 38, 0.35);
        }

        .review-plant-switch {
            align-items: center;
        }

        .review-plant-btn {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            border-radius: 12px;
            padding: 0.35rem 0.7rem;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .review-plant-btn:hover {
            border-color: #60a5fa;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        .review-plant-btn.is-active {
            border-color: #2563eb;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
        }

        .review-plant-badge {
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            background: #e2e8f0;
        }

        .review-plant-badge.is-alert {
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .review-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .review-plant-table th,
        .review-plant-table td {
            padding: 0.72rem 0.85rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .review-table-head th {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #eff6ff !important;
            font-size: 0.78rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .review-plant-table tbody tr:hover {
            background: #f8fafc;
        }
    </style>
@endpush
