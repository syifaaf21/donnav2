@extends('layouts.app')
@section('title', 'Document Review Dashboard')
@section('subtitle', 'Comprehensive overview of all Document Review activities and statuses.')

@section('content')
    <div class="px-4 mt-4">
        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="row g-4 mb-5">
            @php
                $needReviewTotal = collect($reviewStatusData)->sum('need_review');
                $approvedTotal = collect($reviewStatusData)->sum('approved');
                $rejectedTotal = collect($reviewStatusData)->sum('rejected');
                $uncompleteTotal = collect($reviewStatusData)->sum('uncomplete');

                $cards = [
                    [
                        'label' => 'Total Documents',
                        'value' => $totalDocuments,
                        'color' => 'primary',
                        'icon' => 'bi-collection',
                    ],
                    [
                        'label' => 'Need Review',
                        'value' => $needReviewTotal,
                        'color' => 'warning',
                        'icon' => 'bi-clock-history',
                    ],
                    [
                        'label' => 'Approved',
                        'value' => $approvedTotal,
                        'color' => 'success',
                        'icon' => 'bi-check-circle',
                    ],
                    [
                        'label' => 'Rejected',
                        'value' => $rejectedTotal,
                        'color' => 'danger',
                        'icon' => 'bi-x-circle',
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

            {{-- Review Documents per Department Chart --}}
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
                                Documents per Department
                            </h6>
                        </div>

                        <canvas id="reviewDocsChart" style="max-height: 300px;"></canvas>
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

        /* ===================== STACKED BAR CHART - DOCUMENTS PER DEPARTMENT ===================== */
        // only include departments that actually have documents (non-zero counts)
        const filteredDepartmentIds = Object.keys(departmentsReview).filter(deptId => {
            const data = reviewStatusData[Number(deptId)] || {};
            return Object.values(data).some(v => (Number(v) || 0) > 0);
        });

        const reviewLabels = filteredDepartmentIds.map(id => departmentsReview[id]);
        const shortReviewLabels = reviewLabels.map(name => {
            const words = name.split(' ');
            return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
        });

        const reviewNormalData = [];
        const reviewNeedReviewData = [];

        filteredDepartmentIds.forEach(deptId => {
            const data = reviewStatusData[Number(deptId)] || {};

            const needReview = data.need_review ?? 0;
            const normal =
                (data.approved ?? 0) +
                (data.rejected ?? 0) +
                (data.uncomplete ?? 0);

            reviewNormalData.push(normal);
            reviewNeedReviewData.push(needReview);
        });

        new Chart(document.getElementById('reviewDocsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shortReviewLabels,
                datasets: [{
                        label: 'Other Status',
                        data: reviewNormalData,
                        backgroundColor: '#22c55e',
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

        /* ===================== GROUPED BAR CHART - STATUS BREAKDOWN BY DEPARTMENT ===================== */
        const deptLabelsBreakdown = filteredDepartmentIds.map(id => departmentsReview[id]);
        const shortDeptLabels = deptLabelsBreakdown.map(name => {
            const words = name.split(' ');
            return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : name;
        });

        const needReviewArray = [];
        const approvedArray = [];
        const rejectedArray = [];
        const uncompleteArray = [];

        filteredDepartmentIds.forEach(deptId => {
            const data = reviewStatusData[Number(deptId)] || {};
            needReviewArray.push(data.need_review ?? 0);
            approvedArray.push(data.approved ?? 0);
            rejectedArray.push(data.rejected ?? 0);
            uncompleteArray.push(data.uncomplete ?? 0);
        });

        new Chart(document.getElementById('statusBreakdownChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shortDeptLabels,
                datasets: [{
                        label: 'Need Review',
                        data: needReviewArray,
                        backgroundColor: '#fbbf24',
                        borderRadius: 4,
                        barThickness: 18
                    },
                    {
                        label: 'Approved',
                        data: approvedArray,
                        backgroundColor: '#22c55e',
                        borderRadius: 4,
                        barThickness: 18
                    },
                    {
                        label: 'Rejected',
                        data: rejectedArray,
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                        barThickness: 18
                    },
                    {
                        label: 'Uncomplete',
                        data: uncompleteArray,
                        backgroundColor: '#94a3b8',
                        borderRadius: 4,
                        barThickness: 18
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 12
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
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
    </style>
@endpush
