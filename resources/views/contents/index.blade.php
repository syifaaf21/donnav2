@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container my-3">
    {{-- Summary Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 text-center h-100 py-2">
                <div class="card-body p-2">
                    <small class="text-muted">Total Documents</small>
                    <div class="fw-bold text-primary fs-5">{{ $totalDocuments }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 text-center h-100 py-2">
                <div class="card-body p-2">
                    <small class="text-muted">Need Review</small>
                    <div class="fw-bold text-warning fs-5">{{ $needReviewDocuments }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 text-center h-100 py-2">
                <div class="card-body p-2">
                    <small class="text-muted">Document Control</small>
                    <div class="fw-bold text-success fs-5">{{ $documentControls }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 text-center h-100 py-2">
                <div class="card-body p-2">
                    <small class="text-muted">Document Review</small>
                    <div class="fw-bold text-danger fs-5">{{ $documentReviews }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pie Chart & Obsolete Table Side by Side --}}
    <div class="row mb-3">
        <div class="col-md-4 d-flex align-items-stretch">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-body p-2">
                    <small class="text-center mb-2 fw-bold d-block">Active vs Obsolete Documents</small>
                    <div class="d-flex justify-content-center">
                        <canvas id="activeObsoletePie" height="180" width="400" style="max-width:400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-2">
                    <small class="fw-bold mb-2 d-block">🗑️ Obsolete Documents</small>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Title</th>
                                    <th class="small">Category</th>
                                    <th class="small">Status</th>
                                    <th class="small">Obsoleted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($obsoleteDocuments as $doc)
                                    <tr>
                                        <td class="small">{{ $doc->title }}</td>
                                        <td class="small">{{ $doc->category->name ?? '-' }}</td>
                                        <td><span class="badge bg-danger">{{ $doc->status->name ?? '-' }}</span></td>
                                        <td class="small">{{ $doc->obsoleted_at ? $doc->obsoleted_at->format('d M Y H:i') : '-' }}</td>
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

    {{-- Latest Documents --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-2">
            <small class="fw-bold mb-2 d-block">📄 Latest Documents</small>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Title</th>
                            <th class="small">Category</th>
                            <th class="small">Status</th>
                            <th class="small">Uploaded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestDocuments as $doc)
                            <tr>
                                <td class="small">{{ $doc->title }}</td>
                                <td class="small">{{ $doc->category->name ?? '-' }}</td>
                                <td>
                                    <span class="badge
                                        @if($doc->status->name == 'Active') bg-success
                                        @elseif($doc->status->name == 'Rejected') bg-danger
                                        @elseif($doc->status->name == 'Need Review') bg-warning text-dark
                                        @else bg-secondary @endif">
                                        {{ $doc->status->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="small">{{ $doc->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted small">No documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('activeObsoletePie').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Active', 'Obsolete'],
            datasets: [{
                data: [{{ $activeDocuments }}, {{ $obsoleteDocuments->count() ?? 0 }}],
                backgroundColor: ['#28a745', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush
