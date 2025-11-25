@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="my-3">

        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-3 mb-3">
            @php
                $cards = [
                    [
                        'label' => 'Total Documents',
                        'value' => $totalDocuments + $totalFtpp,
                        'color' => 'primary',
                        'icon' => 'bi-files',
                    ],
                    [
                        'label' => 'FTPP',
                        'value' => $ftpp,
                        'color' => 'warning text-dark',
                        'icon' => 'bi-exclamation-circle',
                    ],
                    [
                        'label' => 'Document Control',
                        'value' => $documentControls,
                        'color' => 'success',
                        'icon' => 'bi-gear',
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
                    <div class="card shadow border-0 text-center h-100 py-3">
                        <div class="card-body p-2">
                            <div class="text-{{ $c['color'] }} fs-4 mb-1">
                                <i class="bi {{ $c['icon'] }}"></i>
                            </div>
                            <small class="text-muted">{{ $c['label'] }}</small>
                            <div class="fw-bold fs-5 mt-1 text-{{ $c['color'] }}">{{ $c['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== PIE CHART + OBSOLETE TABLE ===== --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4 d-flex">
                <div class="card shadow border-0 w-100">
                    <div class="card-body p-3">
                        <div class="fw-bold mb-2 d-flex align-items-center gap-1">
                            <i data-feather="pie-chart" class="me-1 w-5 h-5"></i>
                            <span class="text-gray-800">Active vs Obsolete Documents</span>
                        </div>

                        <div class="d-flex justify-content-center">
                            <canvas id="activeObsoletePie" style="max-width: 280px; max-height: 280px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 d-flex">
                <div class="card shadow border-0 w-100">
                    <div class="card-body p-3">
                        <div class="fw-bold mb-2 d-flex align-items-center gap-1">
                            <i data-feather="trash-2" class="me-1 w-5 h-5"></i>
                            <span class="text-gray-800">Obsolete Documents</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Document Name</th>
                                        <th class="small">Status</th>
                                        <th class="small">Obsoleted At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($obsoleteDocuments as $doc)
                                        <tr>
                                            <td class="small">{{ $doc->document->name ?? '-' }}</td>
                                            <td><span class="badge bg-danger">{{ $doc->status->name ?? '-' }}</span></td>
                                            <td class="small">
                                                {{ $doc->obsolete_date ? \Carbon\Carbon::parse($doc->obsolete_date)->format('d M Y') : '-' }}
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted small">No obsolete documents found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Documents ===== --}}

        {{-- Control Documents Chart --}}
        <div class="card shadow border-0 p-3 mb-4">
            <h5 class="fw-bold mb-2">Control Documents</h5>
            <canvas id="controlDocsChart" height="300"></canvas>

        </div>

        {{-- Review Documents Chart --}}
        <div class="card shadow border-0 p-3 mb-4">
            <h5 class="fw-bold mb-2">Review Documents</h5>
            <canvas id="reviewDocsChart" height="300"></canvas>
        </div>

        {{-- ===== FTPP ===== --}}
        <div class="card shadow border-0 p-3 mb-4">
            <h5 class="fw-bold mb-2">FTPP Status Summary</h5>
            <canvas id="ftppStatusChart" height="250"></canvas>
        </div>

    </div>
    <button id="scrollUpBtn"
        class="fixed bottom-5 right-5 w-12 h-12 bg-sky-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-sky-600 transition-opacity"
        title="Scroll to top">
        <i class="bi bi-chevron-up text-lg"></i>
    </button>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    backgroundColor: ['#28a745', '#dc3545'],
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
                    'Open',
                    'Submitted',
                    'Checked by Dept Head',
                    'Approved by Auditor',
                    'Need Revision',
                    'Close'
                ],
                datasets: [{
                    label: 'Total Findings',
                    data: [
                        {{ $chartData['Open'] ?? 0 }},
                        {{ $chartData['Submitted'] ?? 0 }},
                        {{ $chartData['Checked by Dept Head'] ?? 0 }},
                        {{ $chartData['Approved by Auditor'] ?? 0 }},
                        {{ $chartData['Need Revision'] ?? 0 }},
                        {{ $chartData['Close'] ?? 0 }},
                    ],

                    backgroundColor: [
                        '#dc3545', // Open
                        '#fd7e14', // Submitted
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
        new Chart(document.getElementById('controlDocsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shortControlLabels,
                datasets: [{
                    label: 'Control Documents',
                    data: controlData,
                    backgroundColor: '#0d6efd',
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



        // Render Review Documents Chart
        new Chart(document.getElementById('reviewDocsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shortReviewLabels,
                datasets: [{
                    label: 'Review Documents',
                    data: reviewData,
                    backgroundColor: '#198754',
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
    </script>
@endpush
