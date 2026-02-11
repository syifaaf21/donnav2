@extends('layouts.app')
@section('title', 'Dashboard Overview')
@section('subtitle', 'A quick summary of the document statuses and activities.')

@section('content')
    <div class="px-4 mt-4">
        {{-- <div class="mb-4 text-white">
            <h3 class="fw-bold ">Dashboard Overview</h3>
            <p style="font-size: 0.9rem;">
                A quick summary of the document statuses and activities.
            </p>
        </div> --}}
        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-4 mb-5">
            @php
                $cards = [
                    [
                        'label' => 'Total Documents',
                        'value' => $totalDocuments + $totalFtpp,
                        'color' => 'primary',
                        'icon' => 'bi-collection',
                    ],
                    [
                        'label' => 'FTPP',
                        'value' => $ftpp,
                        'color' => 'warning',
                        'icon' => 'bi-file-earmark-post',
                    ],
                    [
                        'label' => 'Document Control',
                        'value' => $documentControls,
                        'color' => 'success',
                        'icon' => 'bi-calendar-range',
                    ],
                    [
                        'label' => 'Document Review',
                        'value' => $documentReviews,
                        'color' => 'danger',
                        'icon' => 'bi-clipboard-check',
                    ],
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
        {{-- ===== CHARTS SECTION ===== --}}
        <div class="row g-3">
            {{-- Pie Chart --}}
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
                            <span>Active vs Obsolete</span>
                        </div>
                        <div class="d-flex justify-content-center">
                            <canvas id="activeObsoletePie" style="max-width: 100%; max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Control Documents Chart --}}
            <div class="col-lg-8">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 h-100 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2"
                                style="font-size: 1rem; color: #1f2937;">
                                {{-- Icon kalender hijau lembut --}}
                                <span
                                    style="display: inline-flex; align-items: center; justify-content: center;
                                   width: 20px; height: 20px;
                                   background-color: rgba(34,197,94,0.15); border-radius: 4px;">
                                    <i data-feather="calendar"
                                        style="width: 16px; height: 16px; color: rgba(34,197,94,0.7);"></i>
                                </span>
                                Document Control
                            </h6>

                            {{-- Toggle --}}
                            <div class="d-flex align-items-center gap-2">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggleObsoleteSwitch">
                                    <span class="slider"></span>
                                </label>
                                <span class="label-text" style="font-size: 0.75rem; color: #6b7280;">
                                    View Details
                                </span>
                            </div>

                        </div>

                        <canvas id="controlDocsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Obsolete Table --}}
        <div id="obsoleteTableContainer"
            class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
       hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2 mt-3 d-none"
            style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-2">

                {{-- Label --}}
                <div class="fw-semibold mb-2 d-flex align-items-center gap-2" style="color: #1f2937; font-size: 1rem;">
                    <div
                        style="width: 24px; height: 24px;
                        background: rgba(34,197,94,0.15); border-radius: 6px;
                        display: flex; align-items: center; justify-content: center;">
                        <i data-feather="file-minus" style="width: 16px; height: 16px; color: rgba(34,197,94,0.7);"></i>
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


        {{-- Review Documents Chart (FULL WIDTH) --}}
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
                    hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">

                        {{-- Label dengan icon lembut --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                style="display: inline-flex; align-items: center; justify-content: center;
                                 width: 24px; height: 24px;
                                 background-color: rgba(239, 68, 68, 0.15); border-radius: 4px;">
                                <i data-feather="clipboard" class="text-red-500"
                                    style="width: 16px; height: 16px; color: #ef4444;"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold" style="font-size: 1rem; color: #1f2937;">
                                Document Review
                            </h6>
                        </div>

                        {{-- Chart --}}
                        <canvas id="reviewDocsChart" height="90"></canvas>

                    </div>
                </div>
            </div>
        </div>

        {{-- FTPP Status + Findings per Department --}}
        <div class="row g-3 mt-2 mb-3">
            <div class="col-lg-6">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        {{-- Label dengan icon --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                style="display: inline-flex; align-items: center; justify-content: center;
                                 width: 24px; height: 24px;
                                 background-color: rgba(251,191,36,0.15); border-radius: 4px;">
                                <i data-feather="file-text"
                                    style="width: 16px; height: 16px; color: rgba(251,191,36,0.7);"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold" style="font-size: 1rem; color: #1f2937;">
                                FTPP Status Summary
                            </h6>
                        </div>

                        <canvas id="ftppStatusChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card bg-white shadow-2xl shadow-black/40 hover:shadow-xl hover:shadow-black/60
            hover:transform hover:translate-y-[-4px] transition-transform duration-200 border-0 p-2 max-height-100"
                    style="border-radius: 10px;">
                    <div class="card-body p-2">
                        {{-- Label dengan icon --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span
                                style="display: inline-flex; align-items: center; justify-content: center;
                                 width: 24px; height: 24px;
                                 background-color: rgba(251,191,36,0.15); border-radius: 4px;">
                                <i data-feather="bar-chart-2"
                                    style="width: 16px; height: 16px; color: rgba(251,191,36,0.7);"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold" style="font-size: 1rem; color: #1f2937;">
                                Findings per Department
                            </h6>
                        </div>

                        <canvas id="findingLineChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <button id="scrollUpBtn"
            class="fixed bottom-5 right-5 w-12 h-12 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300"
            title="Scroll to top" style="background: linear-gradient(135deg, #3b82f6, #0ea5e9); z-index: 50;">
            <i class="bi bi-chevron-up text-lg"></i>
        </button>
    @endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const obsoleteDocs = @json($obsoleteDocuments);
        </script>

        <script>
            /* ===================== PIE CHART ===================== */
            new Chart(document.getElementById('activeObsoletePie'), {
                type: 'pie',
                data: {
                    labels: ['Active', 'Obsolete'],
                    datasets: [{
                        data: [
                            {{ $activeDocuments ?? 0 }},
                            {{ $obsoleteDocuments->count() ?? 0 }}
                        ],
                        backgroundColor: ['#A8E6CF', '#FF8B94'], // soft pastel colors
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


            /* ===================== BAR CHART ===================== */
            const ctx = document.getElementById('ftppStatusChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [
                        'Need Assign',
                        'Need Check',
                        'Need Approve by Auditor',
                        'Need Approve by Lead Auditor',
                        'Need Revision',
                        'Close'
                    ],
                    datasets: [{
                        label: 'Total Findings',
                        data: [
                            {{ $chartData['Need Assign'] ?? 0 }},
                            {{ $chartData['Need Check'] ?? 0 }},
                            {{ $chartData['Checked by Dept Head'] ?? 0 }},
                            {{ $chartData['Need Approve by Lead Auditor'] ?? 0 }},
                            {{ $chartData['Need Revision'] ?? 0 }},
                            {{ $chartData['Close'] ?? 0 }},
                        ],

                        backgroundColor: [
                            '#dc3545', // Need Assign
                            '#fd7e14', // Need Check
                            '#0d6efd', // Checked
                            '#198754', // Approved
                            '#6c757d', // Need Revision
                            '#6c757d' // Close
                        ],
                        borderWidth: 0,
                        borderRadius: 6,
                        barThickness: 28 // biar lebih rapih
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

            const departments = @json($departments); // Semua department
            const departmentsReview = @json($departmentsReview); // Hanya Body, Unit, Electric
            const controlDocuments = @json($controlDocuments);
            const reviewStatusData = @json($reviewStatusData);
            const reviewDocuments = @json($reviewDocuments);
            const controlExtraData = @json($controlExtraData);

            // Labels
            const controlLabels = Object.values(departments);
            const reviewLabels = Object.values(departmentsReview);

            // Shorten labels (2 kata max)
            const shortControlLabels = controlLabels.map(name => {
                const words = name.split(' ');
                return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
            });
            const shortReviewLabels = reviewLabels.map(name => {
                const words = name.split(' ');
                return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
            });

            // Data array sesuai urutan label
            const controlData = Object.keys(departments).map(id => controlDocuments[id] ?? 0);
            const reviewData = Object.keys(departmentsReview).map(id => reviewDocuments[id] ?? 0);

            // Render Control Documents Chart as per-status stacked bars
            const orderedStatusKeys = ['active','need_review','rejected','uncomplete','obsolete'];

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

            const statusDatasets = orderedStatusKeys.map(k => ({
                label: statusReadableMap[k] || k,
                data: deptIds.map(id => Number(lookupStatusCount(controlExtraData[id] || {}, k) || 0)),
                backgroundColor: statusColorMap[k] || '#CBD5E1',
                borderRadius: 6,
                barThickness: 18,
                stack: 'statuses'
            }));

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
                                    size: 12
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


            // Pastel Palette 2 (24 colors)
            const pastelColorsReview = [
                '#7BA7FF', // soft blue
                '#6EC6E2', // muted sky blue
                '#8BD3C7', // soft teal
                '#A3A0FB', // soft indigo
                '#F7C97F', // pastel warm yellow (low saturation)
                '#FFB38A', // soft peach
                '#B7B5F1', // muted lavender
                '#9ED2B6', // desaturated mint green
                '#E5C7FF', // soft purple
                '#C0E4FF', // ice blue
                '#B4C4FF', // steel light blue
                '#FFD8A8', // soft orange
                '#9FC9E9', // slightly greyish blue
                '#C5E8D1' // pale green-blue
            ];

            const reviewNormalData = [];
            const reviewNeedReviewData = [];

            Object.keys(departmentsReview).forEach(deptId => {
                const data = reviewStatusData[Number(deptId)] || {};

                const needReview = data.need_review ?? 0;

                const normal =
                    (data.approved ?? 0) +
                    (data.rejected ?? 0) +
                    (data.uncomplete ?? 0);

                reviewNormalData.push(normal);
                reviewNeedReviewData.push(needReview);
            });

            // Render Review Documents Chart
            new Chart(document.getElementById('reviewDocsChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: shortReviewLabels,
                    datasets: [{
                            label: 'Other Status',
                            data: reviewNormalData,
                            backgroundColor: '#22c55e', // hijau
                            borderRadius: {
                                bottomLeft: 6,
                                bottomRight: 6
                            },
                            barThickness: 22
                        },
                        {
                            label: 'Need Review',
                            data: reviewNeedReviewData,
                            backgroundColor: '#ef4444', // 🔴 merah
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


            /* ===================== FINDING PER DEPARTMENT — LINE CHART ===================== */
            const findingLabels = @json($deptLabels);
            const findingData = @json($deptTotals);

            const shortFindingLabels = findingLabels.map(name => {
                const words = name.split(' ');
                return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
            });

            new Chart(document.getElementById('findingLineChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: shortFindingLabels,
                    datasets: [{
                        label: 'Total Findings',
                        data: findingData,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.2)',
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
            document.addEventListener('DOMContentLoaded', function() {
                const scrollBtn = document.getElementById('scrollUpBtn');

                // Tampilkan tombol saat scroll lebih dari 100px
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 100) {
                        scrollBtn.classList.remove('hidden');
                    } else {
                        scrollBtn.classList.add('hidden');
                    }
                });

                // Scroll ke atas saat tombol diklik
                scrollBtn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });

                // Sembunyikan default
                scrollBtn.classList.add('hidden');
            });
            /* ===================== OBSOLETE TABLE TOGGLE + PAGINATION ===================== */
            const obsoleteData = obsoleteDocs; // dari controller
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
                                <th class="small fw-semibold py-3">Document</th>
                                <th class="small fw-semibold py-3">Department</th>
                                <th class="small fw-semibold py-3">Obsoleted At</th>
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
            feather.replace();
        </script>
    @endpush
    @push('styles')
        <style>
            /* Small Toggle Switch */
            .toggle-switch {
                position: relative;
                display: inline-block;
                width: 32px;
                /* lebih kecil */
                height: 16px;
                /* lebih kecil */
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
                /* warna off */
                transition: 0.4s;
                border-radius: 16px;
            }

            .toggle-switch .slider::before {
                position: absolute;
                content: "";
                height: 12px;
                /* lebih kecil */
                width: 12px;
                /* lebih kecil */
                left: 2px;
                bottom: 2px;
                background-color: white;
                transition: 0.4s;
                border-radius: 50%;
            }

            .toggle-switch input:checked+.slider {
                background-color: rgba(34, 197, 94, 0.7);
                /* hijau lembut */
            }

            .toggle-switch input:checked+.slider::before {
                transform: translateX(16px);
                /* geser sesuai lebar toggle */
            }

            .table-header-blue th {
                background-color: #dbeafe;
                color: #1f2937;
            }
        </style>
    @endpush
