@extends('layouts.app')
@section('title', 'FTPP Dashboard')
@section('subtitle', 'Comprehensive overview of all FTPP (Finding, Target, Plan, Progress) activities and statuses.')

@section('content')
    <div class="px-4 mt-4">
        {{-- Audit Type Tabs (filter) --}}
        <div class="mb-3">
            <div class="flex gap-3 items-center py-2 overflow-x-auto" role="tablist" aria-label="Audit Types">
                <a href="{{ route('dashboard.ftpp') }}"
                   class="no-underline inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold whitespace-nowrap {{ empty($selectedAuditTypeId) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow' : 'bg-white/10 text-white hover:bg-white/20' }}"
                   role="tab">
                    <span class="truncate max-w-[200px]">All</span>
                    <span class="ml-2 inline-flex items-center justify-center bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ array_sum($auditTypeCounts ?? []) }}</span>
                </a>

                @foreach($auditTypes as $atype)
                    @php $count = $auditTypeCounts[$atype->id] ?? 0; @endphp
                    <a href="{{ route('dashboard.ftpp', ['audit_type' => $atype->id]) }}"
                       class="no-underline inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold whitespace-nowrap {{ ((string)$selectedAuditTypeId === (string)$atype->id) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow' : 'bg-white/10 text-white hover:bg-white/20' }}"
                       role="tab">
                        <span class="truncate max-w-[200px]">{{ Str::limit($atype->name, 28) }}</span>
                        <span class="ml-2 inline-flex items-center justify-center bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-4 mb-5">
            @php
                $needAssign = $chartData['Need Assign'] ?? 0;
                $needCheck = $chartData['Need Check'] ?? 0;
                $needApproval = $chartData['Need Approval by Auditor'] ?? 0;
                $close = $chartData['Close'] ?? 0;

                $cards = [
                    [
                        'label' => 'Total FTPP',
                        'value' => $totalFtpp,
                        'color' => 'primary',
                        'icon' => 'bi-file-earmark-post',
                    ],
                    [
                        'label' => 'Need Assign',
                        'value' => $needAssign,
                        'color' => 'danger',
                        'icon' => 'bi-exclamation-circle',
                    ],
                    [
                        'label' => 'Need Check',
                        'value' => $needCheck,
                        'color' => 'warning',
                        'icon' => 'bi-clock-history',
                    ],
                    [
                        'label' => 'Closed',
                        'value' => $close,
                        'color' => 'success',
                        'icon' => 'bi-check-circle',
                    ],
                ];
            @endphp

            @php
                // Status color mapping (soft/formal) for table badges
                $statusColors = [
                    'Need Assign' => '#F7A29A',
                    'Need Check' => '#FCE9B8',
                    'Need Approval by Auditor' => '#9CC2E5',
                    'Need Approval by Lead Auditor' => '#7EA6D1',
                    'Need Revision' => '#F7C6B5',
                    'Close' => '#B7E4C7',
                    'Checked by Dept Head' => '#BEEAEF'
                ];
            @endphp

            @foreach ($cards as $c)
                <div class="col-6 col-md-3">
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
                </div>
            @endforeach
        </div>

        {{-- ===== CHARTS SECTION ROW 1: FTPP Status + Pie Chart ===== --}}
        <div class="row g-3">
            {{-- Bar Chart - FTPP Status --}}
            <div class="col-lg-6">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 h-100 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2"
                            style="font-size: 0.95rem; color: #1f2937;">
                            <div
                                style="width: 28px; height: 28px; background-color: rgba(251,191,36,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="file-text"
                                    style="width: 16px; height: 16px; color: rgba(251,191,36,0.7);"></i>
                            </div>
                            <span>FTPP Status Summary</span>
                        </div>
                        <canvas id="ftppStatusChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            {{-- Pie Chart - Status Distribution --}}
            <div class="col-lg-6">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
                    hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 h-100 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                style="display: inline-flex; align-items: center; justify-content: center;
                                 width: 24px; height: 24px;
                                 background-color: rgba(251,191,36,0.15); border-radius: 4px;">
                                <i data-feather="pie-chart"
                                    style="width: 16px; height: 16px; color: rgba(251,191,36,0.7);"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold" style="font-size: 1rem; color: #1f2937;">
                                Status Distribution
                            </h6>
                        </div>
                        <div class="d-flex justify-content-center">
                            <canvas id="statusPie" style="max-width: 100%; max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== CHARTS SECTION ROW 2: Findings per Department (Full Width) ===== --}}
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2"
                            style="font-size: 0.95rem; color: #1f2937;">
                            <div
                                style="width: 28px; height: 28px; background-color: rgba(251,191,36,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="bar-chart-2"
                                    style="width: 16px; height: 16px; color: rgba(251,191,36,0.7);"></i>
                            </div>
                            <span>Findings per Department</span>
                        </div>
                        <canvas id="findingLineChart" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== DETAILS: Department Status Breakdown (Full Width) ===== --}}
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2"
                            style="font-size: 0.95rem; color: #1f2937;">
                            <div
                                style="width: 28px; height: 28px; background-color: rgba(99,102,241,0.12); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="layers"
                                    style="width: 16px; height: 16px; color: rgba(99,102,241,0.85);"></i>
                            </div>
                            <span>Department Status Details</span>
                        </div>

                        <ul class="nav nav-tabs mb-2" role="tablist" id="deptViewTabs">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active small" href="#" id="deptTabChart" role="tab" aria-selected="true" data-view="chart">Chart</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link small" href="#" id="deptTabTable" role="tab" aria-selected="false" data-view="table">Table</a>
                            </li>
                        </ul>

                        @php
                            // Expected format: [ 'Dept Name' => [ 'Status Name' => count, ... ], ... ]
                            $matrix = $deptStatusMatrix ?? [];
                            $statuses = [];
                            foreach ($matrix as $dept => $map) {
                                if (!is_array($map)) continue;
                                foreach ($map as $s => $v) {
                                    // exclude any status containing 'draft' (case-insensitive)
                                    if (stripos($s, 'draft') !== false) continue;
                                    if (!in_array($s, $statuses)) $statuses[] = $s;
                                }
                            }
                        @endphp

                        @if (empty($matrix))
                            <div class="alert alert-info small mb-0">
                                Department status breakdown not available. Please provide <strong>$deptStatusMatrix</strong> from the controller in the format: <em>['Dept Name' => ['Status Name' => count]]</em>.
                            </div>
                        @else
                            {{-- Chart: Department status (stacked bar) --}}
                            <div class="mb-3" id="deptStatusWrapper">
                                <canvas id="deptStatusChart" height="140"></canvas>
                            </div>

                            <div class="table-responsive mt-2" id="deptStatusTableWrapper" style="display:none;">
                                <table class="table table-sm table-hover mb-0 align-middle" style="border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">
                                    <thead class="table-header-blue">
                                        <tr>
                                            <th class="small py-2">Department</th>
                                            @foreach($statuses as $s)
                                                <th class="small py-2 text-center">{{ $s }}</th>
                                            @endforeach
                                            <th class="small py-2 text-center">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($matrix as $dept => $map)
                                            <tr>
                                                <td class="small py-2">{{ $dept }}</td>
                                                @php $rowTotal = 0; @endphp
                                                @foreach($statuses as $s)
                                                    @php $val = isset($map[$s]) ? (int) $map[$s] : 0; $rowTotal += $val; @endphp
                                                    <td class="small py-2 text-center">{{ $val }}</td>
                                                @endforeach
                                                <td class="small py-2 text-center fw-semibold">{{ $rowTotal }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== CHARTS SECTION ROW 3: Recent Findings Table (Full Width) ===== --}}
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
                    hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    style="display: inline-flex; align-items: center; justify-content: center;
                                     width: 24px; height: 24px;
                                     background-color: rgba(251,191,36,0.15); border-radius: 4px;">
                                    <i data-feather="list"
                                        style="width: 16px; height: 16px; color: rgba(251,191,36,0.7);"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0 fw-semibold" style="font-size: 1rem; color: #1f2937;">
                                        Recent Findings
                                    </h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        Showing 10 most recent findings
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive recent-table">
                            <table class="table table-sm table-hover mb-0 align-middle"
                                style="border-radius: 12px; overflow: hidden; border:1px solid #e5e7eb;">
                                <colgroup>
                                    <col class="col-reg">
                                    <col class="col-dept">
                                    <col class="col-status">
                                    <col class="col-date">
                                </colgroup>
                                <thead class="table-header-blue">
                                    <tr>
                                        <th class="small py-2 text-nowrap col-reg">Registration Number</th>
                                        <th class="small py-2 col-dept">Department</th>
                                        <th class="small py-2 text-center col-status">Status</th>
                                        <th class="small py-2 text-nowrap col-date">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentFindings as $finding)
                                        <tr class="table-row-hover">
                                            <td class="small py-2 text-nowrap col-reg">
                                                <span class="fw-semibold text-bold">{{ $finding->registration_number ?? '-' }}</span>
                                            </td>
                                            <td class="small py-2 col-dept">{{ $finding->department->name ?? '-' }}</td>
                                            <td class="small py-2 text-center col-status">
                                                @php
                                                    $statusName = $finding->status->name ?? '-';
                                                    $badgeColor = $statusColors[$statusName] ?? '#E5E7EB';
                                                    $badgeText = '#1f2937';
                                                @endphp
                                                <span class="badge status-badge px-3 py-1"
                                                      style="background-color: {{ $badgeColor }}; color: {{ $badgeText }};">
                                                    {{ $statusName }}
                                                </span>
                                            </td>
                                            <td class="small py-2 text-nowrap col-date">
                                                {{ $finding->created_at ? $finding->created_at->format('d M Y') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted small py-4 text-center">
                                                No recent findings available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll to Top Button --}}
        <button id="scrollUpBtn"
            class="fixed text-white rounded-full shadow-lg transition-all duration-300"
            title="Scroll to top" style="background: linear-gradient(135deg, #3b82f6, #0ea5e9); z-index: 50; border: none;">
            <i class="bi bi-chevron-up text-lg"></i>
        </button>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($chartData);
        const deptLabels = @json($deptLabels);
        const deptTotals = @json($deptTotals);
        const statusBreakdown = @json($statusBreakdown);
        const deptStatusMatrix = @json($deptStatusMatrix ?? []);
        const deptAllStatuses = @json($allStatuses ?? []);
    </script>

    <script>
        /* ===================== BAR CHART - FTPP STATUS ===================== */
        // Formal-but-soft corporate palette (used across all charts)
        const corporatePalette = [
            '#254E70', // deep slate blue
            '#43698B', // muted steel blue
            '#6CA6A9', // soft teal
            '#8FA3B8', // warm slate
            '#6B8E6E', // moss green
            '#E6B89C', // soft amber/peach
            '#C57D7D', // muted rose
            '#7E5A83'  // soft muted purple
        ];

        // Map important statuses to lighter soft/formal colors (used by charts)
        const statusColorMap = {
            'Need Assign': '#F7A29A',               // light coral
            'Need Check': '#FCE9B8',                // pale amber
            'Need Approval by Auditor': '#9CC2E5',  // light sky
            'Need Approval by Lead Auditor': '#7EA6D1', // muted blue
            'Need Revision': '#F7C6B5',             // light peach
            'Close': '#B7E4C7',                     // soft mint
            'Checked by Dept Head': '#BEEAEF'       // pale teal
        };

        const fallbackColors = corporatePalette.slice();

        const ftppLabels = [
            'Need Assign',
            'Need Check',
            'Checked by Dept Head',
            'Need Approval by Lead Auditor',
            'Need Revision',
            'Close'
        ];

        // Gradient helpers (from start -> end across N steps)
        function hexToRgb(hex) {
            const v = parseInt(hex.replace('#', ''), 16);
            return { r: (v >> 16) & 255, g: (v >> 8) & 255, b: v & 255 };
        }

        function rgbToHex(r, g, b) {
            return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
        }

        function interpolateHex(a, b, t) {
            const A = hexToRgb(a);
            const B = hexToRgb(b);
            const r = Math.round(A.r + (B.r - A.r) * t);
            const g = Math.round(A.g + (B.g - A.g) * t);
            const bl = Math.round(A.b + (B.b - A.b) * t);
            return rgbToHex(r, g, bl);
        }

        function gradientColors(startHex, endHex, steps) {
            if (steps <= 1) return [startHex];
            return Array.from({ length: steps }, (_, i) => interpolateHex(startHex, endHex, i / (steps - 1)));
        }

        // Build a soft red -> soft green gradient for status ordering (Need Assign -> Close)
        const gradientStart = statusColorMap['Need Assign'] || '#F7A29A';
        const gradientEnd = statusColorMap['Close'] || '#B7E4C7';
        const ftppBg = gradientColors(gradientStart, gradientEnd, ftppLabels.length);

        const ctx = document.getElementById('ftppStatusChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ftppLabels,
                datasets: [{
                    label: 'Total Findings',
                    data: [
                        chartData['Need Assign'] ?? 0,
                        chartData['Need Check'] ?? 0,
                        chartData['Checked by Dept Head'] ?? 0,
                        chartData['Need Approval by Lead Auditor'] ?? 0,
                        chartData['Need Revision'] ?? 0,
                        chartData['Close'] ?? 0,
                    ],
                    backgroundColor: ftppBg,
                    borderWidth: 0,
                    borderRadius: 6,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            padding: 6
                        },
                        grid: {
                            color: '#e9ecef'
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 0
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        /* ===================== LINE CHART - FINDINGS PER DEPARTMENT ===================== */
        const shortFindingLabels = deptLabels.map(name => {
            const words = name.split(' ');
            return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
        });
        // small helper: convert hex to rgba
        function hexToRgba(hex, alpha) {
            const bigint = parseInt(hex.replace('#', ''), 16);
            const r = (bigint >> 16) & 255;
            const g = (bigint >> 8) & 255;
            const b = bigint & 255;
            return `rgba(${r},${g},${b},${alpha})`;
        }

        new Chart(document.getElementById('findingLineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: shortFindingLabels,
                datasets: [{
                    label: 'Total Findings',
                    data: deptTotals,
                    borderColor: corporatePalette[0],
                    backgroundColor: hexToRgba(corporatePalette[0], 0.12),
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            padding: 10,
                        }
                    },
                    tooltip: {
                        enabled: true
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
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        /* ===================== PIE CHART - STATUS DISTRIBUTION ===================== */
        const statusLabels = Object.keys(statusBreakdown);
        const statusData = Object.values(statusBreakdown);

        // Build pie colors from statusColorMap where available, fallback to corporatePalette
        const statusColors = statusLabels.map((s, i) => statusColorMap[s] || corporatePalette[i % corporatePalette.length]);

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

        /* ===================== DEPARTMENT STATUS CHART (STACKED) ===================== */
        (function renderDeptStatusChart() {
            try {
                const matrix = deptStatusMatrix || {};
                const departments = Object.keys(matrix);
                if (!departments.length) return;

                // Use provided full status list if available, otherwise collect from matrix
                let statuses = Array.isArray(deptAllStatuses) && deptAllStatuses.length ? deptAllStatuses.slice() : [];
                if (!statuses.length) {
                    const statusSet = new Set();
                    departments.forEach(d => {
                        const map = matrix[d] || {};
                        Object.keys(map).forEach(s => statusSet.add(s));
                    });
                    statuses = Array.from(statusSet);
                }
                if (!statuses.length) return;

                // color generator - reuse colors from Status Distribution (pie) when possible
                const preset = [
                    '#7BA7FF', '#A8E6CF', '#FFD3B6', '#FFAAA5', '#D4A5A5', '#FFE0AC', '#6EC6E2', '#8BD3C7', '#C0E4FF'
                ];

                // build mapping from status labels used in the pie chart to colors
                const statusColorMap = {};
                (Object.keys(statusBreakdown || {})).forEach((lbl, idx) => {
                    statusColorMap[lbl] = statusColors[idx % statusColors.length];
                });

                // ensure 'Need Approval by Auditor' uses the requested color (case-insensitive match)
                Object.keys(statusColorMap).forEach(k => {
                    if (/need approval by lead auditor/i.test(k)) statusColorMap[k] = '#D4C5F9';
                });

                const colorFor = (s, i) => {
                    if (statusColorMap[s]) return statusColorMap[s];
                    if (/need assign/i.test(s)) return '#FFD3B6'; // fallback soft peach
                    if (/need approval by lead auditor/i.test(s)) return '#8BD3C7'; // fallback
                    if (/need approval by auditor/i.test(s)) return '#D4C5F9'; // set to requested color
                    return preset[i % preset.length] || `hsl(${(i*47)%360} 70% 65%)`;
                };

                const datasets = statuses.map((s, idx) => ({
                    label: s,
                    data: departments.map(d => (matrix[d] && typeof matrix[d][s] !== 'undefined') ? matrix[d][s] : 0),
                    backgroundColor: colorFor(s, idx),
                    borderWidth: 0,
                }));

                const ctx = document.getElementById('deptStatusChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: departments,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        interaction: { mode: 'nearest', axis: 'x', intersect: false },
                        scales: {
                            x: { stacked: true, ticks: { autoSkip: false } },
                            y: { stacked: true, beginAtZero: true }
                        }
                    }
                });
            } catch (e) {
                console.error('deptStatusChart error', e);
            }
        })();

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

            // Dept Status: toggle between chart and table using tabs
            const deptTabChart = document.getElementById('deptTabChart');
            const deptTabTable = document.getElementById('deptTabTable');
            const deptChartWrapper = document.getElementById('deptStatusWrapper');
            const deptTableWrapper = document.getElementById('deptStatusTableWrapper');

            function updateDeptView(selected) {
                if (selected === 'table') {
                    deptChartWrapper.style.display = 'none';
                    deptTableWrapper.style.display = '';
                } else {
                    deptChartWrapper.style.display = '';
                    deptTableWrapper.style.display = 'none';
                }
            }

                if (deptTabChart && deptTabTable) {
                deptTabChart.addEventListener('click', function (e) {
                    e.preventDefault();
                    deptTabChart.classList.add('active');
                    deptTabChart.setAttribute('aria-selected','true');
                    deptTabTable.classList.remove('active');
                    deptTabTable.setAttribute('aria-selected','false');
                    updateDeptView('chart');
                });
                deptTabTable.addEventListener('click', function (e) {
                    e.preventDefault();
                    deptTabTable.classList.add('active');
                    deptTabTable.setAttribute('aria-selected','true');
                    deptTabChart.classList.remove('active');
                    deptTabChart.setAttribute('aria-selected','false');
                    updateDeptView('table');
                });
                updateDeptView('chart');
            }

            scrollBtn.classList.add('hidden');
        });

        feather.replace();
    </script>
@endpush

@push('styles')
    <style>
        /* Audit type tabs are implemented with Tailwind utility classes; no custom CSS required */

        /* Dept view tabs: pill-style, subtle border and shadow */
        #deptViewTabs {
            padding-left: 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }
        #deptViewTabs .nav-item {
            margin-right: 0;
        }
        #deptViewTabs .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: .35rem .75rem;
            border-radius: 999px;
            border: 1px solid transparent;
            color: #374151;
            background: transparent;
            transition: all .18s ease-in-out;
            font-weight: 600;
        }
        #deptViewTabs .nav-link:hover {
            transform: translateY(-1px);
            text-decoration: none;
            color: #111827;
        }
        #deptViewTabs .nav-link.active {
            background: #EAF6FF; /* soft blue background for selected tab */
            border-color: #CFE8FF;
            box-shadow: 0 8px 20px rgba(15,23,42,0.06);
            color: #0f172a;
            position: relative;
        }
        #deptViewTabs .nav-link.active::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2D9CDB; /* accent dot */
            display: inline-block;
            margin-right: 8px;
            transform: translateY(1px);
        }
        /* Keep tabs visually inside the card and slightly elevated */
        .card-body > #deptViewTabs { margin-bottom: .6rem; display:flex; justify-content:center; }

        .table-header-blue th {
            background-color: #dbeafe;
            color: #1f2937;
        }

        .table-row-hover:hover {
            background-color: #f3f4f6;
            transition: background-color 0.2s;
        }

        /* Keep badges and cells tidy */
        .status-badge {
            display: inline-block;
            min-width: 110px;
            text-align: center;
        }

        .recent-table table {
            min-width: 720px;
        }

        /* Column sizing to keep proportions stable */
        .col-reg {
            width: 32%;
        }

        .col-dept {
            width: 32%;
        }

        .col-status {
            width: 18%;
        }

        .col-date {
            width: 18%;
        }

        .recent-table th,
        .recent-table td {
            vertical-align: middle;
        }

        #scrollUpBtn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            right: 18px;
            bottom: 18px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush
