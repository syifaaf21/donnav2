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
                        'icon' => 'bi-files',
                        'bg' => 'bg-light',
                    ],
                    [
                        'label' => 'FTPP',
                        'value' => $ftpp,
                        'color' => 'warning text-dark',
                        'icon' => 'bi-exclamation-circle',
                        'bg' => 'bg-light',
                    ],
                    [
                        'label' => 'Document Control',
                        'value' => $documentControls,
                        'color' => 'success',
                        'icon' => 'bi-gear',
                        'bg' => 'bg-light',
                    ],
                    [
                        'label' => 'Document Review',
                        'value' => $documentReviews,
                        'color' => 'danger',
                        'icon' => 'bi-clipboard-check',
                        'bg' => 'bg-light',
                    ],
                ];
            @endphp

            @foreach ($cards as $c)
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden"
                        style="border-radius: 14px; border-left: 4px solid var(--bs-{{ $c['color'] }});">

                        <div class="card-body p-3 d-flex flex-column">

                            {{-- Label --}}
                            <small class="text-muted fw-medium" style="font-size: 0.78rem; letter-spacing: 0.3px;">
                                {{ $c['label'] }}
                            </small>

                            {{-- Value (⚡ highlight utama) --}}
                            <div class="fw-bold mt-1 mb-2 text-{{ $c['color'] }}"
                                style="font-size: 1.8rem; line-height: 1;">
                                {{ $c['value'] }}
                            </div>

                            {{-- Icon (subtle + aligned right) --}}
                            <div class="ms-auto mt-auto opacity-50" style="font-size: 1.4rem;">
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
                <div class="card shadow-sm border-0 h-100 p-2" style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2"
                            style="font-size: 0.85rem; color: #1f2937;">
                            <div
                                style="width: 24px; height: 24px; background: linear-gradient(135deg, #3b82f6, #60a5fa); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="pie-chart" class="text-white" style="width: 14px; height: 14px;"></i>
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
                <div class="card shadow-sm border-0 h-100 p-2" style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #1f2937;">
                                Document Control
                            </h6>

                            {{-- 🔥 Toggle Mini Premium --}}
                            <label class="toggle-switch" style="transform: scale(0.75);">
                                <input type="checkbox" id="toggleObsoleteSwitch">
                                <span class="slider small"></span>
                                <span class="label-text ms-2" style="font-size: 0.9rem; color: #6b7280;">
                                    View Details
                                </span>
                            </label>
                        </div>

                        <canvas id="controlDocsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>


        {{-- Obsolete Table --}}
        <div id="obsoleteTableContainer" class="card shadow-sm border-0 p-2 mt-3 d-none"
            style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-2"> <!-- p-3 → p-2 -->
                <div class="fw-semibold mb-2 d-flex align-items-center gap-2" style="color: #1f2937; font-size: 0.85rem;">
                    <!-- font-size: 0.95 → 0.85 -->

                    <div
                        style="width: 22px; height: 22px; background: linear-gradient(135deg, #ef4444, #f87171);
                border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i data-feather="file-minus" class="text-white" style="width: 13px; height: 13px;"></i>
                    </div>
                    <span>Obsolete Documents</span>
                </div>

                <div id="obsoleteTableContent" style="font-size: 0.8rem;"></div> <!-- table text lebih kecil -->

                <div class="d-flex justify-content-center gap-2 mt-2"> <!-- mt-3 → mt-2 -->
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
                <div class="card shadow-sm border-0 p-2" style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <h6 class="mb-2 fw-semibold" style="font-size: 0.85rem; color: #1f2937;">
                            Document Review
                        </h6>
                        <canvas id="reviewDocsChart" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- FTPP Status + Findings per Department (SEBELAH-SEBELAH) --}}
        <div class="row g-3 mt-2 mb-3">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 p-2" style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <h6 class="mb-2 fw-semibold" style="font-size: 0.85rem; color: #1f2937;">
                            FTPP Status Summary
                        </h6>
                        <canvas id="ftppStatusChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0 p-2 max-height-100" style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <h6 class="mb-2 fw-semibold" style="font-size: 0.85rem; color: #1f2937;">
                            <i class="bi bi-search me-2"></i>Findings per Department
                        </h6>
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

            // Render Control Documents Chart
            // Pastel color set
            const pastelColors = [
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

            // Render Control Documents Chart (Pastel)
            new Chart(document.getElementById('controlDocsChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: shortControlLabels,
                    datasets: [{
                        label: 'Control Documents',
                        data: controlData,
                        backgroundColor: pastelColors, // <--- pastel colors applied here
                        borderRadius: 6,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    const deptId = Object.keys(departments)[index];

                                    // TAMPILKAN NAMA PANJANG DEPARTMENT
                                    return departments[deptId];
                                },
                                label: function(context) {
                                    const deptId = Object.keys(departments)[context.dataIndex];
                                    const mainValue = context.raw;
                                    const extra = controlExtraData[deptId] || {};

                                    let lines = [`Total: ${mainValue}`];
                                    if (extra.obsolete !== undefined) lines.push(`Obsolete: ${extra.obsolete}`);
                                    if (extra.needReview !== undefined) lines.push(
                                        `Need Review: ${extra.needReview}`);
                                    if (extra.uncomplete !== undefined) lines.push(
                                        `Uncomplete: ${extra.uncomplete}`);
                                    if (extra.reject !== undefined) lines.push(`Rejected: ${extra.reject}`);

                                    return lines;
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
                            }
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
                            }
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

            // Render Review Documents Chart
            new Chart(document.getElementById('reviewDocsChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: shortReviewLabels,
                    datasets: [{
                        label: 'Review Documents',
                        data: reviewData,
                        backgroundColor: pastelColorsReview,
                        borderRadius: 6,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
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
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
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
                        <thead>
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
            /* GLOBAL BACKGROUND */
            body {
                /* background: #f6f8fc !important; */
            }

            /*CARD STYLE (Neumorphism Light) */
            .card {
                border-radius: 12px !important;
                background: #ffffff !important;
                border: 1px solid #e5e7eb !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
                transition: all 0.25s ease;
            }

            .card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12) !important;
            }

            .finding-card {
                padding: 4px !important;
                border-radius: 12px !important;
                /* kecil */
                box-shadow:
                    4px 4px 12px rgba(163, 177, 198, 0.25),
                    -4px -4px 12px rgba(255, 255, 255, 0.8) !important;
                /* shadow lebih tipis */
            }

            .finding-card canvas {
                height: 120px !important;
                /* height baru */
            }


            /*SUMMARY ICON BOX*/
            .summary-icon-box {
                width: 46px;
                height: 46px;
                border-radius: 14px;
                background: linear-gradient(135deg, #1e3cff, #2d4fff);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0px 6px 16px rgba(30, 60, 255, 0.3);
            }

            /*TITLES*/
            h6,
            .fw-semibold {
                color: #1e1e2d !important;
                font-weight: 600 !important;
                letter-spacing: 0.3px;
            }

            /*BLUE PANEL LIKE IMAGE*/
            .blue-panel {
                background: linear-gradient(135deg, #1e3cff, #2d4fff);
                border-radius: 24px;
                color: white;
                box-shadow: 0px 12px 22px rgba(30, 60, 255, 0.3);
                padding: 24px 28px;
            }

            /*TOGGLE SWITCH (Modern Blue)*/
            /* HIDE the actual checkbox */
            .toggle-switch input {
                display: none;
            }

            /* Slider wrapper */
            .toggle-switch {
                position: relative;
                display: inline-flex;
                align-items: center;
                cursor: pointer;
            }

            /* Background slider */
            .toggle-switch .slider {
                width: 40px;
                height: 20px;
                background: #d1d5db;
                border-radius: 20px;
                position: relative;
                transition: all .3s ease;
            }

            /* Small slider version */
            .toggle-switch .slider.small {
                width: 34px;
                height: 16px;
            }

            /* Toggle button */
            .toggle-switch .slider::before {
                content: "";
                position: absolute;
                width: 14px;
                height: 14px;
                background: white;
                border-radius: 50%;
                top: 50%;
                left: 3px;
                transform: translateY(-50%);
                transition: all .3s ease;
            }

            /* When checked */
            .toggle-switch input:checked+.slider {
                background: #4f46e5;
            }

            .toggle-switch input:checked+.slider::before {
                transform: translate(16px, -50%);
            }

            /*PIE & BAR LABEL COLORS*/
            .chart-title {
                color: #1e1e2d;
                font-weight: 600;
                font-size: 1rem;
            }

            /*OBSOLETE TABLE STYLE (Modern Clean)*/
            #obsoleteTableContainer table {
                border-radius: 12px !important;
                overflow: hidden;
                border: 1px solid #e5e7eb !important;
                background: #ffffff !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }

            #obsoleteTableContainer table thead th {
                background: #f3f6ff !important;
                color: #1e2b50 !important;
                font-weight: 700;
                border-bottom: 1px solid #e0e7ff !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 1rem !important;
            }

            #obsoleteTableContainer table tbody td {
                color: #3a3f52 !important;
                padding: 0.9rem 1rem !important;
                border-bottom: 1px solid #f0f2fa;
            }

            .table-row-hover:hover {
                background: #f9fafb !important;
            }

            /*PAGINATION BUTTONS*/
            #nextPage,
            #prevPage {
                background: white !important;
                border-radius: 8px !important;
                border: 1px solid #e5e7eb !important;
                padding: 6px 14px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                transition: all 0.2s;
            }

            #nextPage:hover,
            #prevPage:hover {
                background: #f9fafb !important;
                border-color: #d1d5db !important;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            }

            /*SCROLL TO TOP BUTTON*/
            #scrollUpBtn {
                background: linear-gradient(135deg, #1e3cff, #3f69ff) !important;
                box-shadow: 0px 10px 22px rgba(30, 60, 255, 0.35) !important;
            }
        </style>
    @endpush
